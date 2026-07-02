<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index()
    {
        return view('tracking.index');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:80'],
        ]);

        $shipment = $this->findShipment($validated['code']);

        if (! $shipment) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'No encontramos una guia con ese numero.']);
        }

        return redirect()->route('tracking.show', $shipment->guide_number);
    }

    public function show(string $guideNumber)
    {
        $shipment = $this->findShipment($guideNumber);

        abort_unless($shipment, 404);

        $shipment->load(['affiliatedCompany', 'events' => fn ($query) => $query->latest('recorded_at')]);

        return view('tracking.show', compact('shipment'));
    }

    private function findShipment(string $code): ?Shipment
    {
        $code = strtoupper(trim($code));
        $compact = str_replace('-', '', $code);

        return Shipment::query()
            ->where('guide_number', $code)
            ->orWhereRaw("REPLACE(guide_number, '-', '') = ?", [$compact])
            ->first();
    }
}
