<?php

namespace App\Http\Controllers;

use App\Models\AffiliatedCompany;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Audit;

class UserController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->canManageUsers(), 403);

        $users = User::query()
            ->with(['tenant', 'affiliatedCompany'])
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageUsers(), 403);

        $tenants = Tenant::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        $companies = AffiliatedCompany::query()
            ->with('tenant')
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return view('users.create', compact('tenants', 'companies'));
    }

    public function edit(User $user)
    {
        abort_unless(Auth::user()->canManageUsers(), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $user->tenant_id !== Auth::user()->tenant_id, 403);

        $tenants = Tenant::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        $companies = AffiliatedCompany::query()
            ->with('tenant')
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();

        return view('users.edit', compact('user', 'tenants', 'companies'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageUsers(), 403);

        $validated = $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'affiliated_company_id' => ['nullable', 'exists:affiliated_companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        if (! Auth::user()->isSuperAdmin()) {
            abort_if($validated['role'] === 'superadmin', 403);
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }

        $user = User::query()->create($validated);

        Audit::log('user.created', $user, "Usuario {$user->email} creado.");

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        abort_unless(Auth::user()->canManageUsers(), 403);
        abort_if(! Auth::user()->isSuperAdmin() && $user->tenant_id !== Auth::user()->tenant_id, 403);

        $validated = $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'affiliated_company_id' => ['nullable', 'exists:affiliated_companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        if (! Auth::user()->isSuperAdmin()) {
            abort_if($validated['role'] === 'superadmin', 403);
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }

        if (! $validated['password']) {
            unset($validated['password']);
        }

        $user->update($validated);

        Audit::log('user.updated', $user, "Usuario {$user->email} actualizado.");

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }
}
