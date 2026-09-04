<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#022a8c">
        <script>(function(){var t=localStorage.getItem('theme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.classList.add('dark')}})();</script>
        <title>@yield('title', 'Admin') - Tus Envios</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">

        <style>
            :root {
                --te-button-color: #022a8c;
                --te-button-hover: #011f68;
                --te-button-soft: #eef3ff;
                --te-button-soft-text: #022a8c;
                --te-button-text: #ffffff;
            }
            [class~="bg-blue-700"], [class~="bg-blue-800"] { background-color: var(--te-button-color) !important; }
            [class~="bg-blue-700"][class~="text-white"], [class~="bg-blue-800"][class~="text-white"] { color: var(--te-button-text) !important; }
            [class~="hover:bg-blue-800"]:hover, [class~="hover:bg-blue-700"]:hover, [class~="hover:bg-blue-500"]:hover { background-color: var(--te-button-hover) !important; }
            [class~="text-blue-700"], [class~="text-blue-800"] { color: var(--te-button-soft-text) !important; }
            [class~="border-blue-600"], [class~="border-blue-700"], [class~="focus:border-blue-700"]:focus, [class~="focus:ring-blue-700"]:focus { border-color: var(--te-button-color) !important; }
            [class~="bg-blue-50"], [class~="hover:bg-blue-50"]:hover { background-color: var(--te-button-soft) !important; }
            [class~="bg-blue-100"] { background-color: color-mix(in srgb, var(--te-button-color) 12%, white) !important; }
        </style>

        <style>
            @media (min-width: 1024px) {
                html, body { height: 100dvh; overflow: hidden; }
                .app-content-frame { padding-left: 12rem !important; height: 100dvh; display: flex; flex-direction: column; }
                .app-content-frame > header > div { max-width: none; padding: 0.75rem 1rem; }
                .app-content-frame main { flex: 1; min-height: 0; overflow-y: auto; }
                .app-content-frame main > .admin-frame { padding-left: 1rem; padding-right: 1rem; }
            }
        </style>

        <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260521v15">
        <link rel="manifest" href="/site-20260521v15.webmanifest">
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 text-gray-950 dark:bg-gray-900 dark:text-gray-100">
            @include('layouts.navigation')

            <div class="app-content-frame pb-20 pt-16 lg:pb-0 lg:pl-52 lg:pt-0">
                <header class="border-b border-gray-200 bg-white shadow-sm">
                    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 lg:py-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                @hasSection('eyebrow')
                                    <p class="text-xs font-black uppercase tracking-wider text-blue-700">@yield('eyebrow')</p>
                                @endif
                                <h2 class="text-xl font-semibold leading-tight text-gray-900">@yield('page-title', 'Panel admin')</h2>
                                @hasSection('page-description')
                                    <p class="mt-1 max-w-2xl text-sm text-gray-500">@yield('page-description')</p>
                                @endif
                            </div>
                            @hasSection('page-actions')
                                <div class="flex flex-wrap gap-2">
                                    @yield('page-actions')
                                </div>
                            @endif
                        </div>
                    </div>
                </header>

                <main>
                    <div class="admin-frame p-3 lg:p-5">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>

        <style>
            .admin-card { border: 1px solid #e5e7eb; background: #fff; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
            .te-kpi-hover { transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease; }
            .te-kpi-hover:hover { transform: translateY(-2px); }
            .admin-card.p-5 { padding: 1.25rem; }
            .admin-card.p-4 { padding: 1rem; }
            .admin-btn { display: inline-flex; align-items: center; justify-content: center; background: #022a8c; color: #fff; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 700; padding: 0.5rem 1rem; }
            .admin-btn:hover { background: #011f68; }
            .admin-outline-link { display: inline-flex; align-items: center; gap: 0.4rem; border: 1px solid #d1d5db; border-radius: 0.5rem; background: #fff; color: #334155; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; transition: background 0.15s; }
            .admin-outline-link:hover { background: #f9fafb; color: #0f172a; }
            .admin-table th { background: #f9fafb; color: #64748b; font-size: 0.75rem; font-weight: 700; padding: 0.5rem 0.85rem; text-align: left; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; }
            .admin-table td { border-top: 1px solid #e5e7eb; padding: 0.6rem 0.85rem; vertical-align: top; }
            .admin-frame input[type="text"],
            .admin-frame input[type="search"],
            .admin-frame input[type="email"],
            .admin-frame input[type="number"],
            .admin-frame input[type="date"],
            .admin-frame input[type="password"],
            .admin-frame select,
            .admin-frame textarea { border-radius: 0.5rem; }

            .dark .admin-card { background: #1e293b; border-color: #334155; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); }
            .dark .admin-card .text-gray-500 { color: #94a3b8 !important; }
            .dark .admin-card .text-gray-600 { color: #cbd5e1 !important; }
            .dark .admin-card .text-gray-700 { color: #cbd5e1 !important; }
            .dark .admin-card .text-gray-900 { color: #e2e8f0 !important; }
            .dark .admin-card .text-gray-950 { color: #f1f5f9 !important; }
            .dark .admin-table th { background: #0f172a; color: #94a3b8; border-color: #334155; }
            .dark .admin-table td { border-color: #334155; }
            .dark .admin-btn { background: #1d4ed8; }
            .dark .admin-btn:hover { background: #1e40af; }
            .dark .admin-outline-link { background: #1e293b; border-color: #475569; color: #cbd5e1; }
            .dark .admin-outline-link:hover { background: #334155; color: #e2e8f0; }
            .dark .admin-frame input, .dark .admin-frame select, .dark .admin-frame textarea { background: #1e293b; border-color: #475569; color: #e2e8f0; }
            .dark .admin-frame table { border-color: #334155; }
        </style>

        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }
        </script>
        @stack('scripts')
    </body>
</html>