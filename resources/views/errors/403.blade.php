<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Acceso no permitido - Tus Envios</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-950">
        <main class="mx-auto flex min-h-screen max-w-xl items-center px-4 py-10">
            <section class="w-full rounded-lg border border-gray-200 bg-white p-6 text-center shadow-sm">
                <img src="/pwa-icon.svg" alt="Tus Envios" class="mx-auto h-16 w-auto">
                <p class="mt-6 text-xs font-semibold uppercase text-blue-700">Acceso restringido</p>
                <h1 class="mt-2 text-2xl font-bold">No tienes permiso para ver esta seccion</h1>
                <p class="mt-3 text-sm text-gray-600">Si necesitas acceso, solicita al administrador revisar tu rol o estado de usuario.</p>
                <a href="{{ route('dashboard') }}" class="mt-6 inline-flex rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Ir al dashboard
                </a>
            </section>
        </main>
    </body>
</html>

