@php
    $rangeLabel = $dateRange['label'] ?? 'Periodo';
    $deliveryTone = $deliveryRate['total'] === 0 ? '#9ca3af' : ($deliveryRate['rate'] >= 80 ? '#059669' : ($deliveryRate['rate'] >= 50 ? '#d97706' : '#dc2626'));
    $deliveryRingValue = $deliveryRate['total'] === 0 ? 100 : $deliveryRate['rate'];
    $moneyTone = [
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'text' => 'text-emerald-800'],
        'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'text' => 'text-amber-800'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800'],
    ][$moneySummary['tone']] ?? ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800'];
    $catColors = [1 => '#2a78d6', 2 => '#1baf7a', 3 => '#eda100', 4 => '#008300', 5 => '#4a3aa7', 6 => '#e34948', 7 => '#e87ba4', 8 => '#eb6834'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" description="Los resultados de tu negocio, de un vistazo.">
            <x-slot name="eyebrow">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [del] YYYY') }}</x-slot>
            <x-slot name="actions">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <select name="range" id="dash-range" class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <option value="today" {{ $dateRange['range'] === 'today' ? 'selected' : '' }}>Hoy</option>
                            <option value="7d" {{ $dateRange['range'] === '7d' ? 'selected' : '' }}>Ultimos 7 dias</option>
                            <option value="30d" {{ $dateRange['range'] === '30d' ? 'selected' : '' }}>Ultimos 30 dias</option>
                            <option value="90d" {{ $dateRange['range'] === '90d' ? 'selected' : '' }}>Ultimos 90 dias</option>
                            <option value="custom" {{ !in_array($dateRange['range'], ['today','7d','30d','90d']) ? 'selected' : '' }}>Personalizado</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 4 4-4" /></svg>
                    </div>
                    <div id="dash-dates" class="hidden items-center gap-2">
                        <input type="date" name="from" value="{{ $dateRange['from'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <span class="text-sm text-gray-500">a</span>
                        <input type="date" name="to" value="{{ $dateRange['to'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Aplicar</button>
                </form>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="flex h-full flex-col p-3 sm:p-4 lg:p-4">
        @if ($operationHealth['stale'] > 0)
            <section class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-amber-700">Recordatorio diario</p>
                        <h2 class="text-sm font-black text-gray-950">
                            Tienes {{ $operationHealth['stale'] }} guia{{ $operationHealth['stale'] === 1 ? '' : 's' }} sin actualizar en mas de 24 horas
                        </h2>
                    </div>
                    <a href="{{ route('daily-tasks.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-black text-white shadow-sm hover:bg-amber-700">
                        Actualizar guias
                    </a>
                </div>
            </section>
        @endif

        @if ($onboarding['show'])
            <section class="mb-3 rounded-lg border border-blue-200 bg-blue-50 p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-blue-700">Primeros pasos</p>
                        <h2 class="text-sm font-black text-gray-950">Completa la base profesional de tu negocio</h2>
                    </div>
                    <div class="min-w-40">
                        <div class="h-1.5 rounded-full bg-white">
                            <div class="h-1.5 rounded-full bg-blue-700" style="width: {{ round(($onboarding['completed'] / max(1, $onboarding['total'])) * 100) }}%"></div>
                        </div>
                        <p class="mt-0.5 text-right text-[11px] font-bold text-blue-800">{{ $onboarding['completed'] }}/{{ $onboarding['total'] }} completo</p>
                    </div>
                </div>
                <div class="mt-2 grid gap-2 md:grid-cols-3">
                    @foreach ($onboarding['steps'] as $step)
                        <a href="{{ $step['route'] }}" class="rounded-lg border {{ $step['done'] ? 'border-emerald-200 bg-white' : 'border-blue-100 bg-white' }} p-2 hover:border-blue-300">
                            <p class="text-xs font-black text-gray-950">{{ $step['done'] ? 'Listo' : $loop->iteration.'.' }} {{ $step['label'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($trialGuideCounter)
            <section class="mb-3 rounded-lg border border-blue-200 bg-white p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-blue-700">Prueba gratis</p>
                        <h2 class="text-sm font-black text-gray-950">Te quedan {{ $trialGuideCounter['remaining'] }} de {{ $trialGuideCounter['total'] }} guias</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-36 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full rounded-full bg-blue-700" style="width:{{ ($trialGuideCounter['total'] - $trialGuideCounter['remaining']) / max(1, $trialGuideCounter['total']) * 100 }}%"></div>
                        </div>
                        <span class="text-xs font-black text-blue-800">{{ $trialGuideCounter['remaining'] }}/{{ $trialGuideCounter['total'] }}</span>
                    </div>
                </div>
            </section>
        @endif

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Guias creadas</p>
                <p class="mt-1.5 text-3xl font-black text-gray-950">{{ $metrics['shipments_today'] }}</p>
                <p class="mt-1.5 text-xs font-semibold text-gray-500">{{ $rangeLabel }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregas</p>
                <div class="mt-2 flex items-center gap-3">
                    <x-ring-gauge :score="$deliveryRingValue" :size="68" :stroke="8" :color="$deliveryTone" class="shrink-0">
                        <div class="grid h-12 w-12 place-items-center rounded-full bg-white">
                            <span class="text-center text-xs font-black text-gray-950">{{ $deliveryRate['total'] === 0 ? '-' : $deliveryRate['rate'].'%' }}</span>
                        </div>
                    </x-ring-gauge>
                    <div>
                        <p class="text-sm font-bold text-gray-950">{{ $deliveryRate['delivered'] }} de {{ $deliveryRate['total'] }}</p>
                        <p class="text-xs font-semibold text-gray-500">Entregadas en {{ $rangeLabel }}</p>
                    </div>
                </div>
            </div>

            <a href="{{ route('shipments.index', ['status' => 'created']) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Preparacion</p>
                <p class="mt-1.5 text-3xl font-black text-gray-950">{{ $metrics['pending_print'] }}</p>
                <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-blue-700" style="width: {{ min(100, $metrics['pending_print'] * 12) }}%"></div>
                </div>
                <p class="mt-1.5 text-xs font-semibold text-gray-500">Guias por imprimir</p>
            </a>

            <a href="{{ route('shipments.index', ['status' => 'on_route']) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">En movimiento</p>
                <p class="mt-1.5 text-3xl font-black text-gray-950">{{ $metrics['in_transit'] }}</p>
                <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-emerald-600" style="width: {{ min(100, $metrics['in_transit'] * 10) }}%"></div>
                </div>
                <p class="mt-1.5 text-xs font-semibold text-gray-500">En ruta o bodega</p>
            </a>

            <a href="{{ route('daily-tasks.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Sin movimiento</p>
                <p class="mt-1.5 text-3xl font-black {{ $operationHealth['stale'] > 0 ? 'text-amber-700' : 'text-gray-950' }}">{{ $operationHealth['stale'] }}</p>
                <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-amber-500" style="width: {{ min(100, $operationHealth['stale'] * 18) }}%"></div>
                </div>
                <p class="mt-1.5 text-xs font-semibold text-gray-500">Mas de 24h quietas</p>
            </a>
        </section>

        @if (! empty($alerts) || ! empty($inventoryAlerts['low']) || ! empty($inventoryAlerts['out']))
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($alerts as $alert)
                    <a href="{{ $alert['route'] }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-black shadow-sm hover:bg-white {{ $alert['bg'] }}">
                        <svg class="h-4 w-4 {{ $alert['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $alert['icon'] }}" />
                        </svg>
                        <span>{{ $alert['count'] }} {{ $alert['label'] }}</span>
                    </a>
                @endforeach
                @foreach ($inventoryAlerts['out'] as $p)
                    <a href="{{ route('inventory.index', ['stock' => 'out']) }}" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-black text-red-800 shadow-sm hover:bg-red-100">
                        Agotado: {{ $p->name }}
                    </a>
                @endforeach
                @foreach ($inventoryAlerts['low'] as $p)
                    <a href="{{ route('inventory.index', ['stock' => 'low']) }}" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-black text-amber-800 shadow-sm hover:bg-amber-100">
                        Stock bajo: {{ $p->name }} ({{ $p->stock }}/{{ $p->stock_minimum }})
                    </a>
                @endforeach
            </div>
        @endif

        <section class="mt-3 rounded-lg border p-4 shadow-sm {{ $moneyTone['panel'] }}">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-wider {{ $moneyTone['badge'] }}">{{ $moneySummary['label'] }}</span>
                    <h2 class="mt-2 text-base font-black text-gray-950">Resumen de dinero</h2>
                </div>
                <a href="{{ route('shipments.index') }}" class="shrink-0 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-black text-gray-700 shadow-sm hover:bg-gray-50">Ver guias</a>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Creado en {{ $rangeLabel }}</p>
                    <p class="mt-1 text-2xl font-black text-gray-950">${{ number_format($moneySummary['createdValue'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Entregado</p>
                    <p class="mt-1 text-2xl font-black text-emerald-700">${{ number_format($moneySummary['deliveredValue'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Recaudo pendiente</p>
                    <p class="mt-1 text-2xl font-black {{ $moneySummary['collectionOpen'] > 0 ? 'text-amber-700' : 'text-gray-950' }}">${{ number_format($moneySummary['collectionOpen'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Dinero a vigilar</p>
                    <p class="mt-1 text-2xl font-black {{ $moneySummary['moneyToWatch'] > 0 ? 'text-red-700' : 'text-gray-950' }}">${{ number_format($moneySummary['moneyToWatch'], 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <section class="mt-3 rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Estado de guias</h3>
                <span class="text-xs font-bold text-gray-400">{{ $chartStatusDistribution['total'] }} en total</span>
            </div>
            <div class="mt-3">
                <x-charts.status-bar :buckets="$chartStatusBuckets['buckets']" :total="$chartStatusBuckets['total']" />
            </div>

            @if ($chartStatusDistribution['total'] > 0)
                <details class="mt-3 border-t border-gray-100 pt-2">
                    <summary class="cursor-pointer text-xs font-bold text-gray-500 hover:text-gray-700">Ver el detalle de cada estado</summary>
                    <div class="mt-2 grid gap-1.5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($chartStatusDistribution['rows'] as $row)
                            @continue($row['count'] <= 0)
                            <div class="flex items-center justify-between gap-2 rounded-md bg-gray-50 px-2.5 py-1.5">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2 w-2 shrink-0 rounded-sm" style="background: {{ $catColors[$row['slot']] ?? '#2a78d6' }}"></span>
                                    <p class="truncate text-xs font-semibold text-gray-700">{{ $row['label'] }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-black text-gray-950">{{ $row['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </section>

        @php
            $shipmentsByDay = collect($chartShipmentsByDay['days'])->map(fn ($d) => [
                'label' => $d['full'], 'sub' => ucfirst($d['label']).' '.$d['full'], 'value' => $d['count'],
            ])->all();
            $revenueByDay = collect($chartRevenueByDay['days'])->map(fn ($d) => [
                'label' => $d['full'], 'sub' => ucfirst($d['label']).' '.$d['full'], 'value' => $d['revenue'],
            ])->all();
            $monthlyTrend = collect($chartMonthlyTrend['months'])->map(fn ($m) => [
                'label' => $m['label'], 'value' => $m['count'],
            ])->all();
        @endphp

        <section class="mt-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2 text-sm font-black text-gray-950">
                    <span>Graficas y analisis</span>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-black text-gray-700">{{ $rangeLabel }}</span>
                </div>
                <div class="mt-3 grid min-w-0 gap-3" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                    <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Guias creadas</h3>
                        <div class="mt-2" style="min-height:130px">
                            <x-charts.column-chart :data="$shipmentsByDay" color="#2a78d6" format="number" />
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos por entregas</h3>
                        <div class="mt-2" style="min-height:130px">
                            <x-charts.column-chart :data="$revenueByDay" color="#1baf7a" format="currency" />
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tendencia mensual</h3>
                        <div class="mt-2" style="min-height:130px">
                            <x-charts.column-chart :data="$monthlyTrend" color="#0b0b0b" format="number" />
                        </div>
                    </div>

                    @if (! empty($chartTopProducts))
                        <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Productos mas enviados</h3>
                            <div class="mt-2 grid flex-1 content-center gap-2.5">
                                @foreach (array_slice($chartTopProducts, 0, 5) as $p)
                                    <div>
                                        <div class="flex items-baseline justify-between gap-2">
                                            <p class="truncate text-sm font-black text-gray-950" title="{{ $p['name'] }}">{{ $p['name'] }}</p>
                                            <span class="shrink-0 text-xs font-black text-gray-500">{{ $p['count'] }}</span>
                                        </div>
                                        <div class="mt-1 h-2 rounded-full" style="background: #e1e0d9">
                                            <div class="h-2 rounded-full" style="width: {{ $p['pct'] }}%; background: #2a78d6"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>
    </div>

    <script>
        const rangeSelect = document.getElementById('dash-range');
        const datesDiv = document.getElementById('dash-dates');

        if (rangeSelect && datesDiv) {
            function toggleDates() {
                if (rangeSelect.value === 'custom') {
                    datesDiv.classList.remove('hidden');
                    datesDiv.classList.add('flex');
                } else {
                    datesDiv.classList.add('hidden');
                    datesDiv.classList.remove('flex');
                }
            }

            rangeSelect.addEventListener('change', toggleDates);
            toggleDates();
        }
    </script>
</x-app-layout>
