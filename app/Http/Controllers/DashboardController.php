<?php

namespace App\Http\Controllers;

use App\Models\AffiliateSettlement;
use App\Models\InventoryProduct;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): \Illuminate\View\View
    {
        $user = Auth::user();

        $range = request('range', '7d');
        [$from, $to] = $this->resolveDateRange($range);

        $metrics = $this->buildMetrics($user, $from, $to);
        $productFinancials = $this->productFinancials($user, $from, $to);

        $chartShipmentsByDay = $this->chartShipmentsByDay($user, $from, $to);
        $chartFinancialsByDay = $this->chartFinancialsByDay($user, $from, $to);
        $chartTopProducts = $this->chartTopProducts($user, now()->startOfMonth(), now()->endOfDay());
        $chartMonthToDate = $this->chartMonthToDate($user);
        $deliveryRate = $this->deliveryRate($user, $from, $to);
        $operationHealth = ['stale' => $this->staleShipmentsCount($user)];
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

        $dateRange = [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'range' => $range,
            'label' => $this->rangeLabel($from, $to, $range),
        ];

        return view('dashboard', compact(
            'metrics', 'productFinancials', 'alerts', 'dateRange',
            'chartShipmentsByDay', 'chartFinancialsByDay', 'chartTopProducts', 'chartMonthToDate', 'deliveryRate', 'operationHealth', 'inventoryAlerts'
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
            'returned_total' => Shipment::query()->visibleTo($user)->whereIn('status', ['return_pending', 'returned'])->count(),
            'on_route_only' => Shipment::query()->visibleTo($user)->whereIn('status', ['assigned', 'on_route'])->count(),
            'cancelled' => Shipment::query()->visibleTo($user)->where('status', 'cancelled')->count(),
            'total_shipments' => Shipment::query()->visibleTo($user)->count(),
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

    private function chartFinancialsByDay($user, $from = null, $to = null): array
    {
        $from = $from ?? today()->subDays(6);
        $to = $to ?? now()->endOfDay();

        $shipments = Shipment::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->get(['created_at', 'status', 'inventory_snapshot', 'collection_value', 'shipping_value', 'declared_value']);

        $days = (int) ceil($from->startOfDay()->diffInDays($to->endOfDay())) + 1;
        $dailyData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (clone $to)->subDays($i)->startOfDay();
            $dateEnd = (clone $date)->endOfDay();

            $dayShipments = $shipments->filter(function ($s) use ($date, $dateEnd) {
                return $s->created_at >= $date && $s->created_at <= $dateEnd;
            });

            $dayCost = 0;
            $daySales = 0;
            $dayInTransit = 0;
            $dayDelivered = 0;
            $dayReturned = 0;
            $dayCancelled = 0;

            foreach ($dayShipments as $shipment) {
                if ($shipment->status !== 'cancelled') {
                    $snapshot = $shipment->inventory_snapshot ?? [];
                    $shipmentCost = 0;
                    $snapshotSales = 0;

                    if (is_array($snapshot) && count($snapshot) > 0) {
                        foreach ($snapshot as $item) {
                            $quantity = max(1, (int) ($item['quantity'] ?? 1));
                            $shipmentCost += $quantity * (float) ($item['cost'] ?? 0);
                            $snapshotSales += $quantity * (float) ($item['price'] ?? 0);
                        }
                    }

                    $shipmentSales = $snapshotSales > 0 ? $snapshotSales : (float) ($shipment->collection_value ?: ($shipment->declared_value ?: 0));

                    $dayCost += $shipmentCost;
                    $daySales += $shipmentSales;
                }

                if (in_array($shipment->status, ['in_warehouse', 'in_sorting', 'assigned', 'on_route'], true)) {
                    $dayInTransit++;
                } elseif ($shipment->status === 'delivered') {
                    $dayDelivered++;
                } elseif (in_array($shipment->status, ['return_pending', 'returned'], true)) {
                    $dayReturned++;
                } elseif ($shipment->status === 'cancelled') {
                    $dayCancelled++;
                }
            }

            $dailyData[] = [
                'label' => $date->locale('es')->isoFormat('ddd'),
                'full' => $date->format('d/m'),
                'shipments' => $dayShipments->count(),
                'cost' => $dayCost,
                'sales' => $daySales,
                'profit' => $daySales - $dayCost,
                'in_transit' => $dayInTransit,
                'delivered' => $dayDelivered,
                'returned' => $dayReturned,
                'cancelled' => $dayCancelled,
            ];
        }

        $salesValues = array_column($dailyData, 'sales');
        $costValues = array_column($dailyData, 'cost');
        $profitValues = array_column($dailyData, 'profit');
        $inTransitValues = array_column($dailyData, 'in_transit');
        $deliveredValues = array_column($dailyData, 'delivered');
        $returnedValues = array_column($dailyData, 'returned');
        $cancelledValues = array_column($dailyData, 'cancelled');

        [$salesLine, $salesArea, $salesLast] = $this->buildSvgSparkline($salesValues);
        [$costLine, $costArea, $costLast] = $this->buildSvgSparkline($costValues);
        [$profitLine, $profitArea, $profitLast] = $this->buildSvgSparkline($profitValues);
        [$inTransitLine, $inTransitArea, $inTransitLast] = $this->buildSvgSparkline($inTransitValues);
        [$deliveredLine, $deliveredArea, $deliveredLast] = $this->buildSvgSparkline($deliveredValues);
        [$returnedLine, $returnedArea, $returnedLast] = $this->buildSvgSparkline($returnedValues);
        [$cancelledLine, $cancelledArea, $cancelledLast] = $this->buildSvgSparkline($cancelledValues);

        return [
            'days' => $dailyData,
            'sales_line' => $salesLine,
            'sales_area' => $salesArea,
            'sales_last' => $salesLast,
            'cost_line' => $costLine,
            'cost_area' => $costArea,
            'cost_last' => $costLast,
            'profit_line' => $profitLine,
            'profit_area' => $profitArea,
            'profit_last' => $profitLast,
            'in_transit_line' => $inTransitLine,
            'in_transit_area' => $inTransitArea,
            'in_transit_last' => $inTransitLast,
            'delivered_line' => $deliveredLine,
            'delivered_area' => $deliveredArea,
            'delivered_last' => $deliveredLast,
            'returned_line' => $returnedLine,
            'returned_area' => $returnedArea,
            'returned_last' => $returnedLast,
            'cancelled_line' => $cancelledLine,
            'cancelled_area' => $cancelledArea,
            'cancelled_last' => $cancelledLast,
        ];
    }

    private function buildSvgSparkline(array $values): array
    {
        $n = count($values);
        if ($n === 0) {
            return ['M 0 16 L 100 16', 'M 0 16 L 100 16 L 100 32 L 0 32 Z', ['x' => 100, 'y' => 16]];
        }

        $minVal = min($values);
        $maxVal = max($values);

        if ($maxVal == 0 && $minVal == 0) {
            return ['M 0 28 L 100 28', 'M 0 28 L 100 28 L 100 32 L 0 32 Z', ['x' => 100, 'y' => 28]];
        }

        $range = ($maxVal - $minVal) ?: 1;

        $points = [];
        for ($i = 0; $i < $n; $i++) {
            $x = $n > 1 ? round(($i / ($n - 1)) * 100, 1) : 50;
            $y = round(28 - (($values[$i] - $minVal) / $range) * 24, 1);
            $points[] = ['x' => $x, 'y' => $y];
        }

        if ($n === 1) {
            $line = "M 0 {$points[0]['y']} L 100 {$points[0]['y']}";
            $area = "{$line} L 100 32 L 0 32 Z";
            return [$line, $area, ['x' => 100, 'y' => $points[0]['y']]];
        }

        // Catmull-Rom to Cubic Bézier para curvatura suave y natural
        $line = "M {$points[0]['x']} {$points[0]['y']}";
        for ($i = 0; $i < $n - 1; $i++) {
            $p0 = $points[max($i - 1, 0)];
            $p1 = $points[$i];
            $p2 = $points[$i + 1];
            $p3 = $points[min($i + 2, $n - 1)];

            $cp1x = round($p1['x'] + ($p2['x'] - $p0['x']) / 6, 1);
            $cp1y = round(max(3, min(29, $p1['y'] + ($p2['y'] - $p0['y']) / 6)), 1);

            $cp2x = round($p2['x'] - ($p3['x'] - $p1['x']) / 6, 1);
            $cp2y = round(max(3, min(29, $p2['y'] - ($p3['y'] - $p1['y']) / 6)), 1);

            $line .= " C {$cp1x} {$cp1y}, {$cp2x} {$cp2y}, {$p2['x']} {$p2['y']}";
        }

        $area = "{$line} L 100 32 L 0 32 Z";
        $last = end($points);

        return [$line, $area, $last];
    }

    /**
     * Buckets que agrupan los 12 estados internos en los 3 grupos que el plan
     * Emprende muestra (En camino, Entregada, Devuelta). Los colores se asignan
     * en el orden --viz-cat-1..3 de resources/css/app.css.
     */
    private function chartTopProducts($user, $from = null, $to = null): array
    {
        $products = [];

        Shipment::query()
            ->visibleTo($user)
            ->where('status', '!=', 'cancelled')
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->latest()
            ->take(1000)
            ->get(['inventory_snapshot', 'content_description'])
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
        $totalUnits = array_sum($top);

        $circumference = 226.2; // 2 * pi * 36
        $offset = 0;
        $result = [];
        foreach ($top as $name => $count) {
            $share = $totalUnits > 0 ? ($count / $totalUnits) : 0;
            $dashLength = round($share * $circumference, 2);
            $dashRemainder = round($circumference - $dashLength, 2);
            $sharePct = round($share * 100, 1);

            $result[] = [
                'name' => $name,
                'count' => $count,
                'pct' => round(($count / $max) * 100),
                'share_pct' => $sharePct,
                'dash_array' => "{$dashLength} {$dashRemainder}",
                'dash_offset' => round(-$offset, 2),
            ];
            $offset += $dashLength;
        }

        return $result;
    }

    private function chartMonthToDate($user): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfDay();
        $totalDays = (int) now()->daysInMonth;
        $elapsedDays = (int) min(now()->day, $totalDays);

        $prevMonthStart = (clone $start)->subMonth()->startOfMonth();
        $prevMonthSameDayEnd = (clone $prevMonthStart)->day(min($elapsedDays, (int) (clone $prevMonthStart)->daysInMonth))->endOfDay();
        $prevMonthEnd = (clone $prevMonthStart)->endOfMonth();

        $shipments = Shipment::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'collection_value', 'shipping_value', 'declared_value', 'inventory_snapshot', 'status']);

        $prevMonthShipments = Shipment::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->get(['created_at', 'collection_value', 'shipping_value', 'declared_value', 'inventory_snapshot', 'status']);

        $created = 0;
        $revenue = 0;
        foreach ($shipments as $shipment) {
            $created++;
            if ($shipment->status === 'cancelled') continue;

            $snapSales = 0;
            $snap = $shipment->inventory_snapshot ?? [];
            if (is_array($snap) && count($snap) > 0) {
                foreach ($snap as $item) {
                    $snapSales += max(1, (int) ($item['quantity'] ?? 1)) * (float) ($item['price'] ?? 0);
                }
            }
            $revenue += $snapSales > 0 ? $snapSales : (float) ($shipment->collection_value ?: ($shipment->declared_value ?: 0));
        }

        $prevMonthTotalRevenue = 0;
        $prevMonthSamePeriodRevenue = 0;
        foreach ($prevMonthShipments as $s) {
            if ($s->status === 'cancelled') continue;
            $snapSales = 0;
            $snap = $s->inventory_snapshot ?? [];
            if (is_array($snap) && count($snap) > 0) {
                foreach ($snap as $item) {
                    $snapSales += max(1, (int) ($item['quantity'] ?? 1)) * (float) ($item['price'] ?? 0);
                }
            }
            $val = $snapSales > 0 ? $snapSales : (float) ($s->collection_value ?: ($s->declared_value ?: 0));
            $prevMonthTotalRevenue += $val;
            if ($s->created_at <= $prevMonthSameDayEnd) {
                $prevMonthSamePeriodRevenue += $val;
            }
        }

        $expectedRevenue = $totalDays > 0 ? round($revenue / max($elapsedDays, 1) * $totalDays) : 0;
        $expectedShipments = $totalDays > 0 ? round($created / max($elapsedDays, 1) * $totalDays) : 0;

        // Histograma de días del mes
        $monthDays = [];
        $maxDayCount = 1;

        for ($d = 1; $d <= $totalDays; $d++) {
            $dayDate = (clone $start)->day($d)->startOfDay();
            $dayEnd = (clone $dayDate)->endOfDay();
            $isPast = $d < $elapsedDays;
            $isToday = $d === $elapsedDays;
            $isFuture = $d > $elapsedDays;

            if ($isPast || $isToday) {
                $dayShipments = $shipments->filter(function ($s) use ($dayDate, $dayEnd) {
                    return $s->created_at >= $dayDate && $s->created_at <= $dayEnd;
                });
                $cnt = $dayShipments->count();
                $rev = 0;
                foreach ($dayShipments as $s) {
                    if ($s->status === 'cancelled') continue;
                    $snapSales = 0;
                    $snap = $s->inventory_snapshot ?? [];
                    if (is_array($snap) && count($snap) > 0) {
                        foreach ($snap as $item) {
                            $snapSales += max(1, (int) ($item['quantity'] ?? 1)) * (float) ($item['price'] ?? 0);
                        }
                    }
                    $rev += $snapSales > 0 ? $snapSales : (float) ($s->collection_value ?: ($s->declared_value ?: 0));
                }
            } else {
                $cnt = 0;
                $rev = 0;
            }

            if ($cnt > $maxDayCount) $maxDayCount = $cnt;

            $monthDays[] = [
                'day' => $d,
                'date' => $dayDate->format('d/m'),
                'is_past' => $isPast,
                'is_today' => $isToday,
                'is_future' => $isFuture,
                'count' => $cnt,
                'revenue' => $rev,
            ];
        }

        // Agrupación ejecutiva en 4 semanas para visualización limpia
        $weeks = [
            ['label' => 'Sem 1', 'start' => 1, 'end' => 7, 'count' => 0, 'is_current' => false, 'is_future' => false],
            ['label' => 'Sem 2', 'start' => 8, 'end' => 14, 'count' => 0, 'is_current' => false, 'is_future' => false],
            ['label' => 'Sem 3', 'start' => 15, 'end' => 21, 'count' => 0, 'is_current' => false, 'is_future' => false],
            ['label' => 'Sem 4', 'start' => 22, 'end' => $totalDays, 'count' => 0, 'is_current' => false, 'is_future' => false],
        ];

        $maxWeekCount = 1;

        foreach ($weeks as &$w) {
            if ($elapsedDays >= $w['start'] && $elapsedDays <= $w['end']) {
                $w['is_current'] = true;
            } elseif ($elapsedDays < $w['start']) {
                $w['is_future'] = true;
            }

            if (!$w['is_future']) {
                $wStart = (clone $start)->day($w['start'])->startOfDay();
                $wEnd = (clone $start)->day(min($elapsedDays, $w['end']))->endOfDay();
                $w['count'] = $shipments->filter(function ($s) use ($wStart, $wEnd) {
                    return $s->created_at >= $wStart && $s->created_at <= $wEnd;
                })->count();
                if ($w['count'] > $maxWeekCount) $maxWeekCount = $w['count'];
            } else {
                $w['count'] = 0;
            }
        }
        unset($w);

        $benchmarkTarget = $prevMonthTotalRevenue > 0 ? $prevMonthTotalRevenue : $expectedRevenue;
        $revenuePct = $benchmarkTarget > 0 ? round(($revenue / $benchmarkTarget) * 100) : 0;
        $growthVsPrevMonth = $prevMonthSamePeriodRevenue > 0
            ? round((($revenue - $prevMonthSamePeriodRevenue) / $prevMonthSamePeriodRevenue) * 100, 1)
            : null;

        return [
            'created' => $created,
            'revenue' => $revenue,
            'month' => now()->locale('es')->isoFormat('MMMM YYYY'),
            'elapsed_days' => $elapsedDays,
            'total_days' => $totalDays,
            'expected_revenue' => $expectedRevenue,
            'expected_shipments' => $expectedShipments,
            'prev_month_revenue' => $prevMonthTotalRevenue,
            'growth_vs_prev_month' => $growthVsPrevMonth,
            'target_revenue' => $benchmarkTarget,
            'gauge_pct' => min(100, $revenuePct),
            'real_pct' => $revenuePct,
            'weeks' => $weeks,
            'max_week_count' => $maxWeekCount,
            'timeline' => $monthDays,
            'max_day_count' => $maxDayCount,
        ];
    }

    private function deliveryRate($user, $from = null, $to = null): array
    {
        $from = $from ?? today()->subDays(6)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $deliveredCount = Shipment::query()->visibleTo($user)
            ->where('status', 'delivered')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $totalCreated = Shipment::query()->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $cohortDelivered = Shipment::query()->visibleTo($user)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $rate = $totalCreated > 0 ? round(($cohortDelivered / $totalCreated) * 100, 1) : 0;

        $previousFrom = (clone $from)->subDays($from->diffInDays($to));
        $previousTo = (clone $from)->subSecond();

        $deliveredCountPrev = Shipment::query()->visibleTo($user)
            ->where('status', 'delivered')
            ->whereBetween('updated_at', [$previousFrom, $previousTo])
            ->count();

        return [
            'total' => $totalCreated,
            'delivered' => $deliveredCount,
            'cohort_delivered' => $cohortDelivered,
            'rate' => $rate,
            'previousDelivered' => $deliveredCountPrev,
            'rateDelta' => round($deliveredCount - $deliveredCountPrev, 1),
        ];
    }

    private function staleShipmentsCount($user): int
    {
        return Shipment::query()
            ->visibleTo($user)
            ->whereIn('status', ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route', 'failed_delivery', 'rescheduled', 'return_pending'])
            ->where('updated_at', '<=', now()->subDay())
            ->count();
    }

    private function productFinancials($user, $from = null, $to = null): array
    {
        $from = $from ?? today()->subDays(6)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $cost = 0;
        $sales = 0;
        $units = 0;
        $ordersCount = 0;

        Shipment::query()
            ->visibleTo($user)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->take(1000)
            ->get(['inventory_snapshot', 'collection_value', 'shipping_value', 'declared_value'])
            ->each(function ($shipment) use (&$cost, &$sales, &$units, &$ordersCount) {
                $ordersCount++;
                $snapshot = $shipment->inventory_snapshot ?? [];
                $shipmentCost = 0;
                $snapshotSales = 0;
                $shipmentUnits = 0;

                if (is_array($snapshot) && count($snapshot) > 0) {
                    foreach ($snapshot as $item) {
                        $quantity = max(1, (int) ($item['quantity'] ?? 1));
                        $shipmentUnits += $quantity;
                        $shipmentCost += $quantity * (float) ($item['cost'] ?? 0);
                        $snapshotSales += $quantity * (float) ($item['price'] ?? 0);
                    }
                } else {
                    $shipmentUnits = 1;
                }

                $shipmentSales = $snapshotSales > 0 ? $snapshotSales : (float) ($shipment->collection_value ?: ($shipment->declared_value ?: 0));

                $cost += $shipmentCost;
                $sales += $shipmentSales;
                $units += $shipmentUnits;
            });

        return [
            'cost' => $cost,
            'sales' => $sales,
            'profit' => $sales - $cost,
            'units' => $units,
            'orders' => $ordersCount,
            'margin' => $sales > 0 ? round((($sales - $cost) / $sales) * 100, 1) : 0,
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

}
