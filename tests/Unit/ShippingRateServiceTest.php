<?php

namespace Tests\Unit;

use App\Services\ShippingRateService;
use PHPUnit\Framework\TestCase;

class ShippingRateServiceTest extends TestCase
{
    private ShippingRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShippingRateService();
    }

    public function test_calculate_rate_returns_structure(): void
    {
        $result = $this->service->calculateRate('Bogota', 'Medellin', 2.0, 1, 'estandar');

        $this->assertArrayHasKey('origin', $result);
        $this->assertArrayHasKey('destination', $result);
        $this->assertArrayHasKey('rates', $result);
        $this->assertEquals('Bogota', $result['origin']);
        $this->assertEquals('Medellin', $result['destination']);
        $this->assertEquals(2.0, $result['weight_kg']);
        $this->assertEquals(1, $result['pieces']);
    }

    public function test_calculate_rate_with_default_service_type(): void
    {
        $result = $this->service->calculateRate('Bogota', 'Cali', 1.0, 1);

        $this->assertEquals('estandar', $result['service_type']);
    }

    public function test_calculate_rate_returns_carrier_rates(): void
    {
        $result = $this->service->calculateRate('Bogota', 'Barranquilla', 3.0, 2, 'estandar');

        $this->assertIsArray($result['rates']);
        $this->assertNotEmpty($result['rates']);

        foreach ($result['rates'] as $key => $rate) {
            $this->assertArrayHasKey('carrier', $rate);
            $this->assertArrayHasKey('price', $rate);
            $this->assertArrayHasKey('service', $rate);
            $this->assertArrayHasKey('estimated_days', $rate);
            $this->assertArrayHasKey('currency', $rate);
            $this->assertEquals('COP', $rate['currency']);
        }
    }

    public function test_express_rate_is_higher_than_standard(): void
    {
        $standard = $this->service->calculateRate('Bogota', 'Cali', 1.0, 1, 'estandar');
        $express = $this->service->calculateRate('Bogota', 'Cali', 1.0, 1, 'expreso');

        $standardTotal = array_sum(array_column($standard['rates'], 'price'));
        $expressTotal = array_sum(array_column($express['rates'], 'price'));

        $this->assertGreaterThan($standardTotal, $expressTotal);
    }

    public function test_heavier_weight_costs_more(): void
    {
        $light = $this->service->calculateRate('Bogota', 'Cali', 1.0, 1, 'estandar');
        $heavy = $this->service->calculateRate('Bogota', 'Cali', 5.0, 1, 'estandar');

        $lightTotal = array_sum(array_column($light['rates'], 'price'));
        $heavyTotal = array_sum(array_column($heavy['rates'], 'price'));

        $this->assertGreaterThan($lightTotal, $heavyTotal);
    }
}