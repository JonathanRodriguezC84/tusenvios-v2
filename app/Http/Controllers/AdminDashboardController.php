<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Services\BoldPaymentLinkService;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

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

    public function clients(Request $request)
    {
        $this->authorizeAdmin();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,paused,suspended,cancelled'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ]);

        $clients = $this->clientQuery($filters)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $plans = SubscriptionPlan::query()->where('is_active', true)->where('code', 'emprende')->orderBy('monthly_price')->get();

        return view('admin.clients', compact('clients', 'plans', 'filters'));
    }

    public function subscriptions(Request $request)
    {
        $this->authorizeAdmin();

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

    public function plans()
    {
        $this->authorizeAdmin();

        $plans = SubscriptionPlan::query()
            ->withCount('subscriptions')
            ->where('code', 'emprende')
            ->orderBy('monthly_price')
            ->get();

        return view('admin.plans', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'in:emprende', 'unique:subscription_plans,code'],
            'name' => ['required', 'string', 'max:120'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        SubscriptionPlan::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'monthly_price' => (float) $validated['monthly_price'],
            'features' => $validated['features'] ?? [],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('status', 'Plan creado correctamente.');
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'in:emprende', Rule::unique('subscription_plans', 'code')->ignore($plan->id)],
            'name' => ['required', 'string', 'max:120'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $plan->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'monthly_price' => (float) $validated['monthly_price'],
            'features' => $validated['features'] ?? [],
            'is_active' => true,
        ]);

        return back()->with('status', 'Plan actualizado correctamente.');
    }

    public function destroyPlan(SubscriptionPlan $plan)
    {
        $this->authorizeAdmin();

        if ($plan->code === 'emprende') {
            return back()->with('status', 'El plan Emprende es el plan unico y no se puede eliminar.');
        }

        if ($plan->subscriptions()->exists()) {
            return back()->with('status', 'No se puede eliminar: el plan tiene suscripciones asociadas.');
        }

        $plan->delete();

        return back()->with('status', 'Plan eliminado correctamente.');
    }

    public function activity(Request $request)
    {
        $this->authorizeAdmin();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $shipments = Shipment::query()
            ->with(['tenant'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('guide_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$search}%"));
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['tenant_id'] ?? null, fn ($query, $tenantId) => $query->where('tenant_id', $tenantId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        $statusLabels = [
            'created' => 'Por imprimir', 'printed' => 'Impresa', 'in_warehouse' => 'Preparando',
            'in_sorting' => 'Preparando', 'assigned' => 'Asignada', 'on_route' => 'En camino',
            'delivered' => 'Entregada', 'failed_delivery' => 'Novedad', 'rescheduled' => 'Reprogramada',
            'return_pending' => 'Devuelve', 'returned' => 'Devuelta', 'cancelled' => 'Cancelada',
        ];

        return view('admin.activity', compact('shipments', 'filters', 'statusLabels', 'tenants'));
    }

    public function exportActivity(Request $request)
    {
        $this->authorizeAdmin();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $fileName = 'actividad-admin-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Guia', 'Cliente', 'Destinatario', 'Telefono', 'Direccion', 'Estado', 'Zona', 'Recaudo', 'Fecha']);

            Shipment::query()
                ->with(['tenant'])
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($q) use ($search) {
                    $q->where('guide_number', 'like', "%{$search}%")
                      ->orWhere('recipient_name', 'like', "%{$search}%")
                      ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$search}%"));
                }))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
                ->latest()
                ->chunk(200, function ($shipments) use ($handle) {
                    foreach ($shipments as $s) {
                        fputcsv($handle, [
                            $s->guide_number,
                            $s->tenant?->name,
                            $s->recipient_name . ' ' . $s->recipient_lastname,
                            $s->recipient_phone,
                            $s->recipient_address,
                            $s->status,
                            $s->zone,
                            $s->collection_value,
                            $s->created_at->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function createPaymentLink(TenantSubscription $subscription, BoldPaymentLinkService $bold)
    {
        $this->authorizeAdmin();

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

    public function syncPayment(SubscriptionPayment $payment, BoldPaymentLinkService $bold)
    {
        $this->authorizeAdmin();

        try {
            $bold->syncStatus($payment);
        } catch (Throwable $exception) {
            return back()->with('status', $exception->getMessage());
        }

        return redirect()
            ->route('admin.subscriptions')
            ->with('status', 'Estado del pago actualizado.');
    }
    public function showClient(Tenant $tenant)
    {
        $this->authorizeAdmin();

        $tenant->loadCount(['shipments', 'users']);
        $tenant->load(['currentSubscription.plan', 'subscriptions' => fn ($q) => $q->latest()->limit(5)]);
        $plans = SubscriptionPlan::orderBy('monthly_price')->get();

        return view('admin.client-detail', compact('tenant', 'plans'));
    }

    public function generateToken(Tenant $tenant)
    {
        $this->authorizeAdmin();

        $plainToken = 'te_' . Str::random(48);
        $tenant->update(['api_token' => hash('sha256', $plainToken)]);

        return redirect()->route('admin.clients.show', $tenant)->with('new_token', $plainToken);
    }

    public function updateWebhook(Request $request, Tenant $tenant)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'webhook_url' => ['nullable', 'url', 'max:500'],
            'webhook_events' => ['nullable', 'array'],
            'webhook_events.*' => ['in:delivered,failed_delivery,cancelled,on_route,returned'],
        ]);

        $tenant->update([
            'webhook_url' => $validated['webhook_url'] ?? null,
            'webhook_events' => $validated['webhook_events'] ?? null,
        ]);

        return back()->with('status', 'Webhook actualizado.');
    }

    public function walletTransaction(Request $request, Tenant $tenant)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'type' => ['required', 'in:deposit,withdrawal,adjustment'],
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (int) $validated['amount'];
        if ($validated['type'] !== 'deposit') {
            $amount = -abs($amount);
        }

        $tenant->balance += $amount;
        $tenant->save();

        \App\Models\WalletTransaction::create([
            'tenant_id' => $tenant->id,
            'type' => $validated['type'],
            'amount' => $amount,
            'balance_after' => $tenant->balance,
            'notes' => $validated['notes'] ?? null,
            'reference' => 'ADM-' . now()->format('YmdHis'),
            'created_by' => Auth::id(),
        ]);

        return back()->with('status', 'Billetera actualizada. Saldo: $' . number_format($tenant->balance, 0, ',', '.'));
    }

    public function createClient()
    {
        $this->authorizeAdmin();
        $plans = SubscriptionPlan::where('is_active', true)->where('code', 'emprende')->orderBy('monthly_price')->get();

        return view('admin.client-create', compact('plans'));
    }

    public function storeClient(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email'],
            'phone' => ['nullable', 'string', 'max:80'],
            'subdomain' => ['required', 'string', 'max:100', 'unique:tenants,subdomain', 'regex:/^[a-z0-9-]+$/'],
            'guide_prefix' => ['nullable', 'string', 'max:10'],
            'plan_id' => [
                'required',
                Rule::exists('subscription_plans', 'id')
                    ->where('code', 'emprende')
                    ->where('is_active', true),
            ],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subdomain' => $validated['subdomain'],
            'guide_prefix' => $validated['guide_prefix'] ?? null,
            'status' => 'active',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'start_mode' => 'paid',
            'starts_at' => now(),
            'next_payment_at' => now()->addMonth(),
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'role' => 'tenant_admin',
        ]);

        return redirect()->route('admin.clients.show', $tenant)->with('status', 'Cliente creado correctamente.');
    }

    public function updateClientStatus(Request $request, Tenant $tenant)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'status' => ['required', 'in:active,paused,suspended,cancelled'],
        ]);

        $tenant->update(['status' => $validated['status']]);

        return back()->with('status', 'Estado del cliente actualizado.');
    }

    public function updateSubscription(Request $request, TenantSubscription $subscription)
    {
        $this->authorizeAdmin();

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

    public function registerManualPayment(Request $request, TenantSubscription $subscription)
    {
        $this->authorizeAdmin();

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
                'registered_by' => Auth::id(),
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
    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
    }

    private function clientQuery(array $filters = [])
    {
        return Tenant::query()
            ->with(['currentSubscription.plan'])
            ->withCount(['shipments', 'users'])
            ->withMax('shipments', 'created_at')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('subdomain', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['plan_id'] ?? null, function ($query, $planId) {
                $query->whereHas('currentSubscription', fn ($subscription) => $subscription->where('subscription_plan_id', $planId));
            });
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

    // ─── #1: USERS ────────────────────────────────────────────

    public function users(Request $request)
    {
        $this->authorizeAdmin();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'in:superadmin,tenant_admin,affiliate,warehouse,courier'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $users = User::query()
            ->with('tenant')
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->when($filters['role'] ?? null, fn ($q, $r) => $q->where('role', $r))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $roleLabels = ['superadmin' => 'Superadmin', 'tenant_admin' => 'Admin', 'affiliate' => 'Afiliado', 'warehouse' => 'Bodega', 'courier' => 'Mensajero'];

        return view('admin.users', compact('users', 'filters', 'roleLabels'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $validated = $request->validate(['role' => ['required', 'in:superadmin,tenant_admin,affiliate,warehouse,courier']]);
        $user->update($validated);

        return back()->with('status', 'Rol actualizado.');
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $validated = $request->validate(['status' => ['required', 'in:active,inactive']]);
        $user->update($validated);

        return back()->with('status', 'Estado actualizado.');
    }

    // ─── #3: IMPERSONATE ──────────────────────────────────────

    public function impersonate(User $user)
    {
        $this->authorizeAdmin();
        abort_if($user->isSuperAdmin(), 403, 'No puedes impersonar a otro superadmin.');

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Viendo como '.$user->name);
    }

    // ─── #5: BULK ─────────────────────────────────────────────

    public function bulkAction(Request $request)
    {
        $this->authorizeAdmin();

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

    // ─── #6: SETTINGS ─────────────────────────────────────────

    public function systemSettings()
    {
        $this->authorizeAdmin();

        return view('admin.settings');
    }

    public function updateSystemSettings(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'trial_guide_limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (isset($validated['trial_guide_limit'])) {
            \Illuminate\Support\Facades\Cache::forever('system:trial_guide_limit', (int) $validated['trial_guide_limit']);
        }

        return back()->with('status', 'Configuracion guardada.');
    }

    // ─── TRANSPORTADORAS ──────────────────────────────────────

    public function carriers()
    {
        $this->authorizeAdmin();

        $carriers = config('shipping.carriers', []);
        $rawKey = config('services.carrier_api.key', env('CARRIER_API_KEY', ''));
        $apiKey = $rawKey ? substr($rawKey, 0, 4) . str_repeat('•', max(0, strlen($rawKey) - 4)) : '';
        $baseUrl = config('app.url', url('/'));

        return view('admin.carriers', compact('carriers', 'apiKey', 'rawKey', 'baseUrl'));
    }

    public function apiDocs()
    {
        $this->authorizeAdmin();

        return view('admin.api-docs');
    }

    public function whatsapp()
    {
        $this->authorizeAdmin();

        return view('admin.whatsapp');
    }

    public function profile()
    {
        $this->authorizeAdmin();

        return view('admin.profile');
    }

    public function updatePassword(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Auth::user()->update(['password' => bcrypt($validated['password'])]);

        return back()->with('status', 'Contrasena actualizada correctamente.');
    }
}
