<?php

namespace App\Http\Controllers;

use App\Models\AffiliateSettlement;
use App\Models\InventoryProduct;
use App\Models\QuickProduct;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $range = request('range', '7d');
        [$from, $to] = $this->resolveDateRange($range);

        $metrics = $this->buildMetrics($user, $from, $to);
        $onboarding = $this->onboardingFor($user);
        $professionalScore = $this->professionalScore($user);

        $chartShipmentsByDay = $this->chartShipmentsByDay($user, $from, $to);
        $chartStatusDistribution = $this->chartStatusDistribution($user, $from, $to);
        $chartTopProducts = $this->chartTopProducts($user, $from, $to);
        $productSuggestions = $this->productSuggestions($user, $chartTopProducts);
        $chartRevenueByDay = $this->chartRevenueByDay($user, $from, $to);
        $chartMonthlyTrend = $this->chartMonthlyTrend($user);
        $deliveryRate = $this->deliveryRate($user, $from, $to);
        $operationHealth = $this->operationHealth($user, $metrics, $deliveryRate);
        $smartActions = $this->smartActions($user, $metrics, $operationHealth);
        $workdaySummary = $this->workdaySummary($metrics, $operationHealth);
        $todayPriority = $this->todayPriority($user, $metrics, $operationHealth);
        $moneySummary = $this->moneySummary($user, $metrics, $from, $to);
        $growthActions = $this->growthActions($user, $metrics, $professionalScore, $productSuggestions, $deliveryRate);
        $inventoryAlerts = $this->inventoryAlerts($user);
        $recentAudit = $this->recentAudit($user);

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
        $dashboardReportText = $this->dashboardReportText($dateRange, $metrics, $todayPriority, $moneySummary, $operationHealth, $deliveryRate);

        return view('dashboard', compact(
            'metrics', 'onboarding', 'professionalScore', 'alerts', 'trialGuideCounter', 'dateRange',
            'chartShipmentsByDay', 'chartStatusDistribution', 'chartTopProducts', 'productSuggestions', 'chartRevenueByDay',
            'chartMonthlyTrend', 'deliveryRate', 'operationHealth', 'smartActions', 'workdaySummary', 'todayPriority', 'moneySummary', 'dashboardReportText', 'growthActions', 'inventoryAlerts', 'recentAudit'
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

    private function chartStatusDistribution($user, $from = null, $to = null): array
    {
        $statuses = ['created', 'printed', 'in_warehouse', 'on_route', 'delivered', 'failed_delivery', 'cancelled'];
        $labels = [
            'created' => ['label' => 'Por imprimir', 'color' => '#6b7280'],
            'printed' => ['label' => 'Impresa', 'color' => '#9ca3af'],
            'in_warehouse' => ['label' => 'En bodega', 'color' => '#f59e0b'],
            'on_route' => ['label' => 'En camino', 'color' => '#3b82f6'],
            'delivered' => ['label' => 'Entregadas', 'color' => '#10b981'],
            'failed_delivery' => ['label' => 'Novedad', 'color' => '#ef4444'],
            'cancelled' => ['label' => 'Canceladas', 'color' => '#d1d5db'],
        ];

        $total = 0;
        $items = [];

        foreach ($statuses as $status) {
            $count = Shipment::query()->visibleTo($user)->where('status', $status)
                ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
                ->count();
            $items[$status] = $count;
            $total += $count;
        }

        $total = $total ?: 1;
        $segments = [];
        $angle = 0;

        $order = ['delivered', 'on_route', 'in_warehouse', 'created', 'printed', 'failed_delivery', 'cancelled'];

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

    private function productSuggestions($user, array $topProducts): array
    {
        if ($user->canUseInventory() || empty($topProducts)) {
            return ['show' => false, 'items' => [], 'ready_count' => 0, 'repeated_count' => 0];
        }

        $existing = QuickProduct::query()
            ->when(
                $user->role === 'affiliate' && $user->affiliated_company_id,
                fn ($q) => $q->where('affiliated_company_id', $user->affiliated_company_id),
                fn ($q) => $q->where('tenant_id', $user->tenant_id)->whereNull('affiliated_company_id')
            )
            ->pluck('name')
            ->map(fn ($name) => $this->normalizeProductName((string) $name))
            ->filter()
            ->values()
            ->all();

        $repeatedProducts = collect($topProducts)
            ->filter(fn (array $product) => ($product['count'] ?? 0) >= 2);

        $items = collect($topProducts)
            ->filter(fn (array $product) => ($product['count'] ?? 0) >= 2)
            ->reject(fn (array $product) => in_array($this->normalizeProductName((string) $product['name']), $existing, true))
            ->take(3)
            ->map(fn (array $product) => [
                'name' => $product['name'],
                'count' => $product['count'],
                'route' => route('quick-products.index', [
                    'name' => $product['name'],
                    'package_type' => 'merchandise',
                ]),
            ])
            ->values()
            ->all();

        return [
            'show' => count($items) > 0 || $repeatedProducts->isNotEmpty(),
            'items' => $items,
            'ready_count' => $repeatedProducts->count() - count($items),
            'repeated_count' => $repeatedProducts->count(),
        ];
    }

    private function normalizeProductName(string $name): string
    {
        $name = trim(strtolower($name));
        $name = preg_replace('/\s+/', ' ', $name);

        return $name ?: '';
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

    private function operationHealth($user, array $metrics, array $deliveryRate): array
    {
        $stale = Shipment::query()
            ->visibleTo($user)
            ->whereIn('status', ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route', 'failed_delivery', 'rescheduled', 'return_pending'])
            ->where('updated_at', '<=', now()->subDay())
            ->count();

        $active = Shipment::query()
            ->visibleTo($user)
            ->whereIn('status', ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route', 'failed_delivery', 'rescheduled', 'return_pending'])
            ->count();

        $riskPoints = ($metrics['issues'] * 18) + ($metrics['return_pending'] * 14) + ($stale * 10) + ($metrics['pending_print'] * 4);
        $score = max(0, min(100, 100 - $riskPoints));

        if ($active === 0 && $metrics['shipments_today'] === 0) {
            $score = 96;
        }

        $tone = $score >= 85 ? 'emerald' : ($score >= 65 ? 'blue' : ($score >= 45 ? 'amber' : 'red'));
        $label = $score >= 85 ? 'Operacion sana' : ($score >= 65 ? 'Buen ritmo' : ($score >= 45 ? 'Requiere atencion' : 'Prioridad alta'));

        return [
            'score' => $score,
            'label' => $label,
            'tone' => $tone,
            'active' => $active,
            'stale' => $stale,
            'pendingWork' => $metrics['pending_print'] + $metrics['warehouse'] + $metrics['issues'] + $metrics['return_pending'],
            'deliveryRate' => $deliveryRate['rate'],
        ];
    }

    private function smartActions($user, array $metrics, array $operationHealth): array
    {
        return array_values(array_filter([
            $metrics['issues'] > 0 ? [
                'label' => 'Resolver novedades',
                'description' => "{$metrics['issues']} guia(s) necesitan confirmacion o reprogramacion.",
                'route' => route('daily-tasks.index'),
                'tone' => 'red',
            ] : null,
            $metrics['pending_print'] > 0 ? [
                'label' => 'Imprimir pendientes',
                'description' => "{$metrics['pending_print']} guia(s) creadas todavia no tienen etiqueta.",
                'route' => route('daily-tasks.index'),
                'tone' => 'blue',
            ] : null,
            $operationHealth['stale'] > 0 ? [
                'label' => 'Revisar guias quietas',
                'description' => "{$operationHealth['stale']} guia(s) llevan mas de 24 horas sin movimiento.",
                'route' => route('daily-tasks.index'),
                'tone' => 'amber',
            ] : null,
            $user->canCreateShipments() ? [
                'label' => 'Crear nueva guia',
                'description' => 'Registra el proximo envio y manten el flujo andando.',
                'route' => route('shipments.create'),
                'tone' => 'emerald',
            ] : null,
            [
                'label' => 'Ver Tareas Diarias',
                'description' => 'Revisa el orden recomendado para trabajar hoy.',
                'route' => route('daily-tasks.index'),
                'tone' => 'slate',
            ],
        ]));
    }

    private function workdaySummary(array $metrics, array $operationHealth): array
    {
        if ($metrics['issues'] > 0 || $metrics['return_pending'] > 0) {
            return [
                'tone' => 'red',
                'label' => 'Prioridad del dia',
                'title' => 'Empieza resolviendo novedades',
                'description' => 'Hay guias que pueden afectar la experiencia del cliente. Atiendelas antes de crear nuevos envios.',
                'progress' => max(12, min(100, 100 - (($metrics['issues'] + $metrics['return_pending']) * 18))),
            ];
        }

        if ($metrics['pending_print'] > 0 || $metrics['warehouse'] > 0 || $operationHealth['stale'] > 0) {
            return [
                'tone' => 'blue',
                'label' => 'Trabajo operativo',
                'title' => 'Avanza las guias que ya estan en proceso',
                'description' => 'Imprime, prepara o revisa las guias quietas para que el dia no se acumule.',
                'progress' => max(18, min(100, 100 - (($metrics['pending_print'] + $metrics['warehouse'] + $operationHealth['stale']) * 8))),
            ];
        }

        return [
            'tone' => 'emerald',
            'label' => 'Dia despejado',
            'title' => 'Tu operacion esta lista para crecer',
            'description' => 'No tienes pendientes operativos. Puedes crear una nueva guia, preparar productos o mejorar tu marca.',
            'progress' => 100,
        ];
    }

    private function todayPriority($user, array $metrics, array $operationHealth): array
    {
        if ($metrics['issues'] > 0 || $metrics['return_pending'] > 0) {
            $count = $metrics['issues'] + $metrics['return_pending'];

            return [
                'tone' => 'red',
                'label' => 'Prioridad alta',
                'title' => 'Resolver novedades antes de vender mas',
                'description' => 'Estas guias pueden generar reclamos, devoluciones o clientes sin respuesta.',
                'metric' => $count,
                'metricLabel' => 'caso(s) critico(s)',
                'route' => route('daily-tasks.index').'#novedades',
                'action' => 'Resolver ahora',
                'steps' => ['Llamar o escribir al cliente', 'Confirmar datos de entrega', 'Actualizar el estado de la guia'],
            ];
        }

        if ($metrics['pending_print'] > 0) {
            return [
                'tone' => 'blue',
                'label' => 'Preparacion',
                'title' => 'Imprimir guias creadas',
                'description' => 'Cada guia sin etiqueta retrasa preparacion, despacho y seguimiento.',
                'metric' => $metrics['pending_print'],
                'metricLabel' => 'por imprimir',
                'route' => route('shipments.index', ['status' => 'created']),
                'action' => 'Ver guias',
                'steps' => ['Revisar datos del destinatario', 'Imprimir etiquetas pendientes', 'Pasar cada guia a preparacion'],
            ];
        }

        if ($operationHealth['stale'] > 0) {
            return [
                'tone' => 'amber',
                'label' => 'Seguimiento',
                'title' => 'Revisar guias quietas',
                'description' => 'Una guia sin movimiento por mas de 24 horas se percibe como falta de control.',
                'metric' => $operationHealth['stale'],
                'metricLabel' => 'sin movimiento',
                'route' => route('daily-tasks.index').'#tareas',
                'action' => 'Revisar',
                'steps' => ['Abrir guias sin movimiento', 'Confirmar siguiente paso', 'Actualizar estado o novedad'],
            ];
        }

        if ($metrics['warehouse'] > 0 || $metrics['in_transit'] > 0) {
            return [
                'tone' => 'emerald',
                'label' => 'Buen ritmo',
                'title' => 'Cerrar entregas en proceso',
                'description' => 'Tu operacion esta avanzando. El mejor impacto ahora es cerrar estados y mantener al cliente informado.',
                'metric' => $metrics['warehouse'] + $metrics['in_transit'],
                'metricLabel' => 'en proceso',
                'route' => route('daily-tasks.index').'#plan-dia',
                'action' => 'Continuar',
                'steps' => ['Actualizar preparacion o ruta', 'Compartir seguimiento si aplica', 'Cerrar entregas o novedades'],
            ];
        }

        return [
            'tone' => 'emerald',
            'label' => 'Dia despejado',
            'title' => 'Crear la siguiente venta',
            'description' => 'No hay bloqueos importantes. Es buen momento para crear una guia o preparar productos frecuentes.',
            'metric' => $metrics['shipments_today'],
            'metricLabel' => 'guias del periodo',
            'route' => $user->canCreateShipments() ? route('shipments.create') : route('daily-tasks.index').'#oportunidades',
            'action' => $user->canCreateShipments() ? 'Crear guia' : 'Ver oportunidades',
            'steps' => ['Registrar nuevo pedido', 'Usar productos frecuentes', 'Compartir seguimiento profesional'],
        ];
    }

    private function dashboardReportText(array $dateRange, array $metrics, array $todayPriority, array $moneySummary, array $operationHealth, array $deliveryRate): string
    {
        return implode(PHP_EOL, [
            'Reporte ejecutivo - Tus Envios',
            'Periodo: '.$dateRange['label'],
            'Prioridad: '.$todayPriority['title'].' ('.$todayPriority['metric'].' '.$todayPriority['metricLabel'].')',
            'Salud operativa: '.$operationHealth['score'].'/100 - '.$operationHealth['label'],
            'Guias creadas: '.$metrics['shipments_today'],
            'Entregadas: '.$metrics['delivered_today'].' | Tasa de entrega: '.$deliveryRate['rate'].'%',
            'Pendientes por imprimir: '.$metrics['pending_print'],
            'Novedades: '.$metrics['issues'].' | Devoluciones pendientes: '.$metrics['return_pending'],
            'Sin movimiento: '.$operationHealth['stale'],
            'Dinero creado: $'.number_format($moneySummary['createdValue'], 0, ',', '.'),
            'Entregado: $'.number_format($moneySummary['deliveredValue'], 0, ',', '.'),
            'Dinero a vigilar: $'.number_format($moneySummary['moneyToWatch'], 0, ',', '.'),
            '',
            'Plan sugerido:',
            '1. '.($todayPriority['steps'][0] ?? 'Revisar Tareas Diarias'),
            '2. '.($todayPriority['steps'][1] ?? 'Actualizar guias pendientes'),
            '3. '.($todayPriority['steps'][2] ?? 'Cerrar el dia con estados al dia'),
        ]);
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

        $cancelledValue = Shipment::query()
            ->visibleTo($user)
            ->where('status', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
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
            'cancelledValue' => (float) $cancelledValue,
            'pendingSettlementValue' => (float) $metrics['affiliate_pending_value'] + (float) $metrics['settlements_pending_payment_value'],
            'moneyToWatch' => $moneyToWatch,
        ];
    }

    private function growthActions($user, array $metrics, array $professionalScore, array $productSuggestions, array $deliveryRate): array
    {
        if ($user->isSuperAdmin()) {
            return [];
        }

        $actions = [];
        $pendingProfessionalStep = collect($professionalScore['steps'] ?? [])
            ->first(fn (array $step) => ! ($step['done'] ?? false));

        if ($pendingProfessionalStep) {
            $actions[] = [
                'label' => 'Sube la confianza de tu marca',
                'description' => $pendingProfessionalStep['description'],
                'metric' => "{$professionalScore['score']}%",
                'metric_label' => 'profesionalismo',
                'route' => $pendingProfessionalStep['route'],
                'action' => $pendingProfessionalStep['action'],
                'tone' => $professionalScore['score'] >= 60 ? 'blue' : 'amber',
            ];
        }

        if (! empty($productSuggestions['items'])) {
            $first = $productSuggestions['items'][0];
            $actions[] = [
                'label' => 'Ahorra tiempo con productos rapidos',
                'description' => "{$first['name']} aparece varias veces. Guardalo para crear guias mas rapido.",
                'metric' => (string) $first['count'],
                'metric_label' => 'repeticiones',
                'route' => $first['route'],
                'action' => 'Guardar',
                'tone' => 'blue',
            ];
        } elseif (($productSuggestions['ready_count'] ?? 0) > 0) {
            $actions[] = [
                'label' => 'Tus productos frecuentes estan listos',
                'description' => 'Usalos al crear guias para reducir escritura y errores.',
                'metric' => (string) $productSuggestions['ready_count'],
                'metric_label' => 'listos',
                'route' => $user->canCreateShipments() ? route('shipments.create') : route('quick-products.index'),
                'action' => $user->canCreateShipments() ? 'Crear guia' : 'Ver productos',
                'tone' => 'emerald',
            ];
        }

        if ($metrics['shipments_today'] === 0 && $user->canCreateShipments()) {
            $actions[] = [
                'label' => 'Registra tu primer envio del periodo',
                'description' => 'Mantener el flujo actualizado hace que tus clientes vean una operacion mas profesional.',
                'metric' => '0',
                'metric_label' => 'guias',
                'route' => route('shipments.create'),
                'action' => 'Crear',
                'tone' => 'amber',
            ];
        } elseif ($deliveryRate['total'] > 0 && $deliveryRate['rate'] < 80) {
            $actions[] = [
                'label' => 'Mejora tu tasa de entrega',
                'description' => 'Revisa novedades y guias en camino para cerrar mas entregas.',
                'metric' => "{$deliveryRate['rate']}%",
                'metric_label' => 'entrega',
                'route' => route('daily-tasks.index'),
                'action' => 'Revisar',
                'tone' => 'red',
            ];
        } else {
            $actions[] = [
                'label' => 'Comparte seguimiento con tus clientes',
                'description' => 'El enlace de seguimiento refuerza confianza y reduce preguntas por WhatsApp.',
                'metric' => (string) max(0, $metrics['in_transit']),
                'metric_label' => 'en proceso',
                'route' => route('shipments.index'),
                'action' => 'Ver guias',
                'tone' => 'blue',
            ];
        }

        return array_slice($actions, 0, 3);
    }

    private function inventoryAlerts($user): array
    {
        if (! $user->canUseInventory()) return [];

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

    private function recentAudit($user): array
    {
        return DB::table('audit_logs')
            ->where('user_id', $user->id)
            ->latest()->take(5)->get()
            ->map(fn ($log) => [
                'action' => $log->action,
                'description' => $log->description ?? $log->action,
                'date' => $log->created_at,
            ])->toArray();
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

    private function professionalScore($user): array
    {
        if ($user->isSuperAdmin()) {
            return ['show' => false, 'score' => 0, 'label' => '', 'steps' => [], 'completed' => 0, 'total' => 0];
        }

        $brandOwner = $user->role === 'affiliate' && $user->affiliatedCompany
            ? $user->affiliatedCompany : $user->tenant;

        if (! $brandOwner) {
            return ['show' => false, 'score' => 0, 'label' => '', 'steps' => [], 'completed' => 0, 'total' => 0];
        }

        $quickProductsCount = QuickProduct::query()
            ->when(
                $user->role === 'affiliate' && $user->affiliated_company_id,
                fn ($q) => $q->where('affiliated_company_id', $user->affiliated_company_id),
                fn ($q) => $q->where('tenant_id', $user->tenant_id)->whereNull('affiliated_company_id')
            )->count();

        $inventoryCount = InventoryProduct::query()
            ->when(
                $user->role === 'affiliate' && $user->affiliated_company_id,
                fn ($q) => $q->where('affiliated_company_id', $user->affiliated_company_id),
                fn ($q) => $q->where('tenant_id', $user->tenant_id)->whereNull('affiliated_company_id')
            )->count();

        $shipmentsCount = Shipment::query()->visibleTo($user)->count();

        $steps = [
            [
                'label' => 'Logo de marca',
                'description' => 'Haz que etiquetas y seguimiento se vean como tu negocio.',
                'done' => filled($brandOwner->logo_path),
                'route' => route('brand-settings.edit'),
                'action' => 'Subir logo',
            ],
            [
                'label' => 'Color y presencia',
                'description' => 'Usa un color, redes o sitio web para reforzar tu imagen.',
                'done' => filled($brandOwner->brand_color) || filled($brandOwner->brand_instagram) || filled($brandOwner->brand_website),
                'route' => route('brand-settings.edit'),
                'action' => 'Configurar marca',
            ],
            [
                'label' => 'Contacto visible',
                'description' => 'Permite que tus clientes sepan como contactarte.',
                'done' => filled($brandOwner->brand_whatsapp) || filled($brandOwner->brand_phone) || filled($brandOwner->phone),
                'route' => route('brand-settings.edit'),
                'action' => 'Agregar contacto',
            ],
            [
                'label' => 'Mensaje de marca',
                'description' => 'Cierra la experiencia con una nota propia para tus clientes.',
                'done' => filled($brandOwner->brand_message),
                'route' => route('brand-settings.edit'),
                'action' => 'Crear mensaje',
            ],
            [
                'label' => $user->canUseInventory() ? 'Productos en inventario' : 'Productos rapidos',
                'description' => 'Acelera la creacion de guias y evita escribir lo mismo.',
                'done' => $user->canUseInventory() ? $inventoryCount > 0 : $quickProductsCount > 0,
                'route' => $user->canUseInventory() ? route('inventory.index') : route('quick-products.index'),
                'action' => 'Agregar productos',
            ],
            [
                'label' => 'Primera guia creada',
                'description' => 'Activa el flujo real de envio y seguimiento publico.',
                'done' => $shipmentsCount > 0,
                'route' => route('shipments.create'),
                'action' => 'Crear guia',
            ],
            [
                'label' => 'Seguimiento listo',
                'description' => 'Ya puedes compartir enlaces de seguimiento con tus clientes.',
                'done' => $shipmentsCount > 0,
                'route' => route('shipments.index'),
                'action' => 'Ver guias',
            ],
        ];

        $completed = collect($steps)->where('done', true)->count();
        $total = count($steps);
        $score = (int) round(($completed / max(1, $total)) * 100);

        $label = match (true) {
            $score >= 85 => 'Marca lista para vender',
            $score >= 60 => 'Buena base profesional',
            $score >= 35 => 'En construccion',
            default => 'Primeros ajustes',
        };

        return [
            'show' => true,
            'score' => $score,
            'label' => $label,
            'steps' => $steps,
            'completed' => $completed,
            'total' => $total,
        ];
    }
}
