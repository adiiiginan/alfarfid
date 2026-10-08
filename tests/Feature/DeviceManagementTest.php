<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Division;
use App\Models\ScanEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeviceManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', User::ROLE_ADMIN)->first() ?? User::create([
            'full_name'     => 'Admin Device Test',
            'email'         => 'admin_device_' . Str::random(5) . '@alfarfid.local',
            'password_hash' => Hash::make('password123'),
            'role'          => User::ROLE_ADMIN,
        ]);

        $this->division = Division::first() ?? Division::create([
            'division_name' => 'Divisi Test ' . Str::random(4),
            'color_name'    => 'Biru',
            'color_hex'     => '#2563EB',
            'is_active'     => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_devices_index()
    {
        $response = $this->get('/admin/devices');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_devices_index()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/devices');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Perangkat');
    }

    public function test_admin_can_create_new_device_with_auto_generated_api_key()
    {
        $this->actingAs($this->admin);

        $mac = 'AA:BB:CC:11:22:' . strtoupper(Str::random(2));

        $response = $this->post('/admin/devices', [
            'device_name'  => 'Reader Autoclave Test BDG',
            'location'     => 'Pabrik Bandung - Test Area',
            'division_id'  => $this->division->division_id,
            'mac_address'  => $mac,
            'status'       => 'online',
            'installed_at' => now()->toDateString(),
        ]);

        $response->assertRedirect('/admin/devices');
        $response->assertSessionHas('generated_api_key');

        $this->assertDatabaseHas('devices', [
            'device_name' => 'Reader Autoclave Test BDG',
            'mac_address' => strtolower($mac),
            'status'      => 'online',
        ]);
    }

    public function test_admin_can_update_device()
    {
        $this->actingAs($this->admin);

        $device = Device::create([
            'device_name'       => 'Old Device Name',
            'location'          => 'Pabrik Lama',
            'division_id'       => $this->division->division_id,
            'mac_address'       => '11:22:33:44:55:66',
            'api_key_hash'      => Hash::make('secret_key'),
            'status'            => 'online',
            'installed_at'      => now()->toDateString(),
        ]);

        $response = $this->put("/admin/devices/{$device->device_id}", [
            'device_name'  => 'Updated Device Name',
            'location'     => 'Pabrik Surabaya - Line A',
            'division_id'  => $this->division->division_id,
            'mac_address'  => '11:22:33:44:55:66',
            'status'       => 'maintenance',
            'installed_at' => now()->toDateString(),
        ]);

        $response->assertRedirect('/admin/devices');

        $this->assertDatabaseHas('devices', [
            'device_id'   => $device->device_id,
            'device_name' => 'Updated Device Name',
            'location'    => 'Pabrik Surabaya - Line A',
            'status'      => 'maintenance',
        ]);
    }

    public function test_admin_can_regenerate_api_key_for_device()
    {
        $this->actingAs($this->admin);

        $oldHash = Hash::make('old_secret_key');
        $device = Device::create([
            'device_name'       => 'Reader Regen Key',
            'location'          => 'Pabrik Bandung',
            'division_id'       => $this->division->division_id,
            'mac_address'       => '99:88:77:66:55:44',
            'api_key_hash'      => $oldHash,
            'status'            => 'online',
        ]);

        $response = $this->post("/admin/devices/{$device->device_id}/regenerate-key");
        $response->assertRedirect('/admin/devices');
        $response->assertSessionHas('generated_api_key');

        $updated = $device->fresh();
        $this->assertNotEquals($oldHash, $updated->api_key_hash);
    }
}
