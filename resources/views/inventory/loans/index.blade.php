@php
    $qpColor = $theme['color'] ?? '#022a8c';
    $qpText = $theme['text'] ?? '#ffffff';
    $qpBorder = $theme['border'] ?? '#bfdbfe';
    $qpTint = $theme['tint'] ?? '#eff6ff';
    $qpSoft = $theme['soft'] ?? 'rgba(2,42,140,0.12)';
    $currentStatus = $filters['status'] ?? '';
    $hasActiveFilters = !empty($filters['search']) || !empty($filters['status']) || !empty($filters['date_from']) || !empty($filters['date_to']);
@endphp

<x-app-layout>
    @vite(['resources/css/quick-products.css', 'resources/js/quick-products.js'])

    <style>
        .qp-page {
            --qp-color: {{ $qpColor }};
            --qp-color-text: {{ $qpText }};
            --qp-color-border: {{ $qpBorder }};
            --qp-color-tint: {{ $qpTint }};
            --qp-color-soft: {{ $qpSoft }};
        }
    </style>

    <x-slot name="header">
        <x-page-header
            eyebrow="Productos / Préstamos"
            title="Préstamos y Consignaciones de Mercancía"
            description="Control integral de mercancía prestada, saldos pendientes por prestatario, vencimientos, devoluciones y liquidaciones."
        >
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('inventory.loans.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-700 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-800 transition-colors">
                        <span>+</span> Nuevo Préstamo
                    </a>
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        <span>📦</span> Crear Guía / Préstamo
                    </a>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="qp-page p-3 lg:p-5 w-full min-w-0 max-w-full flex flex-col gap-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800 shadow-sm flex items-center justify-between gap-2">
                <span>{{ session('status') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 shrink-0 cursor-pointer">✕</button>
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 min-w-0">
            <a href="{{ route('inventory.loans.index') }}" class="min-w-0 bg-white rounded-xl border {{ $currentStatus === '' ? 'border-blue-600 ring-1 ring-blue-600/20' : 'border-gray-200 hover:border-gray-300' }} p-3 shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block truncate">Total Préstamos</span>
                    <span class="text-xl font-black text-gray-900 mt-0.5 block">{{ $metrics['total'] }}</span>
                </div>
                <span class="text-[11px] text-gray-500 mt-1 block truncate">
                    Histórico: ${{ number_format($metrics['total_lent_value'] ?? 0, 0, ',', '.') }}
                </span>
            </a>

            <a href="{{ route('inventory.loans.index', ['status' => 'open']) }}" class="min-w-0 rounded-xl border {{ $currentStatus === 'open' ? 'border-indigo-600 ring-1 ring-indigo-600/20 bg-indigo-50/60' : 'border-indigo-200 bg-indigo-50/30 hover:bg-indigo-50/50' }} p-3 shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 block truncate">Activos / Parciales</span>
                    <span class="text-xl font-black text-indigo-950 mt-0.5 block">{{ $metrics['active'] }}</span>
                </div>
                <span class="text-[11px] text-indigo-700/80 mt-1 block truncate">
                    {{ $metrics['active_only'] ?? 0 }} activos · {{ $metrics['partial_only'] ?? 0 }} parciales
                </span>
            </a>

            <div class="min-w-0 rounded-xl border border-amber-200 bg-amber-50/40 p-3 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 block truncate">Saldo por Recuperar</span>
                    <span class="text-lg font-black text-amber-950 mt-0.5 block truncate">
                        ${{ number_format($metrics['pending_value'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <span class="text-[11px] font-semibold text-amber-800/90 mt-1 block truncate">
                    {{ $metrics['pending_units'] }} unds pendientes
                </span>
            </div>

            <a href="{{ route('inventory.loans.index', ['status' => 'sold']) }}" class="min-w-0 rounded-xl border {{ $currentStatus === 'sold' ? 'border-blue-600 ring-1 ring-blue-600/20 bg-blue-50/60' : 'border-blue-200 bg-blue-50/30 hover:bg-blue-50/50' }} p-3 shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block truncate">Liquidado (Ventas)</span>
                    <span class="text-lg font-black text-blue-950 mt-0.5 block truncate">
                        ${{ number_format($metrics['sold_value'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <span class="text-[11px] text-blue-700/90 mt-1 block truncate">
                    {{ $metrics['sold_units'] ?? 0 }} unds · {{ $metrics['sold'] }} cerrados
                </span>
            </a>

            <a href="{{ route('inventory.loans.index', ['status' => 'overdue']) }}" class="min-w-0 rounded-xl border {{ $currentStatus === 'overdue' ? 'border-rose-600 ring-1 ring-rose-600/20 bg-rose-50/70' : 'border-rose-200 bg-rose-50/30 hover:bg-rose-50/50' }} p-3 shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 block truncate">Vencidos</span>
                    <span class="text-xl font-black text-rose-900 mt-0.5 block">{{ $metrics['overdue'] }}</span>
                </div>
                <span class="text-[11px] text-rose-700/90 mt-1 block truncate">
                    {{ $metrics['overdue'] > 0 ? 'Requieren seguimiento' : 'Sin préstamos vencidos' }}
                </span>
            </a>

            <a href="{{ route('inventory.loans.index', ['status' => 'returned']) }}" class="min-w-0 rounded-xl border {{ $currentStatus === 'returned' ? 'border-emerald-600 ring-1 ring-emerald-600/20 bg-emerald-50/60' : 'border-emerald-200 bg-emerald-50/30 hover:bg-emerald-50/50' }} p-3 shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block truncate">Devueltos a Bodega</span>
                    <span class="text-xl font-black text-emerald-950 mt-0.5 block">{{ $metrics['returned'] }}</span>
                </div>
                <span class="text-[11px] text-emerald-700/90 mt-1 block truncate">
                    {{ $metrics['returned_units'] ?? 0 }} unds · {{ $metrics['cancelled'] ?? 0 }} anulados
                </span>
            </a>
        </div>

        {{-- Active Borrowers Summary --}}
        @if (isset($activeBorrowers) && $activeBorrowers->isNotEmpty())
            <section x-data="{ open: false }" class="bg-white rounded-xl border border-gray-200 shadow-sm min-w-0 overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-50/80 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-amber-100 text-amber-800 text-xs font-bold shrink-0">👥</span>
                        <div class="min-w-0">
                            <h2 class="text-xs font-bold text-gray-800 truncate">Prestatarios con Mercancía Pendiente</h2>
                            <p class="text-[11px] text-gray-500 truncate">Haz clic en un prestatario para filtrar sus préstamos activos.</p>
                        </div>
                    </div>
                    <button type="button" @click="open = !open" class="rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-[11px] font-bold text-gray-700 hover:bg-gray-50 shrink-0 cursor-pointer">
                        <span x-text="open ? 'Ocultar resumen' : 'Ver {{ $activeBorrowers->count() }} prestatario(s)'"></span>
                    </button>
                </div>

                <div x-show="open" x-cloak class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    @foreach ($activeBorrowers as $borrower)
                        <a href="{{ route('inventory.loans.index', ['search' => $borrower['borrower_name'], 'status' => 'open']) }}"
                           class="min-w-0 rounded-xl border {{ $borrower['has_overdue'] ? 'border-rose-200 bg-rose-50/30 hover:border-rose-300' : 'border-gray-200 bg-gray-50/40 hover:border-blue-300' }} p-3 transition-all flex flex-col justify-between gap-1.5">
                            <div class="flex items-start justify-between gap-2 min-w-0">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-900 block truncate">{{ $borrower['borrower_name'] }}</span>
                                    @if ($borrower['borrower_phone'])
                                        <span class="text-[11px] text-gray-500 block truncate">📞 {{ $borrower['borrower_phone'] }}</span>
                                    @endif
                                </div>
                                @if ($borrower['has_overdue'])
                                    <span class="shrink-0 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200">Vencido</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-gray-200/70 text-[11px]">
                                <span class="text-gray-600">{{ $borrower['loans_count'] }} préstamo(s) · <strong>{{ $borrower['pending_units'] }}</strong> unds</span>
                                <span class="font-black text-gray-900 shrink-0">${{ number_format($borrower['pending_value'], 0, ',', '.') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Status Tabs & Filter Bar --}}
        <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm flex flex-col gap-3 min-w-0">
            @php
                $tabs = [
                    '' => ['label' => 'Todos', 'count' => $metrics['total']],
                    'open' => ['label' => 'Activos y parciales', 'count' => $metrics['active']],
                    'active' => ['label' => 'En préstamo', 'count' => $metrics['active_only'] ?? 0],
                    'partial' => ['label' => 'Parciales', 'count' => $metrics['partial_only'] ?? 0],
                    'overdue' => ['label' => 'Vencidos', 'count' => $metrics['overdue']],
                    'returned' => ['label' => 'Devueltos', 'count' => $metrics['returned']],
                    'sold' => ['label' => 'Liquidados', 'count' => $metrics['sold']],
                    'cancelled' => ['label' => 'Anulados', 'count' => $metrics['cancelled'] ?? 0],
                ];
            @endphp

            <div class="flex flex-wrap items-center gap-1.5 border-b border-gray-100 pb-2.5">
                @foreach ($tabs as $tabKey => $tab)
                    @php
                        $isSelected = $currentStatus === $tabKey;
                        $tabParams = array_filter([
                            'search' => $filters['search'] ?? null,
                            'date_from' => $filters['date_from'] ?? null,
                            'date_to' => $filters['date_to'] ?? null,
                            'status' => $tabKey !== '' ? $tabKey : null,
                        ], fn ($v) => $v !== null && $v !== '');
                    @endphp
                    <a href="{{ route('inventory.loans.index', $tabParams) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition-colors {{ $isSelected ? 'bg-blue-700 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span>{{ $tab['label'] }}</span>
                        <span class="rounded-full px-1.5 py-0.2 text-[10px] {{ $isSelected ? 'bg-white/20 text-white' : 'bg-white text-gray-700' }}">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('inventory.loans.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-center">
                <div class="sm:col-span-2 lg:col-span-5 min-w-0">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Buscar por N° préstamo, cliente, documento, teléfono o producto..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs placeholder-gray-400 focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                </div>
                <div class="lg:col-span-2 min-w-0">
                    <select name="status" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs bg-white focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <option value="">Todos los estados</option>
                        <option value="open" @selected($currentStatus === 'open')>Activos y parciales</option>
                        <option value="active" @selected($currentStatus === 'active')>En préstamo</option>
                        <option value="partial" @selected($currentStatus === 'partial')>Parcial</option>
                        <option value="overdue" @selected($currentStatus === 'overdue')>Vencidos</option>
                        <option value="returned" @selected($currentStatus === 'returned')>Devueltos</option>
                        <option value="sold" @selected($currentStatus === 'sold')>Liquidados (Venta)</option>
                        <option value="cancelled" @selected($currentStatus === 'cancelled')>Anulados</option>
                    </select>
                </div>
                <div class="lg:col-span-3 flex items-center gap-1.5 text-xs text-gray-500 min-w-0">
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full min-w-0 rounded-lg border border-gray-300 px-2 py-1.5 text-xs bg-white" title="Desde">
                    <span class="shrink-0">-</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full min-w-0 rounded-lg border border-gray-300 px-2 py-1.5 text-xs bg-white" title="Hasta">
                </div>
                <div class="lg:col-span-2 flex items-center justify-end gap-1.5">
                    <button type="submit" class="flex-1 lg:flex-none rounded-lg bg-gray-900 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-black cursor-pointer">Filtrar</button>
                    @if ($hasActiveFilters)
                        <a href="{{ route('inventory.loans.index') }}" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Loans List Container --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden min-w-0 flex flex-col">
            {{-- Mobile & Tablet Cards --}}
            <div class="lg:hidden divide-y divide-gray-100">
                @forelse ($loans as $loan)
                    @php
                        $totalUnits = max(1, (int) $loan->total_items);
                        $returnedUnits = $loan->returnedUnits();
                        $soldUnits = $loan->soldUnits();
                        $pendingUnits = $loan->pendingUnits();
                        $pendingValue = $loan->pendingValue();
                        $soldValue = $loan->soldValue();
                        $returnedValue = $loan->returnedValue();
                        $pctReturned = min(100, round(($returnedUnits / $totalUnits) * 100));
                        $pctSold = min(100 - $pctReturned, round(($soldUnits / $totalUnits) * 100));
                    @endphp
                    <div class="p-3.5 flex flex-col gap-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('inventory.loans.show', $loan) }}" class="text-sm font-black text-blue-700 hover:underline">
                                        {{ $loan->loan_number }}
                                    </a>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $loan->statusBadgeClass() }}">
                                        {{ $loan->statusLabel() }}
                                    </span>
                                </div>
                                <span class="block text-[11px] text-gray-400 mt-0.5">
                                    Entrega: {{ $loan->loan_date->format('d/m/Y') }}
                                    @if ($loan->due_date)
                                        · Límite: <span class="{{ $loan->isOverdue() ? 'text-rose-600 font-bold' : '' }}">{{ $loan->due_date->format('d/m/Y') }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                @if ($loan->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">Anulado</span>
                                    <span class="text-[11px] text-gray-400 line-through block mt-0.5">${{ number_format($loan->total_value, 0, ',', '.') }}</span>
                                @elseif ($pendingUnits === 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ✓ Saldo: $0
                                    </span>
                                    @if ($soldValue > 0)
                                        <span class="text-[11px] font-bold text-blue-700 block mt-0.5">
                                            Fact: ${{ number_format($soldValue, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-gray-400 block mt-0.5">
                                        Prest: ${{ number_format($loan->total_value, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-sm font-black text-amber-800 block">
                                        Saldo: ${{ number_format($pendingValue, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-gray-500 block mt-0.5">
                                        Prest: ${{ number_format($loan->total_value, 0, ',', '.') }}
                                    </span>
                                    @if ($soldValue > 0)
                                        <span class="text-[10px] font-semibold text-blue-700 block">
                                            Fact: ${{ number_format($soldValue, 0, ',', '.') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-2.5 text-xs space-y-1">
                            <div class="font-bold text-gray-900">{{ $loan->borrower_name }}</div>
                            <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-[11px] text-gray-500">
                                @if ($loan->borrower_phone)
                                    <span>📞 {{ $loan->borrower_phone }}</span>
                                @endif
                                @if ($loan->borrower_document)
                                    <span>🪪 {{ $loan->borrower_document }}</span>
                                @endif
                                <span>👤 {{ $loan->creator?->name ?? 'Sistema' }}</span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-bold text-gray-700">{{ $loan->total_items }} unds totales</span>
                                <span class="text-gray-500">
                                    <strong class="text-amber-700">{{ $pendingUnits }}</strong> pend ·
                                    <strong class="text-emerald-700">{{ $returnedUnits }}</strong> dev ·
                                    <strong class="text-blue-700">{{ $soldUnits }}</strong> vend
                                </span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-amber-100 overflow-hidden flex">
                                @if ($pctReturned > 0)
                                    <div class="bg-emerald-500 h-full" style="width: {{ $pctReturned }}%"></div>
                                @endif
                                @if ($pctSold > 0)
                                    <div class="bg-blue-600 h-full" style="width: {{ $pctSold }}%"></div>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-500 line-clamp-2">
                                {{ $loan->items->map(fn ($i) => $i->quantity_lent . 'x ' . $i->displayName())->filter()->join(' · ') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <a href="{{ route('inventory.loans.print', $loan) }}" target="_blank" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                🖨️ Remisión
                            </a>
                            <a href="{{ route('inventory.loans.show', $loan) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-blue-800">
                                Gestionar
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center text-gray-500">
                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="font-bold text-gray-700 text-sm">No hay préstamos registrados con estos filtros.</p>
                        <p class="text-xs text-gray-400 mt-1">Crea un préstamo directo o registra uno desde Crear Guía.</p>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                            <a href="{{ route('inventory.loans.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-xs font-bold text-white hover:bg-blue-800 shadow-sm inline-flex items-center gap-1.5">
                                <span>+</span> Nuevo Préstamo
                            </a>
                            <a href="{{ route('shipments.create') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm inline-flex items-center gap-1.5">
                                <span>📦</span> Ir a Crear Guía
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Desktop Table --}}
            <div class="hidden lg:block overflow-x-auto w-full min-w-0">
                <table class="w-full text-left text-xs text-gray-700">
                    <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Préstamo</th>
                            <th class="px-4 py-3">Prestatario</th>
                            <th class="px-4 py-3">Fechas & Plazo</th>
                            <th class="px-4 py-3">Avance & Productos</th>
                            <th class="px-4 py-3 text-right">Valor / Saldo</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($loans as $loan)
                            @php
                                $totalUnits = max(1, (int) $loan->total_items);
                                $returnedUnits = $loan->returnedUnits();
                                $soldUnits = $loan->soldUnits();
                                $pendingUnits = $loan->pendingUnits();
                                $pendingValue = $loan->pendingValue();
                                $soldValue = $loan->soldValue();
                                $returnedValue = $loan->returnedValue();
                                $pctReturned = min(100, round(($returnedUnits / $totalUnits) * 100));
                                $pctSold = min(100 - $pctReturned, round(($soldUnits / $totalUnits) * 100));
                                $today = now()->startOfDay();
                                $dueDiff = $loan->due_date ? $today->diffInDays($loan->due_date->copy()->startOfDay(), false) : null;
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors align-top">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('inventory.loans.show', $loan) }}" class="text-sm font-black text-blue-700 hover:underline">
                                        {{ $loan->loan_number }}
                                    </a>
                                    <span class="block text-[11px] text-gray-400 mt-0.5">Por: {{ $loan->creator?->name ?? 'Sistema' }}</span>
                                    @if ($loan->notes)
                                        <span class="block text-[11px] text-gray-500 max-w-[170px] truncate mt-0.5" title="{{ $loan->notes }}">
                                            📝 {{ $loan->notes }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 max-w-[220px]">
                                    <span class="font-bold text-gray-900 text-xs block truncate" title="{{ $loan->borrower_name }}">{{ $loan->borrower_name }}</span>
                                    @if ($loan->borrower_phone)
                                        <span class="text-[11px] text-gray-500 block mt-0.5">📞 {{ $loan->borrower_phone }}</span>
                                    @endif
                                    @if ($loan->borrower_document)
                                        <span class="text-[11px] text-gray-400 block">🪪 {{ $loan->borrower_document }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-gray-600 block">Entrega: <strong>{{ $loan->loan_date->format('d/m/Y') }}</strong></span>
                                    @if ($loan->due_date)
                                        <span class="block mt-0.5 {{ $loan->isOverdue() ? 'text-rose-600 font-bold' : 'text-gray-500' }}">
                                            Límite: {{ $loan->due_date->format('d/m/Y') }}
                                        </span>
                                        @if ($loan->isActive() && $dueDiff !== null)
                                            @if ($dueDiff < 0)
                                                <span class="inline-block mt-0.5 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700">
                                                    Vencido hace {{ abs((int) $dueDiff) }} d
                                                </span>
                                            @elseif ($dueDiff === 0)
                                                <span class="inline-block mt-0.5 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">
                                                    Vence hoy
                                                </span>
                                            @elseif ($dueDiff <= 3)
                                                <span class="inline-block mt-0.5 rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">
                                                    En {{ (int) $dueDiff }} día(s)
                                                </span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-[11px] text-gray-400 block mt-0.5">Sin fecha límite</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 min-w-[230px] max-w-[300px]">
                                    <div class="flex items-center justify-between gap-2 text-[11px]">
                                        <span class="font-bold text-gray-800">{{ $loan->total_items }} unds</span>
                                        <span class="text-gray-500">
                                            <strong class="text-amber-700">{{ $pendingUnits }}</strong> pend ·
                                            <strong class="text-emerald-700">{{ $returnedUnits }}</strong> dev
                                            @if ($soldUnits > 0)
                                                · <strong class="text-blue-700">{{ $soldUnits }}</strong> vend
                                            @endif
                                        </span>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-amber-100 overflow-hidden flex my-1.5">
                                        @if ($pctReturned > 0)
                                            <div class="bg-emerald-500 h-full" style="width: {{ $pctReturned }}%"></div>
                                        @endif
                                        @if ($pctSold > 0)
                                            <div class="bg-blue-600 h-full" style="width: {{ $pctSold }}%"></div>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-500 truncate" title="{{ $loan->items->map(fn($i) => $i->quantity_lent . 'x ' . $i->displayName())->filter()->join(', ') }}">
                                        {{ $loan->items->take(3)->map(fn ($i) => $i->quantity_lent . 'x ' . $i->displayName())->filter()->join(' · ') }}
                                        @if ($loan->items->count() > 3)
                                            <span class="text-gray-400 font-semibold">+{{ $loan->items->count() - 3 }} más</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if ($loan->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-500">
                                            Anulado
                                        </span>
                                        <span class="text-[11px] text-gray-400 line-through block mt-0.5">
                                            ${{ number_format($loan->total_value, 0, ',', '.') }}
                                        </span>
                                    @elseif ($pendingUnits === 0)
                                        <div class="flex flex-col items-end">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ✓ Saldo: $0
                                            </span>
                                            @if ($soldValue > 0)
                                                <span class="text-[11px] font-bold text-blue-700 block mt-1">
                                                    Facturado: ${{ number_format($soldValue, 0, ',', '.') }}
                                                </span>
                                            @endif
                                            @if ($returnedValue > 0)
                                                <span class="text-[10px] font-semibold text-emerald-700 block mt-0.5">
                                                    Devuelto: ${{ number_format($returnedValue, 0, ',', '.') }}
                                                </span>
                                            @endif
                                            <span class="text-[10px] text-gray-400 block mt-0.5">
                                                Prestado: ${{ number_format($loan->total_value, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-end">
                                            <span class="text-sm font-black text-amber-800 block">
                                                Saldo: ${{ number_format($pendingValue, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] text-gray-500 block mt-0.5">
                                                Prestado: ${{ number_format($loan->total_value, 0, ',', '.') }}
                                            </span>
                                            @if ($soldValue > 0)
                                                <span class="text-[10px] font-bold text-blue-700 block mt-0.5">
                                                    Facturado: ${{ number_format($soldValue, 0, ',', '.') }}
                                                </span>
                                            @endif
                                            @if ($returnedValue > 0)
                                                <span class="text-[10px] font-semibold text-emerald-700 block mt-0.5">
                                                    Devuelto: ${{ number_format($returnedValue, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $loan->statusBadgeClass() }}">
                                        {{ $loan->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('inventory.loans.show', $loan) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            Gestionar
                                        </a>
                                        <a href="{{ route('inventory.loans.print', $loan) }}" target="_blank" class="inline-flex items-center px-2 py-1.5 rounded-lg text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors" title="Imprimir Remisión">
                                            🖨️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="font-bold text-gray-700 text-sm">No hay préstamos de mercancía registrados con estos filtros.</p>
                                    <p class="text-xs text-gray-400 mt-1">Puedes crear un préstamo directo o registrarlo desde Crear Guía.</p>
                                    <div class="mt-4 flex items-center justify-center gap-2">
                                        <a href="{{ route('inventory.loans.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-xs font-bold text-white hover:bg-blue-800 shadow-sm inline-flex items-center gap-1.5">
                                            <span>+</span> Nuevo Préstamo
                                        </a>
                                        <a href="{{ route('shipments.create') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm inline-flex items-center gap-1.5">
                                            <span>📦</span> Ir a Crear Guía / Préstamo
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($loans->hasPages())
                <div class="p-3 border-t border-gray-200 overflow-x-hidden">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
