<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function users(Request $request): \Illuminate\View\View
    {
        $this->authorize('access-admin');

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

    public function updateUserRole(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate(['role' => ['required', 'in:superadmin,tenant_admin,affiliate,warehouse,courier']]);
        $user->update($validated);

        return back()->with('status', 'Rol actualizado.');
    }

    public function updateUserStatus(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate(['status' => ['required', 'in:active,inactive']]);
        $user->update($validated);

        return back()->with('status', 'Estado actualizado.');
    }

    public function impersonate(User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');
        abort_if($user->isSuperAdmin(), 403, 'No puedes impersonar a otro superadmin.');

        auth()->login($user);

        return redirect()->route('dashboard')->with('status', 'Viendo como '.$user->name);
    }
}
