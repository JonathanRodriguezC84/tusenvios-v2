<?php

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use App\Models\Tenant;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isTenantAdmin(), 403);

        $zones = DeliveryZone::query()
            ->with('tenant')
            ->when(! Auth::user()->isSuperAdmin(), function ($query) {
                $query->where(function ($query) {
                    $query
                        ->whereNull('tenant_id')
                        ->orWhere('tenant_id', Auth::user()->tenant_id);
                });
            })
            ->orderBy('price')
            ->paginate(15);

        return view('delivery-zones.index', compact('zones'));
    }

    public function create()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isTenantAdmin(), 403);

        $tenants = Tenant::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return view('delivery-zones.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isTenantAdmin(), 403);

        $validated = $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'coverage_keywords' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['code'] = $validated['code'] ? strtoupper($validated['code']) : null;

        if (! Auth::user()->isSuperAdmin()) {
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }

        $zone = DeliveryZone::query()->create($validated);

        Audit::log('delivery_zone.created', $zone, "Zona {$zone->name} creada.");

        return redirect()
            ->route('delivery-zones.index')
            ->with('status', 'Zona creada correctamente.');
    }

    public function edit(DeliveryZone $deliveryZone)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isTenantAdmin(), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $deliveryZone->tenant_id !== Auth::user()->tenant_id, 403);

        $tenants = Tenant::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return view('delivery-zones.edit', compact('deliveryZone', 'tenants'));
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isTenantAdmin(), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $deliveryZone->tenant_id !== Auth::user()->tenant_id, 403);

        $validated = $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'coverage_keywords' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['code'] = $validated['code'] ? strtoupper($validated['code']) : null;

        if (! Auth::user()->isSuperAdmin()) {
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }

        $deliveryZone->update($validated);

        Audit::log('delivery_zone.updated', $deliveryZone, "Zona {$deliveryZone->name} actualizada.");

        return redirect()
            ->route('delivery-zones.index')
            ->with('status', 'Zona actualizada correctamente.');
    }
}
