<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        $range = request('range', '7d');
        [$from, $to] = $this->resolveDateRange($range);

        $metrics = $this->metrics();
        $operation = $this->operationData($from, $to);
        $collection = $this->collectionData($from, $to);
        $dailyChart = $this->dailyChartData($from, $to);
        $topTenants = $this->topTenantsData($from, $to);
        $recentClients = Tenant::query()
            ->with(['currentSubscription.plan'])
            ->withCount('shipments')
            ->latest()
            ->take(6)
            ->get();

        $dueSubscriptions = TenantSubscription::query()
            ->with(['tenant', 'plan', 'payments' => fn ($query) => $query->latest()])
            ->whereIn('status', ['active', 'past_due'])
            ->whereNotNull('next_payment_at')
            ->orderBy('next_payment_at')
            ->take(8)
            ->get();

        $planCounts = TenantSubscription::query()
            ->where('status', 'active')
            ->with('plan')
            ->get()
            ->groupBy(fn ($s) => $s->plan?->name ?? 'Sin plan')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $revenue = $this->revenueData();

        $dateRange = [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'range' => $range,
            'label' => $this->rangeLabel($from, $to, $range),
        ];

        return view('admin.dashboard', compact('metrics', 'operation', 'collection', 'dailyChart', 'topTenants', 'recentClients', 'dueSubscriptions', 'planCounts', 'revenue', 'dateRange'));
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

    private function revenueData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

        return [
            'monthly' => $months->map(function ($month) {
                $end = (clone $month)->endOfMonth();
                return [
                    'label' => $month->translatedFormat('M'),
                    'value' => (int) SubscriptionPayment::query()
                        ->where('status', 'paid')
                        ->whereBetween('paid_at', [$month, $end])
                        ->sum('amount'),
                ];
            })->all(),
            'mrr' => TenantSubscription::query()
                ->where('status', 'active')
                ->with('plan')
                ->get()
                ->sum(fn ($s) => (int) ($s->plan?->monthly_price ?? 0)),
            'trial_conversion' => TenantSubscription::query()
                ->where('start_mode', 'trial_guides')
                ->count(),
        ];
    }

    private function operationData($from, $to): array
    {
        $currentIds = TenantSubscription::query()
            ->selectRaw('MAX(id)')
            ->groupBy('tenant_id');

        $activeTenantIds = TenantSubscription::query()
            ->whereIn('id', $currentIds)
            ->where('status', 'active')
            ->pluck('tenant_id');

        $rows = Shipment::query()
            ->selectRaw('status, COUNT(*) as total, SUM(collection_value) as value')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->get();

        $groups = [];
        foreach (Shipment::STATUS_GROUPS as $key => $group) {
            $groups[$key] = ['label' => $group['label'], 'count' => 0, 'value' => 0, 'statuses' => $group['statuses']];
        }

        foreach ($rows as $row) {
            $key = Shipment::statusGroupKey($row->status);
            $groups[$key]['count'] += (int) $row->total;
            $groups[$key]['value'] += (float) $row->value;
        }

        $total = collect($groups)->sum('count');
        $delivered = $groups['delivered']['count'];

        return [
            'groups' => $groups,
            'total' => $total,
            'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100) : 0,
            'delivered' => $delivered,
            'in_transit' => $groups['on_route']['count'],
            'period_count' => Shipment::query()->whereBetween('created_at', [$from, $to])->count(),
            'active_tenants_active_ops' => Shipment::query()
                ->whereIn('status', Shipment::STATUS_GROUPS['on_route']['statuses'])
                ->whereIn('tenant_id', $activeTenantIds)
                ->distinct('tenant_id')
                ->count('tenant_id'),
            'stale' => $this->staleCount(),
        ];
    }

    private function staleCount(): int
    {
        return Shipment::query()
            ->whereIn('status', Shipment::STATUS_GROUPS['on_route']['statuses'])
            ->where('updated_at', '<=', now()->subHours(24))
            ->count();
    }

    private function collectionData($from, $to): array
    {
        return [
            'collected' => Shipment::query()
                ->where('status', 'delivered')
                ->whereBetween('created_at', [$from, $to])
                ->sum('collection_value'),
            'collected_total' => Shipment::query()
                ->where('status', 'delivered')
                ->whereBetween('created_at', [$from, $to])
                ->sum('collection_value'),
            'pending_month' => Shipment::query()
                ->whereIn('status', Shipment::STATUS_GROUPS['on_route']['statuses'])
                ->sum('collection_value'),
            'returned_value' => Shipment::query()
                ->whereIn('status', Shipment::STATUS_GROUPS['returned']['statuses'])
                ->whereBetween('created_at', [$from, $to])
                ->sum('collection_value'),
            'avg_ticket' => round(Shipment::query()
                ->whereNotNull('collection_value')
                ->where('collection_value', '>', 0)
                ->whereBetween('created_at', [$from, $to])
                ->avg('collection_value') ?? 0),
        ];
    }

    private function dailyChartData($from, $to): array
    {
        $days = [];
        $cursor = (clone $from)->startOfDay();

        while ($cursor <= $to) {
            $dateEnd = (clone $cursor)->endOfDay();
            $days[] = [
                'label' => $cursor->translatedFormat('D'),
                'full' => $cursor->translatedFormat('d/m'),
                'count' => Shipment::query()
                    ->whereBetween('created_at', [$cursor, $dateEnd])
                    ->count(),
            ];
            $cursor->addDay();
        }
        $max = collect($days)->max('count');

        return ['days' => $days, 'max' => $max > 0 ? $max : 1, 'total' => collect($days)->sum('count')];
    }

    private function topTenantsData($from, $to): array
    {
        $stats = Shipment::query()
            ->select('tenant_id')
            ->selectRaw('COUNT(*) as month_count')
            ->selectRaw('COALESCE(SUM(collection_value), 0) as month_value')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered_count', ['delivered'])
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('tenant_id')
            ->orderByDesc('month_count')
            ->take(6)
            ->get()
            ->keyBy('tenant_id');

        if ($stats->isEmpty()) {
            return [];
        }

        return Tenant::query()
            ->with('currentSubscription.plan')
            ->whereIn('id', $stats->keys())
            ->get()
            ->sortByDesc(fn ($t) => $stats[$t->id]->month_count)
            ->values()
            ->map(fn ($t) => [
                'name' => $t->name,
                'subdomain' => $t->subdomain,
                'plan' => $t->currentSubscription?->plan?->name ?: 'Sin plan',
                'count' => (int) $stats[$t->id]->month_count,
                'delivered_count' => (int) $stats[$t->id]->delivered_count,
                'value' => (float) $stats[$t->id]->month_value,
                'rate' => $stats[$t->id]->month_count > 0 ? round(($stats[$t->id]->delivered_count / $stats[$t->id]->month_count) * 100) : 0,
            ])->all();
    }

    private function metrics(): array
    {
        $currentIds = TenantSubscription::query()
            ->selectRaw('MAX(id)')
            ->groupBy('tenant_id');

        $monthlyValue = TenantSubscription::query()
            ->whereIn('tenant_subscriptions.id', $currentIds)
            ->where('tenant_subscriptions.status', 'active')
            ->join('subscription_plans', 'subscription_plans.id', '=', 'tenant_subscriptions.subscription_plan_id')
            ->sum('subscription_plans.monthly_price');

        return [
            'clients' => Tenant::query()->count(),
            'active_clients' => Tenant::query()->where('status', 'active')->count(),
            'paid_clients' => TenantSubscription::query()->whereIn('id', $currentIds)->where('status', 'active')->count(),
            'monthly_value' => $monthlyValue,
            'shipments_month' => Shipment::query()->where('created_at', '>=', now()->startOfMonth())->count(),
            'users' => User::query()->where('role', '!=', 'superadmin')->count(),
            'overdue_subscriptions' => TenantSubscription::query()
                ->whereIn('id', $currentIds)
                ->whereIn('status', ['active', 'past_due'])
                ->whereDate('next_payment_at', '<', today())
                ->count(),
            'due_soon_subscriptions' => TenantSubscription::query()
                ->whereIn('id', $currentIds)
                ->whereIn('status', ['active', 'past_due'])
                ->whereBetween('next_payment_at', [today(), today()->addDays(5)])
                ->count(),
        ];
    }
}
