<?php

namespace App\Http\Controllers;

use App\Services\ShippingRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingRateController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        return view('shipping-rates.index');
    }

    public function calculate(Request $request, ShippingRateService $service)
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

        return view('shipping-rates.index', ['result' => $result, 'input' => $validated]);
    }
}