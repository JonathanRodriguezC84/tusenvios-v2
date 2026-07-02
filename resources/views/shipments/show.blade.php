@php
    $statusStyles = [
        'created' => 'bg-gray-100 text-gray-800',
        'printed' => 'bg-gray-100 text-gray-800',
        'in_warehouse' => 'bg-amber-100 text-amber-800',
        'in_sorting' => 'bg-amber-100 text-amber-800',
        'assigned' => 'bg-indigo-100 text-indigo-800',
        'on_route' => 'bg-blue-100 text-blue-800',
        'delivered' => 'bg-emerald-100 text-emerald-800',
        'failed_delivery' => 'bg-blue-100 text-blue-900',
        'rescheduled' => 'bg-purple-100 text-purple-800',
        'return_pending' => 'bg-orange-100 text-orange-800',
        'returned' => 'bg-blue-100 text-blue-900',
        'cancelled' => 'bg-gray-200 text-gray-800',
    ];

    $statusLabels = [
        'created' => 'Por imprimir',
        'printed' => 'Impresa',
        'in_warehouse' => 'Preparando',
        'in_sorting' => 'Preparando',
        'assigned' => 'Asignada',
        'on_route' => 'En camino',
        'delivered' => 'Entregada',
        'failed_delivery' => 'Con novedad',
        'rescheduled' => 'Reprogramada',
        'return_pending' => 'Devuelve',
        'returned' => 'Devuelta',
        'courier_unassigned' => 'Mensajero retirado',
        'updated' => 'Actualizada',
        'cancelled' => 'Cancelada',
    ];

    $inventoryMovementLabels = [
        'shipment' => 'Descuento por guia',
        'restock' => 'Reposicion por cancelacion',
        'adjustment' => 'Ajuste manual',
        'initial' => 'Stock inicial',
        'status_change' => 'Cambio de estado',
    ];

    $inventorySnapshot = collect($shipment->inventory_snapshot ?? []);
    $inventorySnapshotUnits = $inventorySnapshot->sum(fn ($item) => (int) ($item['quantity'] ?? 0));
    $inventorySnapshotSale = $inventorySnapshot->sum(fn ($item) => (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0));
    $inventorySnapshotCost = $inventorySnapshot->sum(fn ($item) => (float) ($item['cost'] ?? 0) * (int) ($item['quantity'] ?? 0));
    $inventorySnapshotProfit = $inventorySnapshotSale - $inventorySnapshotCost;

    $printFormats = [
        '100x150' => ['label' => '100 x 150', 'help' => 'Termica estandar'],
        '100x100' => ['label' => '100 x 100', 'help' => 'Termica cuadrada'],
        '80x50' => ['label' => '80 x 50', 'help' => 'Termica pequena'],
        'half-letter' => ['label' => 'Media carta', 'help' => 'Media hoja'],
        'letter' => ['label' => 'Carta', 'help' => 'Impresora normal'],
        'a4' => ['label' => 'A4', 'help' => 'Hoja A4'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Detalle de guia</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $shipment->guide_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">Creada el {{ $shipment->created_at->format('d/m/Y h:i A') }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex">
                <a href="{{ route('shipments.print', $shipment) }}" onclick="event.preventDefault(); window.open(this.href, 'print{{ $shipment->id }}', 'width=800,height=600,scrollbars=yes,resizable=yes')" class="inline-flex items-center justify-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Imprimir
                </a>
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Mis guias
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="grid gap-5">
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Estado actual</p>
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                <span class="rounded-full px-3 py-1 text-sm font-bold {{ $statusStyles[$shipment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                                </span>
                                <p class="text-sm font-semibold text-gray-600">{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</p>
                            </div>
                        </div>

                        @if (Auth::user()->canEditShipments() && $shipment->canBeEdited())
                            <a href="{{ route('shipments.edit', $shipment) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Editar datos
                            </a>
                        @endif
                    </div>
                </section>

                <section class="grid gap-5 lg:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entrega</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</h3>
                        <dl class="mt-4 grid gap-3 text-sm">
                            <div>
                                <dt class="text-gray-500">Telefono</dt>
                                <dd class="font-semibold text-gray-950">{{ $shipment->recipient_phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Direccion</dt>
                                <dd class="font-semibold text-gray-950">{{ $shipment->recipient_address }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Barrio / ciudad</dt>
                                <dd class="font-semibold text-gray-950">{{ $shipment->recipient_neighborhood ?? 'Sin barrio' }} / {{ $shipment->recipient_locality ?? 'Sin ciudad' }}</dd>
                            </div>
                            @if ($shipment->recipient_notes)
                                <div>
                                    <dt class="text-gray-500">Nota</dt>
                                    <dd class="font-semibold text-gray-950">{{ $shipment->recipient_notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Producto y pago</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">{{ $shipment->content_description ?: 'Producto sin descripcion' }}</h3>
                        <dl class="mt-4 grid gap-3 text-sm">
                            @if (! empty($shipment->inventory_snapshot))
                                <div class="rounded-md border {{ $shipment->status === 'cancelled' ? 'border-gray-200 bg-gray-50' : 'border-emerald-100 bg-emerald-50' }} p-3">
                                    <dt class="text-xs font-black uppercase {{ $shipment->status === 'cancelled' ? 'text-gray-600' : 'text-emerald-700' }}">
                                        Productos de inventario{{ $shipment->status === 'cancelled' ? ' repuestos' : '' }}
                                    </dt>
                                    @if ($shipment->status === 'cancelled')
                                        <p class="mt-1 text-xs font-semibold text-gray-600">La guia fue cancelada y las unidades asociadas volvieron al inventario.</p>
                                    @endif
                                    <dd class="mt-2 grid gap-2">
                                        @foreach ($shipment->inventory_snapshot as $item)
                                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                                <span class="font-semibold text-gray-950">
                                                    {{ $item['name'] ?? 'Producto' }}{{ ! empty($item['sku']) ? ' · '.$item['sku'] : '' }}
                                                </span>
                                                <span class="text-xs font-black text-emerald-800">
                                                    x{{ $item['quantity'] ?? 1 }} · ${{ number_format((float) ($item['price'] ?? 0), 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                        <div class="mt-1 grid gap-2 rounded-md border border-emerald-100 bg-white p-3 sm:grid-cols-4">
                                            <div>
                                                <p class="text-xs font-black uppercase text-gray-500">Unidades</p>
                                                <p class="font-black text-gray-950">{{ $inventorySnapshotUnits }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black uppercase text-gray-500">Venta</p>
                                                <p class="font-black text-gray-950">${{ number_format($inventorySnapshotSale, 0, ',', '.') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black uppercase text-gray-500">Costo</p>
                                                <p class="font-black text-gray-950">${{ number_format($inventorySnapshotCost, 0, ',', '.') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black uppercase text-gray-500">Utilidad</p>
                                                <p class="font-black text-emerald-700">${{ number_format($inventorySnapshotProfit, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                            @endif
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-gray-500">Piezas</dt>
                                    <dd class="font-semibold text-gray-950">{{ $shipment->pieces }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Tipo</dt>
                                    <dd class="font-semibold text-gray-950">{{ $shipment->package_type }}</dd>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-gray-500">Valor envio</dt>
                                    <dd class="font-semibold text-gray-950">${{ number_format($shipment->shipping_value, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Recaudo</dt>
                                    <dd class="font-semibold text-gray-950">${{ number_format($shipment->collection_value, 0, ',', '.') }}</dd>
                                </div>
                            </div>
                            <div>
                                <dt class="text-gray-500">Forma de pago</dt>
                                <dd class="font-semibold text-gray-950">
                                    {{ ['cod' => 'Contraentrega', 'cash' => 'Pago normal', 'credit' => 'Credito'][$shipment->payment_method] ?? $shipment->payment_method }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Historial</p>
                            <h3 class="mt-1 text-lg font-black text-gray-950">Movimientos de la guia</h3>
                        </div>
                    </div>

                    <ol class="mt-5 grid gap-4 text-sm">
                        @forelse ($shipment->events->sortByDesc('recorded_at') as $event)
                            <li class="border-l-4 border-red-600 pl-4">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="font-black text-gray-950">{{ $statusLabels[$event->status] ?? $event->status }}</p>
                                        <p class="mt-1 text-gray-600">{{ $event->location ?? 'Sin ubicacion' }}</p>
                                        @if ($event->notes)
                                            <p class="mt-1 text-gray-500">{{ $event->notes }}</p>
                                        @endif
                                    </div>
                                    <span class="text-xs font-semibold text-gray-500">{{ $event->recorded_at->format('d/m/Y h:i A') }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500">Sin movimientos registrados.</li>
                        @endforelse
                    </ol>
                </section>

                @if ($shipment->inventoryMovements->count())
                    <section id="inventario" class="rounded-lg border border-emerald-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">Inventario</p>
                                <h3 class="mt-1 text-lg font-black text-gray-950">Movimientos de stock</h3>
                            </div>
                            <a href="{{ route('inventory.index') }}" class="text-sm font-bold text-blue-800 hover:text-blue-900">Ver inventario</a>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm">
                            @foreach ($shipment->inventoryMovements->sortByDesc('created_at') as $movement)
                                <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="font-black text-gray-950">{{ $movement->product?->name ?? 'Producto eliminado' }}</p>
                                            <p class="mt-1 text-gray-600">{{ $inventoryMovementLabels[$movement->type] ?? $movement->type }}</p>
                                            @if ($movement->notes)
                                                <p class="mt-1 text-xs font-semibold text-gray-500">{{ $movement->notes }}</p>
                                            @endif
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <p class="font-black {{ $movement->quantity_delta < 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                                {{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }} unidad(es)
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-gray-500">Stock despues: {{ $movement->stock_after }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="grid gap-5 content-start xl:sticky xl:top-6">
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Etiqueta</p>
                    <div class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-center">
                        <p class="text-2xl font-black tracking-wider text-gray-950">{{ $shipment->guide_number }}</p>
                        <p class="mt-2 text-sm text-gray-500">Elige el formato segun la impresora.</p>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        @foreach ($printFormats as $formatKey => $format)
                            <a href="{{ route('shipments.print', ['shipment' => $shipment, 'format' => $formatKey]) }}" onclick="event.preventDefault(); window.open(this.href, 'print{{ $shipment->id }}{{ $formatKey }}', 'width=800,height=600,scrollbars=yes,resizable=yes')" class="{{ $formatKey === '100x150' ? 'border-blue-700 bg-blue-700 text-white hover:bg-blue-800' : 'border-gray-300 bg-white text-gray-800 hover:bg-gray-50' }} rounded-md border px-3 py-2 text-center text-xs font-black shadow-sm">
                                {{ $format['label'] }}
                                <span class="{{ $formatKey === '100x150' ? 'text-blue-100' : 'text-gray-500' }} mt-1 block text-[10px] font-semibold">{{ $format['help'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                @if ((Auth::user()->canScanShipments() || Auth::user()->canEditShipments()) && count($nextStatuses))
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Actualizar</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Cambiar estado</h3>
                        @if ($errors->any())
                            <div class="mt-3 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div class="mt-4 grid gap-2">
                            @foreach ($nextStatuses as $nextStatus)
                                @continue($nextStatus === 'cancelled')
                                <form method="POST" action="{{ route('shipments.update-status', $shipment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                                    <button class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                        Marcar como {{ strtolower($statusLabels[$nextStatus] ?? $nextStatus) }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (Auth::user()->canEditShipments() && $shipment->canBeCancelled())
                    <section class="rounded-lg border border-blue-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wider text-blue-800">Cancelar</p>
                        <form id="cancel-shipment-form" method="POST" action="{{ route('shipments.cancel', $shipment) }}" class="mt-3 grid gap-3">
                            @csrf
                            @method('PATCH')
                            <textarea name="notes" rows="2" placeholder="Motivo de cancelacion" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"></textarea>
                            <button type="button" class="rounded-md bg-blue-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-800" onclick="document.getElementById('confirm-cancel-shipment').classList.remove('hidden')">
                                Cancelar guia
                            </button>
                        </form>
                    </section>
                    <x-confirmation-modal id="confirm-cancel-shipment" title="Cancelar guia" message="Se cancelara la guia {{ $shipment->guide_number }}. Esta accion no se puede deshacer." confirmText="Cancelar guia" cancelText="Mantener guia" />
                    <script>
                        document.getElementById('confirm-cancel-shipment-form')?.addEventListener('click', function(e) {
                            document.getElementById('cancel-shipment-form').submit();
                        });
                    </script>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
