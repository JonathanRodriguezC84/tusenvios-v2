<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\Audit;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::query()
            ->with('currentSubscription.plan')
            ->withCount(['affiliatedCompanies', 'shipments'])
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->paginate(15);

        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageTenants(), 403);

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->where('code', 'emprende')
            ->orderBy('monthly_price')
            ->get();

        return view('tenants.create', compact('plans'));
    }

    public function edit(Tenant $tenant)
    {
        abort_unless(Auth::user()->canManageTenants(), 403);

        $tenant->load('currentSubscription.plan');
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->where('code', 'emprende')
            ->orderBy('monthly_price')
            ->get();

        return view('tenants.edit', compact('tenant', 'plans'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageTenants(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subdomain' => ['nullable', 'string', 'max:80', 'unique:tenants,subdomain'],
            'guide_prefix' => ['nullable', 'string', 'size:3'],
            'status' => ['required', 'string', 'max:50'],
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'subscription_status' => ['required', 'in:active,past_due,paused,cancelled'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'next_payment_at' => ['nullable', 'date'],
            'subscription_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $subscription = [
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'status' => $validated['subscription_status'],
            'starts_at' => $validated['starts_at'] ?? now()->toDateString(),
            'ends_at' => $validated['ends_at'] ?? null,
            'next_payment_at' => $validated['next_payment_at'] ?? now()->addMonth()->toDateString(),
            'notes' => $validated['subscription_notes'] ?? null,
        ];

        unset(
            $validated['subscription_plan_id'],
            $validated['subscription_status'],
            $validated['starts_at'],
            $validated['ends_at'],
            $validated['next_payment_at'],
            $validated['subscription_notes'],
        );

        $validated['subdomain'] = $validated['subdomain']
            ? Str::slug($validated['subdomain'])
            : Str::slug($validated['name']);
        $validated['guide_prefix'] = $validated['guide_prefix']
            ? strtoupper($validated['guide_prefix'])
            : null;

        $tenant = DB::transaction(function () use ($validated, $subscription) {
            $tenant = Tenant::query()->create($validated);
            $tenant->subscriptions()->create($subscription);

            return $tenant;
        });

        Audit::log('tenant.created', $tenant, "Cliente {$tenant->name} creado.");

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Cliente creado correctamente.');
    }

    public function update(Request $request, Tenant $tenant)
    {
        abort_unless(Auth::user()->canManageTenants(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subdomain' => ['required', 'string', 'max:80', 'unique:tenants,subdomain,'.$tenant->id],
            'guide_prefix' => ['nullable', 'string', 'size:3'],
            'status' => ['required', 'string', 'max:50'],
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'subscription_status' => ['required', 'in:active,past_due,paused,cancelled'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'next_payment_at' => ['nullable', 'date'],
            'subscription_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $subscription = [
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'status' => $validated['subscription_status'],
            'starts_at' => $validated['starts_at'] ?? now()->toDateString(),
            'ends_at' => $validated['ends_at'] ?? null,
            'next_payment_at' => $validated['next_payment_at'] ?? null,
            'notes' => $validated['subscription_notes'] ?? null,
        ];

        unset(
            $validated['subscription_plan_id'],
            $validated['subscription_status'],
            $validated['starts_at'],
            $validated['ends_at'],
            $validated['next_payment_at'],
            $validated['subscription_notes'],
        );

        $validated['subdomain'] = Str::slug($validated['subdomain']);
        $validated['guide_prefix'] = $validated['guide_prefix']
            ? strtoupper($validated['guide_prefix'])
            : null;

        DB::transaction(function () use ($tenant, $validated, $subscription) {
            $tenant->update($validated);

            $current = $tenant->currentSubscription;

            if ($current) {
                $current->update($subscription);
            } else {
                $tenant->subscriptions()->create($subscription);
            }
        });

        Audit::log('tenant.updated', $tenant, "Cliente {$tenant->name} actualizado.");

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Cliente actualizado correctamente.');
    }
}
