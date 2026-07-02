<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Inventario" title="Categorias" description="Distribucion de productos por categoria y su rendimiento.">
            <x-slot name="actions">
                <a href="{{ route('inventory.reports.categories.export.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Exportar PDF</a>
                <a href="{{ route('inventory.reports.categories.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Exportar CSV</a>
                <a href="{{ route('inventory.reports.rotation') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Rotacion</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Categorias</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $totals['categories'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Productos</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $totals['products'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Unidades</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $totals['units'] }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-amber-700">Alertas</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-800">{{ $totals['low_stock'] + $totals['out_stock'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Venta activa</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-950">${{ number_format((float) $totals['sale_value'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-emerald-700">Utilidad pot.</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-800">${{ number_format((float) $totals['profit_value'], 0, ',', '.') }}</p>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('inventory.reports.categories') }}" class="grid gap-3 md:grid-cols-[minmax(220px,1fr)_220px_auto_auto] md:items-end">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Buscar
                        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Categoria" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Ordenar
                        <select name="sort" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="sale_value" @selected(($filters['sort'] ?? 'sale_value') === 'sale_value')>Mayor venta activa</option>
                            <option value="profit_value" @selected(($filters['sort'] ?? '') === 'profit_value')>Mayor utilidad</option>
                            <option value="units" @selected(($filters['sort'] ?? '') === 'units')>Mas unidades</option>
                            <option value="alerts" @selected(($filters['sort'] ?? '') === 'alerts')>Mas alertas</option>
                            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nombre</option>
                        </select>
                    </label>
                    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Filtrar</button>
                    <a href="{{ route('inventory.reports.categories') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Limpiar</a>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Analisis</p>
                    <h3 class="text-lg font-semibold text-gray-950">Valor y alertas por categoria</h3>
                </div>

                <div class="hidden grid-cols-[minmax(0,1.2fr)_90px_90px_90px_110px_120px_120px_90px_150px] gap-4 border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-semibold uppercase text-gray-500 lg:grid">
                    <span>Categoria</span>
                    <span>Productos</span>
                    <span>Unidades</span>
                    <span>Alertas</span>
                    <span>Costo</span>
                    <span>Venta</span>
                    <span>Utilidad</span>
                    <span>Margen</span>
                    <span>Detalle</span>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        @php($categoryFilter = $row['category'] === 'Sin categoria' ? '' : $row['category'])
                        <div class="grid gap-3 px-5 py-4 text-sm lg:grid-cols-[minmax(0,1.2fr)_90px_90px_90px_110px_120px_120px_90px_150px] lg:gap-4">
                            <div>
                                <a href="{{ route('inventory.index', ['category' => $categoryFilter]) }}" class="font-semibold text-gray-950 hover:text-blue-700">{{ $row['category'] }}</a>
                                <p class="mt-1 text-xs text-gray-500">{{ $row['active'] }} activos / {{ $row['paused'] }} pausados</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Productos</p>
                                <p class="font-semibold text-gray-950">{{ $row['products'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Unidades</p>
                                <p class="font-semibold text-gray-950">{{ $row['units'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Alertas</p>
                                <p class="font-semibold {{ ($row['low_stock'] + $row['out_stock']) > 0 ? 'text-amber-700' : 'text-gray-950' }}">{{ $row['low_stock'] + $row['out_stock'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Costo</p>
                                <p class="font-semibold text-gray-950">${{ number_format((float) $row['cost_value'], 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Venta</p>
                                <p class="font-semibold text-gray-950">${{ number_format((float) $row['sale_value'], 0, ',', '.') }}</p>
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
                                <p class="text-xs font-semibold uppercase text-gray-500 lg:hidden">Detalle</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('inventory.index', ['category' => $categoryFilter]) }}" class="rounded-md bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700 hover:bg-gray-200">
                                        Inventario
                                    </a>
                                    @if ($categoryFilter !== '')
                                        <a href="{{ route('inventory.reports.sales', ['category' => $categoryFilter]) }}" class="rounded-md bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800 hover:bg-emerald-200">
                                            Ventas
                                        </a>
                                        <a href="{{ route('inventory.reports.rotation', ['category' => $categoryFilter]) }}" class="rounded-md bg-blue-100 px-2 py-1 text-xs font-bold text-blue-800 hover:bg-blue-200">
                                            Rotacion
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="font-semibold text-gray-950">No hay categorias para este filtro.</p>
                            <p class="mt-1 text-sm text-gray-500">Cambia la busqueda para ampliar el analisis.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
