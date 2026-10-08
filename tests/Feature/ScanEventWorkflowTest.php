<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Division;
use App\Models\Garment;
use App\Models\GarmentCategory;
use App\Models\ScanEvent;
use App\Models\Tag;
use App\Models\TagGarmentBinding;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScanEventWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private Device $device;
    private string $rawApiKey = 'secret_test_key_12345';
    private string $macAddress = 'AA:BB:CC:DD:EE:FF';
    private Garment $garment;
    private Tag $tag;
    private Division $division;
    private GarmentCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create([
            'division_name' => 'Divisi Steril ' . Str::random(5),
            'color_name'    => 'Hijau Medis',
            'color_hex'     => '#10B981',
            'is_active'     => true,
        ]);

        $this->category = GarmentCategory::create([
            'category_name'     => 'Coverall Cleanroom ' . Str::random(5),
            'default_max_cycle' => 50,
        ]);

        $this->device = Device::create([
            'device_name'       => 'RFID Reader Cleanroom 1',
            'location'          => 'Pintu Masuk Ruang Antara',
            'division_id'       => $this->division->division_id,
            'mac_address'       => 'AA:BB:CC:' . strtoupper(Str::random(2)) . ':' . strtoupper(Str::random(2)) . ':' . strtoupper(Str::random(2)),
            'api_key_hash'      => Hash::make($this->rawApiKey),
            'status'            => 'online',
            'last_heartbeat_at' => now(),
            'installed_at'      => now(),
        ]);

        $this->tag = Tag::create([
            'tag_uid'             => 'E280' . strtoupper(Str::random(12)),
            'division_id'         => $this->division->division_id,
            'rated_max_cycles'    => 200,
            'total_cycles_used'   => 0,
            'status'              => 'active',
            'date_first_deployed' => now(),
        ]);

        $this->garment = Garment::create([
            'garment_code'        => 'GAR-' . strtoupper(Str::random(6)),
            'category_id'         => $this->category->category_id,
            'division_id'         => $this->division->division_id,
            'size'                => 'L',
            'current_tag_id'      => $this->tag->tag_id,
            'current_cycle_count' => 0,
            'max_cycle_limit'     => 50,
            'status'              => 'active',
            'current_stage'       => 'siap_digunakan',
            'stage_changed_at'    => now(),
        ]);

        TagGarmentBinding::create([
            'tag_id'     => $this->tag->tag_id,
            'garment_id' => $this->garment->garment_id,
            'bound_at'   => now(),
            'is_current' => true,
        ]);
    }

    private function postScan(string $tagUid, array $headers = [])
    {
        $defaultHeaders = [
            'X-Device-Mac' => $this->device->mac_address,
            'X-Api-Key'    => $this->rawApiKey,
            'Accept'       => 'application/json',
        ];

        return $this->postJson('/api/scan-events', [
            'tag_uid' => $tagUid,
        ], array_merge($defaultHeaders, $headers));
    }

    public function test_unauthenticated_device_scan_is_rejected_with_401()
    {
        $response = $this->postJson('/api/scan-events', [
            'tag_uid' => $this->tag->tag_uid,
        ]);

        $response->assertStatus(401);
    }

    public function test_stage_flow_siap_digunakan_to_sedang_dipakai()
    {
        $response = $this->postScan($this->tag->tag_uid);

        $response->assertStatus(201)
            ->assertJsonPath('data.event_type', 'usage_checkpoint')
            ->assertJsonPath('data.new_stage', 'sedang_dipakai')
            ->assertJsonPath('data.cycle_count_after', 0);

        $this->assertDatabaseHas('garments', [
            'garment_id'          => $this->garment->garment_id,
            'current_stage'       => 'sedang_dipakai',
            'current_cycle_count' => 0,
        ]);
    }

    public function test_anti_stray_read_rejects_premature_scan_on_sedang_dipakai()
    {
        // Ubah stage ke sedang_dipakai baru saja (0 menit lalu)
        $this->garment->update([
            'current_stage'    => 'sedang_dipakai',
            'stage_changed_at' => now(),
        ]);

        // Scan langsung mencoba pindah ke pre_autoclave (wajib minimal 540 menit / 9 jam)
        $response = $this->postScan($this->tag->tag_uid);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'current_stage', 'required_minutes']);
    }

    public function test_stage_flow_sedang_dipakai_to_pre_autoclave_after_dwell_time()
    {
        // Simulasi garment sudah dipakai 10 jam (600 menit > 540 menit)
        $this->garment->update([
            'current_stage'    => 'sedang_dipakai',
            'stage_changed_at' => now()->subHours(10),
        ]);

        $response = $this->postScan($this->tag->tag_uid);

        $response->assertStatus(201)
            ->assertJsonPath('data.event_type', 'pre_autoclave')
            ->assertJsonPath('data.new_stage', 'pre_autoclave');

        $this->assertDatabaseHas('garments', [
            'garment_id'    => $this->garment->garment_id,
            'current_stage' => 'pre_autoclave',
        ]);
    }

    public function test_stage_flow_pre_autoclave_to_siap_digunakan_completes_cycle()
    {
        // Simulasi garment di pre_autoclave setelah sterilisasi 45 menit (> 30 menit)
        $this->garment->update([
            'current_stage'       => 'pre_autoclave',
            'stage_changed_at'    => now()->subMinutes(45),
            'current_cycle_count' => 5,
        ]);

        $response = $this->postScan($this->tag->tag_uid);

        $response->assertStatus(201)
            ->assertJsonPath('data.event_type', 'post_autoclave')
            ->assertJsonPath('data.new_stage', 'siap_digunakan')
            ->assertJsonPath('data.cycle_count_after', 6);

        $this->assertDatabaseHas('garments', [
            'garment_id'          => $this->garment->garment_id,
            'current_stage'       => 'siap_digunakan',
            'current_cycle_count' => 6,
        ]);
    }

    public function test_cycle_warning_alert_triggered_at_ninety_percent()
    {
        // Garment limit 50, saat ini cycle 44 -> scan selesai jadi 45 (90%)
        $this->garment->update([
            'current_stage'       => 'pre_autoclave',
            'stage_changed_at'    => now()->subMinutes(45),
            'current_cycle_count' => 44,
            'max_cycle_limit'     => 50,
        ]);

        $response = $this->postScan($this->tag->tag_uid);

        $response->assertStatus(201)
            ->assertJsonPath('data.threshold_status', 'warning');

        $this->assertDatabaseHas('alerts', [
            'garment_id' => $this->garment->garment_id,
            'alert_type' => 'cycle_warning',
            'severity'   => 'warning',
            'status'     => 'open',
        ]);
    }

    public function test_cycle_exceeded_alert_triggered_at_limit()
    {
        // Garment limit 50, saat ini cycle 49 -> scan selesai jadi 50 (100%)
        $this->garment->update([
            'current_stage'       => 'pre_autoclave',
            'stage_changed_at'    => now()->subMinutes(45),
            'current_cycle_count' => 49,
            'max_cycle_limit'     => 50,
        ]);

        $response = $this->postScan($this->tag->tag_uid);

        $response->assertStatus(201)
            ->assertJsonPath('data.threshold_status', 'critical');

        $this->assertDatabaseHas('alerts', [
            'garment_id' => $this->garment->garment_id,
            'alert_type' => 'cycle_exceeded',
            'severity'   => 'critical',
            'status'     => 'open',
        ]);
    }

    public function test_tag_registration_listening_flow()
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first() ?? User::create([
            'full_name'     => 'Super Admin',
            'email'         => 'superadmin_' . Str::random(5) . '@alfarfid.local',
            'password_hash' => Hash::make('password123'),
            'role'          => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        // 1. Start listening
        $startRes = $this->postJson('/admin/tag-registration/start');
        $startRes->assertStatus(200);

        // 2. Scan tag baru
        $newTagUid = 'E280999988887777';
        $scanRes = $this->postScan($newTagUid);
        $scanRes->assertStatus(200)
            ->assertJsonPath('mode', 'registration')
            ->assertJsonPath('tag_uid', $newTagUid);

        // 3. Poll scan
        $pollRes = $this->getJson('/admin/tag-registration/poll');
        $pollRes->assertStatus(200)
            ->assertJsonPath('tag_uid', $newTagUid);

        // 4. Stop listening
        $stopRes = $this->postJson('/admin/tag-registration/stop');
        $stopRes->assertStatus(200);
    }
}
