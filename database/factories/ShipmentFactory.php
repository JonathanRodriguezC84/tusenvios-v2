<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        return [
            'tenant_id' => TenantFactory::new(),
            'guide_number' => 'TUS-' . fake()->unique()->randomNumber(8),
            'status' => 'created',
            'sender_name' => fake()->name(),
            'sender_phone' => fake()->phoneNumber(),
            'sender_address' => fake()->address(),
            'recipient_name' => fake()->name(),
            'recipient_lastname' => fake()->lastName(),
            'recipient_phone' => fake()->phoneNumber(),
            'recipient_address' => fake()->address(),
            'recipient_neighborhood' => fake()->word(),
            'recipient_locality' => fake()->city(),
            'package_type' => 'package',
            'pieces' => 1,
            'payment_method' => 'cash',
            'shipping_value' => 10000,
            'declared_value' => 50000,
        ];
    }
}
