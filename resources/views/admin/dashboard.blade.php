@extends('layouts.admin')

@section('title', 'Resumen')
@section('eyebrow', 'Administracion')
@section('page-title', 'Resumen general')
@section('page-description', 'Indicadores rapidos de operacion, recaudo, clientes y pagos de toda la plataforma.')

@section('page-actions')
    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2">
        <div class="relative">
            <select name="range" id="dash-range" class="appearance-none rounded-lg border border-gray-200 bg-white px-3 py-2 pr-8 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <option value="today" {{ $dateRange['range'] === 'today' ? 'selected' : '' }}>Hoy</option>
                <option value="7d" {{ $dateRange['range'] === '7d' ? 'selected' : '' }}>Ultimos 7 dias</option>
                <option value="30d" {{ $dateRange['range'] === '30d' ? 'selected' : '' }}>Ultimos 30 dias</option>
                <option value="90d" {{ $dateRange['range'] === '90d' ? 'selected' : '' }}>Ultimos 90 dias</option>
                <option value="custom" {{ !in_array($dateRange['range'], ['today','7d','30d','90d']) ? 'selected' : '' }}>Personalizado</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 4 4-4" /></svg>
        </div>
        <div id="dash-dates" class="hidden items-center gap-2">
            <input type="date" name="from" value="{{ $dateRange['from'] }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <span class="text-sm text-gray-500">a</span>
            <input type="date" name="to" value="{{ $dateRange['to'] }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
        </div>
        <button type="submit" class="rounded-lg bg-[#022a8c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#011f69]">Aplicar</button>
    </form>
@endsection

