<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Tarifas</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Editar zona
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('delivery-zones.update', $deliveryZone) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        Revisa los campos marcados antes de guardar.
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    @if (Auth::user()->isSuperAdmin())
                        <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                            Cliente
                            <select name="tenant_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                                <option value="">General para todos</option>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" @selected(old('tenant_id', $deliveryZone->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Nombre
                        <input name="name" value="{{ old('name', $deliveryZone->name) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Codigo
                        <input name="code" value="{{ old('code', $deliveryZone->code) }}" class="rounded-md border-gray-300 text-sm uppercase shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Valor
                        <input name="price" type="number" min="0" step="100" value="{{ old('price', (int) $deliveryZone->price) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Estado
                        <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="active" @selected(old('status', $deliveryZone->status) === 'active')>Activa</option>
                            <option value="inactive" @selected(old('status', $deliveryZone->status) === 'inactive')>Inactiva</option>
                        </select>
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                        Localidades o barrios que aplican
                        <textarea name="coverage_keywords" rows="4" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('coverage_keywords', $deliveryZone->coverage_keywords) }}</textarea>
                        <span class="text-xs font-normal text-gray-500">Separa cada palabra con coma. El sistema usara esta lista para sugerir la tarifa al crear una guia.</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('delivery-zones.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
