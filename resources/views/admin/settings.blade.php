@extends('layouts.admin')

@section('title', 'Configuracion')
@section('eyebrow', 'Sistema')
@section('page-title', 'Configuracion global')
@section('page-description', 'Ajustes generales de la plataforma.')

@section('content')
    @if (session('status'))
        <div class="mb-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-800">Revisa los campos.</div>
    @endif

    <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-3">
        <section class="admin-card p-4">
            <h3 class="mb-3 text-xs font-black uppercase tracking-wider text-gray-500">Cambiar contrasena</h3>
            <form method="POST" action="{{ route('admin.password') }}" class="grid gap-3">
                @csrf @method('PATCH')
                <input name="current_password" type="password" required placeholder="Contrasena actual" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <input name="password" type="password" required minlength="8" placeholder="Nueva contrasena" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    <input name="password_confirmation" type="password" required minlength="8" placeholder="Confirmar nueva" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                </div>
                <div class="flex justify-end">
                    <button class="admin-btn text-xs">Actualizar contrasena</button>
                </div>
            </form>
        </section>

        <section class="admin-card p-4">
            <h3 class="mb-3 text-xs font-black uppercase tracking-wider text-gray-500">Parametros del sistema</h3>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-3">
                @csrf @method('PATCH')

                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Guias de prueba por defecto
                    <input name="trial_guide_limit" type="number" min="1" max="100" value="{{ \Illuminate\Support\Facades\Cache::get('system:trial_guide_limit', 10) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700 w-32">
                    <span class="text-xs text-gray-500">Cantidad de guias gratis que recibe un nuevo cliente al registrarse.</span>
                </label>

                <div class="flex justify-end mt-1">
                    <button class="admin-btn" style="background: linear-gradient(135deg, #022a8c 0%, #011f69 100%);">Guardar configuracion</button>
                </div>
            </form>
        </section>

        <section class="admin-card p-4">
            <h3 class="mb-3 text-xs font-black uppercase tracking-wider text-gray-500">Operaciones masivas</h3>
            <p class="text-sm text-gray-500 mb-2">Aplica acciones sobre todas las suscripciones de un plan.</p>

            <form method="POST" action="{{ route('admin.subscriptions.bulk') }}" class="grid gap-3" onsubmit="return confirm('Aplicar esta accion masiva?')">
                @csrf

                <select name="plan_id" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    <option value="">Seleccionar plan...</option>
                    @foreach (\App\Models\SubscriptionPlan::where('code', 'emprende')->where('is_active', true)->orderBy('monthly_price')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->subscriptions()->where('status','active')->count() }} activas)</option>
                    @endforeach
                </select>

                <div class="flex flex-wrap gap-2">
                    <button name="action" value="pause_all" class="rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Pausar todas</button>
                    <button name="action" value="cancel_all" class="rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-100">Cancelar todas</button>
                </div>
            </form>
        </section>
    </div>
@endsection
