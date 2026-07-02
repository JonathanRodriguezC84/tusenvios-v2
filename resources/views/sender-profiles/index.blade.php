<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Remitentes</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Remitentes guardados</h2>
            </div>
            <a href="{{ route('sender-profiles.create') }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Nuevo remitente
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700 lg:col-span-2">
                        Buscar
                        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, telefono, direccion" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Empresa afiliada
                        <select name="affiliated_company_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="">Todas</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(($filters['affiliated_company_id'] ?? '') == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Estado
                        <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="">Todos</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activo</option>
                            <option value="paused" @selected(($filters['status'] ?? '') === 'paused')>Pausado</option>
                        </select>
                    </label>
                </div>

                <div class="mt-3 flex flex-wrap justify-end gap-2">
                    <a href="{{ route('sender-profiles.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Limpiar
                    </a>
                    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                        Filtrar
                    </button>
                </div>
            </form>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Remitente</th>
                            <th class="px-4 py-3">Empresa afiliada</th>
                            <th class="px-4 py-3">Direccion</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($senders as $sender)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-950">{{ $sender->label }}</p>
                                    <p class="text-xs text-gray-500">{{ $sender->name }} {{ $sender->phone ? '- '.$sender->phone : '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $sender->affiliatedCompany?->name ?? 'Tus Envios' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $sender->address }}
                                    @if ($sender->locality)
                                        <span class="text-gray-500">/ {{ $sender->locality }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $sender->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $sender->status === 'active' ? 'Activo' : 'Pausado' }}
                                    </span>
                                    @if ($sender->is_default)
                                        <span class="ml-1 rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">Principal</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('sender-profiles.edit', $sender) }}" class="font-semibold text-blue-700 hover:underline">Editar</a>
                                        <form method="POST" action="{{ route('sender-profiles.update-status', $sender) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $sender->status === 'active' ? 'paused' : 'active' }}">
                                            <button class="font-semibold text-blue-700 hover:underline">
                                                {{ $sender->status === 'active' ? 'Pausar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">Aun no hay remitentes guardados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $senders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

