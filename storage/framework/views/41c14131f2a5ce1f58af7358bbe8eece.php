<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="theme-color" content="#022a8c">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Tus Envios">

        <title><?php echo e(config('app.name', 'Tus Envios')); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
            <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260521v15">
        <link rel="manifest" href="/site-20260521v15.webmanifest">
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-white px-4 py-6 sm:py-8">
            <div class="mx-auto grid min-h-[calc(100vh-3rem)] max-w-6xl items-center gap-8 lg:grid-cols-[1fr_440px]">
                <section class="hidden lg:block">
                    <a href="/" class="inline-flex items-center gap-3">
                        <img src="<?php echo e(asset('images/logotusenvios.png')); ?>" alt="Tus Envios" class="h-20 w-auto max-w-[300px] object-contain">
                    </a>

                    <h1 class="mt-8 max-w-xl text-4xl font-black leading-tight text-gray-950">
                        Empieza a imprimir etiquetas con tu marca.
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-7 text-gray-600">
                        Crea guias ilimitadas, guarda productos frecuentes y organiza tus envios sin inventario.
                    </p>

                    <div class="mt-8 grid max-w-md gap-3">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="font-black text-gray-950">Plan Emprende</p>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Etiquetas y guias ilimitadas para negocios pequenos.</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="font-black text-gray-950">Sin inventario</p>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Solo productos frecuentes para llenar guias mas rapido.</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="font-black text-gray-950">Listo para celular</p>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Crea y consulta guias desde el navegador del telefono.</p>
                        </div>
                    </div>
                </section>

                <section class="w-full rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <a href="/" class="flex items-center gap-3">
                            <img src="/images/logotusenvios-square.png?v=20260521-square" alt="Tus Envios" class="h-11 w-11 rounded-md object-contain">
                            <span>
                                <span class="block text-sm font-black leading-none text-gray-950">Tus Envios</span>
                                <span class="block text-xs font-semibold text-gray-500">Acceso seguro</span>
                            </span>
                        </a>
                    </div>

                    <?php echo e($slot); ?>

                </section>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/layouts/guest.blade.php ENDPATH**/ ?>