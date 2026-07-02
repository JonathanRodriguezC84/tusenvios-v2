<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Tarifas</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Zonas de cobro
                </h2>
            </div>

            <a href="{{ route('delivery-zones.create') }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Nueva zona
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Zona</th>
                            <th class="px-5 py-3">Codigo</th>
                            <th class="px-5 py-3">Valor</th>
                            <th class="px-5 py-3">Cobertura</th>
                            <th class="px-5 py-3">Cliente</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($zones as $zone)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-gray-950">{{ $zone->name }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $zone->code ?? 'Sin codigo' }}</td>
                                <td class="px-5 py-4 font-semibold text-gray-950">${{ number_format($zone->price, 0, ',', '.') }}</td>
                                <td class="max-w-xs px-5 py-4 text-gray-600">
                                    <span class="line-clamp-2">{{ $zone->coverage_keywords ?: 'Manual' }}</span>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $zone->tenant?->name ?? 'General' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $zone->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $zone->status === 'active' ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if (Auth::user()->isSuperAdmin() || $zone->tenant_id === Auth::user()->tenant_id)
                                        <a href="{{ route('delivery-zones.edit', $zone) }}" class="font-semibold text-blue-700 hover:text-blue-900">
                                            Editar
                                        </a>
                                    @else
                                        <span class="text-gray-400">General</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-500">Aun no hay zonas creadas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $zones->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
