<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_shipment(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->post(route('shipments.store'), [
            'sender_name' => 'Sender Name',
            'sender_phone' => '3001234567',
            'sender_address' => 'Address 123',
            'recipient_name' => 'Recipient Name',
            'recipient_lastname' => 'Lastname',
            'recipient_phone' => '3007654321',
            'recipient_address' => 'Address 456',
            'recipient_neighborhood' => 'Neighborhood',
            'recipient_locality' => 'Locality',
            'package_type' => 'package',
            'pieces' => 2,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('shipments.index'));
        $this->assertDatabaseHas('shipments', [
            'sender_name' => 'Sender Name',
            'recipient_name' => 'Recipient Name',
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_guest_cannot_create_shipment(): void
    {
        $response = $this->post(route('shipments.store'), [
            'sender_name' => 'Sender Name',
            'sender_phone' => '3001234567',
            'sender_address' => 'Address 123',
            'recipient_name' => 'Recipient Name',
            'recipient_lastname' => 'Lastname',
            'recipient_phone' => '3007654321',
            'recipient_address' => 'Address 456',
            'recipient_neighborhood' => 'Neighborhood',
            'recipient_locality' => 'Locality',
            'package_type' => 'package',
            'pieces' => 2,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_inactive_user_cannot_create_shipment(): void
    {
        $user = User::factory()->create(['role' => 'superadmin', 'status' => 'inactive']);

        $response = $this->actingAs($user)->post(route('shipments.store'), [
            'sender_name' => 'Sender Name',
            'sender_phone' => '3001234567',
            'sender_address' => 'Address 123',
            'recipient_name' => 'Recipient Name',
            'recipient_lastname' => 'Lastname',
            'recipient_phone' => '3007654321',
            'recipient_address' => 'Address 456',
            'recipient_neighborhood' => 'Neighborhood',
            'recipient_locality' => 'Locality',
            'package_type' => 'package',
            'pieces' => 2,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('billing.blocked'));
    }

    public function test_superadmin_can_list_shipments(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);
        Shipment::factory()->count(3)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('shipments.index'));

        $response->assertOk();
        $response->assertViewHas('shipments');
    }

    public function test_user_can_view_own_tenant_shipment(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'tenant_admin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);
        $shipment = Shipment::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('shipments.show', $shipment));

        $response->assertOk();
        $response->assertViewHas('shipment');
    }

    public function test_user_cannot_view_other_tenant_shipment(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'tenant_admin',
            'status' => 'active',
            'tenant_id' => $tenant1->id,
        ]);
        $shipment = Shipment::factory()->create(['tenant_id' => $tenant2->id]);

        $response = $this->actingAs($user)->get(route('shipments.show', $shipment));

        $response->assertForbidden();
    }

    public function test_superadmin_can_edit_shipment(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);
        $shipment = Shipment::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'created',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.edit', $shipment));

        $response->assertOk();
    }

    public function test_superadmin_can_update_shipment(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);
        $shipment = Shipment::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'created',
        ]);
        $newName = 'Updated Recipient';

        $response = $this->actingAs($user)->patch(route('shipments.update', $shipment), [
            'service_type' => 'standard',
            'sender_name' => $shipment->sender_name,
            'sender_phone' => $shipment->sender_phone,
            'sender_address' => $shipment->sender_address,
            'recipient_name' => $newName,
            'recipient_lastname' => $shipment->recipient_lastname,
            'recipient_phone' => $shipment->recipient_phone,
            'recipient_address' => $shipment->recipient_address,
            'recipient_neighborhood' => $shipment->recipient_neighborhood,
            'recipient_locality' => $shipment->recipient_locality,
            'package_type' => $shipment->package_type,
            'pieces' => $shipment->pieces,
            'payment_method' => $shipment->payment_method,
        ]);

        $response->assertRedirect(route('shipments.show', $shipment));
        $this->assertEquals($newName, $shipment->fresh()->recipient_name);
    }

    public function test_shipment_export_returns_csv(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);
        Shipment::factory()->count(3)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('shipments.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
