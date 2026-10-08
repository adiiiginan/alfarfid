<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Garment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AlertManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Alert $alert;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', User::ROLE_ADMIN)->first() ?? User::create([
            'full_name'     => 'Admin Alerts Test',
            'email'         => 'admin_alerts_' . Str::random(5) . '@alfarfid.local',
            'password_hash' => Hash::make('password123'),
            'role'          => User::ROLE_ADMIN,
        ]);

        $this->alert = Alert::create([
            'alert_type' => 'cycle_warning',
            'severity'   => 'warning',
            'message'    => 'Garment GAR-TEST mendekati batas siklus autoclave.',
            'status'     => 'open',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_alerts_index()
    {
        $response = $this->get('/admin/alerts');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_alerts_index()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/alerts');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Alerts');
        $response->assertSee($this->alert->message);
    }

    public function test_admin_can_filter_alerts_by_status_and_severity()
    {
        $this->actingAs($this->admin);

        // Buat critical alert
        $criticalAlert = Alert::create([
            'alert_type' => 'cycle_exceeded',
            'severity'   => 'critical',
            'message'    => 'Garment GAR-CRITICAL telah melebihi batas siklus.',
            'status'     => 'open',
        ]);

        // Filter critical
        $response = $this->get('/admin/alerts?status=open&severity=critical');

        $response->assertStatus(200);
        $response->assertSee('GAR-CRITICAL');
    }

    public function test_admin_can_acknowledge_open_alert()
    {
        $this->actingAs($this->admin);

        $response = $this->post("/admin/alerts/{$this->alert->alert_id}/acknowledge");

        $response->assertRedirect();
        $this->assertDatabaseHas('alerts', [
            'alert_id' => $this->alert->alert_id,
            'status'   => 'acknowledged',
        ]);
    }

    public function test_admin_can_resolve_open_alert()
    {
        $this->actingAs($this->admin);

        $response = $this->post("/admin/alerts/{$this->alert->alert_id}/resolve");

        $response->assertRedirect();

        $this->assertDatabaseHas('alerts', [
            'alert_id'    => $this->alert->alert_id,
            'status'      => 'resolved',
            'resolved_by' => $this->admin->user_id,
        ]);

        $fresh = $this->alert->fresh();
        $this->assertEquals('resolved', $fresh->status);
        $this->assertNotNull($fresh->resolved_at);
        $this->assertEquals($this->admin->user_id, $fresh->resolved_by);
    }

    public function test_admin_can_bulk_acknowledge_and_bulk_resolve_alerts()
    {
        $this->actingAs($this->admin);

        $alert1 = Alert::create([
            'alert_type' => 'cycle_warning',
            'severity'   => 'warning',
            'message'    => 'Alert Bulk 1',
            'status'     => 'open',
        ]);

        $alert2 = Alert::create([
            'alert_type' => 'cycle_warning',
            'severity'   => 'warning',
            'message'    => 'Alert Bulk 2',
            'status'     => 'open',
        ]);

        // Bulk Acknowledge
        $resAck = $this->post('/admin/alerts/bulk-acknowledge', [
            'alert_ids' => [$alert1->alert_id, $alert2->alert_id],
        ]);
        $resAck->assertRedirect();

        $this->assertEquals('acknowledged', $alert1->fresh()->status);
        $this->assertEquals('acknowledged', $alert2->fresh()->status);

        // Bulk Resolve
        $resRes = $this->post('/admin/alerts/bulk-resolve', [
            'alert_ids' => [$alert1->alert_id, $alert2->alert_id],
        ]);
        $resRes->assertRedirect();

        $this->assertEquals('resolved', $alert1->fresh()->status);
        $this->assertEquals('resolved', $alert2->fresh()->status);
    }
}
