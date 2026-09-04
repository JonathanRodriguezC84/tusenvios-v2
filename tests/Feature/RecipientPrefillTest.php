<?php

namespace Tests\Feature;

use App\Models\FrequentRecipient;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientPrefillTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_prefills_recipient_data(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'role' => 'tenant_admin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
        ]);

        $recipient = FrequentRecipient::create([
            'tenant_id' => $tenant->id,
            'name' => 'SOFIA',
            'lastname' => 'CARDENAS',
            'phone' => '3203332211',
            'address' => 'CALLE 140 #12-44',
            'neighborhood' => 'CEDRITOS',
            'city' => 'BOGOTA',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.create', ['recipient' => $recipient->id]));

        $response->assertOk();
        $response->assertSee('value="SOFIA"', false);
        $response->assertSee('value="CARDENAS"', false);
        $response->assertSee('value="3203332211"', false);
        $response->assertSee('CALLE 140 #12-44', false);
    }
}