<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use App\Models\TenantSubscription;
use App\Services\BoldPaymentLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class AdminSubscriptionController extends Controller
{
    public function subscriptions(Request $request): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        $filters = $request->validate([
            'status' => ['nullable', 'in:active,past_due,paused,cancelled'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ]);

        $currentIds = TenantSubscription::query()
            ->selectRaw('MAX(id)')
            ->groupBy('tenant_id');

        $subscriptions = TenantSubscription::query()
            ->with(['tenant' => fn ($q) => $q->withCount(['shipments', 'users']), 'plan', 'payments' => fn ($query) => $query->latest()])
            ->whereIn('id', $currentIds)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['plan_id'] ?? null, fn ($query, $planId) => $query->where('subscription_plan_id', $planId))
            ->orderByRaw('next_payment_at IS NULL')
            ->orderBy('next_payment_at')
            ->paginate(20)
            ->withQueryString();

        $plans = SubscriptionPlan::query()->where('is_active', true)->where('code', 'emprende')->orderBy('monthly_price')->get();

        $subscriptionMetrics = [
            'overdue' => TenantSubscription::query()
                ->whereIn('id', $currentIds)
                ->whereIn('status', ['active', 'past_due'])
                ->whereDate('next_payment_at', '<', today())
                ->count(),
            'due_soon' => TenantSubscription::query()
                ->whereIn('id', $currentIds)
                ->whereIn('status', ['active', 'past_due'])
                ->whereBetween('next_payment_at', [today(), today()->addDays(5)])
                ->count(),
            'active' => TenantSubscription::query()
                ->whereIn('id', $currentIds)
                ->where('status', 'active')
                ->count(),
        ];

        return view('admin.subscriptions', compact('subscriptions', 'plans', 'filters', 'subscriptionMetrics'));
    }

    public function updateSubscription(Request $request, TenantSubscription $subscription): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'subscription_plan_id' => [
                'required',
                'integer',
                Rule::exists('subscription_plans', 'id')
                    ->where('code', 'emprende')
                    ->where('is_active', true),
            ],
            'status' => ['required', 'in:active,past_due,paused,cancelled'],
            'next_payment_at' => ['nullable', 'date'],
            'is_lifetime' => ['boolean'],
        ]);

        if ($request->boolean('is_lifetime')) {
            $validated['next_payment_at'] = null;
        }

        $plan = SubscriptionPlan::find($validated['subscription_plan_id']);
        $isFundador = $plan && $plan->code === 'fundador';

        if ($isFundador || $validated['status'] === 'active') {
            $validated['start_mode'] = 'paid';
            $validated['trial_guide_limit'] = 0;
            $validated['trial_guide_used'] = 0;
        }

        if ($isFundador) {
            $validated['next_payment_at'] = null;
        }

        $subscription->update($validated);

        if ($subscription->tenant) {
            $subscription->tenant->update([
                'status' => $validated['status'] === 'active' ? 'active' : $subscription->tenant->status,
            ]);
        }

        return back()->with('status', 'Suscripcion actualizada.');
    }

    public function registerManualPayment(Request $request, TenantSubscription $subscription): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $subscription->load(['tenant', 'plan']);

        $validated = $request->validate([
            'amount' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = $validated['amount'] ?? $subscription->plan?->monthly_price ?? 0;

        SubscriptionPayment::query()->create([
            'tenant_id' => $subscription->tenant_id,
            'tenant_subscription_id' => $subscription->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'provider' => 'manual',
            'reference' => 'MANUAL-'.$subscription->tenant_id.'-'.now()->format('YmdHis'),
            'status' => 'paid',
            'amount' => $amount,
            'currency' => 'COP',
            'paid_at' => now(),
            'provider_payload' => [
                'notes' => $validated['notes'] ?? null,
                'registered_by' => auth()->id(),
            ],
        ]);

        $baseDate = $subscription->next_payment_at && $subscription->next_payment_at->isFuture()
            ? $subscription->next_payment_at
            : today();

        $subscription->update([
            'status' => 'active',
            'starts_at' => $subscription->starts_at ?: today(),
            'ends_at' => null,
            'next_payment_at' => $baseDate->copy()->addMonth(),
            'notes' => $validated['notes'] ?? $subscription->notes,
        ]);

        if ($subscription->tenant) {
            $subscription->tenant->update(['status' => 'active']);
        }

        return back()->with('status', 'Pago manual registrado y mensualidad extendida.');
    }

    public function createPaymentLink(TenantSubscription $subscription, BoldPaymentLinkService $bold): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $subscription->load(['tenant', 'plan']);

        if (! $subscription->tenant || ! $subscription->plan) {
            return back()->with('status', 'No se pudo generar el link: la suscripcion no tiene cliente o plan asociado.');
        }

        $payment = SubscriptionPayment::query()->create([
            'tenant_id' => $subscription->tenant_id,
            'tenant_subscription_id' => $subscription->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'provider' => 'bold',
            'reference' => 'TE-'.$subscription->tenant_id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
            'status' => 'pending',
            'amount' => $subscription->plan->monthly_price,
            'currency' => 'COP',
        ]);

        try {
            $bold->createLink($payment);
        } catch (Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'provider_payload' => ['error' => $exception->getMessage()],
            ]);

            return back()->with('status', $exception->getMessage());
        }

        return redirect()
            ->route('admin.subscriptions')
            ->with('status', 'Link de pago creado para '.$subscription->tenant->name.'.');
    }

    public function syncPayment(SubscriptionPayment $payment, BoldPaymentLinkService $bold): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        try {
            $bold->syncStatus($payment);
        } catch (Throwable $exception) {
            return back()->with('status', $exception->getMessage());
        }

        return redirect()
            ->route('admin.subscriptions')
            ->with('status', 'Estado del pago actualizado.');
    }

    public function bulkAction(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'action' => ['required', 'in:pause_all,cancel_all'],
            'plan_id' => ['required_if:action,pause_all,cancel_all', 'integer', 'exists:subscription_plans,id'],
        ]);

        $updated = TenantSubscription::query()
            ->where('subscription_plan_id', $validated['plan_id'])
            ->where('status', 'active')
            ->update(['status' => $validated['action'] === 'pause_all' ? 'paused' : 'cancelled']);

        return back()->with('status', "{$updated} suscripciones actualizadas.");
    }
}
