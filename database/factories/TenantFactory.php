<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company(),
            'document_number' => fake()->numerify('########-#'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'subdomain' => fake()->unique()->slug(2),
            'guide_prefix' => strtoupper(fake()->lexify('???')),
            'status' => 'active',
        ];
    }
}
