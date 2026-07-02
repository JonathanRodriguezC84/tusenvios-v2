@if ($errors->any())
    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        Revisa los campos marcados antes de guardar.
    </div>
@endif

@php
    $selectedCompany = $companies->firstWhere('id', (int) request('affiliated_company_id'));
    $selectedTenantId = $senderProfile?->tenant_id ?? $selectedCompany?->tenant_id;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Cliente
        <select name="tenant_id" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
            <option value="">Seleccionar cliente</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $selectedTenantId) == $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Empresa afiliada
        <select name="affiliated_company_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
            <option value="">Tus Envios</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected(old('affiliated_company_id', $senderProfile?->affiliated_company_id ?? request('affiliated_company_id')) == $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Nombre visible
        <input name="label" value="{{ old('label', $senderProfile?->label) }}" required placeholder="Bodega principal" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Nombre o empresa
        <input name="name" value="{{ old('name', $senderProfile?->name) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Telefono
        <input name="phone" value="{{ old('phone', $senderProfile?->phone) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Estado
        <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
            <option value="active" @selected(old('status', $senderProfile?->status ?? 'active') === 'active')>Activo</option>
            <option value="paused" @selected(old('status', $senderProfile?->status) === 'paused')>Pausado</option>
        </select>
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
        Direccion
        <input name="address" value="{{ old('address', $senderProfile?->address) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Barrio
        <input name="neighborhood" value="{{ old('neighborhood', $senderProfile?->neighborhood) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
    </label>

    <label class="grid gap-1 text-sm font-semibold text-gray-700">
        Localidad
        <input name="locality" value="{{ old('locality', $senderProfile?->locality) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
    </label>

    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 sm:col-span-2">
        <input name="is_default" type="checkbox" value="1" @checked(old('is_default', $senderProfile?->is_default)) class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-700">
        Usar como remitente principal para esa empresa
    </label>
</div>

<div class="mt-5 flex justify-end">
    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
        Guardar remitente
    </button>
</div>

