@extends('layouts.admin')

@section('title', 'Perfil')
@section('eyebrow', 'Administracion')
@section('page-title', 'Cambiar contrasena')
@section('page-description', 'Actualiza tu contrasena de acceso al panel.')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">Revisa los campos.</div>
    @endif

    <section class="admin-card p-5 max-w-md">
        <h3 class="text-sm font-black uppercase text-gray-500 mb-4">Datos de acceso</h3>

        <div class="mb-4">
            <p class="text-xs text-gray-500">Correo</p>
            <p class="font-semibold text-gray-950">{{ Auth::user()->email }}</p>
        </div>

        <form method="POST" action="{{ route('admin.password') }}" class="grid gap-4">
            @csrf
            @method('PATCH')

            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                Contrasena actual
                <input name="current_password" type="password" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            </label>

            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                Nueva contrasena
                <input name="password" type="password" required minlength="8" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            </label>

            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                Confirmar nueva contrasena
                <input name="password_confirmation" type="password" required minlength="8" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            </label>

            <div class="flex justify-end mt-2">
                <button class="admin-btn">Cambiar contrasena</button>
            </div>
        </form>
    </section>
@endsection
