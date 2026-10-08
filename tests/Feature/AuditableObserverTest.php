<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Division;
use App\Models\GarmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditableObserverTest extends TestCase
{
    use DatabaseTransactions;

    public function test_creating_model_records_insert_audit_log()
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first() ?? User::create([
            'full_name'     => 'Test Admin',
            'email'         => 'admin_test_' . Str::random(5) . '@alfarfid.local',
            'password_hash' => Hash::make('password123'),
            'role'          => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        $category = GarmentCategory::create([
            'category_name'     => 'Jas Lab Steril ' . Str::random(6),
            'description'       => 'Jas lab untuk cleanroom',
            'default_max_cycle' => 60,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'garment_categories',
            'record_id'  => $category->category_id,
            'action'     => 'insert',
            'changed_by' => $admin->user_id,
        ]);

        $log = AuditLog::where('table_name', 'garment_categories')
            ->where('record_id', $category->category_id)
            ->where('action', 'insert')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->new_value);
        $decoded = json_decode($log->new_value, true);
        $this->assertEquals($category->category_name, $decoded['category_name']);
        $this->assertEquals(60, $decoded['default_max_cycle']);
    }

    public function test_updating_model_records_diff_audit_log()
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first() ?? User::create([
            'full_name'     => 'Test Admin',
            'email'         => 'admin_test_' . Str::random(5) . '@alfarfid.local',
            'password_hash' => Hash::make('password123'),
            'role'          => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        $division = Division::create([
            'division_name' => 'Divisi Mikrobiologi ' . Str::random(6),
            'color_name'    => 'Biru Langit',
            'color_hex'     => '#3399FF',
            'is_active'     => true,
        ]);

        // Update nama divisi dan warna
        $division->update([
            'division_name' => 'Divisi Mikrobiologi Terpadu',
            'color_hex'     => '#0055AA',
        ]);

        $log = AuditLog::where('table_name', 'divisions')
            ->where('record_id', $division->division_id)
            ->where('action', 'update')
            ->latest('changed_at')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($admin->user_id, $log->changed_by);

        $old = json_decode($log->old_value, true);
        $new = json_decode($log->new_value, true);

        // Hanya field yang berubah yang tersimpan
        $this->assertArrayHasKey('division_name', $new);
        $this->assertEquals('Divisi Mikrobiologi Terpadu', $new['division_name']);
        $this->assertArrayHasKey('color_hex', $new);
        $this->assertEquals('#0055AA', $new['color_hex']);
        $this->assertEquals('#3399FF', $old['color_hex']);
    }

    public function test_sensitive_fields_excluded_from_audit_log()
    {
        $user = User::create([
            'full_name'     => 'Operator Baru',
            'email'         => 'operator_' . Str::random(6) . '@alfarfid.local',
            'password_hash' => Hash::make('SecretPassword123!'),
            'role'          => User::ROLE_OPERATOR,
        ]);

        $log = AuditLog::where('table_name', 'users')
            ->where('record_id', $user->user_id)
            ->where('action', 'insert')
            ->first();

        $this->assertNotNull($log);
        $decoded = json_decode($log->new_value, true);

        $this->assertArrayNotHasKey('password_hash', $decoded);
        $this->assertArrayNotHasKey('password', $decoded);
        $this->assertArrayNotHasKey('remember_token', $decoded);
        $this->assertEquals('Operator Baru', $decoded['full_name']);
    }

    public function test_deleting_model_records_delete_audit_log()
    {
        $division = Division::create([
            'division_name' => 'Divisi Sementara ' . Str::random(6),
            'color_name'    => 'Abu-abu',
            'color_hex'     => '#888888',
        ]);

        $divId = $division->division_id;
        $division->delete();

        $log = AuditLog::where('table_name', 'divisions')
            ->where('record_id', $divId)
            ->where('action', 'delete')
            ->first();

        $this->assertNotNull($log);
        $old = json_decode($log->old_value, true);
        $this->assertStringContainsString('Divisi Sementara', $old['division_name']);
    }
}
