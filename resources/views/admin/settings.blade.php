@extends('layouts.admin')

@section('title', 'Configuracion')
@section('eyebrow', 'Sistema')
@section('page-title', 'Configuracion global')
@section('page-description', 'Ajustes generales de la plataforma.')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">Revisa los campos.</div>
    @endif

    <section class="admin-card p-5 max-w-xl mb-4">
        <h3 class="text-sm font-black uppercase text-gray-500 mb-4">Cambiar contrasena</h3>
        <form method="POST" action="{{ route('admin.password') }}" class="grid gap-3">
            @csrf @method('PATCH')
            <input name="current_password" type="password" required placeholder="Contrasena actual" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            <div class="grid grid-cols-2 gap-3">
                <input name="password" type="password" required minlength="8" placeholder="Nueva contrasena" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <input name="password_confirmation" type="password" required minlength="8" placeholder="Confirmar nueva" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            </div>
            <div class="flex justify-end">
                <button class="admin-btn text-xs">Actualizar contrasena</button>
            </div>
        </form>
    </section>

    <section class="admin-card p-5 max-w-xl">
        <h3 class="text-sm font-black uppercase text-gray-500 mb-4">Parametros del sistema</h3>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-4">
            @csrf @method('PATCH')

            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                Guias de prueba por defecto
                <input name="trial_guide_limit" type="number" min="1" max="100" value="{{ \Illuminate\Support\Facades\Cache::get('system:trial_guide_limit', 10) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700 w-32">
                <span class="text-xs text-gray-500">Cantidad de guias gratis que recibe un nuevo cliente al registrarse.</span>
            </label>

            <div class="flex justify-end mt-2">
                <button class="admin-btn">Guardar configuracion</button>
            </div>
        </form>
    </section>

    <section class="admin-card p-5 max-w-xl mt-4">
        <h3 class="text-sm font-black uppercase text-gray-500 mb-4">Operaciones masivas</h3>
        <p class="text-sm text-gray-500 mb-3">Aplica acciones sobre todas las suscripciones de un plan.</p>

        <form method="POST" action="{{ route('admin.subscriptions.bulk') }}" class="grid gap-3" onsubmit="return confirm('Aplicar esta accion masiva?')">
            @csrf

            <select name="plan_id" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Seleccionar plan...</option>
                @foreach (\App\Models\SubscriptionPlan::where('code', 'emprende')->where('is_active', true)->orderBy('monthly_price')->get() as $p)
                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->subscriptions()->where('status','active')->count() }} activas)</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button name="action" value="pause_all" class="rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Pausar todas</button>
                <button name="action" value="cancel_all" class="rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-100">Cancelar todas</button>
            </div>
        </form>
    </section>
@endsection
