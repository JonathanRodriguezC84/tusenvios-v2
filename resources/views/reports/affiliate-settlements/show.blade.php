<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Liquidacion</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $settlement->settlement_number }}</h2>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                @if ($settlement->status !== 'paid' && Auth::user()->canMarkAffiliateSettlementsPaid())
                    <form method="POST" action="{{ route('reports.affiliate-settlements.paid', $settlement) }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" onsubmit="return confirm('Marcar esta liquidacion como pagada?')">
                        @csrf
                        @method('PATCH')
                        <input
                            type="text"
                            name="payment_reference"
                            value="{{ old('payment_reference') }}"
                            placeholder="Referencia de pago"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">
                            Marcar pagada
                        </button>
                    </form>
                @endif
                <a href="{{ route('reports.affiliate-settlements.export', $settlement) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Descargar CSV
                </a>
                <a href="{{ route('reports.affiliate-settlements.print', $settlement) }}" target="_blank" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Imprimir
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
            @error('payment_reference')
                <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-900">
                    {{ $message }}
                </div>
            @enderror

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <p class="text-sm text-gray-500">Afiliada</p>
                        <p class="mt-1 font-semibold text-gray-950">{{ $settlement->affiliatedCompany?->name ?? 'Sin afiliada' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Periodo</p>
                        <p class="mt-1 font-semibold text-gray-950">{{ $settlement->date_from->format('d/m/Y') }} - {{ $settlement->date_to->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cerrada por</p>
                        <p class="mt-1 font-semibold text-gray-950">{{ $settlement->creator?->name ?? 'Sistema' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Estado</p>
                        @if ($settlement->status === 'paid')
                            <span class="mt-1 inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">Pagada</span>
                        @else
                            <span class="mt-1 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pendiente de pago</span>
                        @endif
                    </div>
                    @if ($settlement->paid_at)
                        <div>
                            <p class="text-sm text-gray-500">Pagada por</p>
                            <p class="mt-1 font-semibold text-gray-950">{{ $settlement->payer?->name ?? 'Sistema' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha de pago</p>
                            <p class="mt-1 font-semibold text-gray-950">{{ $settlement->paid_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    @if ($settlement->payment_reference)
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Referencia de pago</p>
                            <p class="mt-1 font-semibold text-gray-950">{{ $settlement->payment_reference }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Envios</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">${{ number_format($settlement->shipping_total, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Recaudo neto comercio</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">${{ number_format($settlement->net_collection, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total operacion</p>
                    <p class="mt-2 text-3xl font-bold text-gray-950">${{ number_format($settlement->total_to_invoice, 0, ',', '.') }}</p>
                </div>
            </div>

            <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Detalle</p>
                    <h3 class="text-lg font-semibold text-gray-950">{{ $settlement->shipments_count }} guias incluidas</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guia</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Destinatario</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Tarifa</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Envio</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Recaudo</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Comision</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($settlement->items as $item)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold">
                                        <a href="{{ route('shipments.show', $item->shipment) }}" class="text-blue-700 hover:underline">{{ $item->guide_number }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $item->recipient_name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $item->delivery_zone_name ?? 'Manual' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $item->status }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($item->shipping_value, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($item->collection_value, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($item->commission_value, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

