<x-app-layout>
    @vite(['resources/css/inventory.css', 'resources/js/inventory.js'])

    <x-slot name="header">
        <x-page-header eyebrow="Productos / Préstamos" title="Resumen de Préstamos de Mercancía" description="Resumen, control y seguimiento de mercancía entregada a clientes o prestatarios.">
            <x-slot name="actions">
                <a href="{{ route('shipments.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700 shadow-sm inline-flex items-center gap-1.5">
                    <span>📦</span> Ir a Crear Guía / Préstamo
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-3 lg:p-5 max-w-7xl mx-auto flex flex-col gap-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-800 shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">✕</button>
            </div>
        @endif

        {{-- Metrics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">Total Préstamos</span>
                <span class="text-xl font-black text-gray-900">{{ $metrics['total'] }}</span>
            </div>

            <div class="bg-white rounded-xl border border-indigo-200 bg-indigo-50/30 p-3 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 block">Activos / Parciales</span>
                <span class="text-xl font-black text-indigo-900">{{ $metrics['active'] }}</span>
            </div>

            <div class="bg-white rounded-xl border border-amber-200 bg-amber-50/40 p-3 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 block">Unds por Devolver</span>
                <span class="text-xl font-black text-amber-900">{{ $metrics['pending_units'] }}</span>
            </div>

            <div class="bg-white rounded-xl border border-blue-200 bg-blue-50/30 p-3 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block">Valor Prestado</span>
                <span class="text-base font-black text-blue-900 leading-tight block mt-1">
                    ${{ number_format($metrics['pending_value'], 0, ',', '.') }}
                </span>
            </div>

            <div class="bg-white rounded-xl border border-rose-200 bg-rose-50/30 p-3 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 block">Vencidos</span>
                <span class="text-xl font-black text-rose-900">{{ $metrics['overdue'] }}</span>
            </div>

            <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50/30 p-3 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block">Cerrados</span>
                <span class="text-xl font-black text-emerald-900">{{ $metrics['returned'] + $metrics['sold'] }}</span>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
            <form method="GET" action="{{ route('inventory.loans.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar por cliente, documento, N° préstamo o producto..." class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs placeholder-gray-400 focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                </div>
                <div class="w-40">
                    <select name="status" class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs bg-white">
                        <option value="">Todos los estados</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>En préstamo</option>
                        <option value="partial" @selected(($filters['status'] ?? '') === 'partial')>Parcial</option>
                        <option value="overdue" @selected(($filters['status'] ?? '') === 'overdue')>Vencidos</option>
                        <option value="returned" @selected(($filters['status'] ?? '') === 'returned')>Devueltos</option>
                        <option value="sold" @selected(($filters['status'] ?? '') === 'sold')>Liquidados (Venta)</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Anulados</option>
                    </select>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs bg-white" title="Desde">
                    <span>-</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs bg-white" title="Hasta">
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="submit" class="rounded-lg bg-gray-900 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-black">Filtrar</button>
                    @if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['date_from']) || !empty($filters['date_to']))
                        <a href="{{ route('inventory.loans.index') }}" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Loans Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex-1 flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">N° Préstamo</th>
                            <th class="px-4 py-3">Prestatario</th>
                            <th class="px-4 py-3">Fechas</th>
                            <th class="px-4 py-3">Productos & Unidades</th>
                            <th class="px-4 py-3 text-right">Valor Total</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($loans as $loan)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('inventory.loans.show', $loan) }}" class="font-black text-blue-700 hover:underline">
                                        {{ $loan->loan_number }}
                                    </a>
                                    <span class="block text-[11px] text-gray-400">Por: {{ $loan->creator?->name ?? 'Sistema' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-gray-900 block">{{ $loan->borrower_name }}</span>
                                    @if ($loan->borrower_phone)
                                        <span class="text-xs text-gray-500 block">📞 {{ $loan->borrower_phone }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs">
                                    <span class="text-gray-600 block">Entrega: <strong>{{ $loan->loan_date->format('d/m/Y') }}</strong></span>
                                    @if ($loan->due_date)
                                        <span class="block {{ $loan->isOverdue() ? 'text-rose-600 font-bold' : 'text-gray-500' }}">
                                            Límite: {{ $loan->due_date->format('d/m/Y') }}
                                            @if ($loan->isOverdue())
                                                (Vencido)
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="font-bold text-gray-800">{{ $loan->total_items }} unidades prestadas</span>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[11px]">
                                        <span class="text-amber-700 font-semibold">{{ $loan->pendingUnits() }} pendientes</span>
                                        <span>·</span>
                                        <span class="text-emerald-700">{{ $loan->returnedUnits() }} devueltas</span>
                                        @if ($loan->soldUnits() > 0)
                                            <span>·</span>
                                            <span class="text-blue-700">{{ $loan->soldUnits() }} vendidas</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-[11px] text-gray-500 truncate max-w-xs">
                                        {{ $loan->items->map(fn($i) => $i->displayName())->filter()->join(', ') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-gray-900">
                                    ${{ number_format($loan->total_value, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $loan->statusBadgeClass() }}">
                                        {{ $loan->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                    <a href="{{ route('inventory.loans.show', $loan) }}" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        Gestionar
                                    </a>
                                    <a href="{{ route('inventory.loans.print', $loan) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded-md text-xs text-gray-600 bg-gray-100 hover:bg-gray-200" title="Imprimir Remisión">
                                        🖨️
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="font-bold text-gray-700">No hay préstamos de mercancía registrados en el resumen.</p>
                                    <p class="text-xs text-gray-400 mt-1">Para registrar un préstamo, ve a Crear Guía, añade los productos y pulsa en "Check out de Préstamo".</p>
                                    <div class="mt-4">
                                        <a href="{{ route('shipments.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700 shadow-sm inline-flex items-center gap-1.5">
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
                <div class="p-3 border-t border-gray-200">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
