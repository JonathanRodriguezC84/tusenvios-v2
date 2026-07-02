<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Inventario" title="Ventas por producto" description="Productos mas vendidos, ingresos y unidades despachadas.">
            <x-slot name="actions">
                <a href="{{ route('inventory.reports.sales.export.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Exportar PDF</a>
                <a href="{{ route('inventory.reports.sales.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Exportar CSV</a>
                <a href="{{ route('inventory.reports.categories') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Categorias</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Productos</p>
                    <p class="mt-2 text-2xl font-black text-gray-950">{{ $totals['products'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Unidades</p>
                    <p class="mt-2 text-2xl font-black text-gray-950">{{ $totals['units'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Guias unicas</p>
                    <p class="mt-2 text-2xl font-black text-gray-950">{{ $totals['shipments'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Lineas</p>
                    <p class="mt-2 text-2xl font-black text-gray-950">{{ $totals['lines'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Venta productos</p>
                    <p class="mt-2 text-2xl font-black text-emerald-700">${{ number_format((float) $totals['sales_value'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Costo estimado</p>
                    <p class="mt-2 text-2xl font-black text-gray-950">${{ number_format((float) $totals['cost_value'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Utilidad est.</p>
                    <p class="mt-2 text-2xl font-black text-emerald-700">${{ number_format((float) $totals['profit_value'], 0, ',', '.') }}</p>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('inventory.reports.sales') }}" class="grid gap-3 md:grid-cols-[minmax(220px,1.4fr)_1fr_1fr_1fr_180px_auto_auto] md:items-end">
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
                        Desde
                        <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Hasta
                        <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Ordenar
                        <select name="sort" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="sales" @selected(($filters['sort'] ?? 'sales') === 'sales')>Mayor venta</option>
                            <option value="profit" @selected(($filters['sort'] ?? '') === 'profit')>Mayor utilidad</option>
                            <option value="margin" @selected(($filters['sort'] ?? '') === 'margin')>Mayor margen</option>
                            <option value="units" @selected(($filters['sort'] ?? '') === 'units')>Mas unidades</option>
                            <option value="shipments" @selected(($filters['sort'] ?? '') === 'shipments')>Mas guias</option>
                            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nombre</option>
                        </select>
                    </label>
                    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Filtrar</button>
                    <a href="{{ route('inventory.reports.sales') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Limpiar</a>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Resumen</p>
                    <h3 class="text-lg font-semibold text-gray-950">Productos vendidos desde inventario</h3>
                </div>

                <div class="hidden grid-cols-[minmax(0,1.3fr)_80px_80px_110px_110px_110px_80px_minmax(0,1fr)] gap-4 border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-black uppercase text-gray-500 lg:grid">
                    <span>Producto</span>
                    <span>Unidades</span>
                    <span>Guias</span>
                    <span>Venta</span>
                    <span>Costo</span>
                    <span>Utilidad</span>
                    <span>Margen</span>
                    <span>Guias relacionadas</span>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        <div class="grid gap-3 px-5 py-4 text-sm lg:grid-cols-[minmax(0,1.3fr)_80px_80px_110px_110px_110px_80px_minmax(0,1fr)] lg:gap-4">
                            <div>
                                <p class="font-semibold text-gray-950">{{ $row['name'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500">
                                    {{ $row['sku'] ?: 'Sin SKU' }}{{ $row['category'] ? ' / '.$row['category'] : '' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Unidades</p>
                                <p class="font-semibold text-gray-950">{{ $row['units'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Guias</p>
                                <p class="font-semibold text-gray-950">{{ $row['shipments_count'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Venta</p>
                                <p class="font-semibold text-emerald-700">${{ number_format((float) $row['sales_value'], 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Costo</p>
                                <p class="font-semibold text-gray-950">${{ number_format((float) $row['cost_value'], 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Utilidad</p>
                                <p class="font-semibold text-emerald-700">${{ number_format((float) $row['profit_value'], 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Margen</p>
                                <p class="font-semibold text-gray-950">{{ number_format((float) $row['margin_percent'], 1, ',', '.') }}%</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Guias relacionadas</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (array_slice($row['shipments'], 0, 5) as $shipment)
                                        <a href="{{ route('shipments.show', $shipment['id']) }}" class="rounded-md bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700 hover:bg-gray-200">
                                            {{ $shipment['guide_number'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="font-semibold text-gray-950">Aun no hay ventas de inventario.</p>
                            <p class="mt-1 text-sm text-gray-500">Cuando crees guias usando productos de inventario, apareceran aqui.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
