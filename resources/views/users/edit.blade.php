<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Usuarios</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Editar usuario</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('users.update', $user) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        Revisa los campos marcados antes de guardar.
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Nombre
                        <input name="name" value="{{ old('name', $user->name) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Correo
                        <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Nueva contraseÃƒÂ±a
                        <input name="password" type="password" placeholder="Dejar vacio para no cambiar" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Rol
                        <select name="role" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="superadmin" @selected(old('role', $user->role) === 'superadmin')>Administrador</option>
                            <option value="tenant_admin" @selected(old('role', $user->role) === 'tenant_admin')>Administrador cliente</option>
                            <option value="affiliate" @selected(old('role', $user->role) === 'affiliate')>Empresa afiliada</option>
                            <option value="warehouse" @selected(old('role', $user->role) === 'warehouse')>Bodega</option>
                            <option value="courier" @selected(old('role', $user->role) === 'courier')>Mensajero</option>
                            <option value="viewer" @selected(old('role', $user->role) === 'viewer')>Consulta</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Cliente
                        <select name="tenant_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="">Tus Envios</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $user->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Empresa afiliada
                        <select name="affiliated_company_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="">No aplica</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('affiliated_company_id', $user->affiliated_company_id) == $company->id)>{{ $company->name }} - {{ $company->tenant?->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Estado
                        <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="active" @selected(old('status', $user->status) === 'active')>Activo</option>
                            <option value="paused" @selected(old('status', $user->status) === 'paused')>Pausado</option>
                        </select>
                    </label>
                </div>

                <div class="mt-5 flex justify-end">
                    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

