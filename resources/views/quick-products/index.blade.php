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
    $firstProduct = $activeProducts->first();

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
        <x-page-header eyebrow="Productos" title="Productos rapidos" description="Guarda lo que mas vendes y empieza una guia sin repetir datos." />
    </x-slot>

    <div class="qp-page p-3 lg:p-5 h-full overflow-y-auto space-y-3">
        <section class="qp-action-panel">
            <div class="min-w-0">
                <p class="text-xs font-black uppercase tracking-wider text-[var(--qp-color)]">Atajo principal</p>
                <h2 class="mt-1 text-xl font-black text-gray-950">Crea guias desde tus productos frecuentes</h2>
                <p class="mt-1 max-w-2xl text-sm font-semibold text-gray-600">El cliente solo debe elegir el producto, completar los datos del destinatario y crear la guia.</p>
            </div>
            <div class="qp-action-panel__cta">
                @if ($firstProduct)
                    <a href="{{ route('shipments.create', ['quick_product' => $firstProduct->id]) }}" class="qp-btn qp-btn-primary qp-btn-lg">Crear guia ahora</a>
                @else
                    <a href="#new-product" class="qp-btn qp-btn-primary qp-btn-lg">Agregar primer producto</a>
                @endif
                <a href="{{ route('shipments.create') }}" class="qp-btn qp-btn-secondary qp-btn-lg">Guia manual</a>
            </div>
        </section>

        <section class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Productos listos</p>
                        <h3 class="text-base font-black text-gray-950">{{ $activeProducts->count() }} activo(s)</h3>
                    </div>
                    <button type="button" data-qp-edit-toggle class="qp-btn qp-btn-secondary">Editar lista</button>
                </div>

                @if ($activeProducts->count())
                    <div class="qp-card-grid p-4">
                        @foreach ($activeProducts as $product)
                            <article class="qp-product-card">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-black text-gray-950">{{ $product->name }}</p>
                                    <p class="mt-1 text-xs font-bold text-gray-500">{{ $packageLabels[$product->package_type] ?? 'Producto' }}</p>
                                    <p class="mt-2 text-sm font-black text-gray-900">${{ number_format((float) $product->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <a href="{{ route('shipments.create', ['quick_product' => $product->id]) }}" class="qp-btn qp-btn-primary flex-1">Crear guia</a>
                                    <button type="button" data-qp-edit-toggle class="qp-icon-btn" aria-label="Editar {{ $product->name }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L7.5 19.152 3 21l1.848-4.5L16.862 4.487Z" /></svg>
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-4 py-10 text-center">
                        <svg class="mx-auto h-11 w-11 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M6 7l1 13h10l1-13M9 7V5a3 3 0 0 1 6 0v2" /></svg>
                        <p class="mt-3 text-base font-black text-gray-950">Agrega los productos que mas repites</p>
                        <p class="mt-1 text-sm font-semibold text-gray-500">Despues podras crear una guia con un clic.</p>
                    </div>
                @endif
            </div>

            <aside id="new-product" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Nuevo atajo</p>
                <h3 class="mt-1 text-base font-black text-gray-950">Agregar producto</h3>
                <form method="POST" action="{{ route('quick-products.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre del producto</label>
                        <input name="name" value="{{ old('name', request('name')) }}" required placeholder="Ej. Camiseta, kit skincare" class="qp-field">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Como lo envias</label>
                        <select name="package_type" class="qp-field">
                            <option value="merchandise" @selected(old('package_type', request('package_type', 'merchandise')) === 'merchandise')>Mercancia</option>
                            <option value="package" @selected(old('package_type', request('package_type', 'merchandise')) === 'package')>Paquete</option>
                            <option value="document" @selected(old('package_type', request('package_type', 'merchandise')) === 'document')>Documento</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Precio de venta</label>
                        <input name="price" value="{{ old('price', request('price', 0)) }}" type="number" min="0" step="100" inputmode="numeric" class="qp-field">
                    </div>
                    <button class="qp-btn qp-btn-primary w-full">Guardar producto</button>
                </form>

                <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Flujo simple</p>
                    <ol class="mt-2 space-y-2 text-sm font-semibold text-gray-700">
                        <li>1. Guarda el producto.</li>
                        <li>2. Pulsa Crear guia.</li>
                        <li>3. Completa cliente y destino.</li>
                    </ol>
                </div>
            </aside>
        </section>

        <details id="qp-edit-products" class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <summary class="cursor-pointer list-none px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Opcional</p>
                        <h3 class="text-base font-black text-gray-950">Administrar productos</h3>
                    </div>
                    <span class="qp-btn qp-btn-secondary">Abrir</span>
                </div>
            </summary>

            <div class="border-t border-gray-200">
                <div class="qp-head border-b border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-bold uppercase text-gray-500">
                    <span>Producto</span>
                    <span>Tipo</span>
                    <span>Precio</span>
                    <span>Estado</span>
                    <span>Accion</span>
                </div>

                <div class="divide-y divide-gray-200">
                    @if ($products->count() && $products->total() > 0)
                        @foreach ($products as $product)
                            <form method="POST" action="{{ route('quick-products.update', $product) }}" class="qp-row">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <p class="qp-mobile-label">Producto</p>
                                    <input name="name" value="{{ old('name', $product->name) }}" required class="qp-field">
                                </div>

                                <div>
                                    <p class="qp-mobile-label">Tipo</p>
                                    <select name="package_type" class="qp-field">
                                        @foreach ($packageLabels as $value => $label)
                                            <option value="{{ $value }}" @selected(old('package_type', $product->package_type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <p class="qp-mobile-label">Precio</p>
                                    <input name="price" value="{{ old('price', (int) $product->price) }}" type="number" min="0" step="100" inputmode="numeric" class="qp-field">
                                </div>

                                <div>
                                    <p class="qp-mobile-label">Estado</p>
                                    <select name="status" class="qp-field">
                                        <option value="active" @selected(old('status', $product->status) === 'active')>Activo</option>
                                        <option value="paused" @selected(old('status', $product->status) === 'paused')>Pausado</option>
                                    </select>
                                </div>

                                <div>
                                    <p class="qp-mobile-label">Accion</p>
                                    <div class="flex gap-1.5">
                                        <button class="qp-btn qp-btn-primary">Guardar</button>
                                        <button type="button" class="qp-btn qp-btn-secondary" onclick="document.getElementById('confirm-qp-{{ $product->id }}').classList.remove('hidden')">Eliminar</button>
                                    </div>
                                </div>
                            </form>

                            <x-confirmation-modal id="confirm-qp-{{ $product->id }}" title="Eliminar producto" message="Se eliminara permanentemente el producto &quot;{{ $product->name }}&quot;." confirmText="Eliminar" cancelText="Cancelar" />
                            <form id="confirm-qp-{{ $product->id }}-form" method="POST" action="{{ route('quick-products.destroy', $product) }}" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        @endforeach
                    @else
                        <div class="px-4 py-8 text-center text-sm font-semibold text-gray-500">No hay productos para administrar todavia.</div>
                    @endif
                </div>

                @if ($products->hasPages())
                    <div class="border-t border-gray-200 px-5 py-3">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </details>

        @if ($pausedProducts->count())
            <p class="px-1 text-xs font-semibold text-gray-500">{{ $pausedProducts->count() }} producto(s) pausado(s) estan disponibles en Administrar productos.</p>
        @endif
    </div>
</x-app-layout>
