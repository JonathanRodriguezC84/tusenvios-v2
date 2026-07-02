<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Afiliadas</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Editar empresa afiliada</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('affiliated-companies.update', $affiliatedCompany) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        Revisa los campos marcados antes de guardar.
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                        Cliente
                        <select name="tenant_id" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $affiliatedCompany->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Nombre empresa
                        <input name="name" value="{{ old('name', $affiliatedCompany->name) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Prefijo guia
                        <input name="guide_prefix" value="{{ old('guide_prefix', $affiliatedCompany->guide_prefix) }}" maxlength="3" class="uppercase rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        NIT / documento
                        <input name="document_number" value="{{ old('document_number', $affiliatedCompany->document_number) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Contacto
                        <input name="contact_name" value="{{ old('contact_name', $affiliatedCompany->contact_name) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Telefono
                        <input name="phone" value="{{ old('phone', $affiliatedCompany->phone) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Correo
                        <input name="email" type="email" value="{{ old('email', $affiliatedCompany->email) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Estado
                        <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="active" @selected(old('status', $affiliatedCompany->status) === 'active')>Activo</option>
                            <option value="paused" @selected(old('status', $affiliatedCompany->status) === 'paused')>Pausado</option>
                        </select>
                    </label>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-5">
                    <h3 class="text-base font-semibold text-gray-950">Condiciones comerciales</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Forma de pago predeterminada
                            <select name="default_payment_method" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                                <option value="cash" @selected(old('default_payment_method', $affiliatedCompany->default_payment_method) === 'cash')>Contado</option>
                                <option value="credit" @selected(old('default_payment_method', $affiliatedCompany->default_payment_method) === 'credit')>Credito</option>
                                <option value="cod" @selected(old('default_payment_method', $affiliatedCompany->default_payment_method) === 'cod')>Contraentrega</option>
                            </select>
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Cupo credito
                            <input name="credit_limit" type="number" min="0" step="1000" value="{{ old('credit_limit', (int) $affiliatedCompany->credit_limit) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>

                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <input name="allows_cod" type="checkbox" value="1" @checked(old('allows_cod', $affiliatedCompany->allows_cod)) class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-700">
                            Permite recaudo contraentrega
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Comision recaudo %
                            <input name="cod_commission_percent" type="number" min="0" max="100" step="0.1" value="{{ old('cod_commission_percent', $affiliatedCompany->cod_commission_percent) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                            Notas de facturacion
                            <textarea name="billing_notes" rows="3" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('billing_notes', $affiliatedCompany->billing_notes) }}</textarea>
                        </label>
                    </div>
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