@section('content')
    {{-- Alertas operativas --}}
    @if ($operation['stale'] > 0)
        <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-sm font-semibold text-amber-800">
                {{ $operation['stale'] }} guia{{ $operation['stale'] === 1 ? '' : 's' }} sin actualizar en mas de 24 horas
            </p>
            <span class="shrink-0 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white">Alerta</span>
        </div>
    @endif

    {{-- KPIs principales --}}
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">Guias del periodo</p>
            <p class="mt-2 text-3xl font-black text-gray-950">{{ number_format($operation['period_count'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $dateRange['label'] }}</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">En operacion</p>
            <p class="mt-2 text-3xl font-black text-blue-700">{{ number_format($operation['in_transit'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $operation['active_tenants_active_ops'] }} tiendas con envios activos</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">Tasa de entrega</p>
            <p class="mt-2 text-3xl font-black text-emerald-700">{{ $operation['delivery_rate'] }}%</p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($operation['delivered'], 0, ',', '.') }} entregadas del periodo</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">Recaudo cobrado</p>
            <p class="mt-2 text-3xl font-black text-gray-950">${{ number_format($collection['collected_total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">${{ number_format($collection['pending_month'], 0, ',', '.') }} por cobrar en operacion</p>
        </div>
    </section>

    {{-- Estado de la operacion --}}
    <div class="mt-5 mb-2 flex items-center gap-2">
        <h2 class="text-sm font-black uppercase tracking-wider text-gray-900">Estado de la operacion</h2>
    </div>
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($operation['groups'] as $key => $group)
            @php
                $theme = match ($key) {
                    'delivered' => ['accent' => 'emerald', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                    'returned' => ['accent' => 'orange', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700'],
                    'cancelled' => ['accent' => 'gray', 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
                    default => ['accent' => 'blue', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
                };
            @endphp
            <div class="te-kpi-hover relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm hover:border-{{ $theme['accent'] }}-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-{{ $theme['accent'] }}-500 to-transparent"></span>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $group['label'] }}</span>
                    <span class="rounded-full {{ $theme['bg'] }} px-2 py-0.5 text-[11px] font-bold {{ $theme['text'] }}">
                        ${{ number_format($group['value'], 0, ',', '.') }}
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ number_format($group['count'], 0, ',', '.') }}</p>
                <p class="mt-auto pt-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    {{ $operation['total'] > 0 ? round(($group['count'] / $operation['total']) * 100) : 0 }}% del total en el periodo
                </p>
            </div>
        @endforeach
    </section>

    {{-- Recaudo y evolucion --}}
    <section class="mt-5 grid gap-5 lg:grid-cols-2">
        <div class="admin-card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <p class="text-xs font-black uppercase text-gray-500">Recaudo</p>
            </div>
            <div class="grid grid-cols-2 gap-px bg-gray-100">
                <div class="bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-gray-500">Cobrado del periodo</p>
                    <p class="mt-1 text-lg font-black text-emerald-700">${{ number_format($collection['collected'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-gray-500">Por cobrar</p>
                    <p class="mt-1 text-lg font-black text-amber-700">${{ number_format($collection['pending_month'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-gray-500">Valor devuelto</p>
                    <p class="mt-1 text-lg font-black text-orange-700">${{ number_format($collection['returned_value'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-gray-500">Ticket promedio</p>
                    <p class="mt-1 text-lg font-black text-gray-950">${{ number_format($collection['avg_ticket'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <p class="text-xs font-black uppercase text-gray-500">Guias por dia · {{ $dateRange['label'] }}</p>
                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">{{ $dailyChart['total'] }} guias</span>
            </div>
            <div class="p-5">
                @php
                    $barMax = 84;
                    $barH = fn ($count) => $count > 0 ? max(6, round(($count / $dailyChart['max']) * $barMax)) : 2;
                @endphp
                <div class="flex h-44 items-end gap-1.5">
                    @foreach ($dailyChart['days'] as $d)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $d['count'] }}</span>
                            <div class="w-full rounded-t-lg {{ $loop->last ? 'bg-blue-600' : 'bg-blue-200 dark:bg-blue-900' }}" style="height: {{ $barH($d['count']) }}px"></div>
                            <span class="text-[10px] font-semibold text-gray-500">{{ $d['label'] }}</span>
                            <span class="text-[10px] font-semibold text-gray-400">{{ $d['full'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Top tiendas --}}
    <section class="mt-5 admin-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <p class="text-xs font-black uppercase text-gray-500">Top tiendas del mes</p>
            <a href="{{ route('admin.clients') }}" class="text-xs font-bold text-blue-700 hover:underline">Ver clientes</a>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th>Tienda</th>
                        <th>Plan</th>
                        <th>Guias del mes</th>
                        <th>Entregas</th>
                        <th>Tasa</th>
                        <th>Valor guiado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topTenants as $t)
                        <tr>
                            <td>
                                <p class="font-semibold text-gray-950">{{ $t['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $t['subdomain'] }}</p>
                            </td>
                            <td class="text-gray-700">{{ $t['plan'] }}</td>
                            <td class="font-bold text-gray-950">{{ number_format($t['count'], 0, ',', '.') }}</td>
                            <td class="text-gray-700">{{ number_format($t['delivered_count'] ?? $t['count'], 0, ',', '.') }}</td>
                            <td>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $t['rate'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($t['rate'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $t['rate'] }}%
                                </span>
                            </td>
                            <td class="font-semibold text-gray-950">${{ number_format($t['value'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-sm text-gray-500">Sin guias este mes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-5 grid gap-5 xl:grid-cols-2">
        <div class="admin-card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <p class="text-xs font-black uppercase text-gray-500">Clientes recientes</p>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($recentClients as $client)
                    <article class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate font-black text-gray-950">{{ $client->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $client->email ?: 'Sin correo' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-black text-gray-950">{{ $client->currentSubscription?->plan?->name ?: 'Sin plan' }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $client->shipments_count }} guias</p>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">Aun no hay clientes.</p>
                @endforelse
            </div>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <p class="text-xs font-black uppercase text-gray-500">Proximos pagos</p>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($dueSubscriptions as $subscription)
                    <article class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black text-gray-950">{{ $subscription->tenant?->name ?: 'Cliente eliminado' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $subscription->plan?->name ?: 'Sin plan' }}</p>
                            </div>
                            <p class="text-sm font-black text-gray-950 shrink-0">{{ $subscription->next_payment_at?->format('d/m/Y') ?: 'Sin fecha' }}</p>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">No hay pagos pendientes.</p>
                @endforelse
            </div>
        </div>
    </section>

    <script>
        const rangeSelect = document.getElementById('dash-range');
        const datesDiv = document.getElementById('dash-dates');
        if (rangeSelect && datesDiv) {
            function toggleDates() {
                datesDiv.classList.toggle('hidden', rangeSelect.value !== 'custom');
                datesDiv.classList.toggle('flex', rangeSelect.value === 'custom');
            }
            rangeSelect.addEventListener('change', toggleDates);
            toggleDates();
        }
    </script>
@endsection
