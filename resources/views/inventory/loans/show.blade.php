<x-app-layout>
    @vite(['resources/css/inventory.css', 'resources/js/inventory.js'])

    <x-slot name="header">
        <x-page-header eyebrow="Inventario / Préstamos" :title="$loan->loan_number" :description="'Préstamo a: ' . $loan->borrower_name">
            <x-slot name="actions">
                <a href="{{ route('inventory.loans.index') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Listado de Préstamos</a>
                <a href="{{ route('inventory.loans.print', $loan) }}" target="_blank" class="rounded-lg bg-gray-900 text-sm font-bold text-white px-3.5 py-1.5 shadow-sm hover:bg-black">
                    🖨️ Imprimir Remisión
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $toastMessages = [];
        if (session('status')) { $toastMessages[] = ['text' => session('status'), 'type' => 'success']; }
        if ($errors->any()) { $toastMessages[] = ['text' => $errors->first() ?: 'Revisa los campos.', 'type' => 'error']; }
    @endphp

    <script id="inventory-toast-data" type="application/json">{{ json_encode($toastMessages) }}</script>

    <div class="p-3 lg:p-5 max-w-6xl mx-auto space-y-5" x-data="{ showReturnModal: false, showSettleModal: false, showCancelModal: false }">
        {{-- Status Bar & Overview --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black border {{ $loan->statusBadgeClass() }}">
                    {{ $loan->statusLabel() }}
                </span>
                <div>
                    <h2 class="text-base font-black text-gray-900">{{ $loan->loan_number }}</h2>
                    <span class="text-xs text-gray-500">Registrado el {{ $loan->created_at->format('d/m/Y h:i A') }} por {{ $loan->creator?->name ?? 'Usuario' }}</span>
                </div>
            </div>

            @if ($loan->isActive())
                <div class="flex items-center gap-2">
                    <button type="button" @click="showReturnModal = true" class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Registrar Devolución
                    </button>
                    <button type="button" @click="showSettleModal = true" class="rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Liquidar (Venta)
                    </button>
                    @if ($loan->returnedUnits() === 0 && $loan->soldUnits() === 0)
                        <button type="button" @click="showCancelModal = true" class="rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 px-3 py-2 text-xs font-bold">
                            Anular
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Grid: Details & Borrower info --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Borrower Details --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Datos del Prestatario</h3>
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
                                    <span class="text-xs text-rose-600 font-bold ml-1">(Vencido hace {{ $loan->due_date->diffInDays(now()) }} días)</span>
                                @endif
                            </span>
                        @else
                            <span class="text-gray-500">Sin fecha límite específica</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Notas / Observaciones:</span>
                        <p class="text-xs text-gray-700 whitespace-pre-line mt-0.5">{{ $loan->notes ?: 'Sin notas registradas.' }}</p>
                    </div>
                </div>
            </div>

            {{-- Units & Financial balance --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Balance de Mercancía</h3>
                <div class="space-y-2">
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
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-800">Detalle de Productos Prestados</h3>
                <span class="text-xs text-gray-400">{{ $loan->items->count() }} producto(s) en este préstamo</span>
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
                                    <span class="font-bold text-gray-900 block">{{ $item->product?->name ?? 'Producto eliminado' }}</span>
                                    @if ($item->product?->sku)
                                        <span class="text-xs text-gray-400">SKU: {{ $item->product->sku }}</span>
                                    @endif
                                    @if ($item->notes)
                                        <span class="text-xs text-gray-500 block italic mt-0.5">{{ $item->notes }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-gray-800">
                                    {{ $item->quantity_lent }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-emerald-700">
                                    {{ $item->quantity_returned }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-blue-700">
                                    {{ $item->quantity_sold }}
                                </td>
                                <td class="px-4 py-3 text-center font-black text-amber-800 bg-amber-50">
                                    {{ $item->pendingUnits() }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-gray-600">
                                    ${{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900">
                                    ${{ number_format($item->quantity_lent * $item->unit_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200 text-xs font-bold text-gray-900">
                        <tr>
                            <td class="px-4 py-3">TOTALES</td>
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
                    <button type="button" @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <p class="text-xs text-gray-600">Indica la cantidad que ingresa físicamente a bodega. El stock aumentará automáticamente en el inventario.</p>

                <form method="POST" action="{{ route('inventory.loans.return', $loan) }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        @foreach ($loan->items as $idx => $item)
                            @if ($item->pendingUnits() > 0)
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-xs">
                                    <div class="flex-1 mr-2">
                                        <span class="font-bold text-gray-900 block">{{ $item->product?->name }}</span>
                                        <span class="text-[11px] text-gray-500">Pendientes: {{ $item->pendingUnits() }}</span>
                                        <input type="hidden" name="returns[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-[10px] font-bold text-gray-400">Devuelve:</label>
                                        <input type="number" name="returns[{{ $idx }}][quantity]" value="{{ $item->pendingUnits() }}" min="0" max="{{ $item->pendingUnits() }}" class="w-full rounded border-gray-300 px-2 py-1 text-xs font-bold text-center">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Notas de la devolución (opcional):</label>
                        <input type="text" name="return_notes" placeholder="Ej: Entregado en perfecto estado por Carlos" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="showReturnModal = false" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Confirmar Ingreso a Bodega</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: Settle Items (Sale) --}}
        <div x-show="showSettleModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4" @click.away="showSettleModal = false">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-sm font-black text-gray-900">Liquidar Mercancía como Venta</h3>
                    <button type="button" @click="showSettleModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <p class="text-xs text-gray-600">El prestatario se queda con estos productos (los compró o vendió). No se reingresará stock a bodega porque ya salió.</p>

                <form method="POST" action="{{ route('inventory.loans.settle', $loan) }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        @foreach ($loan->items as $idx => $item)
                            @if ($item->pendingUnits() > 0)
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-xs">
                                    <div class="flex-1 mr-2">
                                        <span class="font-bold text-gray-900 block">{{ $item->product?->name }}</span>
                                        <span class="text-[11px] text-gray-500">Pendientes: {{ $item->pendingUnits() }} (${{ number_format($item->unit_price, 0, ',', '.') }}/u)</span>
                                        <input type="hidden" name="settle[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-[10px] font-bold text-gray-400">Vendido:</label>
                                        <input type="number" name="settle[{{ $idx }}][quantity]" value="{{ $item->pendingUnits() }}" min="0" max="{{ $item->pendingUnits() }}" class="w-full rounded border-gray-300 px-2 py-1 text-xs font-bold text-center">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Notas de la liquidación / pago (opcional):</label>
                        <input type="text" name="settle_notes" placeholder="Ej: Pagado en efectivo / Transferencia Nequi" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="showSettleModal = false" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-blue-700">Confirmar Venta / Liquidación</button>
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
                    <button type="button" @click="showCancelModal = false" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100">No, volver</button>
                    <button type="submit" class="rounded-lg bg-rose-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Sí, anular y reponer stock</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
