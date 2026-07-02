<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Reportes</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Productividad por mensajero</h2>
            </div>
            <form method="GET" action="{{ route('reports.couriers') }}" class="flex gap-2">
                <input name="date" type="date" value="{{ $date }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Consultar
                </button>
                <a href="{{ route('reports.collections', ['date' => $date]) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Recaudos
                </a>
                <a href="{{ route('reports.affiliates', ['date_from' => $date, 'date_to' => $date]) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Afiliadas
                </a>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Resumen diario</p>
                    <h3 class="text-lg font-semibold text-gray-950">Mensajeros activos</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Mensajero</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Total</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">En ruta</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Entregadas</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Novedades</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Devoluciones</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Recaudo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($summary as $courier => $row)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">{{ $courier }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $row['total'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $row['on_route'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $row['delivered'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $row['issues'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $row['returns'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">${{ number_format($row['collection'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-gray-500">No hay actividad de mensajeros para esta fecha.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
