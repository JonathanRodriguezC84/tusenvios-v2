<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Administracion</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Empresas afiliadas</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sender-profiles.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Remitentes
                </a>
                <a href="{{ route('affiliated-companies.create') }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Nueva afiliada
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

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Usuarios comerciales</p>
                    <h3 class="text-lg font-semibold text-gray-950">Afiliadas registradas</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Afiliada</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Prefijo</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Contacto</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Condiciones</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guias</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($companies as $company)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="font-semibold text-gray-950">{{ $company->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $company->document_number ?? 'Sin documento' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $company->guide_prefix ?? 'Auto' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $company->tenant?->name ?? 'Sin cliente' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $company->contact_name ?? 'Sin contacto' }}<br>{{ $company->phone ?? 'Sin telefono' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                        <p>{{ ['cash' => 'Contado', 'credit' => 'Credito', 'cod' => 'Contraentrega'][$company->default_payment_method] ?? 'Contado' }}</p>
                                        <p class="text-xs text-gray-500">{{ $company->allows_cod ? 'Recaudo permitido' : 'Sin recaudo' }} · Cupo ${{ number_format($company->credit_limit, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $company->shipments_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $company->status }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex gap-3">
                                            <a href="{{ route('affiliated-companies.edit', $company) }}" class="font-semibold text-blue-700 hover:underline">Editar</a>
                                            <a href="{{ route('sender-profiles.create', ['affiliated_company_id' => $company->id]) }}" class="font-semibold text-blue-700 hover:underline">Remitente</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-gray-500">Todavia no hay afiliadas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $companies->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
