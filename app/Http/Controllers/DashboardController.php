<?php

namespace App\Http\Controllers;

use App\Models\AffiliateSettlement;
use App\Models\InventoryProduct;
use App\Models\QuickProduct;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $range = request('range', '7d');
        [$from, $to] = $this->resolveDateRange($range);

        $metrics = $this->buildMetrics($user, $from, $to);
        $onboarding = $this->onboardingFor($user);

        $chartShipmentsByDay = $this->chartShipmentsByDay($user, $from, $to);
        $chartStatusDistribution = $this->chartStatusDistribution($user);
        $chartTopProducts = $this->chartTopProducts($user, $from, $to);
        $chartRevenueByDay = $this->chartRevenueByDay($user, $from, $to);
        $chartMonthlyTrend = $this->chartMonthlyTrend($user);
        $deliveryRate = $this->deliveryRate($user, $from, $to);
        $operationHealth = ['stale' => $this->staleShipmentsCount($user)];
        $moneySummary = $this->moneySummary($user, $metrics, $from, $to);
        $inventoryAlerts = $this->inventoryAlerts($user);

        $alerts = array_filter([
            $metrics['issues'] > 0 ? [
                'icon' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
                'label' => 'Con novedad',
                'count' => $metrics['issues'],
                'color' => 'text-red-600',
                'bg' => 'bg-red-50',
                'route' => route('shipments.index', ['status' => 'failed_delivery']),
            ] : null,
            $metrics['return_pending'] > 0 ? [
                'icon' => 'M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3',
                'label' => 'Devoluciones pendientes',
                'count' => $metrics['return_pending'],
                'color' => 'text-orange-600',
                'bg' => 'bg-orange-50',
                'route' => route('shipments.index', ['status' => 'return_pending']),
            ] : null,
        ]);

        $trialGuideCounter = null;
        if (! $user->isSuperAdmin()) {
            $trialTenant = $user->tenant ?: $user->affiliatedCompany?->tenant;
            $trialSub = $trialTenant?->currentSubscription;
            if ($trialSub && $trialSub->isTrial()) {
                $trialGuideCounter = [
                    'remaining' => $trialSub->trialGuidesRemaining(),
                    'total' => (int) $trialSub->trial_guide_limit,
                ];
            }
        }

        $dateRange = [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'range' => $range,
            'label' => $this->rangeLabel($from, $to, $range),
        ];

        return view('dashboard', compact(
            'metrics', 'onboarding', 'alerts', 'trialGuideCounter', 'dateRange',
            'chartShipmentsByDay', 'chartStatusDistribution', 'chartTopProducts', 'chartRevenueByDay',
            'chartMonthlyTrend', 'deliveryRate', 'operationHealth', 'moneySummary', 'inventoryAlerts'
        ));
    }

    private function rangeLabel($from, $to, $range): string
    {
        return match ($range) {
            'today' => 'Hoy',
            '7d' => 'Ultimos 7 dias',
            '30d' => 'Ultimos 30 dias',
            '90d' => 'Ultimos 90 dias',
            default => $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y'),
        };
    }

    private function resolveDateRange(string $range): array
    {
        $to = now()->endOfDay();

        return match ($range) {
            'today' => [today()->startOfDay(), $to],
            '30d' => [today()->subDays(29)->startOfDay(), $to],
            '90d' => [today()->subDays(89)->startOfDay(), $to],
            'custom' => [
                request('from') ? \Carbon\Carbon::parse(request('from'))->startOfDay() : today()->subDays(6)->startOfDay(),
                request('to') ? \Carbon\Carbon::parse(request('to'))->endOfDay() : $to,
            ],
            default => [today()->subDays(6)->startOfDay(), $to],
        };
    }

    private function buildMetrics($user, $from = null, $to = null): array
    {
        $from = $from ?? today()->subDays(6);
        $to = $to ?? now()->endOfDay();
        $previousFrom = (clone $from)->subDays($from->diffInDays($to));
        $previousTo = (clone $from)->subSecond();

        $createdInRange = Shipment::query()->visibleTo($user)->whereBetween('created_at', [$from, $to])->count();
        $createdPrevious = Shipment::query()->visibleTo($user)->whereBetween('created_at', [$previousFrom, $previousTo])->count();
        $deliveredInRange = Shipment::query()->visibleTo($user)->where('status', 'delivered')->whereBetween('updated_at', [$from, $to])->get();

        return [
            'shipments_today' => $createdInRange,
            'shipments_yesterday' => $createdPrevious,
            'delta' => $createdPrevious > 0 ? $createdInRange - $createdPrevious : $createdInRange,
            'pending_print' => Shipment::query()->visibleTo($user)->where('status', 'created')->count(),
            'in_transit' => Shipment::query()->visibleTo($user)->whereIn('status', ['in_warehouse', 'in_sorting', 'assigned', 'on_route'])->count(),
            'revenue_today' => $deliveredInRange->sum('collection_value') + $deliveredInRange->sum('shipping_value'),
            'delivered_today' => $deliveredInRange->count(),
            'issues' => Shipment::query()->visibleTo($user)->whereIn('status', ['failed_delivery', 'rescheduled'])->count(),
            'warehouse' => Shipment::query()->visibleTo($user)->whereIn('status', ['printed', 'in_warehouse', 'in_sorting'])->count(),
            'return_pending' => Shipment::query()->visibleTo($user)->where('status', 'return_pending')->count(),
            'collection_today' => $deliveredInRange->sum('collection_value'),
            'collection_open' => Shipment::query()
                ->visibleTo($user)
                ->where('payment_method', 'cod')
                ->whereNotIn('status', ['delivered', 'returned', 'cancelled'])
                ->sum('collection_value'),
            'affiliate_pending_settlement' => Shipment::query()
                ->visibleTo($user)
                ->whereNotNull('affiliated_company_id')
                ->where('status', '!=', 'cancelled')
                ->whereDoesntHave('settlementItems')
                ->count(),
            'affiliate_pending_value' => Shipment::query()
                ->visibleTo($user)
                ->whereNotNull('affiliated_company_id')
                ->where('status', '!=', 'cancelled')
                ->whereDoesntHave('settlementItems')
                ->sum('shipping_value'),
            'settlements_pending_payment' => AffiliateSettlement::query()
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('tenant_id', $user->tenant_id))
                ->where('status', 'closed')
                ->count(),
            'settlements_pending_payment_value' => AffiliateSettlement::query()
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('tenant_id', $user->tenant_id))
                ->where('status', 'closed')
                ->sum('total_to_invoice'),
        ];
    }

    private function chartShipmentsByDay($user, $from = null, $to = null): array
    {
        $from = $from ?? today()->subDays(6);
        $to = $to ?? now()->endOfDay();
        $data = [];
        $max = 0;

        $days = (int) ceil($from->startOfDay()->diffInDays($to->endOfDay())) + 1;
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (clone $to)->subDays($i)->startOfDay();
            $dateEnd = (clone $date)->endOfDay();
            $count = Shipment::query()
                ->visibleTo($user)
                ->whereBetween('created_at', [$date, $dateEnd])
                ->count();
            $data[] = [
                'label' => $date->locale('es')->isoFormat('ddd'),
                'full' => $date->format('d/m'),
                'count' => $count,
            ];
            if ($count > $max) $max = $count;
        }

        return ['days' => $data, 'max' => $max ?: 1];
    }

    private function chartStatusDistribution($user): array
    {
        $labels = [
            'created' => ['label' => 'Por imprimir', 'color' => '#6b7280'],
            'printed' => ['label' => 'Impresa', 'color' => '#9ca3af'],
            'in_warehouse' => ['label' => 'En bodega', 'color' => '#f59e0b'],
            'in_sorting' => ['label' => 'En clasificacion', 'color' => '#fb923c'],
            'assigned' => ['label' => 'Asignada', 'color' => '#6366f1'],
            'on_route' => ['label' => 'En camino', 'color' => '#3b82f6'],
            'delivered' => ['label' => 'Entregadas', 'color' => '#10b981'],
            'failed_delivery' => ['label' => 'Novedad', 'color' => '#ef4444'],
            'rescheduled' => ['label' => 'Reprogramada', 'color' => '#eab308'],
            'return_pending' => ['label' => 'Por devolver', 'color' => '#fb7185'],
            'returned' => ['label' => 'Devuelta', 'color' => '#a78bfa'],
            'cancelled' => ['label' => 'Canceladas', 'color' => '#d1d5db'],
        ];

        $counts = Shipment::query()
            ->visibleTo($user)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = 0;
        $items = [];

        foreach ($labels as $status => $meta) {
            $count = (int) ($counts[$status] ?? 0);
            $items[$status] = $count;
            $total += $count;
        }

        $total = $total ?: 1;
        $segments = [];
        $angle = 0;

        $order = ['delivered', 'on_route', 'assigned', 'in_warehouse', 'in_sorting', 'created', 'printed', 'failed_delivery', 'rescheduled', 'return_pending', 'returned', 'cancelled'];

        foreach ($order as $status) {
            $pct = round(($items[$status] / $total) * 100, 1);
            if ($pct < 0.1) continue;
            $deg = round(($pct / 100) * 360);
            $segments[] = [
                'status' => $status,
                'label' => $labels[$status]['label'],
                'color' => $labels[$status]['color'],
                'count' => $items[$status],
                'pct' => $pct,
                'deg' => $deg,
                'start' => $angle,
                'end' => $angle + $deg,
            ];
            $angle += $deg;
        }

        return ['segments' => $segments, 'total' => $total];
    }

    private function chartTopProducts($user, $from = null, $to = null): array
    {
        $products = [];

        Shipment::query()
            ->visibleTo($user)
            ->where('status', '!=', 'cancelled')
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->latest()
            ->take(200)
            ->get()
            ->each(function ($shipment) use (&$products) {
                $snapshot = $shipment->inventory_snapshot;
                if (is_array($snapshot) && count($snapshot) > 0) {
                    foreach ($snapshot as $item) {
                        $name = trim((string) ($item['name'] ?? $item['product_name'] ?? ''));
                        if ($name === '') continue;
                        $products[$name] = ($products[$name] ?? 0) + (int) ($item['quantity'] ?? 1);
                    }
                    return;
                }

                $desc = $shipment->content_description;
                if (blank($desc)) return;

                $items = preg_split('/\s*\+\s*/', $desc);
                foreach (array_filter($items) as $item) {
                    $item = trim($item);
                    if ($item === '') continue;
                    $cleaned = preg_replace('/[\s\x{00A0}]*-[\s\x{00A0}]*\$?[\s\x{00A0}]*[\d\.,]+(?:[\s\x{00A0}]*COP)?[\s\x{00A0}]*$/iu', '', $item);
                    $cleaned = preg_replace('/[\s\x{00A0}]*X[\s\x{00A0}]*\d+\b/iu', '', $cleaned);
                    $cleaned = trim($cleaned) ?: 'Producto';
                    $products[$cleaned] = ($products[$cleaned] ?? 0) + 1;
                }
            });

        arsort($products);
        $top = array_slice($products, 0, 5);
        $max = !empty($top) ? max($top) : 1;

        $result = [];
        foreach ($top as $name => $count) {
            $result[] = ['name' => $name, 'count' => $count, 'pct' => round(($count / $max) * 100)];
        }

        return $result;
    }

    private function chartRevenueByDay($user, $from = null, $to = null): array
    {
        $from = $from ?? today()->subDays(6);
        $to = $to ?? now()->endOfDay();
        $data = [];
        $max = 0;

        $days = (int) ceil($from->startOfDay()->diffInDays($to->endOfDay())) + 1;
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (clone $to)->subDays($i)->startOfDay();
            $dateEnd = (clone $date)->endOfDay();
            $deliveries = Shipment::query()
                ->visibleTo($user)
                ->where('status', 'delivered')
                ->whereBetween('updated_at', [$date, $dateEnd])
                ->get();
            $revenue = $deliveries->sum('collection_value') + $deliveries->sum('shipping_value');
            $data[] = [
                'label' => $date->locale('es')->isoFormat('ddd'),
                'full' => $date->format('d/m'),
                'revenue' => $revenue,
            ];
            if ($revenue > $max) $max = $revenue;
        }

        return ['days' => $data, 'max' => $max ?: 1];
    }

    private function chartMonthlyTrend($user): array
    {
        $data = [];
        $months = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = ['label' => $date->locale('es')->isoFormat('MMM'), 'year' => $date->format('Y'), 'start' => $date->startOfMonth(), 'end' => $date->endOfMonth()];
        }

        $max = 0;
        foreach ($months as $m) {
            $count = Shipment::query()->visibleTo($user)
                ->whereBetween('created_at', [$m['start'], $m['end']])
                ->count();
            $data[] = ['label' => $m['label'] . ' ' . $m['year'], 'count' => $count];
            if ($count > $max) $max = $count;
        }

        return ['months' => $data, 'max' => $max ?: 1];
    }

    private function deliveryRate($user, $from = null, $to = null): array
    {
        $from = $from ?? today()->subDays(6);
        $to = $to ?? now()->endOfDay();
        $total = Shipment::query()->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $delivered = Shipment::query()->visibleTo($user)->where('status', 'delivered')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $rate = $total > 0 ? round(($delivered / $total) * 100, 1) : 0;

        return ['total' => $total, 'delivered' => $delivered, 'rate' => $rate];
    }

    private function staleShipmentsCount($user): int
    {
        return Shipment::query()
            ->visibleTo($user)
            ->whereIn('status', ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route', 'failed_delivery', 'rescheduled', 'return_pending'])
            ->where('updated_at', '<=', now()->subDay())
            ->count();
    }

    private function moneySummary($user, array $metrics, $from, $to): array
    {
        $createdValue = Shipment::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COALESCE(SUM(collection_value), 0) + COALESCE(SUM(shipping_value), 0) as total')
            ->value('total') ?? 0;

        $issueValue = Shipment::query()
            ->visibleTo($user)
            ->whereIn('status', ['failed_delivery', 'rescheduled', 'return_pending'])
            ->selectRaw('COALESCE(SUM(collection_value), 0) + COALESCE(SUM(shipping_value), 0) as total')
            ->value('total') ?? 0;

        $moneyToWatch = (float) $metrics['collection_open']
            + (float) $metrics['affiliate_pending_value']
            + (float) $metrics['settlements_pending_payment_value']
            + (float) $issueValue;

        $tone = $moneyToWatch > 0 ? 'amber' : ((float) $metrics['revenue_today'] > 0 ? 'emerald' : 'blue');
        $label = $moneyToWatch > 0
            ? 'Dinero por vigilar'
            : ((float) $metrics['revenue_today'] > 0 ? 'Flujo saludable' : 'Sin movimientos de dinero');

        return [
            'tone' => $tone,
            'label' => $label,
            'createdValue' => (float) $createdValue,
            'deliveredValue' => (float) $metrics['revenue_today'],
            'collectionOpen' => (float) $metrics['collection_open'],
            'issueValue' => (float) $issueValue,
            'moneyToWatch' => $moneyToWatch,
        ];
    }

    private function inventoryAlerts($user): array
    {
        if (! $user->canUseInventory()) return ['low' => [], 'out' => []];

        $low = InventoryProduct::query()
            ->where('status', 'active')
            ->whereColumn('stock', '<=', 'stock_minimum')
            ->where('stock', '>', 0)
            ->when($user->tenant_id, fn ($q) => $q->where('tenant_id', $user->tenant_id))
            ->orderBy('stock')->orderBy('name')->take(6)->get();

        $out = InventoryProduct::query()
            ->where('status', 'active')->where('stock', '<=', 0)
            ->when($user->tenant_id, fn ($q) => $q->where('tenant_id', $user->tenant_id))
            ->orderBy('name')->take(3)->get();

        return ['low' => $low, 'out' => $out];
    }

    private function onboardingFor($user): array
    {
        if ($user->isSuperAdmin()) return ['show' => false, 'steps' => [], 'completed' => 0, 'total' => 0];

        $brandOwner = $user->role === 'affiliate' && $user->affiliatedCompany
            ? $user->affiliatedCompany : $user->tenant;

        if (! $brandOwner) return ['show' => false, 'steps' => [], 'completed' => 0, 'total' => 0];

        $hasBrand = filled($brandOwner->logo_path) || filled($brandOwner->brand_whatsapp)
            || filled($brandOwner->brand_instagram) || filled($brandOwner->brand_website);

        $quickProductsCount = QuickProduct::query()
            ->when(
                $user->role === 'affiliate' && $user->affiliated_company_id,
                fn ($q) => $q->where('affiliated_company_id', $user->affiliated_company_id),
                fn ($q) => $q->where('tenant_id', $user->tenant_id)->whereNull('affiliated_company_id')
            )->count();

        $shipmentsCount = Shipment::query()->visibleTo($user)->count();

        $steps = [
            ['label' => 'Personaliza tu marca', 'description' => 'Logo, WhatsApp y redes para tus etiquetas.', 'route' => route('brand-settings.edit'), 'action' => 'Configurar', 'done' => $hasBrand],
        ];

        if (! $user->canUseInventory()) {
            $steps[] = ['label' => 'Crea productos rapidos', 'description' => 'Los productos que mas envias, para crear guias mas rapido.', 'route' => route('quick-products.index'), 'action' => 'Agregar', 'done' => $quickProductsCount > 0];
        } else {
            $inventoryCount = InventoryProduct::query()
                ->when($user->role === 'affiliate' && $user->affiliated_company_id,
                    fn ($q) => $q->where('affiliated_company_id', $user->affiliated_company_id),
                    fn ($q) => $q->where('tenant_id', $user->tenant_id)->whereNull('affiliated_company_id')
                )->count();
            $steps[] = ['label' => 'Agrega productos al inventario', 'description' => 'Controla tu stock y prepara envios mas rapido.', 'route' => route('inventory.index'), 'action' => 'Agregar', 'done' => $inventoryCount > 0];
        }

        $steps[] = ['label' => 'Crea tu primera guia', 'description' => 'Registra un envio y prueba la impresion.', 'route' => route('shipments.create'), 'action' => 'Crear guia', 'done' => $shipmentsCount > 0];

        $completed = collect($steps)->where('done', true)->count();

        return ['show' => $completed < count($steps), 'steps' => $steps, 'completed' => $completed, 'total' => count($steps)];
    }

}
