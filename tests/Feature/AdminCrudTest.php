<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_tenant_admin_cannot_access_admin(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'tenant_admin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_can_list_clients(): void
    {
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);
        Tenant::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('admin.clients'));

        $response->assertOk();
        $response->assertViewHas('clients');
    }

    public function test_superadmin_can_list_users(): void
    {
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);
        $tenant = Tenant::factory()->create();
        User::factory()->count(2)->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.users'));

        $response->assertOk();
    }

    public function test_superadmin_can_view_activity(): void
    {
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('admin.activity'));

        $response->assertOk();
    }
}
