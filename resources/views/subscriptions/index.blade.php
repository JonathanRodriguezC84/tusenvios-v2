@php
    $statusLabels = [
        'active' => 'Activa',
        'past_due' => 'Pago pendiente',
        'paused' => 'Pausada',
        'cancelled' => 'Cancelada',
    ];

    $statusStyles = [
        'active' => 'bg-emerald-100 text-emerald-800',
        'past_due' => 'bg-amber-100 text-amber-800',
        'paused' => 'bg-gray-100 text-gray-700',
        'cancelled' => 'bg-blue-100 text-blue-900',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Administracion</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Cobros y suscripciones
                </h2>
            </div>

            <a href="{{ route('tenants.create') }}" class="inline-flex items-center justify-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Nuevo negocio
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Activas</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $metrics['active'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Pago pendiente</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $metrics['past_due'] }}</p>
                </div>
                <a href="{{ route('subscriptions.index', ['due' => 'due_soon']) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-amber-200 hover:bg-amber-50">
                    <p class="text-sm text-gray-500">Vencen en 7 dias</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $metrics['due_soon'] }}</p>
                </a>
                <a href="{{ route('subscriptions.index', ['due' => 'overdue']) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-blue-200 hover:bg-blue-50">
                    <p class="text-sm text-gray-500">Vencidas</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $metrics['overdue'] }}</p>
                </a>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Mensual activo</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">${{ number_format($metrics['monthly_value'], 0, ',', '.') }}</p>
                </div>
            </div>

            <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Seguimiento</p>
                        <h3 class="mt-1 text-lg font-semibold text-gray-950">Negocios por cobrar</h3>
                    </div>

                    <form method="GET" action="{{ route('subscriptions.index') }}" class="mt-4 grid gap-3 md:grid-cols-[180px_180px_180px_auto]">
                        <select name="status" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Todos los estados</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>

                        <select name="plan_id" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Todos los planes</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(($filters['plan_id'] ?? '') == $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>

                        <select name="due" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Todas las fechas</option>
                            <option value="overdue" @selected(($filters['due'] ?? '') === 'overdue')>Vencidas</option>
                            <option value="due_soon" @selected(($filters['due'] ?? '') === 'due_soon')>Vencen pronto</option>
                            <option value="no_date" @selected(($filters['due'] ?? '') === 'no_date')>Sin fecha</option>
                        </select>

                        <div class="flex gap-2">
                            <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                                Filtrar
                            </button>
                            <a href="{{ route('subscriptions.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Negocio</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Plan</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Valor</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Proximo pago</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Contacto</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($subscriptions as $subscription)
                                @php
                                    $isOverdue = $subscription->next_payment_at && $subscription->next_payment_at->isPast() && ! $subscription->next_payment_at->isToday();
                                    $isDueSoon = $subscription->next_payment_at && ! $isOverdue && $subscription->next_payment_at->lte(today()->addDays(7));
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="font-semibold text-gray-950">{{ $subscription->tenant->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $subscription->tenant->subdomain }}.tusenvios.com.co</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $subscription->plan->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$subscription->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $statusLabels[$subscription->status] ?? $subscription->status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">
                                        ${{ number_format($subscription->plan->monthly_price, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="font-semibold {{ $isOverdue ? 'text-blue-800' : ($isDueSoon ? 'text-amber-700' : 'text-gray-950') }}">
                                            {{ $subscription->next_payment_at?->format('d/m/Y') ?? 'Sin fecha' }}
                                        </p>
                                        @if ($isOverdue)
                                            <p class="text-xs text-blue-700">Vencido</p>
                                        @elseif ($isDueSoon)
                                            <p class="text-xs text-amber-700">Por cobrar</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                        {{ $subscription->tenant->email ?? 'Sin correo' }}<br>
                                        {{ $subscription->tenant->phone ?? 'Sin telefono' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('subscriptions.paid', $subscription) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-md bg-blue-700 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-800">
                                                    Pagado
                                                </button>
                                            </form>
                                            <a href="{{ route('tenants.edit', $subscription->tenant) }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-gray-500">No hay suscripciones con esos filtros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $subscriptions->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
