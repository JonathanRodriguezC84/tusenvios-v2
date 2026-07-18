<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'role' => 'tenant_admin',
            'status' => 'active',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_bulk_status_update_requires_auth(): void
    {
        $response = $this->patch(route('shipments.bulk-status'), [
            'shipment_ids' => [1],
            'status' => 'in_warehouse',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_bulk_status_update_changes_multiple_shipments(): void
    {
        $shipments = Shipment::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'created',
        ]);

        $response = $this->actingAs($this->user)->patch(route('shipments.bulk-status'), [
            'shipment_ids' => $shipments->pluck('id')->toArray(),
            'status' => 'printed',
        ]);

        $response->assertSessionHas('status');

        foreach ($shipments as $shipment) {
            $fresh = $shipment->fresh();
            $this->assertEquals('printed', $fresh->status);
        }
    }

    public function test_bulk_status_skips_invalid_transitions(): void
    {
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'created',
        ]);

        $response = $this->actingAs($this->user)->patch(route('shipments.bulk-status'), [
            'shipment_ids' => [$shipment->id],
            'status' => 'delivered',
        ]);

        $response->assertSessionHas('status');

        $fresh = $shipment->fresh();
        $this->assertEquals('created', $fresh->status);
    }

    public function test_bulk_status_requires_valid_shipment_ids(): void
    {
        $response = $this->actingAs($this->user)->patch(route('shipments.bulk-status'), [
            'shipment_ids' => [99999],
            'status' => 'printed',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_bulk_status_requires_status_field(): void
    {
        $response = $this->actingAs($this->user)->patch(route('shipments.bulk-status'), [
            'shipment_ids' => [1],
        ]);

        $response->assertSessionHasErrors('status');
    }
}
