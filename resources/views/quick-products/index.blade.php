@php
    use App\Services\InventoryService;
    $theme = app(InventoryService::class)->themeVariables();
    $qpColor = $theme['color'];
    $qpText = $theme['text'];
    $qpBorder = $theme['border'];
    $qpTint = $theme['tint'];
    $qpSoft = $theme['soft'];

    $packageLabels = [
        'package' => 'Paquete',
        'document' => 'Documento',
        'merchandise' => 'Mercancia',
    ];

    $visibleProducts = $products->getCollection();
    $activeProducts = $visibleProducts->where('status', 'active');
    $pausedProducts = $visibleProducts->where('status', 'paused');

    $toastMessages = [];
    if (session('status')) { $toastMessages[] = ['text' => session('status'), 'type' => 'success']; }
    if ($errors->any()) { $toastMessages[] = ['text' => $errors->first() ?: 'Revisa los campos.', 'type' => 'error']; }
@endphp

<x-app-layout>
    @vite(['resources/css/quick-products.css', 'resources/js/quick-products.js'])

    <script id="qp-toast-data" type="application/json">{{ json_encode($toastMessages) }}</script>

    <style>
        .qp-page { --qp-color: {{ $qpColor }}; --qp-color-text: {{ $qpText }}; --qp-color-border: {{ $qpBorder }}; --qp-color-tint: {{ $qpTint }}; --qp-color-soft: {{ $qpSoft }}; }
    </style>

    <x-slot name="header">
        <x-page-header eyebrow="Productos" title="Productos rapidos" description="Guarda lo que mas vendes y empieza una guia sin repetir datos.">
    <x-slot name="actions">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-2">
            <a href="{{ route('quick-products.template') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Descargar plantilla Excel</a>
            <form method="POST" action="{{ route('quick-products.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <label class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    <span id="qp-file-label">Seleccionar archivo</span>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required class="sr-only" onchange="document.getElementById('qp-file-label').textContent = this.files.length ? this.files[0].name : 'Seleccionar archivo'">
                </label>
                <button class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-800">Subir masivamente</button>
            </form>
            @if (Auth::user()->canCreateShipments())
                <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
            @endif
        </div>
    </x-slot>
