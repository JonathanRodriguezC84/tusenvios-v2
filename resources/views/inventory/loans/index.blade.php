<x-app-layout>
    @vite(['resources/css/inventory.css', 'resources/js/inventory.js'])

    <style>
        .loans-page-v01 {
            --inventory-theme-color: {{ $theme['color'] }};
            --inventory-theme-text: {{ $theme['text'] }};
            --inventory-theme-tint: {{ $theme['tint'] }};
            --inventory-theme-border: {{ $theme['border'] }};
            --inventory-theme-soft: {{ $theme['soft'] }};
        }
    </style>

    @php
        $toastMessages = [];
        if (session('status')) { $toastMessages[] = ['text' => session('status'), 'type' => 'success']; }
        if ($errors->any()) { $toastMessages[] = ['text' => $errors->first() ?: 'Revisa los campos.', 'type' => 'error']; }
    @endphp

    <script id="inventory-toast-data" type="application/json">{{ json_encode($toastMessages) }}</script>

    <x-slot name="header">
        <x-page-header eyebrow="Inventario" title="Préstamos de Mercancía" description="Controla productos entregados a terceros, consignaciones y fechas de retorno.">
            <x-slot name="actions">
                <a href="{{ route('inventory.index') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Volver a Inventario</a>
                <a href="{{ route('inventory.loans.create') }}" class="rounded-lg text-sm font-bold text-white px-4 py-1.5 shadow-sm" style="background:{{ $theme['color'] }}">+ Nuevo Préstamo</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="loans-page-v01 p-3 lg:p-5 h-full flex flex-col space-y-4">
        {{-- Metrics Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Activos</span>
                <span class="text-2xl font-black text-indigo-700 mt-1 block">{{ number_format($metrics['active']) }}</span>
                <span class="text-[11px] text-gray-400">Préstamos en curso</span>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Vencidos</span>
                <span class="text-2xl font-black {{ $metrics['overdue'] > 0 ? 'text-rose-600' : 'text-gray-700' }} mt-1 block">{{ number_format($metrics['overdue']) }}</span>
                <span class="text-[11px] text-gray-400">Fecha límite superada</span>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Unidades Fuera</span>
                <span class="text-2xl font-black text-amber-600 mt-1 block">{{ number_format($metrics['pending_units']) }}</span>
                <span class="text-[11px] text-gray-400">Pendientes por retornar</span>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Valor Prestado</span>
                <span class="text-xl font-black text-gray-900 mt-1 block">${{ number_format($metrics['pending_value'], 0, ',', '.') }}</span>
                <span class="text-[11px] text-gray-400">Mercancía en la calle</span>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm col-span-2 md:col-span-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Histórico</span>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">{{ $metrics['returned'] }} devueltos</span>
                    <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">{{ $metrics['sold'] }} liquidados</span>
                </div>
                <span class="text-[11px] text-gray-400 mt-1 block">{{ number_format($metrics['total']) }} totales</span>
            </div>
        </div>

        {{-- Filters & Search --}}
        <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
            <form method="GET" action="{{ route('inventory.loans.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar por N° préstamo, prestatario, teléfono o producto..." class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" autocomplete="off">
                </div>
                <div>
                    <select name="status" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                        <option value="">Todos los estados</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activos</option>
                        <option value="overdue" @selected(($filters['status'] ?? '') === 'overdue')>Vencidos</option>
                        <option value="partial" @selected(($filters['status'] ?? '') === 'partial')>Parciales</option>
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
                                        {{ $loan->items->pluck('product.name')->filter()->join(', ') }}
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
                                    <p class="font-bold text-gray-700">No hay préstamos de mercancía registrados.</p>
                                    <p class="text-xs text-gray-400 mt-1">Crea tu primer préstamo para entregar mercancía a terceros con trazabilidad de retorno.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('inventory.loans.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-xs font-bold text-white hover:bg-blue-800">
                                            + Crear Préstamo
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
