<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Administracion</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Usuarios</h2>
            </div>
            <a href="{{ route('users.create') }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Nuevo usuario
            </a>
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
                    <p class="text-xs font-semibold uppercase text-gray-500">Accesos</p>
                    <h3 class="text-lg font-semibold text-gray-950">Usuarios registrados</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Usuario</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Rol</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Afiliada</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="font-semibold text-gray-950">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $user->role }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $user->tenant?->name ?? 'Tus Envios' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $user->affiliatedCompany?->name ?? 'No aplica' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $user->status ?? 'active' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <a href="{{ route('users.edit', $user) }}" class="font-semibold text-blue-700 hover:underline">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-gray-500">Todavia no hay usuarios registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $users->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

