<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierRouteController extends Controller
{
    public function index()
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'courier'], true), 403);

        $shipments = Shipment::query()
            ->with('affiliatedCompany')
            ->visibleTo(Auth::user())
            ->whereIn('status', ['assigned', 'on_route', 'failed_delivery', 'rescheduled', 'return_pending'])
            ->latest()
            ->paginate(15);

        return view('courier-route.index', compact('shipments'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'courier'], true), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $shipment->canTransitionTo($validated['status'])) {
            return back()
                ->withErrors(['status' => 'Ese cambio de estado no esta permitido para esta guia.']);
        }

        $shipment->update([
            'status' => $validated['status'],
            'delivery_attempts' => in_array($validated['status'], ['failed_delivery', 'rescheduled'], true)
                ? $shipment->delivery_attempts + 1
                : $shipment->delivery_attempts,
        ]);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => $validated['status'],
            'location' => 'Ruta',
            'notes' => $validated['notes'],
        ]);

        Audit::log('shipment.route_status_updated', $shipment, "Guia {$shipment->guide_number} actualizada en ruta a {$validated['status']}.");

        return redirect()
            ->route('courier-route.index')
            ->with('status', 'Estado actualizado correctamente.');
    }
}
