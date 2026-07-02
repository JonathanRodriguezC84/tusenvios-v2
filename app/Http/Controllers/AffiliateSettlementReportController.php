<?php

namespace App\Http\Controllers;

use App\Models\AffiliatedCompany;
use App\Models\AffiliateSettlement;
use App\Models\AffiliateSettlementItem;
use App\Models\Shipment;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AffiliateSettlementReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $data = $this->settlementData($request);

        return view('reports.affiliates', $data);
    }

    public function settlements(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $filters = $this->settlementHistoryFilters($request);

        $companies = AffiliatedCompany::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        $baseQuery = $this->settlementHistoryQuery();

        $totals = [
            'all' => (clone $baseQuery)->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
            'paid' => (clone $baseQuery)->where('status', 'paid')->count(),
        ];

        $filteredQuery = $this->applySettlementHistoryFilters($baseQuery, $filters);

        $filteredTotals = [
            'settlements' => (clone $filteredQuery)->count(),
            'shipments' => (clone $filteredQuery)->sum('shipments_count'),
            'net_collection' => (clone $filteredQuery)->sum('net_collection'),
            'total_to_invoice' => (clone $filteredQuery)->sum('total_to_invoice'),
        ];

        $settlements = $filteredQuery
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('reports.affiliate-settlements.index', compact('settlements', 'filters', 'totals', 'filteredTotals', 'companies'));
    }

    public function exportSettlements(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $filters = $this->settlementHistoryFilters($request);
        $fileName = 'historial-liquidaciones-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Consecutivo',
                'Afiliada',
                'Periodo desde',
                'Periodo hasta',
                'Guias',
                'Envios',
                'Recaudo',
                'Comision',
                'Neto comercio',
                'Total operacion',
                'Estado',
                'Cerrada por',
                'Fecha cierre',
                'Pagada por',
                'Fecha pago',
                'Referencia pago',
            ]);

            $this->applySettlementHistoryFilters($this->settlementHistoryQuery(), $filters)
                ->latest()
                ->chunk(200, function ($settlements) use ($handle) {
                    foreach ($settlements as $settlement) {
                        fputcsv($handle, [
                            $settlement->settlement_number,
                            $settlement->affiliatedCompany?->name ?? 'Sin afiliada',
                            $settlement->date_from->format('Y-m-d'),
                            $settlement->date_to->format('Y-m-d'),
                            $settlement->shipments_count,
                            $settlement->shipping_total,
                            $settlement->collection_total,
                            $settlement->commission_total,
                            $settlement->net_collection,
                            $settlement->total_to_invoice,
                            $settlement->status === 'paid' ? 'Pagada' : 'Pendiente de pago',
                            $settlement->creator?->name ?? 'Sistema',
                            $settlement->closed_at?->format('Y-m-d H:i:s'),
                            $settlement->payer?->name ?? '',
                            $settlement->paid_at?->format('Y-m-d H:i:s'),
                            $settlement->payment_reference,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function showSettlement(AffiliateSettlement $settlement)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $settlement->tenant_id !== Auth::user()->tenant_id, 403);

        $settlement->load(['affiliatedCompany', 'creator', 'payer', 'items.shipment']);

        return view('reports.affiliate-settlements.show', compact('settlement'));
    }

    public function markAsPaid(Request $request, AffiliateSettlement $settlement)
    {
        abort_unless(Auth::user()->canMarkAffiliateSettlementsPaid(), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $settlement->tenant_id !== Auth::user()->tenant_id, 403);

        if ($settlement->status === 'paid') {
            return back()->with('status', 'Esta liquidacion ya estaba marcada como pagada.');
        }

        $validated = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $settlement->update([
            'status' => 'paid',
            'paid_by' => Auth::id(),
            'paid_at' => now(),
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        Audit::log('affiliate_settlement.paid', $settlement, "Liquidacion {$settlement->settlement_number} marcada como pagada.");

        return back()->with('status', 'Liquidacion marcada como pagada correctamente.');
    }

    public function close(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $validated = $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'affiliated_company_id' => ['required', 'integer', 'exists:affiliated_companies,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $company = AffiliatedCompany::query()->findOrFail($validated['affiliated_company_id']);
        abort_if(! Auth::user()->isSuperAdmin() && $company->tenant_id !== Auth::user()->tenant_id, 403);

        $existing = AffiliateSettlement::query()
            ->where('affiliated_company_id', $company->id)
            ->where('date_from', $validated['date_from'])
            ->where('date_to', $validated['date_to'])
            ->whereIn('status', ['closed', 'paid'])
            ->first();

        if ($existing) {
            return redirect()
                ->route('reports.affiliate-settlements.show', $existing)
                ->with('status', 'Esta liquidacion ya estaba cerrada.');
        }

        $shipments = Shipment::query()
            ->with(['affiliatedCompany', 'deliveryZone'])
            ->where('affiliated_company_id', $company->id)
            ->where('status', '!=', 'cancelled')
            ->whereDoesntHave('settlementItems')
            ->whereBetween('created_at', [$validated['date_from'].' 00:00:00', $validated['date_to'].' 23:59:59'])
            ->latest()
            ->get();

        if ($shipments->isEmpty()) {
            return back()->withErrors(['affiliated_company_id' => 'No hay guias para cerrar en este periodo.']);
        }

        $settlement = DB::transaction(function () use ($validated, $company, $shipments) {
            $collection = $shipments->where('payment_method', 'cod')->sum('collection_value');
            $commission = $collection * ((float) ($company->cod_commission_percent ?? 0) / 100);
            $settlementNumber = $this->nextSettlementNumber();

            $settlement = AffiliateSettlement::query()->create([
                'tenant_id' => $company->tenant_id,
                'affiliated_company_id' => $company->id,
                'created_by' => Auth::id(),
                'settlement_number' => $settlementNumber,
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'shipments_count' => $shipments->count(),
                'shipping_total' => $shipments->sum('shipping_value'),
                'collection_total' => $collection,
                'commission_total' => $commission,
                'net_collection' => $collection - $commission,
                'total_to_invoice' => $shipments->sum('shipping_value') + $commission,
                'status' => 'closed',
                'notes' => $validated['notes'] ?? null,
                'closed_at' => now(),
            ]);

            foreach ($shipments as $shipment) {
                $commissionValue = $shipment->payment_method === 'cod'
                    ? ((float) $shipment->collection_value * ((float) ($company->cod_commission_percent ?? 0) / 100))
                    : 0;

                AffiliateSettlementItem::query()->create([
                    'affiliate_settlement_id' => $settlement->id,
                    'shipment_id' => $shipment->id,
                    'guide_number' => $shipment->guide_number,
                    'recipient_name' => $shipment->recipient_name,
                    'status' => $shipment->status,
                    'payment_method' => $shipment->payment_method,
                    'delivery_zone_name' => $shipment->deliveryZone?->name,
                    'shipping_value' => $shipment->shipping_value,
                    'collection_value' => $shipment->collection_value,
                    'commission_value' => $commissionValue,
                ]);
            }

            Audit::log('affiliate_settlement.closed', $settlement, "Liquidacion {$settlement->settlement_number} cerrada.");

            return $settlement;
        });

        return redirect()
            ->route('reports.affiliate-settlements.show', $settlement)
            ->with('status', 'Liquidacion cerrada correctamente.');
    }

    public function export(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $data = $this->settlementData($request);
        $fileName = 'liquidacion-afiliadas-'.$data['dateFrom'].'-'.$data['dateTo'].'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'RESUMEN POR AFILIADA',
            ]);

            fputcsv($handle, [
                'Afiliada',
                'Guias',
                'Entregadas',
                'Envios',
                'Recaudo',
                'Comision recaudo',
                'Neto comercio',
                'Total operacion',
            ]);

            foreach ($data['summary'] as $row) {
                fputcsv($handle, [
                    $row['company']?->name ?? 'Sin afiliada',
                    $row['shipments'],
                    $row['delivered'],
                    $row['shipping_total'],
                    $row['collection_total'],
                    $row['commission_total'],
                    $row['net_collection'],
                    $row['total_to_invoice'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'DETALLE DE GUIAS',
            ]);

            fputcsv($handle, [
                'Guia',
                'Afiliada',
                'Destinatario',
                'Tarifa',
                'Estado',
                'Forma de pago',
                'Valor envio',
                'Valor recaudo',
                'Fecha',
            ]);

            foreach ($data['shipments'] as $shipment) {
                fputcsv($handle, [
                    $shipment->guide_number,
                    $shipment->affiliatedCompany?->name ?? 'RCI',
                    $shipment->recipient_name,
                    $shipment->deliveryZone?->name ?? 'Manual',
                    $shipment->status,
                    $shipment->payment_method,
                    $shipment->shipping_value,
                    $shipment->collection_value,
                    $shipment->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSettlement(AffiliateSettlement $settlement)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $settlement->tenant_id !== Auth::user()->tenant_id, 403);

        $settlement->load(['affiliatedCompany', 'creator', 'payer', 'items']);
        $fileName = 'liquidacion-'.$settlement->settlement_number.'.csv';

        return response()->streamDownload(function () use ($settlement) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['LIQUIDACION']);
            fputcsv($handle, ['Consecutivo', $settlement->settlement_number]);
            fputcsv($handle, ['Afiliada', $settlement->affiliatedCompany?->name ?? 'Sin afiliada']);
            fputcsv($handle, ['Periodo', $settlement->date_from->format('Y-m-d').' - '.$settlement->date_to->format('Y-m-d')]);
            fputcsv($handle, ['Estado', $settlement->status === 'paid' ? 'Pagada' : 'Pendiente de pago']);
            fputcsv($handle, ['Cerrada por', $settlement->creator?->name ?? 'Sistema']);
            fputcsv($handle, ['Fecha cierre', $settlement->closed_at?->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Pagada por', $settlement->payer?->name ?? '']);
            fputcsv($handle, ['Fecha pago', $settlement->paid_at?->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Referencia pago', $settlement->payment_reference]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Guias',
                'Envios',
                'Recaudo',
                'Comision',
                'Neto comercio',
                'Total operacion',
            ]);
            fputcsv($handle, [
                $settlement->shipments_count,
                $settlement->shipping_total,
                $settlement->collection_total,
                $settlement->commission_total,
                $settlement->net_collection,
                $settlement->total_to_invoice,
            ]);
            fputcsv($handle, []);

            fputcsv($handle, ['DETALLE DE GUIAS']);
            fputcsv($handle, [
                'Guia',
                'Destinatario',
                'Tarifa',
                'Estado',
                'Forma de pago',
                'Valor envio',
                'Valor recaudo',
                'Comision',
            ]);

            foreach ($settlement->items as $item) {
                fputcsv($handle, [
                    $item->guide_number,
                    $item->recipient_name,
                    $item->delivery_zone_name ?? 'Manual',
                    $item->status,
                    $item->payment_method,
                    $item->shipping_value,
                    $item->collection_value,
                    $item->commission_value,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function printSettlement(AffiliateSettlement $settlement)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $settlement->tenant_id !== Auth::user()->tenant_id, 403);

        $settlement->load(['affiliatedCompany', 'creator', 'payer', 'items']);

        return view('reports.affiliate-settlements.print', compact('settlement'));
    }

    private function settlementData(Request $request): array
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'affiliated_company_id' => ['nullable', 'integer', 'exists:affiliated_companies,id'],
        ]);

        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? today()->toDateString();

        $companies = AffiliatedCompany::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        $shipments = Shipment::query()
            ->with(['affiliatedCompany', 'deliveryZone'])
            ->visibleTo(Auth::user())
            ->whereNotNull('affiliated_company_id')
            ->where('status', '!=', 'cancelled')
            ->whereDoesntHave('settlementItems')
            ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
            ->when($filters['affiliated_company_id'] ?? null, fn ($query, $companyId) => $query->where('affiliated_company_id', $companyId))
            ->latest()
            ->get();

        $summary = $shipments
            ->groupBy('affiliated_company_id')
            ->map(function ($items) {
                $company = $items->first()->affiliatedCompany;
                $collection = $items->where('payment_method', 'cod')->sum('collection_value');
                $commission = $collection * ((float) ($company?->cod_commission_percent ?? 0) / 100);

                return [
                    'company' => $company,
                    'shipments' => $items->count(),
                    'delivered' => $items->where('status', 'delivered')->count(),
                    'shipping_total' => $items->sum('shipping_value'),
                    'collection_total' => $collection,
                    'commission_total' => $commission,
                    'net_collection' => $collection - $commission,
                    'total_to_invoice' => $items->sum('shipping_value') + $commission,
                ];
            })
            ->sortBy(fn ($row) => $row['company']?->name ?? '');

        $totals = [
            'shipments' => $summary->sum('shipments'),
            'shipping_total' => $summary->sum('shipping_total'),
            'collection_total' => $summary->sum('collection_total'),
            'commission_total' => $summary->sum('commission_total'),
            'net_collection' => $summary->sum('net_collection'),
            'total_to_invoice' => $summary->sum('total_to_invoice'),
        ];

        return compact('companies', 'shipments', 'summary', 'totals', 'dateFrom', 'dateTo', 'filters');
    }

    private function settlementHistoryFilters(Request $request): array
    {
        return $request->validate([
            'status' => ['nullable', 'in:closed,paid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'affiliated_company_id' => ['nullable', 'integer', 'exists:affiliated_companies,id'],
        ]);
    }

    private function settlementHistoryQuery()
    {
        return AffiliateSettlement::query()
            ->with(['affiliatedCompany', 'creator', 'payer'])
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id));
    }

    private function applySettlementHistoryFilters($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $dateFrom) => $query->whereDate('date_from', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, $dateTo) => $query->whereDate('date_to', '<=', $dateTo))
            ->when($filters['affiliated_company_id'] ?? null, fn ($query, $companyId) => $query->where('affiliated_company_id', $companyId));
    }

    private function nextSettlementNumber(): string
    {
        $year = now()->format('Y');
        $lastNumber = AffiliateSettlement::query()
            ->where('settlement_number', 'like', "LIQ-{$year}-%")
            ->orderByDesc('id')
            ->value('settlement_number');

        $sequence = $lastNumber && preg_match('/-(\d{6})$/', $lastNumber, $matches)
            ? (int) $matches[1] + 1
            : 1;

        return 'LIQ-'.$year.'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}

