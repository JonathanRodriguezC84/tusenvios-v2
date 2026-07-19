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

        $metrics = $this->metrics();
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

        return view('admin.dashboard', compact('metrics', 'recentClients', 'dueSubscriptions', 'planCounts', 'revenue'));
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
