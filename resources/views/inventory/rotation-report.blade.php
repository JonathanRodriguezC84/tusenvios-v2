@php
    $actionLabels = [
        'agotado' => 'Agotado',
        'reponer' => 'Reponer',
        'moviendo' => 'En movimiento',
        'quieto' => 'Quieto',
        'pausado' => 'Pausado',
    ];

    $actionClasses = [
        'agotado' => 'bg-red-100 text-red-800',
        'reponer' => 'bg-amber-100 text-amber-800',
        'moviendo' => 'bg-emerald-100 text-emerald-800',
        'quieto' => 'bg-gray-100 text-gray-700',
        'pausado' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Inventario" title="Rotacion de productos" description="Productos con mayor y menor movimiento en el periodo.">
            <x-slot name="actions">
                <a href="{{ route('inventory.reports.rotation.export.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Exportar PDF</a>
                <a href="{{ route('inventory.reports.rotation.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Exportar CSV</a>
                <a href="{{ route('inventory.reports.sales') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Reporte ventas</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Productos</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $totals['products'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Vendidas</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $totals['units'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Stock actual</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $totals['stock_units'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Valor stock</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">${{ number_format((float) $totals['stock_value'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-blue-700">Compra sug.</p>
                    <p class="mt-2 text-2xl font-semibold text-blue-800">{{ $totals['reorder_units'] }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-amber-700">Por reponer</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-800">{{ $totals['restock'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Quietos</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $totals['quiet'] }}</p>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('inventory.reports.rotation') }}" class="grid gap-3 md:grid-cols-[minmax(180px,1fr)_1fr_130px_110px_190px_auto_auto] md:items-end">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Buscar
                        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Producto, SKU o categoria" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Categoria
                        <select name="category" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Todas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Accion
                        <select name="action" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="">Todas</option>
                            <option value="comprar" @selected(($filters['action'] ?? '') === 'comprar')>Compra sugerida</option>
                            @foreach ($actionLabels as $action => $label)
                                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Periodo
                        <select name="days" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            @foreach ([7, 30, 60, 90] as $option)
                                <option value="{{ $option }}" @selected($days === $option)>{{ $option }} dias</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Ordenar
                        <select name="sort" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="velocity" @selected(($filters['sort'] ?? 'velocity') === 'velocity')>Mayor rotacion</option>
                            <option value="units" @selected(($filters['sort'] ?? '') === 'units')>Mas unidades</option>
                            <option value="reorder" @selected(($filters['sort'] ?? '') === 'reorder')>Mayor compra sugerida</option>
                            <option value="stock" @selected(($filters['sort'] ?? '') === 'stock')>Menor valor stock</option>
                            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nombre</option>
                        </select>
                    </label>
                    <button class="shrink-0 rounded-md bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Filtrar</button>
                    <a href="{{ route('inventory.reports.rotation') }}" class="shrink-0 inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Limpiar</a>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Analisis</p>
                    <h3 class="text-lg font-semibold text-gray-950">Movimiento de los ultimos {{ $days }} dias</h3>
                </div>

                <div class="hidden grid-cols-[minmax(0,1.4fr)_80px_95px_90px_100px_100px_120px_120px] gap-4 border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-semibold uppercase text-gray-500 lg:grid">
                    <span>Producto</span>
                    <span>Vendidas</span>
                    <span>Promedio/dia</span>
                    <span>Stock</span>
                    <span>Cobertura</span>
                    <span>Comprar</span>
                    <span>Accion</span>
                    <span>Entrada</span>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        @php($product = $row['product'])
                        <div class="grid gap-3 px-5 py-4 text-sm lg:grid-cols-[minmax(0,1.4fr)_80px_95px_90px_100px_100px_120px_120px] lg:gap-4">
                            <div>
                                <p class="font-semibold text-gray-950">{{ $product->name }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500">
                                    {{ $product->sku ?: 'Sin SKU' }}{{ $product->category ? ' / '.$product->category : '' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Vendidas</p>
                                <p class="font-semibold text-gray-950">{{ $row['units'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Promedio/dia</p>
                                <p class="font-semibold text-gray-950">{{ number_format((float) $row['daily_average'], 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Stock</p>
                                <p class="font-semibold text-gray-950">{{ $product->stock }}</p>
                                <p class="text-xs text-gray-500">Min. {{ $product->stock_minimum }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Cobertura</p>
                                <p class="font-semibold text-gray-950">
                                    {{ is_null($row['days_of_stock']) ? 'Sin ventas' : $row['days_of_stock'].' dias' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Comprar</p>
                                <p class="font-semibold text-blue-800">{{ $row['reorder_quantity'] }}</p>
                                <p class="text-xs text-gray-500">${{ number_format((float) $row['reorder_cost'], 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $actionClasses[$row['action']] }}">
                                    {{ $actionLabels[$row['action']] }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Entrada</p>
                                @if ($product->status === 'active' && $row['reorder_quantity'] > 0)
                                    <form method="POST" action="{{ route('inventory.movement', $product) }}" class="grid gap-2">
                                        @csrf
                                        <input type="hidden" name="type" value="manual_in">
                                        <input type="hidden" name="quantity" value="{{ $row['reorder_quantity'] }}">
                                        <input type="hidden" name="notes" value="Reposicion sugerida desde rotacion">
                                        <button class="rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800 hover:bg-blue-100">
                                            Registrar {{ $row['reorder_quantity'] }}
                                        </button>
                                    </form>
                                @elseif ($row['reorder_quantity'] > 0)
                                    <p class="text-xs font-semibold text-gray-500">Activalo para reponer</p>
                                @else
                                    <p class="text-xs font-semibold text-gray-400">Sin compra</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="font-semibold text-gray-950">No hay productos para este filtro.</p>
                            <p class="mt-1 text-sm text-gray-500">Cambia la busqueda o el periodo para ampliar el analisis.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
