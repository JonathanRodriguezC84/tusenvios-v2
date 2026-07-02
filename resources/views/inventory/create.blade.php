<x-app-layout>
    @vite(['resources/css/inventory.css'])

    <style>
        .inv-create-page { --inv-color: {{ $theme['color'] }}; --inv-text: {{ $theme['text'] }}; --inv-tint: {{ $theme['tint'] }}; --inv-border: {{ $theme['border'] }}; --inv-soft: {{ $theme['soft'] }}; }
        .inv-drop-zone { border: 2px dashed #d1d5db; border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.2s; cursor: pointer; }
        .inv-drop-zone:hover { border-color: var(--inv-color); background: var(--inv-tint); }
    </style>

    @php
        $toastMessages = [];
        if (session('status')) { $toastMessages[] = ['text' => session('status'), 'type' => 'success']; }
        if ($errors->any()) { $toastMessages[] = ['text' => $errors->first() ?: 'Revisa los campos.', 'type' => 'error']; }
    @endphp

    <script id="inv-create-toast" type="application/json">{{ json_encode($toastMessages) }}</script>

    <x-slot name="header">
        <x-page-header eyebrow="Inventario" title="Agregar producto" description="Registra un nuevo producto con nombre, precio, stock y categoria.">
            <x-slot name="actions">
                <a href="{{ route('inventory.template') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Descargar plantilla CSV</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="inv-create-page p-3 lg:p-5 h-full flex flex-col gap-4">
        {{-- TOP: Two equal columns --}}
        <div class="grid lg:grid-cols-2 gap-4 flex-1 min-h-0">
            {{-- LEFT: Add individual product --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 flex flex-col">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500 mb-4 shrink-0">Producto individual</h3>
                <form method="POST" action="{{ route('inventory.store') }}" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    <div class="flex-1 space-y-3 overflow-y-auto">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombre del producto</label>
                            <input name="name" value="{{ old('name') }}" required placeholder="Ej. Camiseta negra talla M" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-0.5">SKU</label>
                                <input name="sku" id="sku-input" value="{{ old('sku') }}" placeholder="SKU-001" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                                <label class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none ml-1">
                                    <input type="checkbox" id="auto-sku" name="auto_sku" value="1" class="rounded border-gray-300 text-blue-700 w-4 h-4" onchange="document.getElementById('sku-input').disabled=this.checked; if(this.checked) document.getElementById('sku-input').value=''">
                                    No tengo SKU (generar automatico)
                                </label>
                            </div>
                            <div x-data="categoryPicker()" class="relative">
                                <label class="block text-xs font-semibold text-gray-600 mb-0.5">Categoria</label>
                                <div class="flex items-center gap-2">
                                    <input x-model="selected" name="category" list="cat_list_create" placeholder="Escribe o selecciona" class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                                    <datalist id="cat_list_create">
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}">
                                        @endforeach
                                    </datalist>
                                    <button type="button" @click="saveCategory()" :disabled="!selected.trim()" class="shrink-0 flex items-center justify-center w-8 h-8 rounded-lg border transition-colors" :class="saving ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-blue-600 hover:text-blue-700 disabled:opacity-40 disabled:cursor-not-allowed'" :title="saving ? 'Guardado' : 'Guardar como categoria permanente'">
                                        <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <svg x-show="saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-0.5">Costo ($)</label>
                                <input name="cost" value="{{ old('cost', 0) }}" required type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-0.5">Precio venta ($)</label>
                                <input name="price" value="{{ old('price', 0) }}" required type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-0.5">Stock inicial</label>
                                <input name="stock" value="{{ old('stock', 0) }}" required type="number" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-0.5">Stock minimo</label>
                                <input name="stock_minimum" value="{{ old('stock_minimum', 0) }}" required type="number" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            </div>
                        </div>
                    </div>
                    <button class="w-full rounded-lg py-2 text-sm font-bold text-white shadow-sm shrink-0 mt-3" style="background:{{ $theme['color'] }}">Guardar producto</button>
                </form>
            </div>

            {{-- RIGHT: CSV Import --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 flex flex-col">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500 mb-4 shrink-0">Importar varios productos desde CSV</h3>
                <form method="POST" action="{{ route('inventory.import') }}" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    <div class="flex-1">
                        <label class="inv-drop-zone flex flex-col items-center justify-center h-full min-h-[180px] cursor-pointer">
                            <input name="inventory_file" type="file" accept=".csv,text/csv" required class="hidden" onchange="this.closest('.inv-drop-zone').querySelector('.inv-file-name').textContent = this.files[0]?.name || ''; this.closest('.inv-drop-zone').querySelector('.inv-file-icon')?.classList.add('hidden')">
                            <svg class="inv-file-icon w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <p class="text-sm font-semibold text-gray-700">Arrastra tu archivo CSV aqui</p>
                            <p class="text-xs text-gray-500 mt-1">o haz click para seleccionar</p>
                            <p class="inv-file-name text-xs font-semibold text-blue-700 mt-2"></p>
                        </label>
                    </div>
                    <div class="flex gap-2 shrink-0 mt-3">
                        <button type="button" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50" onclick="document.querySelector('[name=inventory_file]').click()">Seleccionar archivo</button>
                        <button class="flex-1 rounded-lg px-3 py-2 text-sm font-bold text-white shadow-sm" style="background:{{ $theme['color'] }}">Importar</button>
                    </div>
                </form>
                <p class="mt-2 text-xs text-gray-500 shrink-0">
                    Columnas: Producto, SKU, Categoria, Costo, Precio, Stock, Stock minimo, Estado.
                    <a href="{{ route('inventory.template') }}" class="font-semibold underline">Descargar plantilla</a>
                </p>
            </div>
        </div>

        {{-- BOTTOM HALF: Recent additions list --}}
        <div class="flex-1 min-h-0 rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Agregados recientemente</h3>
            </div>
            @php
                $user = auth()->user();
                $recent = \App\Models\InventoryProduct::query()
                    ->when($user->tenant_id, fn ($q) => $q->where('tenant_id', $user->tenant_id))
                    ->when($user->role === 'affiliate' && $user->affiliated_company_id, fn ($q) => $q->where('affiliated_company_id', $user->affiliated_company_id), fn ($q) => $q->whereNull('affiliated_company_id'))
                    ->latest()
                    ->take(10)
                    ->get();
            @endphp
            @if ($recent->count())
                <div class="flex-1 overflow-y-auto divide-y divide-gray-200">
                    @foreach ($recent as $product)
                        <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-950 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $product->category ?: 'Sin categoria' }}{{ $product->sku ? ' · '.$product->sku : '' }}</p>
                            </div>
                            <div class="text-right w-24">
                                <p class="text-sm font-bold text-gray-950">${{ number_format($product->price, 0, ',', '.') }}</p>
                                <p class="text-xs {{ $product->stock > 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $product->stock }} und.</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex-1 flex items-center justify-center">
                    <p class="text-sm text-gray-500">Aun no hay productos agregados.</p>
                </div>
            @endif
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
                            const dl = document.getElementById('cat_list_create');
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

        document.addEventListener('DOMContentLoaded', () => {
            const data = document.getElementById('inv-create-toast');
            if (!data) return;
            const container = document.createElement('div');
            container.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:grid;gap:0.5rem;max-width:28rem;';
            document.body.appendChild(container);
            JSON.parse(data.textContent || '[]').forEach((msg) => {
                const el = document.createElement('div');
                el.style.cssText = `border-radius:0.5rem;padding:0.85rem 1rem;font-size:0.82rem;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.12);${msg.type === 'error' ? 'border:1px solid #fecaca;background:#fef2f2;color:#991b1b' : 'border:1px solid #a7f3d0;background:#ecfdf5;color:#065f46'}`;
                el.textContent = msg.text;
                container.appendChild(el);
                window.setTimeout(() => el.remove(), 4000);
            });
        });
    </script>
</x-app-layout>
