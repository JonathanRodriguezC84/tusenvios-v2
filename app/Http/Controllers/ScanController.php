<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->canScanShipments(), 403);

        return view('scan.index');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canScanShipments(), 403);

        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shipment = $this->findShipment($validated['barcode']);

        if (! $shipment || ! $shipment->isVisibleTo(Auth::user())) {
            return back()
                ->withInput()
                ->withErrors(['barcode' => 'No encontramos una guia con ese codigo.']);
        }

        $nextStatus = $validated['status'] === 'auto'
            ? $shipment->nextScanStatusFor(Auth::user())
            : $validated['status'];

        if (! $nextStatus) {
            return back()
                ->withInput()
                ->withErrors(['status' => 'Esta guia no tiene una accion automatica disponible para tu rol.']);
        }

        if (! $shipment->canTransitionTo($nextStatus)) {
            return back()
                ->withInput()
                ->withErrors(['status' => 'Ese cambio de estado no esta permitido para esta guia.']);
        }

        $shipment->update([
            'status' => $nextStatus,
        ]);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => $nextStatus,
            'location' => $validated['location'] ?: 'Operacion',
            'notes' => $validated['notes'],
        ]);

        Audit::log('shipment.scanned', $shipment, "Guia {$shipment->guide_number} escaneada y actualizada a {$nextStatus}.");

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Movimiento registrado correctamente.');
    }

    private function findShipment(string $barcode): ?Shipment
    {
        $barcode = strtoupper(trim($barcode));
        $compact = str_replace('-', '', $barcode);

        return Shipment::query()
            ->where('guide_number', $barcode)
            ->orWhereRaw("REPLACE(guide_number, '-', '') = ?", [$compact])
            ->first();
    }
}
