<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;

class TrackingApiController extends Controller
{
    public function show(string $guideNumber)
    {
        $guideNumber = strtoupper(trim($guideNumber));

        $shipment = Shipment::where('guide_number', $guideNumber)->first();

        if (!$shipment) {
            return response()->json(['message' => 'Guia no encontrada.'], 404);
        }

        $statusLabels = [
            'created' => 'Por imprimir', 'printed' => 'Impresa', 'in_warehouse' => 'Preparando',
            'in_sorting' => 'Preparando', 'assigned' => 'Asignada', 'on_route' => 'En camino',
            'delivered' => 'Entregada', 'failed_delivery' => 'Novedad', 'rescheduled' => 'Reprogramada',
            'return_pending' => 'Devuelve', 'returned' => 'Devuelta', 'cancelled' => 'Cancelada',
        ];

        return response()->json([
            'guide_number' => $shipment->guide_number,
            'status' => $shipment->status,
            'status_label' => $statusLabels[$shipment->status] ?? $shipment->status,
            'recipient_city' => $shipment->recipient_city ?: $shipment->recipient_locality,
            'zone' => $shipment->zone,
            'declared_value' => $shipment->declared_value,
            'pieces' => $shipment->pieces,
            'history' => $shipment->events()
                ->latest('recorded_at')
                ->get(['status', 'location', 'notes', 'recorded_at'])
                ->map(fn ($e) => [
                    'status' => $e->status,
                    'label' => $statusLabels[$e->status] ?? $e->status,
                    'location' => $e->location,
                    'notes' => $e->notes,
                    'date' => $e->recorded_at?->format('Y-m-d H:i:s'),
                ]),
            'created_at' => $shipment->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $shipment->updated_at->format('Y-m-d H:i:s'),
        ]);
    }
}
