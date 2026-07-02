@extends('layouts.admin')

@section('title', 'Nuevo cliente')
@section('eyebrow', 'Clientes')
@section('page-title', 'Registrar nuevo negocio')
@section('page-description', 'Crea un tenant con su plan, usuario admin y subdominio.')

@section('page-actions')
    <a href="{{ route('admin.clients') }}" class="admin-outline-link">Volver a clientes</a>
@endsection

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">Revisa los campos marcados.</div>
    @endif

    <section class="admin-card p-5 max-w-2xl">
        <h3 class="text-sm font-black uppercase text-gray-500">Datos del negocio</h3>
        <form method="POST" action="{{ route('admin.clients.store') }}" class="mt-4 grid gap-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Nombre del negocio
                    <input name="name" value="{{ old('name') }}" required maxlength="255" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="Tienda Ejemplo">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Correo del negocio
                    <input name="email" value="{{ old('email') }}" required type="email" maxlength="255" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="contacto@tienda.com">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Telefono
                    <input name="phone" value="{{ old('phone') }}" maxlength="80" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="300 123 4567">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Subdominio
                    <input name="subdomain" value="{{ old('subdomain') }}" required maxlength="100" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="tienda-ejemplo">
                    <p class="text-xs text-gray-500">Solo letras minusculas, numeros y guiones.</p>
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Prefijo de guias
                    <input name="guide_prefix" value="{{ old('guide_prefix') }}" maxlength="10" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="TE">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Plan
                    <select name="plan_id" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        <option value="">Seleccionar plan</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} — ${{ number_format($plan->monthly_price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <hr class="border-gray-200">

            <h3 class="text-sm font-black uppercase text-gray-500">Usuario administrador</h3>
            <p class="text-xs text-gray-500">Se creara un usuario con rol tenant_admin para este negocio.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Nombre
                    <input name="admin_name" value="{{ old('admin_name') }}" required maxlength="255" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="Juan Perez">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Correo
                    <input name="admin_email" value="{{ old('admin_email') }}" required type="email" maxlength="255" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="admin@tienda.com">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Contrasena (min. 8 caracteres)
                    <input name="admin_password" required type="password" minlength="8" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                </label>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <a href="{{ route('admin.clients') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Crear cliente</button>
            </div>
        </form>
    </section>
@endsection
