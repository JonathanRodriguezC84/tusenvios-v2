<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" description="Resumen de actividad, guias y rendimiento de tu negocio.">
            <x-slot name="eyebrow">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [del] YYYY') }}</x-slot>
            <x-slot name="actions">
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
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
                    <a href="{{ route('shipments.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">
                        Crear guia
                    </a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8">
        @php $rangeLabel = $dateRange['range'] === 'today' ? 'Hoy' : ($dateRange['range'] === '7d' ? 'Ultimos 7 dias' : ($dateRange['range'] === '30d' ? 'Ultimos 30 dias' : ($dateRange['range'] === '90d' ? 'Ultimos 90 dias' : $dateRange['from'] . ' - ' . $dateRange['to']))); @endphp
        @if ($onboarding['show'])
            <section class="mb-5 rounded-lg border border-blue-100 bg-blue-50 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-700">Primeros pasos</p>
                        <h3 class="mt-1 text-base font-semibold text-gray-950">Completa tu cuenta</h3>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-blue-800">{{ $onboarding['completed'] }}/{{ $onboarding['total'] }}</span>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    @foreach ($onboarding['steps'] as $step)
                        <a href="{{ $step['route'] }}" class="rounded-md border {{ $step['done'] ? 'border-emerald-200 bg-white' : 'border-blue-100 bg-white' }} p-3 hover:shadow-sm">
                            <p class="text-sm font-semibold text-gray-950">{{ $step['done'] ? '✓' : $loop->iteration.'.' }} {{ $step['label'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-600">{{ $step['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($trialGuideCounter)
            <section class="mb-5 rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-white p-4">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-700">Prueba gratis</p>
                        <h3 class="mt-1 text-base font-bold text-gray-950">Te quedan {{ $trialGuideCounter['remaining'] }} de {{ $trialGuideCounter['total'] }} guias</h3>
                        @if ($trialGuideCounter['remaining'] <= 3)
                            <p class="mt-0.5 text-sm text-gray-600">Crea algunas guias mas y activa tu plan mensual.</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-100 rounded-full h-3 w-48 overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full" style="width:{{ ($trialGuideCounter['total'] - $trialGuideCounter['remaining']) / $trialGuideCounter['total'] * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-blue-800">{{ $trialGuideCounter['remaining'] }}/{{ $trialGuideCounter['total'] }}</span>
                    </div>
                </div>
            </section>
        @endif

<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Envios <span class="text-blue-700">{{ $rangeLabel }}</span></p>
                <p class="mt-1 text-3xl font-black text-gray-950">{{ $metrics['shipments_today'] }}</p>
                @if ($metrics['delta'] != 0)
                    <p class="mt-0.5 text-xs font-semibold {{ $metrics['delta'] > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $metrics['delta'] > 0 ? '+' : '' }}{{ $metrics['delta'] }} vs ayer
                    </p>
                @endif
            </div>
            <a href="{{ route('shipments.index', ['status' => 'created']) }}" class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase text-gray-500">Por imprimir</p>
                <p class="mt-1 text-3xl font-black text-gray-950">{{ $metrics['pending_print'] }}</p>
            </a>
            <a href="{{ route('shipments.index', ['status' => 'on_route']) }}" class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase text-gray-500">En transito</p>
                <p class="mt-1 text-3xl font-black text-gray-950">{{ $metrics['in_transit'] }}</p>
            </a>
            <div class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs font-black uppercase text-gray-500">Ingresos <span class="text-blue-700">{{ $rangeLabel }}</span></p>
                <p class="mt-1 text-3xl font-black text-emerald-700">${{ number_format((float) $metrics['revenue_today'], 0, ',', '.') }}</p>
            </div>
        </section>

        @if (! empty($alerts))
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($alerts as $alert)
                    <a href="{{ $alert['route'] }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:shadow-md transition-shadow {{ $alert['bg'] }}">
                        <svg class="h-4 w-4 {{ $alert['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $alert['icon'] }}" />
                        </svg>
                        <span>{{ $alert['count'] }} {{ $alert['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Envios <span class="text-blue-700">{{ $rangeLabel }}</span></h3>
                @php $sd = $chartShipmentsByDay; @endphp
                <div class="mt-3 flex items-end justify-around gap-1.5" style="height: 110px;">
                    @foreach($sd['days'] as $d)
                        @php $h = max(6, round(($d['count'] / $sd['max']) * 90)); @endphp
                        <div class="flex flex-1 flex-col items-center gap-1 h-full justify-end">
                            <span class="text-xs font-bold text-gray-950">{{ $d['count'] }}</span>
                            <div class="w-full rounded-sm transition-all hover:opacity-80" style="height: {{ $h }}px; background: var(--te-button-color, #022a8c); min-height: 4px; border-radius: 3px 3px 0 0;"></div>
                            <span class="text-xs font-semibold text-gray-500">{{ $d['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos <span class="text-blue-700">{{ $rangeLabel }}</span></h3>
                @php $rd = $chartRevenueByDay; @endphp
                <div class="mt-3 flex items-end justify-around gap-1.5" style="height: 110px;">
                    @foreach($rd['days'] as $d)
                        @php $h = max(6, round(($d['revenue'] / $rd['max']) * 90)); @endphp
                        <div class="flex flex-1 flex-col items-center gap-1 h-full justify-end">
                            <span class="text-xs font-bold text-emerald-700">${{ number_format($d['revenue'], 0, ',', '.') }}</span>
                            <div class="w-full rounded-sm transition-all hover:opacity-80" style="height: {{ $h }}px; background: #10b981; min-height: 4px; border-radius: 3px 3px 0 0;"></div>
                            <span class="text-xs font-semibold text-gray-500">{{ $d['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Distribucion de envios</h3>
                @php $donut = $chartStatusDistribution; @endphp
                <div class="mt-3 flex flex-col items-center gap-3 sm:flex-row sm:gap-4">
                    <div class="shrink-0" style="width: 90px; height: 90px; border-radius: 50%;
                        background: conic-gradient(
                            @foreach ($donut['segments'] as $seg)
                                {{ $seg['color'] }} {{ $seg['start'] }}deg {{ $seg['end'] }}deg{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        );">
                    </div>
                    <div class="grid gap-1 text-xs">
                        @foreach ($donut['segments'] as $seg)
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $seg['color'] }}"></span>
                                <span class="font-semibold text-gray-700">{{ $seg['label'] }}</span>
                                <span class="font-bold text-gray-950">{{ $seg['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        @if (! empty($chartTopProducts))
            <section class="mt-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Productos mas enviados</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-5">
                    @foreach ($chartTopProducts as $p)
                        <div>
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="truncate text-sm font-semibold text-gray-950" title="{{ $p['name'] }}">{{ $p['name'] }}</p>
                                <span class="shrink-0 text-xs font-bold text-gray-500">{{ $p['count'] }}</span>
                            </div>
                            <div class="mt-1.5 h-2.5 w-full rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full transition-all" style="width: {{ $p['pct'] }}%; background: var(--te-button-color, #022a8c); border-radius: 999px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tendencia mensual</h3>
                @php $mt = $chartMonthlyTrend; @endphp
                <div class="mt-3 flex items-end justify-around gap-2" style="height: 100px;">
                    @foreach ($mt['months'] as $m)
                        @php $h = max(6, round(($m['count'] / $mt['max']) * 75)); @endphp
                        <div class="flex flex-1 flex-col items-center gap-1 h-full justify-end">
                            <span class="text-xs font-bold text-gray-950">{{ $m['count'] }}</span>
                            <div class="w-full" style="height:{{ $h }}px; background:var(--te-button-color, #022a8c); border-radius:3px 3px 0 0; min-height:4px;"></div>
                            <span class="text-xs font-semibold text-gray-500">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tasa de entrega</h3>
                <div class="mt-3 flex flex-col items-center gap-2">
                    <div class="text-4xl font-black {{ $deliveryRate['rate'] >= 80 ? 'text-emerald-600' : ($deliveryRate['rate'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $deliveryRate['rate'] }}%</div>
                    <p class="text-xs text-gray-500">{{ $deliveryRate['delivered'] }} de {{ $deliveryRate['total'] }} guias entregadas</p>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full {{ $deliveryRate['rate'] >= 80 ? 'bg-emerald-500' : ($deliveryRate['rate'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width:{{ $deliveryRate['rate'] }}%"></div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Actividad reciente</h3>
                <div class="mt-2 divide-y divide-gray-100 text-sm">
                    @forelse ($recentAudit as $audit)
                        <div class="flex items-center justify-between py-1.5 gap-2">
                            <p class="text-xs text-gray-700 truncate">{{ \Illuminate\Support\Str::limit($audit['description'], 40) }}</p>
                            <span class="text-xs text-gray-400 shrink-0">{{ \Carbon\Carbon::parse($audit['date'])->diffForHumans(null, true) }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 py-2">Sin actividad reciente</p>
                    @endforelse
                </div>
            </section>
        </div>

        @if (! empty($inventoryAlerts['low']) || ! empty($inventoryAlerts['out']))
            <section class="mt-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Alertas de inventario</h3>
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($inventoryAlerts['out'] as $p)
                        <a href="{{ route('inventory.index', ['stock' => 'out']) }}" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100">
                            ⚠️ Agotado: {{ $p->name }}
                        </a>
                    @endforeach
                    @foreach ($inventoryAlerts['low'] as $p)
                        <a href="{{ route('inventory.index', ['stock' => 'low']) }}" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                            Stock bajo: {{ $p->name }} ({{ $p->stock }}/{{ $p->stock_minimum }})
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
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
