<?php

namespace App\Services;

use App\Models\DeliveryZone;

class ShippingRateService
{
    protected array $carrierRates = [];

    public function __construct(?array $carrierRates = null)
    {
        $this->carrierRates = $carrierRates ?? config('shipping.carriers', []);
    }

    public function calculateRate(string $origin, string $destination, float $weightKg = 0, int $pieces = 1, ?string $serviceType = null): array
    {
        $zone = $this->findZone($origin, $destination);
        $baseRates = $this->getBaseRates($zone, $weightKg, $pieces, $serviceType);

        return [
            'origin' => $origin,
            'destination' => $destination,
            'zone' => $zone,
            'weight_kg' => $weightKg,
            'pieces' => $pieces,
            'service_type' => $serviceType ?? 'estandar',
            'rates' => $baseRates,
        ];
    }

    protected function findZone(string $origin, string $destination): ?string
    {
        $zone = DeliveryZone::query()
            ->where('locality', 'like', "%{$destination}%")
            ->orWhere('city', 'like', "%{$destination}%")
            ->first();

        return $zone?->zone ?? $zone?->name ?? null;
    }

    protected function getBaseRates(?string $zone, float $weightKg, int $pieces, ?string $serviceType): array
    {
        $carriers = config('shipping.carriers', []);

        if (empty($carriers)) {
            return $this->defaultRates($zone);
        }

        $rates = [];
        foreach ($carriers as $key => $carrier) {
            $rate = $this->calculateCarrierRate($carrier, $zone, $weightKg, $pieces, $serviceType);
            if ($rate) {
                $rates[$key] = $rate;
            }
        }

        return $rates;
    }

    protected function calculateCarrierRate(array $carrier, ?string $zone, float $weightKg, int $pieces, ?string $serviceType): ?array
    {
        $baseRate = $carrier['base_rate'] ?? 5000;
        $perKg = $carrier['per_kg'] ?? 1000;
        $perPiece = $carrier['per_piece'] ?? 0;
        $zoneMultiplier = 1;

        if ($zone && isset($carrier['zone_multipliers'][$zone])) {
            $zoneMultiplier = $carrier['zone_multipliers'][$zone];
        }

        $total = ($baseRate + ($weightKg * $perKg) + ($pieces * $perPiece)) * $zoneMultiplier;

        if ($serviceType === 'expreso' && isset($carrier['express_multiplier'])) {
            $total *= $carrier['express_multiplier'];
        }

        return [
            'carrier' => $carrier['name'],
            'logo' => $carrier['logo'] ?? null,
            'service' => $serviceType === 'expreso' ? 'Express' : 'Estandar',
            'price' => round($total),
            'currency' => 'COP',
            'estimated_days' => $serviceType === 'expreso'
                ? ($carrier['express_days'] ?? '1-2')
                : ($carrier['standard_days'] ?? '2-5'),
        ];
    }

    protected function defaultRates(?string $zone): array
    {
        return [
            'nacional' => [
                'carrier' => 'Servicio Nacional',
                'logo' => null,
                'service' => 'Estandar',
                'price' => 0,
                'currency' => 'COP',
                'estimated_days' => '2-5',
                'note' => 'Configura las tarifas de envio en config/shipping.php',
            ],
        ];
    }
}