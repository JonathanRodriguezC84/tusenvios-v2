<?php

namespace Tests\Feature;

use App\Models\FrequentRecipient;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrequentRecipientTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'tenant_admin']);
    }

    public function test_recipients_index_requires_auth(): void
    {
        $response = $this->get(route('recipients.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_recipients_index_visible_to_auth_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('recipients.index'));
        $response->assertOk();
    }

    public function test_recipient_search_returns_results(): void
    {
        FrequentRecipient::create([
            'user_id' => $this->user->id,
            'name' => 'Juan Perez',
            'phone' => '3001234567',
            'city' => 'Bogota',
            'use_count' => 5,
        ]);

        $response = $this->actingAs($this->user)->get(route('recipients.search', ['q' => 'Juan']));
        $response->assertOk();
        $this->assertContains('Juan Perez', array_column($response->json(), 'name'));
    }

    public function test_recipient_search_requires_q_parameter(): void
    {
        $response = $this->actingAs($this->user)->get(route('recipients.search'));
        $response->assertOk();
        $response->assertJson([]);
    }

    public function test_recipient_destroy(): void
    {
        $recipient = FrequentRecipient::create([
            'user_id' => $this->user->id,
            'name' => 'Maria Garcia',
            'phone' => '3109876543',
            'city' => 'Medellin',
            'use_count' => 3,
        ]);

        $response = $this->actingAs($this->user)->delete(route('recipients.destroy', $recipient));
        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertModelMissing($recipient);
    }
}