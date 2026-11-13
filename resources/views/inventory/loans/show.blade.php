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
                    @if ($loan->isActive() && $loan->returnedUnits() == 0 && $loan->soldUnits() == 0)
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('loan-modal', { detail: 'cancel' }))" class="rounded-lg border border-rose-300 text-rose-700 px-2.5 py-1.5 text-xs font-bold hover:bg-rose-50 cursor-pointer">
                            Anular
                        </button>
                    @endif
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $loanItemsState = $loan->items->mapWithKeys(fn ($i) => [
            $i->id => [
                'action' => 'none',
                'quantity' => $i->pendingUnits(),
                'pending' => $i->pendingUnits(),
                'unit_price' => (float) $i->unit_price,
            ],
        ]);
    @endphp

    <div class="p-3 lg:p-5 max-w-5xl mx-auto space-y-5"
         x-data="loanInlineManager({{ $loanItemsState->toJson() }})"
         @loan-modal.window="if ($event.detail === 'cancel') showCancelModal = true">
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

        {{-- Products Table with inline per-product decision selector --}}
        <form method="POST" action="{{ route('inventory.loans.process', $loan) }}" @submit="validateSubmit($event)" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @csrf
            <div class="p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-800">Detalle de Productos Prestados</h3>
                    <span class="text-xs text-gray-400">
                        {{ $loan->items->count() }} producto(s) ·
                        @if ($loan->isActive())
                            Elige en cada producto si se devuelve a bodega o se liquida como venta.
                        @else
                            Préstamo cerrado.
                        @endif
                    </span>
                </div>
                @if ($loan->isActive())
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold">
                        <span class="text-gray-400 mr-1">Aplicar a todos:</span>
                        <button type="button" @click="setAllAction('return')" class="rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-emerald-800 hover:bg-emerald-100 cursor-pointer">
                            ↩ Todos Devueltos
                        </button>
                        <button type="button" @click="setAllAction('sell')" class="rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-blue-800 hover:bg-blue-100 cursor-pointer">
                            💰 Todos Vendidos
                        </button>
                        <button type="button" @click="setAllAction('none')" class="rounded-md border border-gray-200 bg-gray-50 px-2.5 py-1 text-gray-600 hover:bg-gray-100 cursor-pointer">
                            Limpiar
                        </button>
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-3 py-3 text-center">Prestado</th>
                            <th class="px-3 py-3 text-center text-emerald-700">Devuelto</th>
                            <th class="px-3 py-3 text-center text-blue-700">Vendido</th>
                            <th class="px-3 py-3 text-center text-amber-800 bg-amber-50">Pendiente</th>
                            <th class="px-3 py-3 text-right">Precio Unit.</th>
                            <th class="px-3 py-3 text-right">Subtotal</th>
                            @if ($loan->isActive())
                                <th class="px-4 py-3 text-left bg-gray-100/70">Decisión</th>
                                <th class="px-3 py-3 text-center bg-gray-100/70">Cant.</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($loan->items as $idx => $item)
                            <tr class="transition-colors"
                                @if ($loan->isActive() && $item->pendingUnits() > 0)
                                    :class="{
                                        'bg-emerald-50/40': state[{{ $item->id }}].action === 'return',
                                        'bg-blue-50/40': state[{{ $item->id }}].action === 'sell'
                                    }"
                                @endif>
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
                                <td class="px-3 py-3 text-center font-bold">{{ $item->quantity_lent }}</td>
                                <td class="px-3 py-3 text-center font-bold text-emerald-700">{{ $item->quantity_returned }}</td>
                                <td class="px-3 py-3 text-center font-bold text-blue-700">{{ $item->quantity_sold }}</td>
                                <td class="px-3 py-3 text-center font-black text-amber-800 bg-amber-50">{{ $item->pendingUnits() }}</td>
                                <td class="px-3 py-3 text-right text-xs">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right font-bold text-gray-900">${{ number_format($item->quantity_lent * $item->unit_price, 0, ',', '.') }}</td>

                                @if ($loan->isActive())
                                    <td class="px-4 py-3 bg-gray-50/60">
                                        @if ($item->pendingUnits() > 0)
                                            <input type="hidden" name="actions[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                            <select name="actions[{{ $idx }}][action]"
                                                    x-model="state[{{ $item->id }}].action"
                                                    @change="onActionChange({{ $item->id }})"
                                                    class="w-full min-w-[165px] rounded-lg border px-2.5 py-1.5 text-xs font-bold focus:ring-1"
                                                    :class="{
                                                        'border-gray-300 bg-white text-gray-700': state[{{ $item->id }}].action === 'none',
                                                        'border-emerald-400 bg-emerald-50 text-emerald-800': state[{{ $item->id }}].action === 'return',
                                                        'border-blue-400 bg-blue-50 text-blue-800': state[{{ $item->id }}].action === 'sell'
                                                    }">
                                                <option value="none">— Mantener pendiente —</option>
                                                <option value="return">↩ Devolver a bodega</option>
                                                <option value="sell">💰 Vender / Liquidar</option>
                                            </select>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500">Completado</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center bg-gray-50/60">
                                        @if ($item->pendingUnits() > 0)
                                            <input type="number"
                                                   name="actions[{{ $idx }}][quantity]"
                                                   x-model.number="state[{{ $item->id }}].quantity"
                                                   :readonly="state[{{ $item->id }}].action === 'none'"
                                                   min="0"
                                                   max="{{ $item->pendingUnits() }}"
                                                   class="w-16 rounded-lg border px-2 py-1 text-xs font-black text-center"
                                                   :class="state[{{ $item->id }}].action === 'none' ? 'border-gray-200 bg-gray-100 text-gray-400' : 'border-gray-300 bg-white text-gray-900'">
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200 text-xs font-bold text-gray-900">
                        <tr>
                            <td class="px-4 py-3 uppercase">Totales</td>
                            <td class="px-3 py-3 text-center">{{ $loan->total_items }}</td>
                            <td class="px-3 py-3 text-center text-emerald-700">{{ $loan->returnedUnits() }}</td>
                            <td class="px-3 py-3 text-center text-blue-700">{{ $loan->soldUnits() }}</td>
                            <td class="px-3 py-3 text-center text-amber-800 bg-amber-100 font-black">{{ $loan->pendingUnits() }}</td>
                            <td class="px-3 py-3"></td>
                            <td class="px-3 py-3 text-right text-sm font-black">${{ number_format($loan->total_value, 0, ',', '.') }}</td>
                            @if ($loan->isActive())
                                <td colspan="2" class="px-4 py-3 bg-gray-100/70"></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($loan->isActive())
                <div class="p-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2.5 py-1 font-bold text-emerald-800" x-show="returnUnits() > 0">
                                <span>↩</span> <span x-text="returnUnits() + ' a devolver'"></span>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-md bg-blue-100 px-2.5 py-1 font-bold text-blue-800" x-show="sellUnits() > 0">
                                <span>💰</span> <span x-text="sellUnits() + ' a vender (' + formatMoney(sellAmount()) + ')'"></span>
                            </span>
                            <span class="text-gray-400 font-semibold" x-show="returnUnits() === 0 && sellUnits() === 0">
                                Selecciona en la columna "Decisión" qué productos se devuelven o venden.
                            </span>
                        </div>
                        <input type="text"
                               name="process_notes"
                               placeholder="Nota opcional (ej: Pago Nequi / Devuelto en buen estado)..."
                               class="flex-1 min-w-[220px] rounded-lg border border-gray-300 px-3 py-1.5 text-xs bg-white">
                    </div>

                    <button type="submit"
                            :disabled="returnUnits() === 0 && sellUnits() === 0"
                            class="shrink-0 rounded-xl bg-gray-900 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm hover:bg-black disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                        Guardar decisiones
                    </button>
                </div>
            @endif
        </form>

        {{-- MODAL: Cancel Loan --}}
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
        function loanInlineManager(initialState) {
            return {
                showCancelModal: false,
                state: initialState || {},
                onActionChange(id) {
                    const row = this.state[id];
                    if (!row) return;
                    if (row.action === 'none') {
                        row.quantity = row.pending;
                    } else if (!row.quantity || row.quantity <= 0 || row.quantity > row.pending) {
                        row.quantity = row.pending;
                    }
                },
                setAllAction(action) {
                    Object.keys(this.state).forEach(id => {
                        if (this.state[id].pending > 0) {
                            this.state[id].action = action;
                            this.state[id].quantity = this.state[id].pending;
                        }
                    });
                },
                returnUnits() {
                    return Object.values(this.state).reduce((sum, r) => {
                        return sum + (r.action === 'return' ? Math.max(0, Math.min(r.pending, Number(r.quantity) || 0)) : 0);
                    }, 0);
                },
                sellUnits() {
                    return Object.values(this.state).reduce((sum, r) => {
                        return sum + (r.action === 'sell' ? Math.max(0, Math.min(r.pending, Number(r.quantity) || 0)) : 0);
                    }, 0);
                },
                sellAmount() {
                    return Object.values(this.state).reduce((sum, r) => {
                        return sum + (r.action === 'sell' ? (Math.max(0, Math.min(r.pending, Number(r.quantity) || 0)) * (Number(r.unit_price) || 0)) : 0);
                    }, 0);
                },
                formatMoney(amount) {
                    return '$' + new Intl.NumberFormat('es-CO').format(amount || 0);
                },
                validateSubmit(e) {
                    if (this.returnUnits() === 0 && this.sellUnits() === 0) {
                        e.preventDefault();
                        alert('Selecciona al menos un producto para devolver o vender.');
                    }
                }
            };
        }
    </script>
</x-app-layout>
