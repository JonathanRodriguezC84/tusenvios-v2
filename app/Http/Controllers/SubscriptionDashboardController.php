<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionDashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $filters = $request->validate([
            'status' => ['nullable', 'in:active,past_due,paused,cancelled'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'due' => ['nullable', 'in:overdue,due_soon,no_date'],
        ]);

        $currentSubscriptionIds = TenantSubscription::query()
            ->selectRaw('MAX(tenant_subscriptions.id)')
            ->groupBy('tenant_id');

        $baseQuery = TenantSubscription::query()
            ->with(['tenant', 'plan'])
            ->whereIn('tenant_subscriptions.id', $currentSubscriptionIds);

        $metrics = [
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'past_due' => (clone $baseQuery)->where('status', 'past_due')->count(),
            'due_soon' => (clone $baseQuery)
                ->whereIn('status', ['active', 'past_due'])
                ->whereBetween('next_payment_at', [today(), today()->addDays(7)])
                ->count(),
            'overdue' => (clone $baseQuery)
                ->whereIn('status', ['active', 'past_due'])
                ->whereDate('next_payment_at', '<', today())
                ->count(),
            'monthly_value' => (clone $baseQuery)
                ->where('status', 'active')
                ->join('subscription_plans', 'tenant_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
                ->sum('subscription_plans.monthly_price'),
        ];

        $subscriptions = (clone $baseQuery)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['plan_id'] ?? null, fn ($query, $planId) => $query->where('subscription_plan_id', $planId))
            ->when(($filters['due'] ?? null) === 'overdue', fn ($query) => $query->whereDate('next_payment_at', '<', today()))
            ->when(($filters['due'] ?? null) === 'due_soon', fn ($query) => $query->whereBetween('next_payment_at', [today(), today()->addDays(7)]))
            ->when(($filters['due'] ?? null) === 'no_date', fn ($query) => $query->whereNull('next_payment_at'))
            ->orderByRaw('next_payment_at IS NULL')
            ->orderBy('next_payment_at')
            ->paginate(20)
            ->withQueryString();

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->where('code', 'emprende')
            ->orderBy('monthly_price')
            ->get();

        return view('subscriptions.index', compact('subscriptions', 'plans', 'metrics', 'filters'));
    }

    public function markAsPaid(TenantSubscription $subscription)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $baseDate = $subscription->next_payment_at && $subscription->next_payment_at->isFuture()
            ? $subscription->next_payment_at
            : today();

        $subscription->update([
            'status' => 'active',
            'starts_at' => $subscription->starts_at ?: today(),
            'ends_at' => null,
            'next_payment_at' => $baseDate->copy()->addMonth(),
        ]);

        Audit::log('subscription.paid', $subscription, "Pago registrado para {$subscription->tenant->name}.");

        return redirect()
            ->route('subscriptions.index')
            ->with('status', 'Pago registrado correctamente.');
    }
}
