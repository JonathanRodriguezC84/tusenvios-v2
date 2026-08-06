@php
    $rangeLabel = $dateRange['label'] ?? 'Periodo';
    $catColors = [1 => '#2a78d6', 2 => '#1baf7a', 3 => '#eda100', 4 => '#008300', 5 => '#4a3aa7', 6 => '#e34948', 7 => '#e87ba4', 8 => '#eb6834'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" description="Tus envios de un vistazo.">
            <x-slot name="actions">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
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
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Aplicar</button>
                </form>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="flex h-full flex-col p-3 sm:p-4 lg:p-4">
        @if ($operationHealth['stale'] > 0)
            <div class="mb-3 flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5">
                <p class="text-sm font-semibold text-amber-800">
                    {{ $operationHealth['stale'] }} guia{{ $operationHealth['stale'] === 1 ? '' : 's' }} sin actualizar en mas de 24 horas
                </p>
                <a href="{{ route('daily-tasks.index') }}" class="shrink-0 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700">
                    Actualizar
                </a>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="{{ route('shipments.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-gray-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Guias creadas</p>
                    <svg class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <p class="mt-3 text-4xl font-black text-gray-950">{{ $metrics['shipments_today'] }}</p>
                <p class="mt-2 text-sm font-medium text-gray-400">{{ $rangeLabel }}</p>
            </a>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Entregadas</p>
                <p class="mt-3 text-4xl font-black {{ $deliveryRate['total'] === 0 ? 'text-gray-300' : ($deliveryRate['rate'] >= 80 ? 'text-emerald-600' : ($deliveryRate['rate'] >= 50 ? 'text-amber-600' : 'text-red-600')) }}">
                    {{ $deliveryRate['total'] === 0 ? '-' : $deliveryRate['rate'].'%' }}
                </p>
                <p class="mt-2 text-sm font-medium text-gray-400">{{ $deliveryRate['delivered'] }} de {{ $deliveryRate['total'] }} guias</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Dinero por cobrar</p>
                <p class="mt-3 text-4xl font-black {{ $moneySummary['collectionOpen'] > 0 ? 'text-gray-900' : 'text-gray-300' }}">${{ number_format($moneySummary['collectionOpen'], 0, ',', '.') }}</p>
                <p class="mt-2 text-sm font-medium text-gray-400">Recaudo pendiente</p>
            </div>
        </section>

        @php
            $chartData = collect($chartShipmentsByDay['days'])->map(fn ($d) => [
                'label' => $d['full'], 'sub' => ucfirst($d['label']).' '.$d['full'], 'value' => $d['count'],
            ])->all();
        @endphp

        <section class="mt-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-gray-900">Guias {{ $rangeLabel }}</h2>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                    {{ array_sum(array_column($chartData, 'value')) }} en total
                </span>
            </div>
            <div class="mt-4">
                <x-charts.column-chart :data="$chartData" color="#2a78d6" format="number" />
            </div>
        </section>
    </div>

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
</x-app-layout>