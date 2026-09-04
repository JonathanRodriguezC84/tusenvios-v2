<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#022a8c">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Tus Envios">

        <title>{{ config('app.name', 'Tus Envios') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Cargar la misma fuente Inter de la página de inicio -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260521v15">
        <link rel="manifest" href="/site-20260521v15.webmanifest">
    </head>
    <body class="font-sans text-gray-800 antialiased relative min-h-screen" style="font-family: 'Inter', sans-serif; background-color: #f4f7fb;">
        <div class="min-h-screen grid grid-cols-1 md:grid-cols-2">
            
            <!-- Columna Izquierda: Formulario sobre fondo claro -->
            <div class="flex flex-col justify-center px-6 py-12 sm:px-16 md:px-12 lg:px-20 relative" style="background-color: #f4f7fb;">
                <div class="mx-auto w-full max-w-md">
                    <!-- Logo arriba del formulario de forma limpia -->
                    <div class="mb-8 text-center sm:text-left">
                        <a href="/" class="inline-flex items-center gap-3">
                            <div class="bg-white rounded-xl p-3.5 shadow-sm border border-gray-200 inline-block">
                                <img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios" class="h-10 w-auto object-contain">
                            </div>
                        </a>
                    </div>

                    <!-- Formulario Centrado -->
                    {{ $slot }}
                </div>
            </div>

            <!-- Columna Derecha: Fondo Oscuro Profesional con Frase Inspiradora (Oculta en móviles) -->
            <div class="hidden md:flex flex-col justify-between p-12 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #011f69 0%, #022a8c 50%, #00103b 100%);">
                <!-- Efectos de luces difusas en el fondo -->
                <div class="absolute -top-40 -left-40 h-[600px] w-[600px] rounded-full opacity-40 blur-[120px] pointer-events-none" style="background: radial-gradient(circle, rgba(31,115,255,0.15) 0%, transparent 70%);"></div>
                <div class="absolute -bottom-40 -right-40 h-[500px] w-[500px] rounded-full opacity-35 blur-[100px] pointer-events-none" style="background: radial-gradient(circle, rgba(255,122,0,0.08) 0%, transparent 70%);"></div>

                <!-- Espaciador arriba para mantener el balance del layout -->
                <div></div>

                <!-- Frase Inspiradora Centrada y Grande -->
                <div class="flex-1 flex flex-col justify-center items-center text-center max-w-xl mx-auto relative z-10 py-12">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-300 block mb-6">Emprendimiento & Logística</span>
                    
                    <!-- Icono de comillas grandes centrado -->
                    <span class="text-8xl font-serif text-blue-400/30 block leading-none select-none mb-3">“</span>
                    
                    <p class="text-3xl sm:text-4xl font-extrabold leading-normal text-white tracking-tight italic" style="font-family: 'Inter', sans-serif;">
                        Detrás de cada paquete hay un cliente feliz y un emprendedor que cumple sus sueños. Hagamos cada entrega memorable.
                    </p>
                    
                    <div class="mt-8 flex items-center justify-center gap-3">
                        <div class="h-px w-8 bg-blue-400/50"></div>
                        <span class="text-sm font-semibold text-blue-300">Equipo Tus Envíos</span>
                        <div class="h-px w-8 bg-blue-400/50"></div>
                    </div>
                </div>

                <!-- Pie de página derecho -->
                <div class="relative z-10 text-center text-xs text-gray-400 font-semibold w-full">
                    © 2026 Tus Envíos. Todos los derechos reservados.
                </div>
            </div>

        </div>
    </body>
</html>
