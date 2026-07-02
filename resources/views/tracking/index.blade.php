<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#022a8c">
    <title>Rastrear guia | Tus Envios</title>
    <meta name="description" content="Rastrea tu guia de envio en segundos. Consulta el estado de tu paquete con Tus Envios.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260521v15">
    <link rel="manifest" href="/site-20260521v15.webmanifest">
    <style>
        .te-hero-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(2,42,140,0.05) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-950">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="/images/logotusenvios-square.png?v=20260521-square" alt="Tus Envios" class="h-11 w-11 rounded-md object-contain">
                <span>
                    <span class="block text-base font-black leading-none">Tus Envios</span>
                    <span class="block text-xs font-semibold text-gray-500">tusenvios.com.co</span>
                </span>
            </a>
            <a href="{{ route('login') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Ingresar
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
        <div class="te-hero-pattern rounded-2xl border border-gray-200 bg-white p-6 sm:p-10 shadow-sm">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50">
                    <svg class="h-8 w-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                </div>
                <h1 class="mt-4 text-2xl font-black text-gray-950 sm:text-3xl">Rastrea tu guia</h1>
                <p class="mt-2 text-gray-600">Ingresa el numero de guia y consulta el estado de tu envio al instante.</p>
            </div>

            <form method="POST" action="{{ route('tracking.search') }}" class="mt-8">
                @csrf
                <div class="mx-auto max-w-lg">
                    <label for="tracking-code" class="block text-sm font-semibold text-gray-700">Numero de guia</label>
                    <div class="mt-1.5 flex gap-3">
                        <input
                            id="tracking-code"
                            name="code"
                            value="{{ old('code') }}"
                            autofocus
                            placeholder="Ej: DA-2026-000001"
                            class="flex-1 rounded-lg border-gray-300 text-lg font-semibold shadow-sm focus:border-blue-700 focus:ring-blue-700"
                        >
                        <button class="rounded-lg bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-800">
                            Buscar
                        </button>
                    </div>
                    @error('code')
                        <p class="mt-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{{ $message }}</p>
                    @enderror
                </div>
            </form>

            <div class="mt-10 mx-auto max-w-lg">
                <p class="text-xs font-semibold uppercase text-gray-400">Estados de un envio</p>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div class="rounded-lg bg-gray-50 p-3 text-center">
                        <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-gray-200">
                            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-gray-700">Creada</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-3 text-center">
                        <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-amber-100">
                            <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 10l8 4m0-10v10" /></svg>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-amber-800">En bodega</p>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-3 text-center">
                        <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                            <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m4 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-blue-800">En camino</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-3 text-center">
                        <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-emerald-800">Entregado</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-gray-200 bg-white mt-8">
        <div class="mx-auto max-w-5xl px-4 py-4 sm:px-6 text-center text-xs text-gray-500">
            Powered by <a href="https://tusenvios.com.co" class="font-semibold text-blue-700 hover:underline">Tus Envios</a>
        </div>
    </footer>
</body>
</html>