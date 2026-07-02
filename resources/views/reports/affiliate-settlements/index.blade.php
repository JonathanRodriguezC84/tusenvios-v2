<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Reportes</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Liquidaciones cerradas</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" action="{{ route('reports.affiliate-settlements.index') }}" class="mb-4 grid gap-2 rounded-lg border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-[180px_220px_150px_150px_auto_auto] lg:items-end">
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700">Estado</label>
                    <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        <option value="">Todas ({{ $totals['all'] }})</option>
                        <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Pendientes de pago ({{ $totals['closed'] }})</option>
                        <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Pagadas ({{ $totals['paid'] }})</option>
                    </select>
                </div>
                <div>
                    <label for="affiliated_company_id" class="block text-sm font-semibold text-gray-700">Afiliada</label>
                    <select id="affiliated_company_id" name="affiliated_company_id" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        <option value="">Todas</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected(($filters['affiliated_company_id'] ?? '') == $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-gray-700">Desde</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-semibold text-gray-700">Hasta</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                </div>
                <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Filtrar
                </button>
                <div class="flex gap-2">
                    <a href="{{ route('reports.affiliate-settlements.export-list', request()->query()) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        CSV
                    </a>
                    <a href="{{ route('reports.affiliate-settlements.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Limpiar
                    </a>
                </div>
            </form>

            <div class="mb-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Liquidaciones</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950">{{ $filteredTotals['settlements'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Guias incluidas</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950">{{ $filteredTotals['shipments'] }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Neto comercio</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950">${{ number_format($filteredTotals['net_collection'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Total operacion</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950">${{ number_format($filteredTotals['total_to_invoice'], 0, ',', '.') }}</p>
                </div>
            </div>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Historial</p>
                    <h3 class="text-lg font-semibold text-gray-950">Liquidaciones generadas</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Consecutivo</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Afiliada</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Periodo</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guias</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Total operacion</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Pago</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($settlements as $settlement)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">{{ $settlement->settlement_number }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $settlement->affiliatedCompany?->name ?? 'Sin afiliada' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $settlement->date_from->format('d/m/Y') }} - {{ $settlement->date_to->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $settlement->shipments_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-950">${{ number_format($settlement->total_to_invoice, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($settlement->status === 'paid')
                                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">Pagada</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pendiente de pago</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                        @if ($settlement->status === 'paid')
                                            <p class="font-semibold text-gray-950">{{ $settlement->paid_at?->format('d/m/Y H:i') }}</p>
                                            <p class="text-xs text-gray-500">{{ $settlement->payer?->name ?? 'Sistema' }}</p>
                                            @if ($settlement->payment_reference)
                                                <p class="text-xs text-gray-500">{{ $settlement->payment_reference }}</p>
                                            @endif
                                        @else
                                            <span class="text-sm text-amber-700">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <a href="{{ route('reports.affiliate-settlements.show', $settlement) }}" class="font-semibold text-blue-700 hover:underline">Ver</a>
                                        <a href="{{ route('reports.affiliate-settlements.export', $settlement) }}" class="ml-3 font-semibold text-gray-700 hover:underline">CSV</a>
                                        <a href="{{ route('reports.affiliate-settlements.print', $settlement) }}" target="_blank" class="ml-3 font-semibold text-gray-700 hover:underline">Imprimir</a>
                                        @if ($settlement->status !== 'paid' && Auth::user()->canMarkAffiliateSettlementsPaid())
                                            <form method="POST" action="{{ route('reports.affiliate-settlements.paid', $settlement) }}" class="mt-2" onsubmit="return confirm('Marcar esta liquidacion como pagada?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="font-semibold text-emerald-700 hover:underline">Marcar pagada</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-gray-500">Aun no hay liquidaciones cerradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $settlements->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

