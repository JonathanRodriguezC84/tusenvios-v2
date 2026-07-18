<?php

namespace Tests\Unit;

use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\ShipmentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ShipmentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ShipmentPolicy;
    }

    public function test_superadmin_can_create_shipments(): void
    {
        $user = User::factory()->create(['role' => 'superadmin', 'status' => 'active']);
        $this->assertTrue($this->policy->create($user));
    }

    public function test_tenant_admin_can_create_shipments(): void
    {
        $user = User::factory()->create(['role' => 'tenant_admin', 'status' => 'active']);
        $this->assertTrue($this->policy->create($user));
    }

    public function test_inactive_user_cannot_create_shipments(): void
    {
        $user = User::factory()->create(['role' => 'tenant_admin', 'status' => 'inactive']);
        $this->assertFalse($this->policy->create($user));
    }

    public function test_shipment_visible_to_own_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'tenant_admin', 'status' => 'active', 'tenant_id' => $tenant->id]);
        $shipment = Shipment::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertTrue($this->policy->view($user, $shipment));
    }

    public function test_shipment_not_visible_to_other_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'tenant_admin', 'status' => 'active', 'tenant_id' => $tenantA->id]);
        $shipment = Shipment::factory()->create(['tenant_id' => $tenantB->id]);

        $this->assertFalse($this->policy->view($user, $shipment));
    }

    public function test_superadmin_sees_all_shipments(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'superadmin', 'status' => 'active']);
        $shipment = Shipment::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertTrue($this->policy->view($user, $shipment));
    }

    public function test_only_editable_shipments_can_be_updated(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'tenant_admin', 'status' => 'active', 'tenant_id' => $tenant->id]);
        $editable = Shipment::factory()->create(['tenant_id' => $tenant->id, 'status' => 'created']);
        $locked = Shipment::factory()->create(['tenant_id' => $tenant->id, 'status' => 'on_route']);

        $this->assertTrue($this->policy->update($user, $editable));
        $this->assertFalse($this->policy->update($user, $locked));
    }

    public function test_warehouse_can_edit_shipments(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'warehouse', 'status' => 'active', 'tenant_id' => $tenant->id]);
        $shipment = Shipment::factory()->create(['tenant_id' => $tenant->id, 'status' => 'created']);

        $this->assertTrue($this->policy->update($user, $shipment));
    }

    public function test_affiliate_cannot_edit_shipments(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'affiliate', 'status' => 'active', 'tenant_id' => $tenant->id]);
        $shipment = Shipment::factory()->create(['tenant_id' => $tenant->id, 'status' => 'created']);

        $this->assertFalse($this->policy->update($user, $shipment));
    }
}
