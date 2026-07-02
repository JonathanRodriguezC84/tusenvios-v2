<?php

namespace App\Http\Controllers;

use App\Models\AffiliatedCompany;
use App\Models\SenderProfile;
use App\Models\Tenant;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SenderProfileController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'affiliated_company_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:active,paused'],
        ]);

        $senders = SenderProfile::query()
            ->with(['tenant', 'affiliatedCompany'])
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('label', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('locality', 'like', "%{$search}%");
                });
            })
            ->when($filters['affiliated_company_id'] ?? null, fn ($query, $companyId) => $query->where('affiliated_company_id', $companyId))
            ->when(array_key_exists('status', $filters) && $filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->paginate(15)
            ->withQueryString();

        $companies = AffiliatedCompany::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return view('sender-profiles.index', compact('senders', 'companies', 'filters'));
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);

        [$tenants, $companies] = $this->formOptions();

        return view('sender-profiles.create', compact('tenants', 'companies'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);

        $validated = $this->validatedSender($request);
        $sender = SenderProfile::query()->create($validated);

        $this->syncDefaultSender($sender);

        Audit::log('sender-profile.created', $sender, "Remitente {$sender->label} creado.");

        return redirect()
            ->route('sender-profiles.index')
            ->with('status', 'Remitente guardado correctamente.');
    }

    public function edit(SenderProfile $senderProfile)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);
        $this->authorizeTenant($senderProfile);

        [$tenants, $companies] = $this->formOptions();

        return view('sender-profiles.edit', compact('senderProfile', 'tenants', 'companies'));
    }

    public function update(Request $request, SenderProfile $senderProfile)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);
        $this->authorizeTenant($senderProfile);

        $senderProfile->update($this->validatedSender($request));
        $this->syncDefaultSender($senderProfile);

        Audit::log('sender-profile.updated', $senderProfile, "Remitente {$senderProfile->label} actualizado.");

        return redirect()
            ->route('sender-profiles.index')
            ->with('status', 'Remitente actualizado correctamente.');
    }

    public function updateStatus(Request $request, SenderProfile $senderProfile)
    {
        abort_unless(Auth::user()->canManageAffiliates(), 403);
        $this->authorizeTenant($senderProfile);

        $validated = $request->validate([
            'status' => ['required', 'in:active,paused'],
        ]);

        $senderProfile->update($validated);

        Audit::log('sender-profile.status-updated', $senderProfile, "Estado de remitente {$senderProfile->label} actualizado.");

        return back()->with('status', 'Estado del remitente actualizado correctamente.');
    }

    private function validatedSender(Request $request): array
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'affiliated_company_id' => ['nullable', 'exists:affiliated_companies,id'],
            'label' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,paused'],
        ]);

        if (! Auth::user()->isSuperAdmin()) {
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }

        if (! empty($validated['affiliated_company_id'])) {
            $companyBelongsToTenant = AffiliatedCompany::query()
                ->where('id', $validated['affiliated_company_id'])
                ->where('tenant_id', $validated['tenant_id'])
                ->exists();

            abort_unless($companyBelongsToTenant, 403);
        }

        $validated['is_default'] = $request->boolean('is_default');

        return $validated;
    }

    private function formOptions(): array
    {
        $tenants = Tenant::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        $companies = AffiliatedCompany::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return [$tenants, $companies];
    }

    private function authorizeTenant(SenderProfile $senderProfile): void
    {
        abort_if(! Auth::user()->isSuperAdmin() && $senderProfile->tenant_id !== Auth::user()->tenant_id, 403);
    }

    private function syncDefaultSender(SenderProfile $senderProfile): void
    {
        if (! $senderProfile->is_default) {
            return;
        }

        SenderProfile::query()
            ->where('tenant_id', $senderProfile->tenant_id)
            ->where('id', '!=', $senderProfile->id)
            ->where('affiliated_company_id', $senderProfile->affiliated_company_id)
            ->update(['is_default' => false]);
    }
}
