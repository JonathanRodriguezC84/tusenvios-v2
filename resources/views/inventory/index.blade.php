<x-app-layout>
    @vite(['resources/css/inventory.css', 'resources/js/inventory.js'])

    <style>
        .inventory-page-v01 {
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
        <x-page-header eyebrow="Inventario" title="Listado" description="Gestiona tus productos, stock y precios.">
            <x-slot name="actions">
                <a href="{{ route('inventory.movements') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Movimientos</a>
                <a href="{{ route('inventory.reports.sales') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reportes</a>
                <a href="{{ route('inventory.create') }}" class="rounded-lg text-sm font-bold text-white px-4 py-1.5 shadow-sm" style="background:{{ $theme['color'] }}">+ Agregar</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if ($metrics['products'] === 0)
        <div class="flex flex-col items-center justify-center h-full text-center px-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-950">Tu inventario esta vacio</h3>
            <p class="mt-2 text-sm text-gray-600 max-w-md leading-relaxed">
                Agrega tus primeros productos para controlar stock, precios y utilidad.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('inventory.create') }}" class="rounded-xl bg-blue-700 px-6 py-3 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Agregar producto</a>
                <a href="{{ route('inventory.template') }}" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Importar CSV</a>
            </div>
        </div>
    @else
    <div class="inventory-page-v01 p-3 lg:p-5 h-full flex flex-col">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap items-end gap-2 flex-1">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar producto, SKU o categoria..." class="flex-1 min-w-[180px] rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" autocomplete="off">
                <select name="status" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                    <option value="">Estado</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activos</option>
                    <option value="paused" @selected(($filters['status'] ?? '') === 'paused')>Pausados</option>
                </select>
                <select name="stock" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                    <option value="">Stock</option>
                    <option value="ok" @selected(($filters['stock'] ?? '') === 'ok')>OK</option>
                    <option value="low" @selected(($filters['stock'] ?? '') === 'low')>Bajo</option>
                    <option value="out" @selected(($filters['stock'] ?? '') === 'out')>Agotado</option>
                </select>
                <select name="sort" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                    <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Recientes</option>
                    <option value="alert" @selected(($filters['sort'] ?? '') === 'alert')>Alertas</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nombre</option>
                    <option value="stock_low" @selected(($filters['sort'] ?? '') === 'stock_low')>Menor stock</option>
                </select>
                <button class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Filtrar</button>
                <a href="{{ route('inventory.index') }}" class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpiar</a>
                <a href="{{ route('inventory.template') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700 ml-auto">Importar CSV</a>
            </form>
            @php
                $invChips = collect();
                if (!empty($filters['search'])) $invChips->push(['label' => '"'.$filters['search'].'"', 'route' => route('inventory.index', array_merge(request()->except(['search', 'page']), ['search' => '']))]);
                if (!empty($filters['status'])) $invChips->push(['label' => ($filters['status'] === 'active' ? 'Activos' : 'Pausados'), 'route' => route('inventory.index', array_merge(request()->except(['status', 'page']), ['status' => '']))]);
                if (!empty($filters['stock'])) $invChips->push(['label' => 'Stock: '.($filters['stock'] === 'ok' ? 'OK' : ($filters['stock'] === 'low' ? 'Bajo' : 'Agotado')), 'route' => route('inventory.index', array_merge(request()->except(['stock', 'page']), ['stock' => '']))]);
            @endphp
            @if ($invChips->isNotEmpty())
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach ($invChips as $chip)
                        <a href="{{ $chip['route'] }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-2.5 py-0.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">
                            {{ $chip['label'] }}
                            <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex-1 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
            @if ($products->count())
                <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                    <p class="text-xs font-semibold text-gray-500">{{ $products->total() }} producto(s)</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 p-4" id="inventory-product-list">
                    @foreach ($products as $product)
                        @php
                            $stockAlert = $product->status === 'paused' ? 'text-gray-500 bg-gray-100' : ($product->stock <= 0 ? 'text-red-700 bg-red-100' : ($product->isLowStock() ? 'text-amber-700 bg-amber-100' : 'text-emerald-700 bg-emerald-100'));
                            $stockBg = $product->status === 'paused' ? 'border-gray-200 bg-gray-50' : ($product->stock <= 0 ? 'border-red-200 bg-red-50' : ($product->isLowStock() ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white'));
                        @endphp
                        <div class="rounded-xl border shadow-sm p-3 flex flex-col gap-3 {{ $stockBg }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-950 truncate">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ $product->category ?: 'Sin categoria' }}{{ $product->sku ? ' · '.$product->sku : '' }}</p>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full whitespace-nowrap {{ $stockAlert }}">{{ $product->stock }} / {{ $product->stock_minimum }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div><p class="text-gray-500">Precio</p><p class="font-bold text-gray-950">${{ number_format($product->price, 0, ',', '.') }}</p></div>
                                <div><p class="text-gray-500">Valor stock</p><p class="font-bold text-gray-950">${{ number_format($product->stock * $product->price, 0, ',', '.') }}</p></div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-gray-500" style="width:44px">Entrada</span>
                                    <input type="number" id="qty-in-{{ $product->id }}" value="1" min="1" max="9999" style="width:44px;border:1px solid #d1d5db;border-radius:0.25rem;padding:2px 4px;font-size:0.75rem;text-align:center;flex-shrink:0">
                                    <button type="button" onclick="quickMovement({{ $product->id }}, 'manual_in', document.getElementById('qty-in-{{ $product->id }}').value)" style="background:#16a34a;color:#fff;border:0;border-radius:0.25rem;padding:2px 8px;font-size:0.75rem;font-weight:700;cursor:pointer;flex:1">Aceptar</button>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-gray-500" style="width:44px">Salida</span>
                                    <input type="number" id="qty-out-{{ $product->id }}" value="1" min="1" max="{{ $product->stock }}" style="width:44px;border:1px solid #d1d5db;border-radius:0.25rem;padding:2px 4px;font-size:0.75rem;text-align:center;flex-shrink:0">
                                    <button type="button" onclick="quickMovement({{ $product->id }}, 'manual_out', document.getElementById('qty-out-{{ $product->id }}').value)" style="background:#dc2626;color:#fff;border:0;border-radius:0.25rem;padding:2px 8px;font-size:0.75rem;font-weight:700;cursor:pointer;flex:1" @disabled($product->stock <= 0)>Aceptar</button>
                                </div>
                                <div class="flex gap-1.5">
                                    <a href="{{ route('inventory.movements', ['search' => $product->sku ?: $product->name]) }}" class="rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 flex-1 text-center">Kardex</a>
                                    <button type="button" class="rounded border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 flex-1 text-center" onclick="document.getElementById('inv-del-{{ $product->id }}').classList.remove('hidden')">Eliminar</button>
                                </div>
                            </div>
                        </div>
                        <x-confirmation-modal id="inv-del-{{ $product->id }}" title="Eliminar producto" message="Se eliminara &ldquo;{{ $product->name }}&rdquo; del inventario." confirmText="Eliminar" cancelText="Cancelar" />
                        <form id="inv-del-{{ $product->id }}-form" method="POST" action="{{ route('inventory.destroy', $product) }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach
                </div>
            @else
                <div class="py-10 text-center">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    <p class="font-semibold text-gray-950">No hay productos con estos filtros.</p>
                    <a href="{{ route('inventory.index') }}" class="mt-2 text-sm text-blue-700 hover:underline">Limpiar filtros</a>
                </div>
            @endif

            @if ($products->hasPages())
                <div class="border-t border-gray-200 px-5 py-3">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
    @endif

    {{-- Slide-over: Add product --}}
    <div x-data="{ open: false }" @open-add-product.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/30" @click="open = false"></div>
        <div class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-xl flex flex-col" @click.outside="open = false">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-bold text-gray-900">Agregar producto</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <form method="POST" action="{{ route('inventory.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombre del producto</label>
                        <input name="name" value="{{ old('name') }}" required placeholder="Ej. Camiseta negra talla M" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">SKU</label>
                            <input name="sku" id="sku-edit-input" value="{{ old('sku') }}" placeholder="SKU-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <label class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none ml-1">
                                <input type="checkbox" id="auto-sku-edit" name="auto_sku" value="1" class="rounded border-gray-300 text-blue-700 w-4 h-4" onchange="document.getElementById('sku-edit-input').disabled=this.checked; if(this.checked) document.getElementById('sku-edit-input').value=''">
                                No tengo SKU (generar automatico)
                            </label>
                        </div>
                        <div x-data="categoryPicker()" class="relative">
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Categoria</label>
                            <div class="flex items-center gap-2">
                                <input x-model="selected" name="category" list="cat_list_index" placeholder="Escribe o selecciona" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                                <datalist id="cat_list_index">
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                                <button type="button" @click="saveCategory()" :disabled="!selected.trim()" class="shrink-0 flex items-center justify-center w-9 h-9 rounded-lg border transition-colors" :class="saving ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-blue-600 hover:text-blue-700 disabled:opacity-40 disabled:cursor-not-allowed'" :title="saving ? 'Guardado' : 'Guardar como categoria permanente'">
                                    <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <svg x-show="saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Costo unitario ($)</label>
                            <input name="cost" value="{{ old('cost', 0) }}" required type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Precio venta ($)</label>
                            <input name="price" value="{{ old('price', 0) }}" required type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Stock inicial</label>
                            <input name="stock" value="{{ old('stock', 0) }}" required type="number" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Stock minimo</label>
                            <input name="stock_minimum" value="{{ old('stock_minimum', 0) }}" required type="number" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                    </div>
                    <button class="w-full rounded-lg py-2.5 text-sm font-bold text-white shadow-sm" style="background:{{ $theme['color'] }}">Guardar producto</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function categoryPicker() {
            return {
                selected: '{{ old("category") }}',
                saving: false,

                async saveCategory() {
                    const name = this.selected.trim();
                    if (!name) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    this.saving = true;
                    try {
                        const res = await fetch('/categories', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ name }),
                        });
                        if (res.ok) {
                            const dl = document.getElementById('cat_list_index');
                            const opt = document.createElement('option');
                            opt.value = name;
                            dl.appendChild(opt);
                        } else if (res.status === 409) {
                            // Already exists — that's fine
                        }
                    } catch (e) { console.error(e); }
                    this.saving = true;
                    setTimeout(() => { this.saving = false; }, 1500);
                },
            };
        }
    </script>

    {{-- Edit Product Modal --}}
    <div id="edit-product-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/30" onclick="closeEditProduct()"></div>
        <div class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-xl flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-bold text-gray-900">Editar producto</h3>
                <button onclick="closeEditProduct()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <form id="edit-product-form" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombre del producto</label>
                        <input id="edit-name" name="name" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">SKU</label>
                            <input id="edit-sku" name="sku" placeholder="SKU-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <label class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none ml-1">
                                <input type="checkbox" id="edit-auto-sku" name="auto_sku" value="1" class="rounded border-gray-300 text-blue-700 w-4 h-4" onchange="document.getElementById('edit-sku').disabled=this.checked; if(this.checked) document.getElementById('edit-sku').value=''">
                                No tengo SKU (generar automatico)
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Estado</label>
                            <select id="edit-status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
                                <option value="active">Activo</option>
                                <option value="paused">Pausado</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Categoria</label>
                        <input id="edit-category" name="category" list="cat_list_edit" placeholder="Escribe o selecciona" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <datalist id="cat_list_edit">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Costo unitario ($)</label>
                            <input id="edit-cost" name="cost" required type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Precio venta ($)</label>
                            <input id="edit-price" name="price" required type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Stock minimo</label>
                        <input id="edit-stock-min" name="stock_minimum" required type="number" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-blue-700 px-4 py-3 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditProduct(id, name, sku, category, cost, price, stock, stockMin, status) {
            const form = document.getElementById('edit-product-form');
            form.action = '/inventory/' + id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-sku').value = sku || '';
            document.getElementById('edit-category').value = category || '';
            document.getElementById('edit-cost').value = cost;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-stock-min').value = stockMin;
            document.getElementById('edit-status').value = status;
            document.getElementById('edit-product-modal').classList.remove('hidden');
        }

        function closeEditProduct() {
            document.getElementById('edit-product-modal').classList.add('hidden');
        }

        function quickMovement(productId, type, quantity) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const qty = parseInt(quantity) || 1;
            if (qty <= 0) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/inventory/' + productId + '/movement';
            form.style.display = 'none';

            const fields = {
                '_token': csrf,
                'type': type,
                'quantity': qty,
                'notes': type === 'manual_in' ? 'Entrada rapida' : 'Salida rapida',
            };

            Object.entries(fields).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</x-app-layout>
