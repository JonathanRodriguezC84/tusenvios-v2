<x-app-layout>
    @vite(['resources/css/inventory.css', 'resources/js/inventory.js'])

    <x-slot name="header">
        <x-page-header eyebrow="Inventario / Préstamos" title="Nuevo Préstamo de Mercancía" description="Registra la entrega de productos en préstamo o consignación descontando temporalmente el stock.">
            <x-slot name="actions">
                <a href="{{ route('inventory.loans.index') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Cancelar</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $productsJson = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => (float) $p->price,
            'stock' => (int) $p->stock,
        ])->values()->toJson();
    @endphp

    <div class="p-3 lg:p-5 max-w-5xl mx-auto" x-data="merchandiseLoanForm({{ $productsJson }})">
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-800 shadow-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Por favor revisa los siguientes errores:
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inventory.loans.store') }}" @submit="handleSubmit($event)">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Left column: Borrower Info & Dates --}}
                <div class="md:col-span-1 space-y-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-blue-700">1. Datos del Receptor</h3>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nombre / Entidad *</label>
                            <input type="text" name="borrower_name" value="{{ old('borrower_name') }}" required placeholder="Ej: Carlos Gómez / Stand Centro" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Teléfono / WhatsApp</label>
                            <input type="text" name="borrower_phone" value="{{ old('borrower_phone') }}" placeholder="Ej: 3001234567" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cédula / NIT</label>
                            <input type="text" name="borrower_document" value="{{ old('borrower_document') }}" placeholder="Ej: 10203040" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Dirección / Ubicación</label>
                            <input type="text" name="borrower_address" value="{{ old('borrower_address') }}" placeholder="Ej: Calle 10 # 5-20" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-blue-700">2. Fechas del Préstamo</h3>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Fecha de Entrega *</label>
                            <input type="date" name="loan_date" value="{{ old('loan_date', $defaultLoanDate) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Fecha Límite de Retorno</label>
                            <input type="date" name="due_date" value="{{ old('due_date', $defaultDueDate) }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                            <span class="text-[11px] text-gray-400 mt-0.5 block">Fecha en que debe devolver o liquidar la mercancía.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Notas / Condiciones</label>
                            <textarea name="notes" rows="2" placeholder="Ej: Préstamo para sesión de fotos / Consignación semana 1" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Right column: Products list --}}
                <div class="md:col-span-2 space-y-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-blue-700">3. Mercancía a Entregar</h3>
                                <p class="text-xs text-gray-500">El stock seleccionado se descontará de bodega al guardar.</p>
                            </div>
                            <button type="button" @click="addItem()" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100">
                                <span>+</span> Agregar Producto
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="p-3 rounded-lg border border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                    <div class="flex-1 w-full sm:w-auto">
                                        <label class="block text-[11px] font-bold text-gray-500 mb-0.5">Producto *</label>
                                        <select :name="`items[${index}][product_id]`" x-model="item.productId" @change="onProductSelect(item)" required class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs bg-white">
                                            <option value="">Selecciona un producto...</option>
                                            <template x-for="p in availableProducts" :key="p.id">
                                                <option :value="p.id" x-text="`${p.name} (Stock: ${p.stock})`" :selected="item.productId == p.id"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div class="w-24">
                                        <label class="block text-[11px] font-bold text-gray-500 mb-0.5">Cantidad *</label>
                                        <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" :max="item.maxStock" min="1" required class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs font-bold text-center">
                                    </div>

                                    <div class="w-28 text-right hidden sm:block">
                                        <span class="block text-[11px] font-bold text-gray-500 mb-0.5">Subtotal</span>
                                        <span class="block text-xs font-bold text-gray-800" x-text="formatMoney(item.quantity * item.price)"></span>
                                    </div>

                                    <div class="pt-4 sm:pt-0">
                                        <button type="button" @click="removeItem(index)" class="text-rose-600 hover:text-rose-800 p-1" title="Eliminar fila" x-show="items.length > 1">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Total Summary --}}
                        <div class="mt-4 pt-3 border-t border-gray-200 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-gray-500 font-semibold">Total unidades a prestar:</span>
                                <span class="text-sm font-black text-gray-900 ml-1" x-text="totalUnits"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-500 font-semibold">Valor total estimado:</span>
                                <span class="text-base font-black text-blue-700 ml-1" x-text="formatMoney(totalValue)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('inventory.loans.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</a>
                        <button type="submit" class="rounded-xl bg-blue-700 px-6 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">
                            Registrar Préstamo y Descontar Stock
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function merchandiseLoanForm(products) {
            return {
                availableProducts: products,
                items: [
                    { productId: '', quantity: 1, price: 0, maxStock: 9999 }
                ],
                addItem() {
                    this.items.push({ productId: '', quantity: 1, price: 0, maxStock: 9999 });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                onProductSelect(item) {
                    const found = this.availableProducts.find(p => p.id == item.productId);
                    if (found) {
                        item.price = found.price;
                        item.maxStock = found.stock;
                        if (item.quantity > found.stock) {
                            item.quantity = found.stock;
                        }
                    } else {
                        item.price = 0;
                        item.maxStock = 9999;
                    }
                },
                get totalUnits() {
                    return this.items.reduce((sum, i) => sum + (Number(i.quantity) || 0), 0);
                },
                get totalValue() {
                    return this.items.reduce((sum, i) => sum + ((Number(i.quantity) || 0) * (Number(i.price) || 0)), 0);
                },
                formatMoney(amount) {
                    return '$' + new Intl.NumberFormat('es-CO').format(amount || 0);
                },
                handleSubmit(e) {
                    for (const item of this.items) {
                        if (!item.productId) {
                            alert('Por favor selecciona un producto para cada fila.');
                            e.preventDefault();
                            return;
                        }
                        if (item.quantity > item.maxStock) {
                            alert(`La cantidad no puede superar el stock disponible (${item.maxStock}).`);
                            e.preventDefault();
                            return;
                        }
                    }
                }
            }
        }
    </script>
</x-app-layout>
