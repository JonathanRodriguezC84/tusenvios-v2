<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Administracion</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Clientes</h2>
            </div>
            @if (Auth::user()->canManageTenants())
                <a href="{{ route('tenants.create') }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Nuevo cliente
                </a>
            @endif
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
                    <p class="text-xs font-semibold uppercase text-gray-500">Cuentas SaaS</p>
                    <h3 class="text-lg font-semibold text-gray-950">Clientes registrados</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Subdominio</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Prefijo</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Contacto</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Plan</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Proximo pago</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Afiliadas</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Guias</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($tenants as $tenant)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="font-semibold text-gray-950">{{ $tenant->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $tenant->legal_name ?? 'Sin razon social' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-blue-700">{{ $tenant->subdomain }}.tusenvios.com.co</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $tenant->guide_prefix ?? 'Auto' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $tenant->email ?? 'Sin correo' }}<br>{{ $tenant->phone ?? 'Sin telefono' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="font-semibold text-gray-950">{{ $tenant->currentSubscription?->plan?->name ?? 'Sin plan' }}</p>
                                        <p class="text-xs text-gray-500">{{ $tenant->currentSubscription?->status ?? 'Sin suscripcion' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                        {{ $tenant->currentSubscription?->next_payment_at?->format('d/m/Y') ?? 'Sin fecha' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $tenant->affiliated_companies_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $tenant->shipments_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $tenant->status }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <a href="{{ route('tenants.edit', $tenant) }}" class="font-semibold text-blue-700 hover:underline">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-gray-500">Todavia no hay clientes registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $tenants->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
