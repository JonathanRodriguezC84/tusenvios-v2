<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Reportes</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Reporte de guias
                </h2>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('shipments.export', $filters) }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Descargar CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Vista previa</p>
                    <h3 class="text-lg font-semibold text-gray-950">{{ $shipments->count() }} guias encontradas</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guia</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Barcode</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Destinatario</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Zona</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Tarifa</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Liquidacion</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Envio</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Recaudo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($shipments as $shipment)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">{{ $shipment->guide_number }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->barcodeValue() }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->affiliatedCompany?->name ?? 'RCI' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->recipient_name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->recipient_locality ?: ($shipment->zone ?? 'Sin zona') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->deliveryZone?->name ?? 'Manual' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->status }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                        @if ($shipment->settlementItems->isNotEmpty())
                                            @php
                                                $settlement = $shipment->settlementItems->first()->settlement;
                                            @endphp
                                            <a href="{{ route('reports.affiliate-settlements.show', $settlement) }}" class="font-semibold text-blue-700 hover:underline">
                                                {{ $settlement->settlement_number }}
                                            </a>
                                            <span class="block text-xs text-gray-500">{{ $settlement->status === 'paid' ? 'Pagada' : 'Pendiente de pago' }}</span>
                                        @elseif (! $shipment->affiliated_company_id)
                                            No aplica
                                        @else
                                            Pendiente
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($shipment->shipping_value, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($shipment->collection_value, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-5 py-8 text-center text-gray-500">No hay guias para este reporte.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
