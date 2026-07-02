<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierPerformanceReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $filters = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = $filters['date'] ?? today()->toDateString();

        $shipments = Shipment::query()
            ->with('courier')
            ->visibleTo(Auth::user())
            ->whereDate('updated_at', $date)
            ->whereNotNull('courier_id')
            ->get();

        $summary = $shipments
            ->groupBy(fn ($shipment) => $shipment->courier?->name ?? 'Sin mensajero')
            ->map(fn ($items) => [
                'total' => $items->count(),
                'delivered' => $items->where('status', 'delivered')->count(),
                'issues' => $items->whereIn('status', ['failed_delivery', 'rescheduled'])->count(),
                'returns' => $items->whereIn('status', ['return_pending', 'returned'])->count(),
                'on_route' => $items->where('status', 'on_route')->count(),
                'collection' => $items->where('status', 'delivered')->sum('collection_value'),
            ])
            ->sortKeys();

        return view('reports.couriers', compact('summary', 'date'));
    }
}
