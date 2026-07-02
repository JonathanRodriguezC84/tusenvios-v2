@extends('layouts.admin')

@section('title', 'Clientes')
@section('eyebrow', 'Control')
@section('page-title', 'Clientes')
@section('page-description', 'Negocios registrados, estado de acceso, plan actual y uso.')

@section('page-actions')
    <a href="{{ route('admin.clients.create') }}" class="admin-outline-link">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nuevo cliente
    </a>
@endsection

@section('content')
    @php
        $statusLabels = ['active' => 'Activo', 'paused' => 'Pausado', 'suspended' => 'Suspendido', 'cancelled' => 'Cancelado'];
        $paymentLabels = ['active' => 'Al dia', 'past_due' => 'Pendiente', 'paused' => 'Pausada', 'cancelled' => 'Cancelada'];
    @endphp

    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
    @endif

    <section class="admin-card p-4">
        <form method="GET" action="{{ route('admin.clients') }}" class="grid gap-3 lg:grid-cols-[1fr_170px_190px_auto]">
            <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Buscar negocio, correo o telefono" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todos</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="plan_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todos los planes</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected(($filters['plan_id'] ?? '') == $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="admin-btn">Buscar</button>
                <a href="{{ route('admin.clients') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="admin-card mt-4 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th>Negocio</th>
                        <th>Plan</th>
                        <th>Pago</th>
                        <th>Uso</th>
                        <th>Acceso</th>
                        <th>Control</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        @php
                            $subscription = $client->currentSubscription;
                            $plan = $subscription?->plan;
                        @endphp
                        <tr>
                            <td>
                                <p class="font-semibold text-gray-950"><a href="{{ route('admin.clients.show', $client) }}" class="hover:text-blue-700">{{ $client->name }}</a></p>
                                <p class="mt-1 text-xs text-gray-500">{{ $client->email ?: 'Sin correo' }} / {{ $client->phone ?: 'Sin telefono' }}</p>
                                <p class="mt-1 text-xs font-bold text-blue-800">{{ $client->subdomain ? $client->subdomain.'.tusenvios.com.co' : 'Sin subdominio' }}</p>
                            </td>
                            <td>
                                <p class="font-semibold text-gray-950">{{ $plan?->name ?: 'Sin plan' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $plan ? '$'.number_format($plan->monthly_price, 0, ',', '.') : '$0' }} / mes</p>
                            </td>
                            <td>
                                <p class="font-semibold text-gray-950">{{ $paymentLabels[$subscription?->status ?? 'paused'] ?? 'Sin pago' }}</p>
                                <p class="mt-1 text-xs text-gray-500">Proximo: {{ $subscription?->next_payment_at?->format('d/m/Y') ?: 'Sin fecha' }}</p>
                            </td>
                            <td>
                                <p class="font-semibold text-gray-950">{{ $client->shipments_count }} guias</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $client->users_count }} usuarios</p>
                            </td>
                            <td>
                                <p class="font-semibold text-gray-950">{{ $statusLabels[$client->status] ?? $client->status }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $client->shipments_max_created_at ? \Illuminate\Support\Carbon::parse($client->shipments_max_created_at)->format('d/m/Y H:i') : 'Sin guias' }}</p>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.clients.status', $client) }}" class="flex min-w-[220px] gap-2" onsubmit="return confirm('Cambiar estado de {{ $client->name }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                        @foreach ($statusLabels as $value => $label)
                                            <option value="{{ $value }}" @selected($client->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="admin-btn text-xs">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-gray-500">No hay clientes para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4">{{ $clients->links() }}</div>
    </section>
@endsection