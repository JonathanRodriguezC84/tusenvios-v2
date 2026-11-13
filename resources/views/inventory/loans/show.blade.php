@php
    $qpColor = $theme['color'] ?? '#022a8c';
    $qpText = $theme['text'] ?? '#ffffff';
    $qpBorder = $theme['border'] ?? '#bfdbfe';
    $qpTint = $theme['tint'] ?? '#eff6ff';
    $qpSoft = $theme['soft'] ?? 'rgba(2,42,140,0.12)';

    $loanItemsState = $loan->items->mapWithKeys(fn ($i) => [
        $i->id => [
            'checked' => false,
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
        <x-page-header eyebrow="Productos / Préstamos" title="Préstamo {{ $loan->loan_number }}" description="Preselecciona los productos de la lista y elige si se devuelven a bodega o se liquidan como venta.">
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
         x-data="loanSelectionManager({{ $loanItemsState->toJson() }})"
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
            <form id="loan-process-form" method="POST" action="{{ route('inventory.loans.process', $loan) }}" class="rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col min-h-0 flex-1">
                @csrf

                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-black text-gray-950">Administrar productos del préstamo</h3>
                        <p class="text-xs text-gray-500">
                            {{ $loan->items->count() }} producto(s) ·
                            @if ($loan->isActive())
                                Marca los cuadros a la izquierda para habilitar Devolución o Venta.
                            @else
                                Este préstamo ya se encuentra cerrado.
                            @endif
                        </p>
                    </div>

                    @if ($loan->isActive())
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold text-gray-600 mr-1" x-show="selectedCount() > 0" x-text="selectedUnits() + ' und(s) · ' + formatMoney(selectedAmount())"></span>
                            <button type="button"
                                    @click="submitAction('return')"
                                    :disabled="selectedUnits() <= 0"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-opacity">
                                <span>↩</span> Devolución
                            </button>
                            <button type="button"
                                    @click="submitAction('sell')"
                                    :disabled="selectedUnits() <= 0"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-800 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-opacity">
                                <span>💰</span> Venta
                            </button>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 flex flex-col min-h-0 flex-1">
                    <div class="overflow-x-auto flex-1 min-h-0">
                        <table class="w-full text-left text-sm text-gray-700">
                            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-bold uppercase text-gray-500">
                                <tr>
                                    @if ($loan->isActive())
                                        <th class="pl-5 pr-2 py-2.5 w-10">
                                            <input type="checkbox"
                                                   :checked="allSelected()"
                                                   @change="toggleAll($event.target.checked)"
                                                   title="Seleccionar todos los pendientes"
                                                   class="h-4 w-4 rounded border-gray-300 text-blue-700 focus:ring-blue-600 cursor-pointer">
                                        </th>
                                    @endif
                                    <th class="px-4 py-2.5">Nombre de producto</th>
                                    <th class="px-3 py-2.5 text-center">Prestado</th>
                                    <th class="px-3 py-2.5 text-center">Devuelto</th>
                                    <th class="px-3 py-2.5 text-center">Vendido</th>
                                    <th class="px-3 py-2.5 text-center">Pendiente</th>
                                    @if ($loan->isActive())
                                        <th class="px-3 py-2.5 text-center">Cant.</th>
                                    @endif
                                    <th class="px-3 py-2.5 text-right">Precio unit.</th>
                                    <th class="px-5 py-2.5 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($loan->items as $idx => $item)
                                    <tr class="transition-colors"
                                        @if ($loan->isActive() && $item->pendingUnits() > 0)
                                            :class="state[{{ $item->id }}].checked ? 'bg-blue-50/50' : 'hover:bg-gray-50'"
                                        @endif>
                                        @if ($loan->isActive())
                                            <td class="pl-5 pr-2 py-3 w-10">
                                                @if ($item->pendingUnits() > 0)
                                                    <input type="hidden" name="actions[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                                    <input type="hidden" name="actions[{{ $idx }}][action]" :value="state[{{ $item->id }}].checked ? bulkAction : 'none'">
                                                    <input type="checkbox"
                                                           x-model="state[{{ $item->id }}].checked"
                                                           @change="onCheckChange({{ $item->id }})"
                                                           class="h-4 w-4 rounded border-gray-300 text-blue-700 focus:ring-blue-600 cursor-pointer">
                                                @else
                                                    <span class="text-xs text-gray-300">—</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 cursor-pointer" @if($loan->isActive() && $item->pendingUnits() > 0) @click="toggleRow({{ $item->id }})" @endif>
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
                                        @if ($loan->isActive())
                                            <td class="px-3 py-2.5 text-center w-24">
                                                @if ($item->pendingUnits() > 0)
                                                    <input type="number"
                                                           name="actions[{{ $idx }}][quantity]"
                                                           x-model.number="state[{{ $item->id }}].quantity"
                                                           :readonly="!state[{{ $item->id }}].checked"
                                                           min="1"
                                                           max="{{ $item->pendingUnits() }}"
                                                           class="qp-field text-center font-bold"
                                                           :class="!state[{{ $item->id }}].checked ? 'bg-gray-100 text-gray-400' : 'bg-white text-gray-900'">
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-500">Listo</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="px-3 py-3 text-right text-xs font-semibold text-gray-700">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-5 py-3 text-right font-bold text-gray-900">${{ number_format($item->quantity_lent * $item->unit_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($loan->isActive())
                        <div class="border-t border-gray-200 bg-gray-50 px-5 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between shrink-0">
                            <input type="text"
                                   name="process_notes"
                                   placeholder="Nota opcional (ej: Pago Nequi / Devuelto en buen estado)..."
                                   class="qp-field sm:max-w-md">

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button"
                                        @click="submitAction('return')"
                                        :disabled="selectedUnits() <= 0"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-opacity">
                                    <span>↩</span> Registrar Devolución
                                </button>
                                <button type="button"
                                        @click="submitAction('sell')"
                                        :disabled="selectedUnits() <= 0"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-800 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-opacity">
                                    <span>💰</span> Registrar Venta
                                </button>
                            </div>
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
        function loanSelectionManager(initialState) {
            return {
                showCancelModal: false,
                bulkAction: 'none',
                state: initialState || {},
                toggleRow(id) {
                    const row = this.state[id];
                    if (!row || row.pending <= 0) return;
                    row.checked = !row.checked;
                    this.onCheckChange(id);
                },
                onCheckChange(id) {
                    const row = this.state[id];
                    if (!row) return;
                    if (row.checked && (!row.quantity || row.quantity <= 0 || row.quantity > row.pending)) {
                        row.quantity = row.pending;
                    }
                },
                allSelected() {
                    const pendingRows = Object.values(this.state).filter(r => r.pending > 0);
                    return pendingRows.length > 0 && pendingRows.every(r => r.checked);
                },
                toggleAll(checked) {
                    Object.values(this.state).forEach(r => {
                        if (r.pending > 0) {
                            r.checked = checked;
                            if (checked && (!r.quantity || r.quantity <= 0 || r.quantity > r.pending)) {
                                r.quantity = r.pending;
                            }
                        }
                    });
                },
                selectedCount() {
                    return Object.values(this.state).filter(r => r.checked && r.pending > 0).length;
                },
                selectedUnits() {
                    return Object.values(this.state).reduce((sum, r) => {
                        if (!r.checked || r.pending <= 0) return sum;
                        return sum + Math.max(0, Math.min(r.pending, Number(r.quantity) || 0));
                    }, 0);
                },
                selectedAmount() {
                    return Object.values(this.state).reduce((sum, r) => {
                        if (!r.checked || r.pending <= 0) return sum;
                        return sum + (Math.max(0, Math.min(r.pending, Number(r.quantity) || 0)) * (Number(r.unit_price) || 0));
                    }, 0);
                },
                formatMoney(amount) {
                    return '$' + new Intl.NumberFormat('es-CO').format(amount || 0);
                },
                submitAction(action) {
                    if (this.selectedUnits() <= 0) {
                        alert('Selecciona al menos un producto con el cuadro de la izquierda.');
                        return;
                    }
                    this.bulkAction = action;
                    this.$nextTick(() => {
                        document.getElementById('loan-process-form').submit();
                    });
                }
            };
        }
    </script>
</x-app-layout>
