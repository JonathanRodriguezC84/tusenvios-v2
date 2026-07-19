<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'tenant_admin']);
    }

    public function test_audit_log_index_requires_auth(): void
    {
        $response = $this->get(route('audit-logs.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_audit_log_index_visible_to_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('audit-logs.index'));
        $response->assertOk();
    }

    public function test_audit_log_index_filters_by_action(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'shipment.created',
            'description' => 'Test shipment created',
        ]);

        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'shipment.status_updated',
            'description' => 'Status updated',
        ]);

        $response = $this->actingAs($this->admin)->get(route('audit-logs.index', ['action' => 'shipment.created']));
        $response->assertOk();
        $response->assertSee('Test shipment created');
    }

    public function test_audit_log_export_csv(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'shipment.created',
            'description' => 'CSV export test',
        ]);

        $response = $this->actingAs($this->admin)->get(route('audit-logs.export'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_audit_log_stores_properties(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'test.action',
            'description' => 'Test with properties',
            'properties' => ['key' => 'value', 'nested' => ['a' => 1]],
        ]);

        $this->assertEquals(['key' => 'value', 'nested' => ['a' => 1]], $log->properties);
    }
}
