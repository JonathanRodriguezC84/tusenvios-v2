<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Reportes</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Liquidacion por afiliada</h2>
            </div>

            <form method="GET" action="{{ route('reports.affiliates') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[160px_160px_220px_auto_auto]">
                <input name="date_from" type="date" value="{{ $dateFrom }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                <input name="date_to" type="date" value="{{ $dateTo }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                <select name="affiliated_company_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    <option value="">Todas las afiliadas</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(($filters['affiliated_company_id'] ?? '') == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Consultar
                </button>
                <a href="{{ route('reports.affiliates.export', request()->query()) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Descargar CSV
                </a>
                <a href="{{ route('reports.affiliate-settlements.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Cerradas
                </a>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Guias pendientes</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">{{ $totals['shipments'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Envios a facturar</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">${{ number_format($totals['shipping_total'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Comision recaudo</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">${{ number_format($totals['commission_total'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Recaudo contraentrega</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950">${{ number_format($totals['collection_total'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Neto a entregar comercio</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950">${{ number_format($totals['net_collection'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total operacion</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950">${{ number_format($totals['total_to_invoice'], 0, ',', '.') }}</p>
                </div>
            </div>

            <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                @if ($errors->any())
                    <div class="border-b border-blue-200 bg-blue-50 px-5 py-3 text-sm text-blue-900">
                        {{ $errors->first() }}
                    </div>
                @endif
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Resumen</p>
                    <h3 class="text-lg font-semibold text-gray-950">Pendientes por comercio</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Afiliada</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guias</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Entregadas</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Envios</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Recaudo</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Comision</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Neto comercio</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Total operacion</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($summary as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="font-semibold text-gray-950">{{ $row['company']?->name ?? 'Sin afiliada' }}</p>
                                        <p class="text-xs text-gray-500">{{ $row['company']?->cod_commission_percent ?? 0 }}% recaudo</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $row['shipments'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $row['delivered'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($row['shipping_total'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($row['collection_total'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($row['commission_total'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">${{ number_format($row['net_collection'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">${{ number_format($row['total_to_invoice'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <form method="POST" action="{{ route('reports.affiliates.close') }}" onsubmit="return confirm('Cerrar esta liquidacion?')">
                                            @csrf
                                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                            <input type="hidden" name="affiliated_company_id" value="{{ $row['company']?->id }}">
                                            <button class="rounded-md bg-blue-700 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-800">
                                                Cerrar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-8 text-center text-gray-500">No hay guias de afiliadas en este periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Detalle</p>
                    <h3 class="text-lg font-semibold text-gray-950">Guias pendientes</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guia</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Afiliada</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Tarifa</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Envio</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Recaudo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($shipments as $shipment)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold">
                                        <a href="{{ route('shipments.show', $shipment) }}" class="text-blue-700 hover:underline">{{ $shipment->guide_number }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->affiliatedCompany?->name ?? 'RCI' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->deliveryZone?->name ?? 'Manual' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $shipment->status }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($shipment->shipping_value, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($shipment->collection_value, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-gray-500">No hay detalle para este periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

