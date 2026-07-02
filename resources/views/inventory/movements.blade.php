@php
    $movementLabels = [
        'initial' => 'Inicial',
        'adjustment' => 'Ajuste',
        'shipment' => 'Guia',
        'restock' => 'Reposicion',
        'manual_in' => 'Entrada',
        'manual_out' => 'Salida',
        'status_change' => 'Estado',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Inventario" title="Historial de movimientos" description="Entradas, salidas y ajustes de stock de tus productos.">
            <x-slot name="actions">
                <a href="{{ route('inventory.movements.export.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Exportar PDF
                </a>
                <a href="{{ route('inventory.movements.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Exportar CSV
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('inventory.movements') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_170px_170px_180px_auto_auto] md:items-end">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Buscar
                        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Producto, SKU, guia o nota" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Desde
                        <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Hasta
                        <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Tipo
                        <select name="type" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Todos</option>
                            @foreach ($movementLabels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Filtrar</button>
                    <a href="{{ route('inventory.movements') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Limpiar</a>
                </form>
            </section>

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Movimientos</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $movementSummary['total'] }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-emerald-700">Entradas</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-800">+{{ $movementSummary['entries'] }}</p>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-red-700">Salidas</p>
                    <p class="mt-2 text-2xl font-semibold text-red-800">-{{ $movementSummary['exits'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Neto</p>
                    <p class="mt-2 text-2xl font-semibold {{ $movementSummary['net'] < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $movementSummary['net'] > 0 ? '+' : '' }}{{ $movementSummary['net'] }}</p>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-blue-700">Guias</p>
                    <p class="mt-2 text-2xl font-semibold text-blue-800">{{ $movementSummary['shipment_units'] }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-amber-700">Reposicion</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-800">{{ $movementSummary['restock_units'] }}</p>
                </div>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Kardex</p>
                        <h3 class="text-lg font-semibold text-gray-950">Movimientos registrados</h3>
                    </div>
                    <p class="text-sm text-gray-500">{{ $movements->total() }} movimientos</p>
                </div>

                <div class="hidden grid-cols-[150px_minmax(0,1fr)_120px_110px_120px_minmax(0,1fr)] gap-4 border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-semibold uppercase text-gray-500 lg:grid">
                    <span>Fecha</span>
                    <span>Producto</span>
                    <span>Tipo</span>
                    <span>Cantidad</span>
                    <span>Stock</span>
                    <span>Detalle</span>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <div class="grid gap-3 px-5 py-4 text-sm lg:grid-cols-[150px_minmax(0,1fr)_120px_110px_120px_minmax(0,1fr)] lg:items-start lg:gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Fecha</p>
                                <p class="font-semibold text-gray-700">{{ $movement->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Producto</p>
                                <p class="font-semibold text-gray-950">{{ $movement->product?->name ?? 'Producto eliminado' }}</p>
                                @if ($movement->product?->sku)
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $movement->product->sku }}</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Tipo</p>
                                <p class="font-semibold text-gray-700">{{ $movementLabels[$movement->type] ?? $movement->type }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Cantidad</p>
                                <p class="font-semibold {{ $movement->quantity_delta < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Stock</p>
                                <p class="font-semibold text-gray-950">{{ $movement->stock_after }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Detalle</p>
                                @if ($movement->shipment)
                                    <a href="{{ route('shipments.show', $movement->shipment) }}" class="font-semibold text-blue-800 hover:text-blue-900">{{ $movement->shipment->guide_number }}</a>
                                @endif
                                @if ($movement->notes)
                                    <p class="mt-1 text-gray-600">{{ $movement->notes }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="font-semibold text-gray-950">Sin movimientos registrados.</p>
                            <p class="mt-1 text-sm text-gray-500">Cuando agregues stock o crees guias con inventario, apareceran aqui.</p>
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $movements->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
