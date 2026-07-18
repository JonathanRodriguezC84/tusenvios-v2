<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_page_loads_for_valid_guide(): void
    {
        $shipment = Shipment::factory()->create();

        $response = $this->get(route('tracking.show', $shipment->guide_number));

        $response->assertOk();
        $response->assertSee($shipment->recipient_name);
    }

    public function test_tracking_page_returns_404_for_invalid_guide(): void
    {
        $response = $this->get(route('tracking.show', 'INVALID-GUIDE-999'));

        $response->assertNotFound();
    }

    public function test_tracking_api_returns_shipment_data(): void
    {
        $shipment = Shipment::factory()->create([
            'status' => 'in_warehouse',
        ]);

        $response = $this->getJson("/api/v1/track/{$shipment->guide_number}");

        $response->assertOk();
        $response->assertJsonPath('status', 'in_warehouse');
        $response->assertJsonPath('guide_number', $shipment->guide_number);
    }
}
