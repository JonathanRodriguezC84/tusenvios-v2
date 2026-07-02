<?php

namespace App\Http\Controllers\Api;

use App\Services\ShippingRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ShippingRateApiController extends Controller
{
    public function __invoke(Request $request, ShippingRateService $service): JsonResponse
    {
        $validated = $request->validate([
            'origin' => 'nullable|string|max:100',
            'destination' => 'required|string|max:100',
            'weight_kg' => 'nullable|numeric|min:0',
            'pieces' => 'nullable|integer|min:1',
            'service_type' => 'nullable|string|in:estandar,expreso',
        ]);

        $origin = $validated['origin'] ?? 'Bogota';
        $destination = $validated['destination'];
        $weight = $validated['weight_kg'] ?? 0;
        $pieces = $validated['pieces'] ?? 1;
        $serviceType = $validated['service_type'] ?? null;

        $result = $service->calculateRate($origin, $destination, $weight, $pieces, $serviceType);

        return response()->json($result);
    }
}