</x-page-header>
    </x-slot>

    <div class="qp-page p-3 lg:p-5 h-full flex flex-col gap-3">
        <aside id="new-product" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm shrink-0">
            <p class="text-xs font-black uppercase tracking-wider text-gray-900">Nuevo atajo</p>
            <h3 class="mt-1 text-base font-black text-gray-900">Agregar producto</h3>
            <form method="POST" action="{{ route('quick-products.store') }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_1fr_1fr_1fr_1fr_1fr_auto] lg:items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre del producto</label>
                    <input name="name" value="{{ old('name', request('name')) }}" required placeholder="Ej. Camiseta, kit skincare" class="qp-field">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">SKU</label>
                    <input name="sku" value="{{ old('sku', request('sku')) }}" placeholder="Ej. CAM-001" class="qp-field">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tipo</label>
                    <select name="package_type" class="qp-field">
                        <option value="merchandise" @selected(old('package_type', request('package_type', 'merchandise')) === 'merchandise')>Mercancia</option>
                        <option value="package" @selected(old('package_type', request('package_type', 'merchandise')) === 'package')>Paquete</option>
                        <option value="document" @selected(old('package_type', request('package_type', 'merchandise')) === 'document')>Documento</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Costo</label>
                    <input name="cost" value="{{ old('cost', request('cost', 0)) }}" type="number" min="0" step="100" inputmode="numeric" class="qp-field">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Precio venta</label>
                    <input name="price" value="{{ old('price', request('price', 0)) }}" type="number" min="0" step="100" inputmode="numeric" class="qp-field">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">UND</label>
                    <input name="stock" value="{{ old('stock', request('stock', 0)) }}" type="number" min="0" step="1" inputmode="numeric" class="qp-field">
                </div>
                <button class="qp-btn qp-btn-primary">Guardar</button>
            </form>
        </aside>

        <section class="flex-1 min-h-0 flex flex-col">
            <div id="qp-edit-products" class="rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col min-h-0">
                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between shrink-0">
                    <h3 class="text-base font-black text-gray-950">Administrar productos</h3>
                    <form method="GET" action="{{ route('quick-products.index') }}" class="flex items-center gap-2">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar producto..." class="qp-field sm:w-64">
                        @if (request()->filled('q'))
                            <a href="{{ route('quick-products.index') }}" class="whitespace-nowrap text-sm font-semibold text-gray-500 hover:text-gray-700">Limpiar</a>
                        @endif
                    </form>
                </div>

                <div class="border-t border-gray-200 flex flex-col min-h-0">
                    <div class="qp-head border-b border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-bold uppercase text-gray-500 shrink-0">
                        <span>Nombre de producto</span>
                        <span>SKU</span>
                        <span>Tipo</span>
                        <span>Costo</span>
                        <span>Precio venta</span>
                        <span>UND</span>
                        <span>Estado</span>
                        <span>Acciones</span>
                    </div>

                    <div class="divide-y divide-gray-200 overflow-y-auto min-h-0 flex-1">
                        @if ($products->count() && $products->total() > 0)
                            @foreach ($products as $product)
                                <form method="POST" action="{{ route('quick-products.update', $product) }}" class="qp-row qp-row-editable" data-qp-row>
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <p class="qp-mobile-label">Nombre de producto</p>
                                        <input name="name" value="{{ old('name', $product->name) }}" required class="qp-field" disabled>
                                    </div>

                                    <div>
                                        <p class="qp-mobile-label">SKU</p>
                                        <input name="sku" value="{{ old('sku', $product->sku) }}" placeholder="Sin SKU" class="qp-field" disabled>
                                    </div>

                                    <div>
                                        <p class="qp-mobile-label">Tipo</p>
                                        <select name="package_type" class="qp-field" disabled>
                                            @foreach ($packageLabels as $value => $label)
                                                <option value="{{ $value }}" @selected(old('package_type', $product->package_type) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <p class="qp-mobile-label">Costo</p>
                                        <input name="cost" value="{{ old('cost', (int) $product->cost) }}" type="number" min="0" step="100" inputmode="numeric" class="qp-field" disabled>
                                    </div>

                                    <div>
                                        <p class="qp-mobile-label">Precio venta</p>
                                        <input name="price" value="{{ old('price', (int) $product->price) }}" type="number" min="0" step="100" inputmode="numeric" class="qp-field" disabled>
                                    </div>

                                    <div>
                                        <p class="qp-mobile-label">UND</p>
                                        <input name="stock" value="{{ old('stock', (int) $product->stock) }}" type="number" min="0" step="1" inputmode="numeric" class="qp-field" disabled>
                                    </div>

                                    <div>
                                        <p class="qp-mobile-label">Estado</p>
                                        <select name="status" class="qp-field" disabled>
                                            <option value="active" @selected(old('status', $product->status) === 'active')>Activo</option>
                                            <option value="paused" @selected(old('status', $product->status) === 'paused')>Pausado</option>
                                        </select>
                                    </div>

                                    <div>
                                        <p class="qp-mobile-label">Acciones</p>
                                        <div class="qp-row-actions">
                                            <button type="button" class="qp-btn qp-btn-secondary qp-row-edit-btn">Editar</button>
                                            <button type="submit" class="qp-btn qp-btn-primary qp-row-save-btn">Guardar</button>
                                            <button type="button" class="qp-btn qp-btn-danger qp-row-delete-btn" onclick="document.getElementById('confirm-qp-{{ $product->id }}').classList.remove('hidden')">Eliminar</button>
                                        </div>
                                    </div>
                                </form>

                                <x-confirmation-modal id="confirm-qp-{{ $product->id }}" title="Eliminar producto" message="Se eliminara permanentemente el producto &quot;{{ $product->name }}&quot;." confirmText="Eliminar" cancelText="Cancelar" />
                                <form id="confirm-qp-{{ $product->id }}-form" method="POST" action="{{ route('quick-products.destroy', $product) }}" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endforeach
                        @else
                            <div class="px-4 py-8 text-center text-sm font-semibold text-gray-500">{{ request()->filled('q') ? 'No hay productos que coincidan con &quot;'.e(request('q')).'&quot;.' : 'No hay productos para administrar todavia.' }}</div>
                        @endif
                    </div>

                    @if ($products->hasPages())
                        <div class="border-t border-gray-200 px-5 py-3 shrink-0">
                            {{ $products->links('vendor.pagination.compact') }}
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if ($pausedProducts->count())
            <p class="px-1 text-xs font-semibold text-gray-500">{{ $pausedProducts->count() }} producto(s) pausado(s) estan disponibles en Administrar productos.</p>
        @endif
    </div>
</x-app-layout>
