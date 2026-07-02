<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = $filters['date'] ?? today()->toDateString();

        $shipments = Shipment::query()
            ->with(['affiliatedCompany', 'courier'])
            ->visibleTo(Auth::user())
            ->whereDate('updated_at', $date)
            ->where('collection_value', '>', 0)
            ->whereIn('status', ['delivered', 'failed_delivery', 'rescheduled', 'return_pending'])
            ->latest('updated_at')
            ->get();

        $totals = [
            'delivered' => $shipments->where('status', 'delivered')->sum('collection_value'),
            'pending' => $shipments->whereIn('status', ['failed_delivery', 'rescheduled', 'return_pending'])->sum('collection_value'),
            'count' => $shipments->count(),
        ];

        $byCourier = $shipments
            ->groupBy(fn ($shipment) => $shipment->courier?->name ?? 'Sin mensajero')
            ->map(fn ($items) => [
                'delivered' => $items->where('status', 'delivered')->sum('collection_value'),
                'pending' => $items->where('status', '!=', 'delivered')->sum('collection_value'),
                'count' => $items->count(),
            ]);

        return view('reports.collections', compact('shipments', 'totals', 'byCourier', 'date'));
    }
}
