<?php

namespace App\Http\Controllers;

use App\Models\AffiliatedCompany;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Audit;

class AffiliatedCompanyController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);

        $companies = AffiliatedCompany::query()
            ->with('tenant')
            ->withCount('shipments')
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->paginate(15);

        return view('affiliated-companies.index', compact('companies'));
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);

        $tenants = Tenant::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return view('affiliated-companies.create', compact('tenants'));
    }

    public function edit(AffiliatedCompany $affiliatedCompany)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $affiliatedCompany->tenant_id !== Auth::user()->tenant_id, 403);

        $tenants = Tenant::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return view('affiliated-companies.edit', compact('affiliatedCompany', 'tenants'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);

        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'guide_prefix' => ['nullable', 'string', 'size:3'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'default_payment_method' => ['required', 'in:cash,credit,cod'],
            'allows_cod' => ['nullable', 'boolean'],
            'cod_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'billing_notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,paused'],
        ]);

        if (! Auth::user()->isSuperAdmin()) {
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }
        $validated['guide_prefix'] = $validated['guide_prefix']
            ? strtoupper($validated['guide_prefix'])
            : null;
        $validated['allows_cod'] = $request->boolean('allows_cod');
        $validated['cod_commission_percent'] = $validated['cod_commission_percent'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

        $company = AffiliatedCompany::query()->create($validated);

        Audit::log('affiliate.created', $company, "Afiliada {$company->name} creada.");

        return redirect()
            ->route('affiliated-companies.index')
            ->with('status', 'Empresa afiliada creada correctamente.');
    }

    public function update(Request $request, AffiliatedCompany $affiliatedCompany)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $affiliatedCompany->tenant_id !== Auth::user()->tenant_id, 403);

        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'guide_prefix' => ['nullable', 'string', 'size:3'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'default_payment_method' => ['required', 'in:cash,credit,cod'],
            'allows_cod' => ['nullable', 'boolean'],
            'cod_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'billing_notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,paused'],
        ]);

        if (! Auth::user()->isSuperAdmin()) {
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }

        $validated['guide_prefix'] = $validated['guide_prefix']
            ? strtoupper($validated['guide_prefix'])
            : null;
        $validated['allows_cod'] = $request->boolean('allows_cod');
        $validated['cod_commission_percent'] = $validated['cod_commission_percent'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

        $affiliatedCompany->update($validated);

        Audit::log('affiliate.updated', $affiliatedCompany, "Afiliada {$affiliatedCompany->name} actualizada.");

        return redirect()
            ->route('affiliated-companies.index')
            ->with('status', 'Empresa afiliada actualizada correctamente.');
    }
}
