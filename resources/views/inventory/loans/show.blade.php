@php
    $qpColor = $theme['color'] ?? '#022a8c';
    $qpText = $theme['text'] ?? '#ffffff';
    $qpBorder = $theme['border'] ?? '#bfdbfe';
    $qpTint = $theme['tint'] ?? '#eff6ff';
    $qpSoft = $theme['soft'] ?? 'rgba(2,42,140,0.12)';

    $loanItemsState = $loan->items->mapWithKeys(fn ($i) => [
        $i->id => [
            'action' => 'none',
            'quantity' => $i->pendingUnits(),
            'pending' => $i->pendingUnits(),
            'unit_price' => (float) $i->unit_price,
        ],
    ]);
@endphp

<x-app-layout>
    @vite(['resources/css/quick-products.css', 'resources/js/quick-products.js'])

    <style>
        .qp-page { --qp-color: {{ $qpColor }}; --qp-color-text: {{ $qpText }}; --qp-color-border: {{ $qpBorder }}; --qp-color-tint: {{ $qpTint }}; --qp-color-soft: {{ $qpSoft }}; }
    </style>

    <x-slot name="header">
        <x-page-header eyebrow="Productos / Préstamos" title="Préstamo {{ $loan->loan_number }}" description="Gestiona devoluciones a bodega o liquida productos como venta.">
            <x-slot name="actions">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-2">
                    <a href="{{ route('inventory.loans.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        ← Volver al listado
                    </a>
                    <a href="{{ route('inventory.loans.print', $loan) }}" target="_blank" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">
                        🖨️ Imprimir Remisión
                    </a>
                    @if ($loan->isActive() && $loan->returnedUnits() == 0 && $loan->soldUnits() == 0)
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('loan-modal', { detail: 'cancel' }))" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50 cursor-pointer">
                            Anular préstamo
                        </button>
                    @endif
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="qp-page p-3 lg:p-5 h-full flex flex-col gap-3"
         x-data="loanInlineManager({{ $loanItemsState->toJson() }})"
         @loan-modal.window="if ($event.detail === 'cancel') showCancelModal = true">

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800 shadow-sm flex items-center justify-between shrink-0">
                <span>{{ session('status') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-800 shadow-sm shrink-0">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Resumen superior estilo quick-products --}}
        <aside class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm shrink-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-gray-900">Resumen del préstamo</p>
                    <h3 class="mt-0.5 text-base font-black text-gray-900">{{ $loan->borrower_name }}</h3>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $loan->statusBadgeClass() }}">
                    {{ $loan->statusLabel() }}
                </span>
            </div>

            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-6 text-xs">
                <div>
                    <span class="block font-semibold text-gray-600">Teléfono / WhatsApp</span>
                    @if ($loan->borrower_phone)
                        <a href="https://wa.me/57{{ preg_replace('/\D/', '', $loan->borrower_phone) }}" target="_blank" class="font-bold text-blue-700 hover:underline">
                            {{ $loan->borrower_phone }}
                        </a>
                    @else
                        <span class="font-bold text-gray-800">—</span>
                    @endif
                </div>
                <div>
                    <span class="block font-semibold text-gray-600">Dirección</span>
                    <span class="font-bold text-gray-900 block truncate" title="{{ $loan->borrower_address }}">{{ $loan->borrower_address ?: '—' }}</span>
                </div>
                <div>
                    <span class="block font-semibold text-gray-600">Entrega / Límite</span>
                    <span class="font-bold text-gray-900">
                        {{ $loan->loan_date->format('d/m/Y') }}
                        @if ($loan->due_date)
                            · <span class="{{ $loan->isOverdue() ? 'text-red-700' : '' }}">{{ $loan->due_date->format('d/m/Y') }}</span>
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block font-semibold text-gray-600">Prestado / Devuelto / Vendido</span>
                    <span class="font-bold text-gray-900">
                        {{ $loan->total_items }} unds ·
                        <span class="text-emerald-700">{{ $loan->returnedUnits() }} dev.</span> ·
                        <span class="text-blue-700">{{ $loan->soldUnits() }} vend.</span>
                    </span>
                </div>
                <div>
                    <span class="block font-semibold text-gray-600">Pendiente actual</span>
                    <span class="text-sm font-black text-amber-800">{{ $loan->pendingUnits() }} unds</span>
                </div>
                <div>
                    <span class="block font-semibold text-gray-600">Valor total préstamo</span>
                    <span class="text-sm font-black text-gray-950">${{ number_format($loan->total_value, 0, ',', '.') }}</span>
                </div>
            </div>

            @if ($loan->notes)
                <div class="mt-2.5 pt-2 border-t border-emerald-200/70 text-xs text-gray-700">
                    <span class="font-bold">Notas:</span> <span class="whitespace-pre-line">{{ $loan->notes }}</span>
                </div>
            @endif
        </aside>

        {{-- Tabla principal estilo Administrar productos --}}
        <section class="flex-1 min-h-0 flex flex-col">
            <form method="POST" action="{{ route('inventory.loans.process', $loan) }}" @submit="validateSubmit($event)" class="rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col min-h-0 flex-1">
                @csrf

                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-black text-gray-950">Administrar productos del préstamo</h3>
                        <p class="text-xs text-gray-500">
                            {{ $loan->items->count() }} producto(s) ·
                            @if ($loan->isActive())
                                Selecciona en cada producto si se devuelve a bodega o se liquida como venta.
                            @else
                                Este préstamo ya se encuentra cerrado.
                            @endif
                        </p>
                    </div>

                    @if ($loan->isActive())
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="setAllAction('return')" class="qp-btn qp-btn-secondary cursor-pointer">
                                ↩ Todos Devueltos
                            </button>
                            <button type="button" @click="setAllAction('sell')" class="qp-btn qp-btn-primary cursor-pointer">
                                💰 Todos Vendidos
                            </button>
                            <button type="button" @click="setAllAction('none')" class="qp-btn border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 cursor-pointer">
                                Limpiar
                            </button>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 flex flex-col min-h-0 flex-1">
                    <div class="overflow-x-auto flex-1 min-h-0">
                        <table class="w-full text-left text-sm text-gray-700">
                            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-bold uppercase text-gray-500">
                                <tr>
                                    <th class="px-5 py-2.5">Nombre de producto</th>
                                    <th class="px-3 py-2.5 text-center">Prestado</th>
                                    <th class="px-3 py-2.5 text-center">Devuelto</th>
                                    <th class="px-3 py-2.5 text-center">Vendido</th>
                                    <th class="px-3 py-2.5 text-center">Pendiente</th>
                                    <th class="px-3 py-2.5 text-right">Precio unit.</th>
                                    <th class="px-3 py-2.5 text-right">Subtotal</th>
                                    @if ($loan->isActive())
                                        <th class="px-4 py-2.5">Decisión</th>
                                        <th class="px-4 py-2.5 text-center">UND</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($loan->items as $idx => $item)
                                    <tr class="transition-colors"
                                        @if ($loan->isActive() && $item->pendingUnits() > 0)
                                            :class="{
                                                'bg-emerald-50/40': state[{{ $item->id }}].action === 'return',
                                                'bg-blue-50/40': state[{{ $item->id }}].action === 'sell'
                                            }"
                                        @endif>
                                        <td class="px-5 py-3">
                                            <div class="font-bold text-gray-900">{{ $item->displayName() }}</div>
                                            <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                                                @if ($item->displaySku())
                                                    <span>SKU: {{ $item->displaySku() }}</span>
                                                @endif
                                                @if ($item->product_type === 'quick')
                                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-600">Producto rápido</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center font-semibold text-gray-900">{{ $item->quantity_lent }}</td>
                                        <td class="px-3 py-3 text-center font-semibold text-emerald-700">{{ $item->quantity_returned }}</td>
                                        <td class="px-3 py-3 text-center font-semibold text-blue-700">{{ $item->quantity_sold }}</td>
                                        <td class="px-3 py-3 text-center font-black text-amber-800">{{ $item->pendingUnits() }}</td>
                                        <td class="px-3 py-3 text-right text-xs font-semibold text-gray-700">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right font-bold text-gray-900">${{ number_format($item->quantity_lent * $item->unit_price, 0, ',', '.') }}</td>

                                        @if ($loan->isActive())
                                            <td class="px-4 py-2.5 min-w-[190px]">
                                                @if ($item->pendingUnits() > 0)
                                                    <input type="hidden" name="actions[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                                    <select name="actions[{{ $idx }}][action]"
                                                            x-model="state[{{ $item->id }}].action"
                                                            @change="onActionChange({{ $item->id }})"
                                                            class="qp-field font-semibold">
                                                        <option value="none">Mantener pendiente</option>
                                                        <option value="return">↩ Devolver a bodega</option>
                                                        <option value="sell">💰 Vender / Liquidar</option>
                                                    </select>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Completado</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-center w-24">
                                                @if ($item->pendingUnits() > 0)
                                                    <input type="number"
                                                           name="actions[{{ $idx }}][quantity]"
                                                           x-model.number="state[{{ $item->id }}].quantity"
                                                           :readonly="state[{{ $item->id }}].action === 'none'"
                                                           min="0"
                                                           max="{{ $item->pendingUnits() }}"
                                                           class="qp-field text-center font-bold"
                                                           :class="state[{{ $item->id }}].action === 'none' ? 'bg-gray-100 text-gray-400' : 'bg-white text-gray-900'">
                                                @else
                                                    <span class="text-xs text-gray-300">—</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($loan->isActive())
                        <div class="border-t border-gray-200 bg-gray-50 px-5 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between shrink-0">
                            <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                                <div class="flex flex-wrap items-center gap-2 text-xs shrink-0">
                                    <span class="rounded-md bg-emerald-100 px-2.5 py-1 font-bold text-emerald-800" x-show="returnUnits() > 0" x-text="'↩ ' + returnUnits() + ' a devolver'"></span>
                                    <span class="rounded-md bg-blue-100 px-2.5 py-1 font-bold text-blue-800" x-show="sellUnits() > 0" x-text="'💰 ' + sellUnits() + ' a vender (' + formatMoney(sellAmount()) + ')'"></span>
                                    <span class="font-semibold text-gray-500" x-show="returnUnits() === 0 && sellUnits() === 0">
                                        Elige una decisión en los productos para continuar.
                                    </span>
                                </div>
                                <input type="text"
                                       name="process_notes"
                                       placeholder="Nota opcional (ej: Pago Nequi / Devuelto en buen estado)..."
                                       class="qp-field sm:max-w-sm">
                            </div>

                            <button type="submit"
                                    :disabled="returnUnits() === 0 && sellUnits() === 0"
                                    class="qp-btn qp-btn-lg qp-btn-primary disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                Guardar decisiones
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </section>

        {{-- MODAL: Cancel Loan --}}
        <div x-show="showCancelModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl max-w-sm w-full p-5 shadow-2xl space-y-4 border border-gray-200" @click.away="showCancelModal = false">
                <div class="flex items-center gap-2 text-red-700 font-black text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    ¿Anular este préstamo?
                </div>
                <p class="text-xs text-gray-600">Al anular, se restituirán inmediatamente todas las <strong>{{ $loan->total_items }} unidades</strong> al stock de bodega y el préstamo quedará anulado.</p>
                <form method="POST" action="{{ route('inventory.loans.cancel', $loan) }}" class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                    @csrf
                    <button type="button" @click="showCancelModal = false" class="qp-btn border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">No, volver</button>
                    <button type="submit" class="qp-btn qp-btn-danger cursor-pointer">Sí, anular y reponer stock</button>
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
