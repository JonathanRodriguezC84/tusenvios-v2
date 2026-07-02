<?php

return [
    'carriers' => [
        'servientrega' => [
            'name' => 'Servientrega',
            'logo' => null,
            'base_rate' => 5000,
            'per_kg' => 1200,
            'per_piece' => 500,
            'standard_days' => '2-4',
            'express_multiplier' => 1.5,
            'express_days' => '1-2',
            'zone_multipliers' => [
                'local' => 1.0,
                'regional' => 1.3,
                'nacional' => 1.8,
            ],
        ],
        'interrapidisimo' => [
            'name' => 'Interrapidisimo',
            'logo' => null,
            'base_rate' => 5500,
            'per_kg' => 1500,
            'per_piece' => 500,
            'standard_days' => '2-3',
            'express_multiplier' => 1.6,
            'express_days' => '1',
            'zone_multipliers' => [
                'local' => 1.0,
                'regional' => 1.4,
                'nacional' => 2.0,
            ],
        ],
        'tcc' => [
            'name' => 'TCC',
            'logo' => null,
            'base_rate' => 4500,
            'per_kg' => 1000,
            'per_piece' => 300,
            'standard_days' => '3-5',
            'express_multiplier' => 1.4,
            'express_days' => '1-2',
            'zone_multipliers' => [
                'local' => 1.0,
                'regional' => 1.2,
                'nacional' => 1.7,
            ],
        ],
    ],
];