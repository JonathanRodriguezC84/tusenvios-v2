<?php

namespace Tests\Feature;

use App\Models\InventoryProduct;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_list_inventory(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);
        InventoryProduct::create(['name' => 'Product A', 'price' => 100, 'stock' => 10, 'cost' => 50, 'tenant_id' => $tenant->id]);
        InventoryProduct::create(['name' => 'Product B', 'price' => 200, 'stock' => 20, 'cost' => 100, 'tenant_id' => $tenant->id]);
        InventoryProduct::create(['name' => 'Product C', 'price' => 300, 'stock' => 30, 'cost' => 150, 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('inventory.index'));

        $response->assertOk();
        $response->assertViewHas('products');
    }

    public function test_superadmin_can_create_inventory_product(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->post(route('inventory.store'), [
            'name' => 'New Product',
            'price' => 150,
            'stock' => 25,
            'cost' => 75,
            'stock_minimum' => 5,
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseHas('inventory_products', [
            'name' => 'New Product',
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_guest_cannot_access_inventory(): void
    {
        $response = $this->get(route('inventory.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_can_view_create_form(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->get(route('inventory.create'));

        $response->assertOk();
    }

    public function test_superadmin_can_export_inventory(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);
        InventoryProduct::create(['name' => 'Export A', 'price' => 50, 'stock' => 5, 'cost' => 25, 'tenant_id' => $tenant->id]);
        InventoryProduct::create(['name' => 'Export B', 'price' => 60, 'stock' => 8, 'cost' => 30, 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('inventory.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_user_without_subscription_cannot_use_inventory(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'tenant_admin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->get(route('inventory.index'));

        $response->assertForbidden();
    }
}
