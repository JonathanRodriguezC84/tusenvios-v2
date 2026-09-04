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

    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-10 shadow-xl">
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Acceso al panel</p>
            <h2 class="mt-1.5 text-2xl font-black leading-tight text-gray-900">Ingresar a tu cuenta</h2>
            <p class="mt-2 text-sm leading-relaxed text-gray-500">Entra para crear guías, imprimir etiquetas y revisar tus envíos de forma rápida.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="grid gap-4">
                <label class="grid gap-1.5 text-sm font-semibold text-gray-700">
                    Correo electrónico
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="correo@negocio.com" data-uppercase="false" class="mt-1 w-full rounded-lg border-gray-200 bg-gray-50 py-3 px-4 text-base shadow-sm placeholder-gray-400 transition-all duration-200 focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </label>

                <label class="grid gap-1.5 text-sm font-semibold text-gray-700">
                    Contraseña
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="mt-1 w-full rounded-lg border-gray-200 bg-gray-50 py-3 px-4 text-base shadow-sm placeholder-slate-400 transition-all duration-200 focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </label>

                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none py-1">
                    <input id="remember_me" type="checkbox" class="login-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                    <span class="ms-2.5 text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">Recordarme en este equipo</span>
                </label>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                @if (Route::has('password.request'))
                    <a class="text-sm font-bold text-gray-500 hover:text-gray-800 hover:underline transition-colors focus:outline-none" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif

                <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-bold text-white shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" style="background: linear-gradient(135deg, #022a8c 0%, #011f69 100%); color: #ffffff;">
                    Entrar
                </button>
            </div>

            <div class="mt-8 grid gap-2.5 border-t border-gray-100 pt-5 text-center sm:text-left">
                <a href="{{ route('register') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                    ¿No tienes cuenta? Regístrate gratis
                </a>
                <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-400 hover:text-gray-600 hover:underline transition-colors">
                    Volver a la página de inicio
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
