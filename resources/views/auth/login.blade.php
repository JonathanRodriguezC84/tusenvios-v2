<x-guest-layout>
    <style>
        .login-checkbox {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            max-width: 18px !important;
            min-height: 18px !important;
            max-height: 18px !important;
            flex: 0 0 18px !important;
            aspect-ratio: 1 / 1;
            padding: 0 !important;
        }
    </style>

    <div class="mb-6">
        <p class="text-xs font-semibold uppercase text-blue-700">Acceso al panel</p>
        <h2 class="mt-1 text-xl font-semibold leading-tight text-gray-900">Ingresar a Tus Envios</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">Entra para crear guias, imprimir etiquetas y revisar tus envios.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="grid gap-4">
            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                Correo
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="correo@negocio.com" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </label>

            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                Contrasena
                <input id="password" type="password" name="password" required autocomplete="current-password" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </label>

            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="login-checkbox rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-700" name="remember">
                <span class="ms-2 text-sm text-gray-600">Recordarme</span>
            </label>
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-semibold text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2" href="{{ route('password.request') }}">
                    Olvide mi contrasena
                </a>
            @endif

            <button class="rounded-md bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Entrar
            </button>
        </div>

        <div class="mt-6 grid gap-2 border-t border-gray-200 pt-4">
            <a href="{{ route('register') }}" class="text-sm font-semibold text-blue-700 hover:underline">
                Crear cuenta para mi negocio
            </a>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-600 hover:underline">
                Volver a la portada
            </a>
        </div>
    </form>
</x-guest-layout>
