<x-app-layout>
    @vite(['resources/css/inventory.css', 'resources/js/inventory.js'])

    <x-slot name="header">
        <x-page-header eyebrow="Productos / Préstamos" title="Préstamo {{ $loan->loan_number }}" description="Gestión, seguimiento de devoluciones y liquidación de mercancía entregada.">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.loans.index') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        ← Volver al listado
                    </a>
                    <a href="{{ route('inventory.loans.print', $loan) }}" target="_blank" class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-black inline-flex items-center gap-1.5 shadow-sm">
                        <span>🖨️</span> Imprimir Remisión
                    </a>
                    @if ($loan->isActive())
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('loan-modal', { detail: 'return' }))" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 inline-flex items-center gap-1 shadow-sm cursor-pointer">
                            <span>↩</span> Registrar Devolución
                        </button>
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('loan-modal', { detail: 'settle' }))" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-700 inline-flex items-center gap-1 shadow-sm cursor-pointer">
                            <span>💰</span> Liquidar / Vender
                        </button>
                        @if ($loan->returnedUnits() == 0 && $loan->soldUnits() == 0)
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('loan-modal', { detail: 'cancel' }))" class="rounded-lg border border-rose-300 text-rose-700 px-2.5 py-1.5 text-xs font-bold hover:bg-rose-50 cursor-pointer">
                                Anular
                            </button>
                        @endif
                    @endif
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $pendingItemsJson = $loan->items
            ->filter(fn ($i) => $i->pendingUnits() > 0)
            ->values()
            ->map(fn ($i) => [
                'id' => $i->id,
                'name' => $i->displayName(),
                'pending' => $i->pendingUnits(),
                'unit_price' => (float) $i->unit_price,
                'return_checked' => true,
                'return_qty' => $i->pendingUnits(),
                'settle_checked' => true,
                'settle_qty' => $i->pendingUnits(),
            ]);
    @endphp

    <div class="p-3 lg:p-5 max-w-5xl mx-auto space-y-5"
         x-data="loanManagement({{ $pendingItemsJson->toJson() }})"
         @loan-modal.window="openModal($event.detail)">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-800 shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-bold text-rose-800 shadow-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Overview Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Borrower Card --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-blue-700">Prestatario / Receptor</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $loan->statusBadgeClass() }}">
                        {{ $loan->statusLabel() }}
                    </span>
                </div>
                <div class="space-y-1.5 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 block">Nombre:</span>
                        <span class="font-bold text-gray-900">{{ $loan->borrower_name }}</span>
                    </div>
                    @if ($loan->borrower_phone)
                        <div>
                            <span class="text-xs text-gray-400 block">Teléfono / WhatsApp:</span>
                            <a href="https://wa.me/57{{ preg_replace('/\D/', '', $loan->borrower_phone) }}" target="_blank" class="font-semibold text-blue-700 hover:underline">
                                📞 {{ $loan->borrower_phone }}
                            </a>
                        </div>
                    @endif
                    @if ($loan->borrower_document)
                        <div>
                            <span class="text-xs text-gray-400 block">Documento / NIT:</span>
                            <span class="font-semibold text-gray-800">{{ $loan->borrower_document }}</span>
                        </div>
                    @endif
                    @if ($loan->borrower_address)
                        <div>
                            <span class="text-xs text-gray-400 block">Dirección:</span>
                            <span class="font-semibold text-gray-800">{{ $loan->borrower_address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Dates & Status Details --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Fechas y Compromiso</h3>
                <div class="space-y-1.5 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 block">Fecha de Entrega:</span>
                        <span class="font-bold text-gray-900">{{ $loan->loan_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Fecha Límite Pactada:</span>
                        @if ($loan->due_date)
                            <span class="font-bold {{ $loan->isOverdue() ? 'text-rose-600' : 'text-gray-900' }}">
                                {{ $loan->due_date->format('d/m/Y') }}
                                @if ($loan->isOverdue())
                                    <span class="text-xs text-rose-600 block">(¡Vencido!)</span>
                                @endif
                            </span>
                        @else
                            <span class="text-gray-500">Sin fecha límite</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Registrado por:</span>
                        <span class="font-semibold text-gray-700">{{ $loan->creator?->name ?? 'Sistema' }}</span>
                    </div>
                    @if ($loan->notes)
                        <div class="pt-1 border-t border-gray-100">
                            <span class="text-xs text-gray-400 block">Notas:</span>
                            <p class="text-xs text-gray-600 whitespace-pre-line">{{ $loan->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Balance summary --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-2">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Balance del Préstamo</h3>
                <div class="space-y-2 pt-1">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Total Prestado:</span>
                        <span class="font-black text-gray-900">{{ $loan->total_items }} unds</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-emerald-700 font-semibold">Devuelto a bodega:</span>
                        <span class="font-black text-emerald-700">{{ $loan->returnedUnits() }} unds</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-blue-700 font-semibold">Liquidado / Vendido:</span>
                        <span class="font-black text-blue-700">{{ $loan->soldUnits() }} unds</span>
                    </div>
                    <div class="pt-2 border-t border-gray-100 flex justify-between items-center text-sm">
                        <span class="text-amber-800 font-bold">Pendiente en poder del cliente:</span>
                        <span class="font-black text-amber-700 text-base">{{ $loan->pendingUnits() }} unds</span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-400">
                        <span>Valor total:</span>
                        <span class="font-bold text-gray-700">${{ number_format($loan->total_value, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-800">Detalle de Productos Prestados</h3>
                    <span class="text-xs text-gray-400">{{ $loan->items->count() }} producto(s) en este préstamo</span>
                </div>
                @if ($loan->isActive())
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="openModal('return')" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 inline-flex items-center gap-1 shadow-sm cursor-pointer">
                            <span>↩</span> Seleccionar Devolución
                        </button>
                        <button type="button" @click="openModal('settle')" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-700 inline-flex items-center gap-1 shadow-sm cursor-pointer">
                            <span>💰</span> Seleccionar Venta / Liquidar
                        </button>
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3 text-center">Prestado</th>
                            <th class="px-4 py-3 text-center text-emerald-700">Devuelto</th>
                            <th class="px-4 py-3 text-center text-blue-700">Vendido</th>
                            <th class="px-4 py-3 text-center text-amber-800 bg-amber-50">Pendiente</th>
                            <th class="px-4 py-3 text-right">Precio Unitario</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($loan->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 block">{{ $item->displayName() }}</span>
                                        @if ($item->product_type === 'quick')
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">Rápido</span>
                                        @endif
                                    </div>
                                    @if ($item->displaySku())
                                        <span class="text-xs text-gray-400">SKU: {{ $item->displaySku() }}</span>
                                    @endif
                                    @if ($item->notes)
                                        <p class="text-xs text-gray-500 italic mt-0.5">{{ $item->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-bold">{{ $item->quantity_lent }}</td>
                                <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ $item->quantity_returned }}</td>
                                <td class="px-4 py-3 text-center font-bold text-blue-700">{{ $item->quantity_sold }}</td>
                                <td class="px-4 py-3 text-center font-black text-amber-800 bg-amber-50">{{ $item->pendingUnits() }}</td>
                                <td class="px-4 py-3 text-right text-xs">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900">${{ number_format($item->quantity_lent * $item->unit_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200 text-xs font-bold text-gray-900">
                        <tr>
                            <td class="px-4 py-3 uppercase">Totales</td>
                            <td class="px-4 py-3 text-center">{{ $loan->total_items }}</td>
                            <td class="px-4 py-3 text-center text-emerald-700">{{ $loan->returnedUnits() }}</td>
                            <td class="px-4 py-3 text-center text-blue-700">{{ $loan->soldUnits() }}</td>
                            <td class="px-4 py-3 text-center text-amber-800 bg-amber-100 font-black">{{ $loan->pendingUnits() }}</td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-right text-sm font-black">${{ number_format($loan->total_value, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- MODAL 1: Return Items --}}
        <div x-show="showReturnModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4" @click.away="showReturnModal = false">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-sm font-black text-gray-900">Registrar Devolución a Bodega</h3>
                    <button type="button" @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">✕</button>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-600">Marca los productos que regresan a bodega e indica la cantidad.</p>
                    <div class="flex items-center gap-2 shrink-0 text-[11px] font-bold">
                        <button type="button" @click="toggleAllReturn(true)" class="text-emerald-700 hover:underline cursor-pointer">Todos</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" @click="toggleAllReturn(false)" class="text-gray-500 hover:underline cursor-pointer">Ninguno</button>
                    </div>
                </div>

                <form method="POST" action="{{ route('inventory.loans.return', $loan) }}" class="space-y-4" @submit="prepareReturnSubmit($event)">
                    @csrf
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        <template x-for="(item, idx) in items" :key="item.id">
                            <div class="flex items-center justify-between p-2.5 rounded-lg border text-xs transition-colors"
                                 :class="item.return_checked ? 'border-emerald-300 bg-emerald-50/40' : 'border-gray-200 bg-gray-50 opacity-60'">
                                <label class="flex items-center gap-2.5 flex-1 mr-2 cursor-pointer">
                                    <input type="checkbox"
                                           x-model="item.return_checked"
                                           @change="onReturnCheck(item)"
                                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                    <div class="min-w-0">
                                        <span class="font-bold text-gray-900 block truncate" x-text="item.name"></span>
                                        <span class="text-[11px] text-gray-500" x-text="'Pendientes: ' + item.pending"></span>
                                    </div>
                                </label>
                                <input type="hidden" :name="`returns[${idx}][item_id]`" :value="item.id">
                                <div class="w-24 shrink-0">
                                    <label class="block text-[10px] font-bold text-gray-400">Devuelve:</label>
                                    <input type="number"
                                           :name="`returns[${idx}][quantity]`"
                                           x-model.number="item.return_qty"
                                           :readonly="!item.return_checked"
                                           min="0"
                                           :max="item.pending"
                                           class="w-full rounded border-gray-300 px-2 py-1 text-xs font-bold text-center"
                                           :class="!item.return_checked ? 'bg-gray-100 text-gray-400' : 'bg-white'">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs">
                        <span class="font-bold text-emerald-900">Unidades seleccionadas a devolver:</span>
                        <span class="font-black text-emerald-700 text-sm" x-text="totalReturnUnits() + ' unds'"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Notas de la devolución (opcional):</label>
                        <input type="text" name="return_notes" placeholder="Ej: Entregado en perfecto estado" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="showReturnModal = false" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 cursor-pointer">Cancelar</button>
                        <button type="submit" :disabled="totalReturnUnits() <= 0" class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 disabled:opacity-50 cursor-pointer">Confirmar Ingreso a Bodega</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: Settle Items (Sale) --}}
        <div x-show="showSettleModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4" @click.away="showSettleModal = false">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-sm font-black text-gray-900">Liquidar Mercancía como Venta</h3>
                    <button type="button" @click="showSettleModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">✕</button>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-600">Marca cuáles productos se vendieron o liquidan e indica la cantidad.</p>
                    <div class="flex items-center gap-2 shrink-0 text-[11px] font-bold">
                        <button type="button" @click="toggleAllSettle(true)" class="text-blue-700 hover:underline cursor-pointer">Todos</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" @click="toggleAllSettle(false)" class="text-gray-500 hover:underline cursor-pointer">Ninguno</button>
                    </div>
                </div>

                <form method="POST" action="{{ route('inventory.loans.settle', $loan) }}" class="space-y-4" @submit="prepareSettleSubmit($event)">
                    @csrf
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        <template x-for="(item, idx) in items" :key="item.id">
                            <div class="flex items-center justify-between p-2.5 rounded-lg border text-xs transition-colors"
                                 :class="item.settle_checked ? 'border-blue-300 bg-blue-50/40' : 'border-gray-200 bg-gray-50 opacity-60'">
                                <label class="flex items-center gap-2.5 flex-1 mr-2 cursor-pointer">
                                    <input type="checkbox"
                                           x-model="item.settle_checked"
                                           @change="onSettleCheck(item)"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                                    <div class="min-w-0">
                                        <span class="font-bold text-gray-900 block truncate" x-text="item.name"></span>
                                        <span class="text-[11px] text-gray-500" x-text="'Pendientes: ' + item.pending + ' (' + formatMoney(item.unit_price) + '/u)'"></span>
                                    </div>
                                </label>
                                <input type="hidden" :name="`settle[${idx}][item_id]`" :value="item.id">
                                <div class="w-24 shrink-0">
                                    <label class="block text-[10px] font-bold text-gray-400">Vendido:</label>
                                    <input type="number"
                                           :name="`settle[${idx}][quantity]`"
                                           x-model.number="item.settle_qty"
                                           :readonly="!item.settle_checked"
                                           min="0"
                                           :max="item.pending"
                                           class="w-full rounded border-gray-300 px-2 py-1 text-xs font-bold text-center"
                                           :class="!item.settle_checked ? 'bg-gray-100 text-gray-400' : 'bg-white'">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-blue-50 border border-blue-200 px-3 py-2 text-xs">
                        <span class="font-bold text-blue-900" x-text="'Total a liquidar (' + totalSettleUnits() + ' unds):'"></span>
                        <span class="font-black text-blue-700 text-sm" x-text="formatMoney(totalSettleAmount())"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Notas de la liquidación / pago (opcional):</label>
                        <input type="text" name="settle_notes" placeholder="Ej: Pagado en efectivo / Transferencia Nequi" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="showSettleModal = false" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 cursor-pointer">Cancelar</button>
                        <button type="submit" :disabled="totalSettleUnits() <= 0" class="rounded-lg bg-blue-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-blue-700 disabled:opacity-50 cursor-pointer">Confirmar Venta / Liquidación</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 3: Cancel Loan --}}
        <div x-show="showCancelModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-4" @click.away="showCancelModal = false">
                <div class="flex items-center gap-2 text-rose-600 font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    ¿Anular este préstamo?
                </div>
                <p class="text-xs text-gray-600">Al anular, se restituirán inmediatamente todas las <strong>{{ $loan->total_items }} unidades</strong> al inventario de bodega y el préstamo quedará registrado como anulado.</p>
                <form method="POST" action="{{ route('inventory.loans.cancel', $loan) }}" class="flex justify-end gap-2 pt-2 border-t">
                    @csrf
                    <button type="button" @click="showCancelModal = false" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 cursor-pointer">No, volver</button>
                    <button type="submit" class="rounded-lg bg-rose-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-rose-700 cursor-pointer">Sí, anular y reponer stock</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function loanManagement(items) {
            return {
                showReturnModal: false,
                showSettleModal: false,
                showCancelModal: false,
                items: items || [],
                openModal(type) {
                    this.showReturnModal = (type === 'return');
                    this.showSettleModal = (type === 'settle');
                    this.showCancelModal = (type === 'cancel');
                },
                onReturnCheck(item) {
                    item.return_qty = item.return_checked ? item.pending : 0;
                },
                onSettleCheck(item) {
                    item.settle_qty = item.settle_checked ? item.pending : 0;
                },
                toggleAllReturn(state) {
                    this.items.forEach(i => {
                        i.return_checked = state;
                        i.return_qty = state ? i.pending : 0;
                    });
                },
                toggleAllSettle(state) {
                    this.items.forEach(i => {
                        i.settle_checked = state;
                        i.settle_qty = state ? i.pending : 0;
                    });
                },
                totalReturnUnits() {
                    return this.items.reduce((sum, i) => sum + (i.return_checked ? Math.max(0, Number(i.return_qty) || 0) : 0), 0);
                },
                totalSettleUnits() {
                    return this.items.reduce((sum, i) => sum + (i.settle_checked ? Math.max(0, Number(i.settle_qty) || 0) : 0), 0);
                },
                totalSettleAmount() {
                    return this.items.reduce((sum, i) => sum + (i.settle_checked ? (Math.max(0, Number(i.settle_qty) || 0) * (Number(i.unit_price) || 0)) : 0), 0);
                },
                formatMoney(amount) {
                    return '$' + new Intl.NumberFormat('es-CO').format(amount || 0);
                },
                prepareReturnSubmit(e) {
                    if (this.totalReturnUnits() <= 0) {
                        e.preventDefault();
                        alert('Selecciona al menos un producto y una cantidad mayor a 0.');
                    }
                },
                prepareSettleSubmit(e) {
                    if (this.totalSettleUnits() <= 0) {
                        e.preventDefault();
                        alert('Selecciona al menos un producto y una cantidad mayor a 0.');
                    }
                }
            };
        }
    </script>
</x-app-layout>
