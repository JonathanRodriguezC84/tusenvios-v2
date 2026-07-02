<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Reportes</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Recaudos</h2>
            </div>
            <form method="GET" action="{{ route('reports.collections') }}" class="flex gap-2">
                <input name="date" type="date" value="{{ $date }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Consultar
                </button>
                <a href="{{ route('reports.couriers', ['date' => $date]) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Mensajeros
                </a>
                <a href="{{ route('reports.affiliates', ['date_from' => $date, 'date_to' => $date]) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Afiliadas
                </a>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Recaudo entregado</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">${{ number_format($totals['delivered'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Recaudo pendiente</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">${{ number_format($totals['pending'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Guias con recaudo</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">{{ $totals['count'] }}</p>
                </div>
            </div>

            <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Resumen por mensajero</p>
                    <h3 class="text-lg font-semibold text-gray-950">Corte del dia</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Mensajero</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guias</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Entregado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Pendiente</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($byCourier as $courier => $summary)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">{{ $courier }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $summary['count'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($summary['delivered'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($summary['pending'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-500">No hay recaudos para esta fecha.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Detalle</p>
                    <h3 class="text-lg font-semibold text-gray-950">Guias con recaudo</h3>
                </div>
                @php
                    $statusLabels = [
                        'delivered' => 'Entregado',
                        'failed_delivery' => 'No entregado',
                        'rescheduled' => 'Reprogramado',
                        'return_pending' => 'Devolucion pendiente',
                    ];
                @endphp
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guia</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Mensajero</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Destinatario</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Recaudo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($shipments as $shipment)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold">
                                        <a href="{{ route('shipments.show', $shipment) }}" class="text-blue-700 hover:underline">{{ $shipment->guide_number }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->courier?->name ?? 'Sin mensajero' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->recipient_name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $statusLabels[$shipment->status] ?? $shipment->status }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($shipment->collection_value, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-gray-500">No hay detalle para esta fecha.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
