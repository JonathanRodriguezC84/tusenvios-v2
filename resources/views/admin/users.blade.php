@extends('layouts.admin')

@section('title', 'Usuarios')
@section('eyebrow', 'Administracion')
@section('page-title', 'Usuarios')
@section('page-description', 'Todos los usuarios de la plataforma con su rol y estado.')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    <section class="admin-card p-4 mb-4">
        <form method="GET" action="{{ route('admin.users') }}" class="flex flex-wrap gap-3 items-end">
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o correo..." class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700 w-48">
            <select name="role" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todos los roles</option>
                @foreach ($roleLabels as $v => $l)
                    <option value="{{ $v }}" @selected(($filters['role'] ?? '') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todos</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activos</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactivos</option>
            </select>
            <button class="admin-btn">Filtrar</button>
            <a href="{{ route('admin.users') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpiar</a>
        </form>
    </section>

    <section class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Negocio</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th width="80">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <p class="font-semibold text-gray-950">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </td>
                            <td>
                                <p class="text-xs text-gray-500">{{ $user->tenant?->name ?: '—' }}</p>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.role', $user) }}" class="inline-flex gap-1">
                                    @csrf @method('PATCH')
                                    <select name="role" class="rounded border-gray-300 text-xs shadow-sm focus:border-blue-700 focus:ring-blue-700" onchange="this.form.submit()">
                                        @foreach ($roleLabels as $v => $l)
                                            <option value="{{ $v }}" @selected($user->role === $v)>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.status', $user) }}" class="inline-flex gap-1">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'inactive' : 'active' }}">
                                    <button class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->status === 'active' ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-xs text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if (!$user->isSuperAdmin())
                                    <a href="{{ route('admin.users.impersonate', $user) }}" class="rounded border border-blue-300 bg-white px-2 py-1 text-3xs font-semibold text-blue-700 hover:bg-blue-50" onclick="return confirm('Entrar como {{ $user->name }}?')">Entrar</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-gray-500 py-8">No hay usuarios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4">{{ $users->links() }}</div>
    </section>
@endsection
