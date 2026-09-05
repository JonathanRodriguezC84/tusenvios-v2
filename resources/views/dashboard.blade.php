@php
    $rangeLabel = $dateRange['label'] ?? 'Periodo';
    $catColors = [1 => '#2a78d6', 2 => '#1baf7a', 3 => '#eda100', 4 => '#008300', 5 => '#4a3aa7', 6 => '#e34948', 7 => '#e87ba4', 8 => '#eb6834'];

    /* Delta de guias vs periodo anterior */
    $prevCount = (int) ($metrics['shipments_yesterday'] ?? 0);
    $curCount = (int) ($metrics['shipments_today'] ?? 0);
    $deltaAbs = $curCount - $prevCount;
    $deltaPct = $prevCount > 0 ? round(($deltaAbs / $prevCount) * 100) : null;
    $deltaUp = $deltaAbs > 0;
    $deltaFlat = $deltaAbs === 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" description="Tus envios de un vistazo.">
            <x-slot name="eyebrow">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</x-slot>
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
                    <button type="submit" class="rounded-lg bg-[#022a8c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#011f69]">Aplicar</button>
                </form>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
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

        <style>
            .te-kpi-hover { transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease; }
            .te-kpi-hover:hover { transform: translateY(-2px); }
        </style>

        <div class="mb-2 flex items-center gap-2">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900">Resumen de operacion</h2>
        </div>

        <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            {{-- Guias creadas --}}
            <a href="{{ route('shipments.index') }}" class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-blue-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-800">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-blue-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/70 dark:text-blue-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7.5l-.625-4.35A2 2 0 0 0 17.39 1.5H6.61a2 2 0 0 0-1.985 1.65L4 7.5m16 0H4m16 0v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-9m8 0v2.625A.375.375 0 0 1 11.625 11h-1.25A.375.375 0 0 1 10 10.625V7.5m4 0v2.625c0 .207.168.375.375.375h1.25a.375.375 0 0 0 .375-.375V7.5m0 0h2.25" /></svg>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold {{ $deltaFlat ? 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' : ($deltaUp ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-red-50 text-red-600 dark:bg-red-950/60 dark:text-red-300') }}">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="{{ $deltaFlat ? 'M5 19l14 0 0-7' : ($deltaUp ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7') }}" /></svg>
                            {{ $deltaFlat ? 'Sin cambios' : ($deltaPct !== null ? abs($deltaPct).'%' : '+'.abs($deltaAbs)) }}
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span data-count="{{ $curCount }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($curCount, 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">Guias creadas · {{ $rangeLabel }}</p>
                </div>

                {{-- Micro-gráfico de barras (Volumen diario) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <div class="absolute inset-x-0 bottom-0 flex items-end gap-1 h-full">
                        @foreach ($chartShipmentsByDay['days'] as $d)
                            <div title="{{ $d['full'] }}: {{ $d['count'] }} guías" class="flex-1 rounded-t transition-opacity hover:opacity-75 {{ $loop->last ? 'bg-blue-600 dark:bg-blue-500 shadow-sm' : 'bg-blue-100 dark:bg-blue-900/60' }}" style="height: {{ max(12, round(($d['count'] / $chartShipmentsByDay['max']) * 100)) }}%"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 flex items-center justify-between gap-2 border-t border-gray-100 pt-2 text-xs font-semibold text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>Previo: {{ $prevCount }} guías</span>
                    <span class="{{ $deltaUp ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-600 dark:text-gray-400' }}">{{ $deltaUp ? '+' : '' }}{{ $deltaAbs }} en periodo</span>
                </div>
            </a>

            {{-- Costo de productos --}}
            <div class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-amber-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-amber-600">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-amber-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/70 dark:text-amber-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                            {{ $productFinancials['units'] }} unds.
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span class="text-sm font-semibold text-gray-400 dark:text-gray-500">$</span>
                        <span data-count="{{ round($productFinancials['cost']) }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format(round($productFinancials['cost']), 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">Costo de productos · {{ $rangeLabel }}</p>
                </div>

                {{-- Micro-gráfico financiero SVG (Ámbar) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="grad-cost" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $chartFinancialsByDay['cost_area'] }}" fill="url(#grad-cost)" />
                        <path d="{{ $chartFinancialsByDay['cost_line'] }}" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        <circle cx="{{ $chartFinancialsByDay['cost_last']['x'] }}" cy="{{ $chartFinancialsByDay['cost_last']['y'] }}" r="3" fill="#f59e0b" class="shadow-sm" />
                    </svg>
                    <div class="absolute inset-0 flex items-stretch">
                        @foreach ($chartFinancialsByDay['days'] as $d)
                            <div class="flex-1 cursor-pointer" title="{{ $d['full'] }}: Costo ${{ number_format($d['cost'], 0, ',', '.') }}"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 flex items-center justify-between gap-2 border-t border-gray-100 pt-2 text-xs font-semibold text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>{{ $productFinancials['units'] }} unidades</span>
                    <span class="text-gray-700 dark:text-gray-200 font-bold">${{ number_format($productFinancials['units'] > 0 ? round($productFinancials['cost'] / $productFinancials['units']) : 0, 0, ',', '.') }} c/u</span>
                </div>
            </div>

            {{-- Ingreso por ventas --}}
            <div class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-emerald-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-emerald-700">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-emerald-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/70 dark:text-emerald-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.31M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                            {{ $productFinancials['orders'] }} pedidos
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span class="text-sm font-semibold text-gray-400 dark:text-gray-500">$</span>
                        <span data-count="{{ round($productFinancials['sales']) }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format(round($productFinancials['sales']), 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">Ingreso por ventas · {{ $rangeLabel }}</p>
                </div>

                {{-- Micro-gráfico financiero SVG (Esmeralda) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="grad-sales" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $chartFinancialsByDay['sales_area'] }}" fill="url(#grad-sales)" />
                        <path d="{{ $chartFinancialsByDay['sales_line'] }}" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        <circle cx="{{ $chartFinancialsByDay['sales_last']['x'] }}" cy="{{ $chartFinancialsByDay['sales_last']['y'] }}" r="3" fill="#10b981" class="shadow-sm" />
                    </svg>
                    <div class="absolute inset-0 flex items-stretch">
                        @foreach ($chartFinancialsByDay['days'] as $d)
                            <div class="flex-1 cursor-pointer" title="{{ $d['full'] }}: Ventas ${{ number_format($d['sales'], 0, ',', '.') }}"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 flex items-center justify-between gap-2 border-t border-gray-100 pt-2 text-xs font-semibold text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>{{ $productFinancials['units'] }} unds vendidas</span>
                    <span class="text-gray-700 dark:text-gray-200 font-bold">Ticket ${{ number_format($productFinancials['orders'] > 0 ? round($productFinancials['sales'] / $productFinancials['orders']) : 0, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Utilidad --}}
            <div class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-violet-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-violet-700">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-violet-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-950/70 dark:text-violet-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6.75V10.5" /></svg>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-bold {{ $productFinancials['profit'] >= 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-300' }}">{{ $productFinancials['profit'] >= 0 ? 'Ganancia' : 'Perdida' }}</span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span class="text-sm font-semibold text-gray-400 dark:text-gray-500">$</span>
                        <span data-count="{{ abs(round($productFinancials['profit'])) }}" class="{{ $productFinancials['profit'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-600 dark:text-red-400' }} text-2xl sm:text-3xl font-extrabold tracking-tight">{{ number_format(abs(round($productFinancials['profit'])), 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">Utilidad · {{ $rangeLabel }}</p>
                </div>

                {{-- Micro-gráfico financiero SVG (Violeta) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="grad-profit" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $chartFinancialsByDay['profit_area'] }}" fill="url(#grad-profit)" />
                        <path d="{{ $chartFinancialsByDay['profit_line'] }}" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        <circle cx="{{ $chartFinancialsByDay['profit_last']['x'] }}" cy="{{ $chartFinancialsByDay['profit_last']['y'] }}" r="3" fill="#8b5cf6" class="shadow-sm" />
                    </svg>
                    <div class="absolute inset-0 flex items-stretch">
                        @foreach ($chartFinancialsByDay['days'] as $d)
                            <div class="flex-1 cursor-pointer" title="{{ $d['full'] }}: Utilidad ${{ number_format($d['profit'], 0, ',', '.') }}"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span>Margen neto</span>
                        <span class="{{ $productFinancials['margin'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $productFinancials['margin'] >= 0 ? '+' : '' }}{{ $productFinancials['margin'] }}%</span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-full rounded-full {{ $productFinancials['margin'] >= 0 ? 'bg-emerald-500' : 'bg-red-500' }}" style="width: {{ min(100, abs($productFinancials['margin'])) }}%"></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-5 mb-2 flex items-center gap-2">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900">Resumen de logistica</h2>
        </div>

        <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            {{-- En camino --}}
            <a href="{{ route('shipments.index', ['status' => 'on_route']) }}" class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-blue-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-800">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-blue-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/70 dark:text-blue-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" /></svg>
                            {{ $metrics['in_transit'] }} en operacion
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span data-count="{{ $metrics['in_transit'] }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($metrics['in_transit'], 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">En camino · guias en operacion</p>
                </div>

                {{-- Micro-gráfico logístico SVG (En camino) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="grad-in-transit" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0284c7" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $chartFinancialsByDay['in_transit_area'] }}" fill="url(#grad-in-transit)" />
                        <path d="{{ $chartFinancialsByDay['in_transit_line'] }}" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        <circle cx="{{ $chartFinancialsByDay['in_transit_last']['x'] }}" cy="{{ $chartFinancialsByDay['in_transit_last']['y'] }}" r="3" fill="#0284c7" class="shadow-sm" />
                    </svg>
                    <div class="absolute inset-0 flex items-stretch">
                        @foreach ($chartFinancialsByDay['days'] as $d)
                            <div class="flex-1 cursor-pointer" title="{{ $d['full'] }}: En camino {{ $d['in_transit'] }} guías"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 flex items-center justify-between gap-2 border-t border-gray-100 pt-2 text-xs font-semibold text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>En bodega: <b class="text-gray-900 dark:text-white">{{ $metrics['warehouse'] }}</b></span>
                    <span>En ruta: <b class="text-gray-900 dark:text-white">{{ $metrics['on_route_only'] }}</b></span>
                </div>
            </a>

            {{-- Entregadas --}}
            <a href="{{ route('shipments.index', ['status' => 'delivered']) }}" class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-emerald-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-emerald-700">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-emerald-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/70 dark:text-emerald-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ $deliveryRate['rate'] }}% de entrega</span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span data-count="{{ $deliveryRate['delivered'] }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($deliveryRate['delivered'], 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">Entregadas · {{ $rangeLabel }}</p>
                </div>

                {{-- Micro-gráfico logístico SVG (Entregadas) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="grad-delivered" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $chartFinancialsByDay['delivered_area'] }}" fill="url(#grad-delivered)" />
                        <path d="{{ $chartFinancialsByDay['delivered_line'] }}" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        <circle cx="{{ $chartFinancialsByDay['delivered_last']['x'] }}" cy="{{ $chartFinancialsByDay['delivered_last']['y'] }}" r="3" fill="#10b981" class="shadow-sm" />
                    </svg>
                    <div class="absolute inset-0 flex items-stretch">
                        @foreach ($chartFinancialsByDay['days'] as $d)
                            <div class="flex-1 cursor-pointer" title="{{ $d['full'] }}: Entregadas {{ $d['delivered'] }} guías"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span>De {{ $deliveryRate['total'] }} guias</span>
                        <span class="{{ $deliveryRate['rateDelta'] >= 0 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-red-600 dark:text-red-400' }}">
                            {{ $deliveryRate['rateDelta'] >= 0 ? '+' : '' }}{{ $deliveryRate['rateDelta'] }} vs anterior
                        </span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $deliveryRate['rate']) }}%"></div>
                    </div>
                </div>
            </a>

            {{-- Devoluciones --}}
            <a href="{{ route('shipments.index', ['status' => 'returned']) }}" class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-orange-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-orange-800">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-orange-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-950/70 dark:text-orange-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-orange-50 px-2 py-0.5 text-xs font-bold text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">{{ $metrics['returned_total'] }} en total</span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span data-count="{{ $metrics['return_pending'] }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($metrics['return_pending'], 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">Devoluciones · pendientes</p>
                </div>

                {{-- Micro-gráfico logístico SVG (Devoluciones) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="grad-returned" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f97316" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#f97316" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $chartFinancialsByDay['returned_area'] }}" fill="url(#grad-returned)" />
                        <path d="{{ $chartFinancialsByDay['returned_line'] }}" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        <circle cx="{{ $chartFinancialsByDay['returned_last']['x'] }}" cy="{{ $chartFinancialsByDay['returned_last']['y'] }}" r="3" fill="#f97316" class="shadow-sm" />
                    </svg>
                    <div class="absolute inset-0 flex items-stretch">
                        @foreach ($chartFinancialsByDay['days'] as $d)
                            <div class="flex-1 cursor-pointer" title="{{ $d['full'] }}: Devoluciones {{ $d['returned'] }} guías"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span>Pendientes de gestion</span>
                        <span class="text-gray-700 dark:text-gray-200 font-bold">{{ round($metrics['returned_total'] > 0 ? ($metrics['return_pending'] / $metrics['returned_total']) * 100 : 0) }}%</span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-orange-500" style="width: {{ $metrics['returned_total'] > 0 ? min(100, round(($metrics['return_pending'] / $metrics['returned_total']) * 100)) : 0 }}%"></div>
                    </div>
                </div>
            </a>

            {{-- Canceladas --}}
            <a href="{{ route('shipments.index', ['status' => 'cancelled']) }}" class="te-kpi-hover relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm hover:border-gray-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-gray-400 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $metrics['total_shipments'] > 0 ? round(($metrics['cancelled'] / $metrics['total_shipments']) * 100, 1) : 0 }}% del total</span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span data-count="{{ $metrics['cancelled'] }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($metrics['cancelled'], 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">Canceladas</p>
                </div>

                {{-- Micro-gráfico logístico SVG (Canceladas) --}}
                <div class="relative mt-3 h-8 shrink-0">
                    <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="grad-cancelled" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#64748b" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#64748b" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $chartFinancialsByDay['cancelled_area'] }}" fill="url(#grad-cancelled)" />
                        <path d="{{ $chartFinancialsByDay['cancelled_line'] }}" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        <circle cx="{{ $chartFinancialsByDay['cancelled_last']['x'] }}" cy="{{ $chartFinancialsByDay['cancelled_last']['y'] }}" r="3" fill="#64748b" class="shadow-sm" />
                    </svg>
                    <div class="absolute inset-0 flex items-stretch">
                        @foreach ($chartFinancialsByDay['days'] as $d)
                            <div class="flex-1 cursor-pointer" title="{{ $d['full'] }}: Canceladas {{ $d['cancelled'] }} guías"></div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <span>Del total de guias</span>
                        <span class="text-gray-700 dark:text-gray-200 font-bold">{{ number_format($metrics['total_shipments'], 0, ',', '.') }} guias</span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-gray-400 dark:bg-gray-500" style="width: {{ min(100, round($metrics['total_shipments'] > 0 ? ($metrics['cancelled'] / $metrics['total_shipments']) * 100 : 0)) }}%"></div>
                    </div>
                </div>
            </a>
        </section>

        @if (count($alerts) > 0)
            <div class="mt-4 mb-2 flex items-center gap-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900">Requieren tu atencion</h2>
            </div>
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($alerts as $alert)
                    <a href="{{ $alert['route'] }}" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 sm:p-4 shadow-sm hover:shadow-md">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $alert['bg'] }}">
                            <svg class="h-5 w-5 {{ $alert['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $alert['icon'] }}" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-gray-900">{{ $alert['label'] }}</span>
                            <span class="block text-xs font-semibold text-gray-500">Necesita tu atencion</span>
                        </span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-sm font-black text-gray-900">{{ $alert['count'] }}</span>
                    </a>
                @endforeach
            </section>
        @endif

        @if (Auth::user()->canUseInventory() && (count($inventoryAlerts['low']) > 0 || count($inventoryAlerts['out']) > 0))
            <div class="mt-4 mb-2 flex items-center gap-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900">Inventario por reponer</h2>
            </div>
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @forelse ($inventoryAlerts['out'] as $product)
                    <a href="{{ route('inventory.index', ['search' => $product->name]) }}" class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm hover:bg-red-100">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-600 text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-red-900">Sin stock: {{ $product->name }}</span>
                            <span class="block text-xs font-semibold text-red-600">Agotado, reponlo pronto</span>
                        </span>
                        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-sm font-black text-red-700">Reponer</span>
                    </a>
                @empty
                @endforelse
                @forelse ($inventoryAlerts['low'] as $product)
                    <a href="{{ route('inventory.index', ['search' => $product->name]) }}" class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm hover:bg-amber-100">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2.25m0 3.75h.008v.008H12V15Z" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-amber-900">Stock bajo: {{ $product->name }}</span>
                            <span class="block text-xs font-semibold text-amber-700">{{ $product->stock }} unidades, minimo {{ $product->stock_minimum }}</span>
                        </span>
                        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-sm font-black text-amber-700">Reponer</span>
                    </a>
                @empty
                @endforelse
            </section>
        @endif

        <div class="mt-4 mb-2 flex items-center gap-2">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900">Resumen operacion mensual</h2>
        </div>

        <section class="mt-2 grid grid-cols-1 gap-3 sm:gap-4 lg:grid-cols-3">
            {{-- 1. Guias en el mes: 4 Semanas del mes limpias y ejecutivas --}}
            <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-blue-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">{{ ucfirst($chartMonthToDate['month']) }}</span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span data-count="{{ $chartMonthToDate['created'] }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($chartMonthToDate['created'], 0, ',', '.') }}</span>
                        <span class="text-xs font-medium text-gray-400">guías</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Día {{ $chartMonthToDate['elapsed_days'] }} de {{ $chartMonthToDate['total_days'] }} del mes
                    </p>
                </div>

                {{-- Micro-gráfico de semanas del mes (4 columnas limpias y espaciadas) --}}
                <div class="relative mt-3 h-9 shrink-0">
                    <div class="flex items-end justify-between gap-2.5 h-full w-full">
                        @foreach ($chartMonthToDate['weeks'] as $w)
                            @php
                                $heightPct = $w['count'] > 0 ? max(16, round(($w['count'] / max(1, $chartMonthToDate['max_week_count'])) * 100)) : 8;
                            @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full group" title="{{ $w['label'] }}: {{ $w['count'] }} guías{{ $w['is_current'] ? ' (en curso)' : ($w['is_future'] ? ' (por comenzar)' : '') }}">
                                <div class="w-full flex items-end justify-center h-6">
                                    @if ($w['is_current'])
                                        <div class="w-full max-w-[28px] rounded-t bg-blue-600 dark:bg-blue-500 shadow-sm transition-all group-hover:bg-blue-700" style="height: {{ $heightPct }}%;"></div>
                                    @elseif ($w['is_future'])
                                        <div class="w-full max-w-[28px] rounded-t bg-gray-100 dark:bg-gray-800 border-t border-dashed border-gray-300 dark:border-gray-600" style="height: 4px;"></div>
                                    @else
                                        <div class="w-full max-w-[28px] rounded-t bg-blue-400/80 hover:bg-blue-500 dark:bg-blue-600/70 dark:hover:bg-blue-500 transition-all" style="height: {{ $heightPct }}%;"></div>
                                    @endif
                                </div>
                                <span class="mt-1 text-[10px] font-bold leading-none {{ $w['is_current'] ? 'text-blue-600 dark:text-blue-400 font-black' : 'text-gray-400' }}">{{ $w['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span>Ritmo actual</span>
                        <span class="text-gray-700 dark:text-gray-200 font-bold">{{ round($chartMonthToDate['created'] / max(1, $chartMonthToDate['elapsed_days'])) }} guías/día</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[11px] text-gray-400">
                        <span>Proyección fin de mes</span>
                        <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $chartMonthToDate['expected_shipments'] }} guías</span>
                    </div>
                </div>
            </div>

            {{-- 2. Ingresos del mes: Barra de ritmo financiero estilizada --}}
            <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-emerald-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        @if ($chartMonthToDate['prev_month_revenue'] > 0)
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $chartMonthToDate['growth_vs_prev_month'] >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                {{ $chartMonthToDate['growth_vs_prev_month'] >= 0 ? '+' : '' }}{{ $chartMonthToDate['growth_vs_prev_month'] }}% vs mes anterior
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                {{ round(($chartMonthToDate['elapsed_days'] / max(1, $chartMonthToDate['total_days'])) * 100) }}% del mes
                            </span>
                        @endif
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span class="text-sm font-semibold text-gray-400">$</span>
                        <span data-count="{{ round($chartMonthToDate['revenue']) }}" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format(round($chartMonthToDate['revenue']), 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Facturación acumulada del mes
                    </p>
                </div>

                {{-- Barra de ritmo y avance mensual estilo Linear/Stripe --}}
                <div class="relative mt-3 h-9 shrink-0 flex flex-col justify-center">
                    <div class="flex items-center justify-between text-[11px] font-semibold mb-1">
                        <span class="text-gray-400">$0</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                            @if ($chartMonthToDate['prev_month_revenue'] > 0)
                                {{ $chartMonthToDate['gauge_pct'] }}% de mes anterior
                            @else
                                Proyección: ${{ number_format($chartMonthToDate['expected_revenue'], 0, ',', '.') }}
                            @endif
                        </span>
                        <span class="text-gray-400">
                            @if ($chartMonthToDate['prev_month_revenue'] > 0)
                                ${{ number_format($chartMonthToDate['prev_month_revenue'], 0, ',', '.') }}
                            @else
                                ${{ number_format($chartMonthToDate['expected_revenue'], 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                    <div class="relative h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500"
                             style="width: {{ min(100, $chartMonthToDate['gauge_pct']) }}%"></div>
                    </div>
                </div>

                <div class="mt-2.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span>Promedio diario</span>
                        <span class="text-gray-700 dark:text-gray-200 font-bold">${{ number_format(round($chartMonthToDate['revenue'] / max(1, $chartMonthToDate['elapsed_days'])), 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[11px] text-gray-400">
                        <span>Proyección fin de mes</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">${{ number_format($chartMonthToDate['expected_revenue'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- 3. Productos mas enviados: Grafica de barras individuales por producto (Top 3) --}}
            <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-purple-500 to-transparent"></span>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        @php
                            $totalUnitsTop = array_sum(array_column($chartTopProducts, 'count'));
                            $palette = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'];
                            $topDisplay = array_slice($chartTopProducts, 0, 3);
                        @endphp
                        <span class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-bold text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                            Top {{ count($topDisplay) }}
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline gap-1">
                        <span class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($totalUnitsTop, 0, ',', '.') }}</span>
                        <span class="text-xs font-medium text-gray-400">unidades</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Los más vendidos en {{ ucfirst($chartMonthToDate['month']) }}
                    </p>
                </div>

                {{-- Gráfica de barras: una barra individual por cada producto más vendido --}}
                <div class="mt-3 space-y-2">
                    @forelse ($topDisplay as $product)
                        @php
                            $barColor = $palette[$loop->index % count($palette)];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $barColor }};"></span>
                                    <span class="truncate font-semibold text-gray-700 dark:text-gray-300" title="{{ $product['name'] }}">{{ $product['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0 font-medium">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $product['count'] }}</span>
                                    <span class="text-[10px] text-gray-400 font-semibold">({{ $product['share_pct'] }}%)</span>
                                </div>
                            </div>
                            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $product['pct'] }}%; background-color: {{ $barColor }};"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs font-semibold text-gray-400">Sin productos registrados en este periodo.</p>
                    @endforelse
                </div>

                <div class="mt-2.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span>Líder de ventas</span>
                        <span class="text-gray-700 dark:text-gray-200 font-bold truncate max-w-[130px]">{{ $chartTopProducts[0]['name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[11px] text-gray-400">
                        <span>Concentración Top</span>
                        <span class="text-purple-600 dark:text-purple-400 font-bold">{{ round((array_sum(array_column($topDisplay, 'count')) / max(1, $totalUnitsTop)) * 100) }}% del volumen</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            document.querySelectorAll('[data-count]').forEach(function (el) {
                const target = parseFloat(el.dataset.count || '0');
                const decimals = parseInt(el.dataset.decimals || '0', 10);
                const fmt = function (v) {
                    return decimals > 0
                        ? v.toFixed(decimals).replace('.', ',')
                        : Math.round(v).toLocaleString('es-CO');
                };
                if (reduceMotion) { el.textContent = fmt(target); return; }

                const dur = 750;
                const t0 = performance.now();
                (function tick(now) {
                    const p = Math.min((now - t0) / dur, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = fmt(target * eased);
                    if (p < 1) requestAnimationFrame(tick);
                })(t0);
            });

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
        })();
    </script>
</x-app-layout>