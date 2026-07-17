<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>(function(){var t=localStorage.getItem('theme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.classList.add('dark')}})();</script>
        <title>@yield('title', 'Admin') - Tus Envios</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .admin-shell { min-height: 100vh; background: #f3f4f6; color: #0f172a; }
            .admin-sidebar { background: #ffffff; color: #334155; border-right: 1px solid #e5e7eb; transition: transform 0.25s ease; }
            .admin-sidebar-mobile-toggle { display: none; }
            .admin-sidebar-backdrop { display: none; }
            @media (max-width: 1023px) {
                .admin-sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; width: 16rem; transform: translateX(-100%); overflow-y: auto; }
                .admin-sidebar.open { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,0.12); }
                .admin-sidebar-backdrop { display: block; position: fixed; inset: 0; z-index: 49; background: rgba(0,0,0,0.3); }
                .admin-sidebar-backdrop.hidden { display: none; }
                .admin-sidebar-mobile-toggle { display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; border: 1px solid #d1d5db; background: #fff; color: #334155; cursor: pointer; position: fixed; top: 0.75rem; left: 0.75rem; z-index: 40; }
                .admin-content { padding-top: 3.75rem !important; }
            }
            .admin-logo-wrap { min-height: 5rem; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #e5e7eb; padding: 0 1.2rem; overflow: visible; }
            .admin-logo { display: block !important; width: 100% !important; max-width: 7rem !important; height: auto !important; max-height: 2.5rem !important; object-fit: contain !important; }
            .admin-sidebar-body { padding: 0.75rem 0.6rem; }
            .admin-link { display: flex; align-items: center; gap: 0.5rem; border-radius: 0.375rem; padding: 0.5rem 0.6rem; font-size: 0.75rem; font-weight: 600; color: #475569; line-height: 1.3; }
            .admin-link svg { width: 1.25rem; height: 1.25rem; flex: 0 0 1.25rem; stroke-width: 2; }
            .admin-link:hover { background: #f9fafb; color: #0f172a; }
            .admin-link.active { background: #eff6ff; color: #1d4ed8; }
            .admin-card { border: 1px solid #e5e7eb; background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,.05); }
            .admin-card.p-5 { padding: 1.25rem; }
            .admin-card.p-4 { padding: 1rem; }
            .admin-btn { display: inline-flex; align-items: center; justify-content: center; background: #022a8c; color: #fff; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 700; padding: 0.5rem 0.85rem; min-height: 2.5rem; }
            .admin-btn:hover { background: #011f68; }
            .admin-table th { background: #f8fafc; color: #64748b; font-size: 0.7rem; font-weight: 700; padding: 0.5rem 0.85rem; text-align: left; text-transform: uppercase; letter-spacing: 0.05em; }
            .admin-table td { border-top: 1px solid #e5e7eb; padding: 0.6rem 0.85rem; vertical-align: top; }
            .admin-sidebar-divider { border-color: #e5e7eb; }
            .admin-outline-link { display: inline-flex; align-items: center; gap: 0.4rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background: #fff; color: #334155; padding: 0.4rem 0.85rem; font-size: 0.75rem; font-weight: 700; min-height: 2.25rem; text-decoration: none; transition: background 0.15s; }
            .admin-outline-link:hover { background: #f9fafb; color: #0f172a; }
            .admin-title-icon { display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; background: #eff6ff; color: #1d4ed8; flex: 0 0 2.25rem; }
            .admin-title-icon svg { width: 1.15rem; height: 1.15rem; }
            @media (min-width: 1024px) {
                html, body { height: 100dvh; overflow: hidden; }
                .admin-shell { display: grid; grid-template-columns: 12rem minmax(0,1fr); height: 100dvh; }
                .admin-sidebar { min-height: 100vh; position: sticky; top: 0; overflow-y: auto; }
                main.min-w-0 { display: flex; flex-direction: column; height: 100dvh; min-height: 0; }
                main.min-w-0 > header { flex-shrink: 0; }
                .admin-content { padding: 0.75rem; flex: 1; min-height: 0; overflow-y: auto; }
            }
            .dark .admin-shell { background: #0f172a; color: #e2e8f0; }
            .dark .admin-sidebar { background: #1e293b; color: #cbd5e1; border-color: #334155; }
            .dark .admin-logo-wrap { border-color: #334155; }
            .dark .admin-link { color: #94a3b8; }
            .dark .admin-link:hover { background: #1e293b; color: #e2e8f0; }
            .dark .admin-link.active { background: #1e3a5f; color: #60a5fa; }
            .dark .admin-card { background: #1e293b; border-color: #334155; box-shadow: 0 1px 2px rgba(0,0,0,.2); }
            .dark .admin-table th { background: #0f172a; color: #94a3b8; }
            .dark .admin-table td { border-color: #334155; }
            .dark .admin-btn { background: #1d4ed8; }
            .dark .admin-btn:hover { background: #1e40af; }
            .dark .admin-outline-link { background: #1e293b; border-color: #475569; color: #cbd5e1; }
            .dark .admin-outline-link:hover { background: #334155; color: #e2e8f0; }
            .dark .admin-title-icon { background: #1e3a5f; color: #60a5fa; }
            .dark .admin-sidebar-divider { border-color: #334155; }
            .dark .admin-sidebar-divider.text-gray-500,
            .dark .admin-sidebar-divider .text-gray-500 { color: #94a3b8; }
            .dark .admin-card .text-gray-500 { color: #94a3b8 !important; }
            .dark .admin-card .text-gray-600 { color: #cbd5e1 !important; }
            .dark .admin-card .text-gray-700 { color: #cbd5e1 !important; }
            .dark .admin-card .text-gray-900 { color: #e2e8f0 !important; }
            .dark .admin-card .text-gray-950 { color: #f1f5f9 !important; }
            .dark .admin-card .bg-gray-50 { background: #0f172a !important; }
            .dark .admin-card .bg-gray-100 { background: #0f172a !important; }
            .dark .admin-card .border-gray-100 { border-color: #334155 !important; }
            .dark .admin-card .border-gray-200 { border-color: #334155 !important; }
            .dark input, .dark select, .dark textarea { background: #1e293b; border-color: #475569; color: #e2e8f0; }
            .dark input::placeholder { color: #64748b; }
            .dark table { border-color: #334155; }
            .dark .divide-gray-200 > * { border-color: #334155 !important; }
            .dark .divide-gray-100 > * { border-color: #1e293b !important; }
            .dark .hover\:bg-gray-50:hover { background: #1e293b !important; }
            .dark .border-t { border-color: #334155; }
            .dark .border-b { border-color: #334155; }
        </style>
            <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260521v15">
        <link rel="manifest" href="/site-20260521v15.webmanifest">
    </head>
    <body class="font-sans antialiased">
        @php
            $adminSections = [
                'Administracion' => [
                    [
                        'label' => 'Resumen',
                        'route' => 'admin.dashboard',
                        'active' => 'admin.dashboard',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-4H4v4Z" />',
                    ],
                    [
                        'label' => 'Clientes',
                        'route' => 'admin.clients',
                        'active' => 'admin.clients',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 20a8 8 0 0 1 16 0" />',
                    ],
                    [
                        'label' => 'Suscripciones',
                        'route' => 'admin.subscriptions',
                        'active' => 'admin.subscriptions',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 11h16M7 15h4M6 19h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />',
                    ],
                    [
                        'label' => 'Usuarios',
                        'route' => 'admin.users',
                        'active' => 'admin.users',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 12 21c-2.173 0-4.2-.598-5.93-1.637m0 0a9.37 9.37 0 0 0 2.625-.372M6 19.372v-.008m6.001-1.437a4.125 4.125 0 0 0-5.497-5.498 4.125 4.125 0 0 0 0 5.498m0 0v.003m0 0c.587.176 1.2.272 1.832.272M12 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />',
                    ],
                    [
                        'label' => 'Actividad',
                        'route' => 'admin.activity',
                        'active' => 'admin.activity',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16V9m4 7V6m4 10v-4" />',
                    ],
                ],
                'Configuracion' => [
                    [
                        'label' => 'Planes',
                        'route' => 'admin.plans',
                        'active' => 'admin.plans',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />',
                    ],
                    [
                        'label' => 'Transportadoras',
                        'route' => 'admin.carriers',
                        'active' => 'admin.carriers',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />',
                    ],
                    [
                        'label' => 'API Docs',
                        'route' => 'admin.api-docs',
                        'active' => 'admin.api-docs',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
                    ],
                    [
                        'label' => 'WhatsApp',
                        'route' => 'admin.whatsapp',
                        'active' => 'admin.whatsapp',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />',
                    ],
                    [
                        'label' => 'Auditoria',
                        'route' => 'audit-logs.index',
                        'active' => 'audit-logs.*',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
                    ],
                    [
                        'label' => 'Sistema',
                        'route' => 'admin.settings',
                        'active' => 'admin.settings',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
                    ],
                ],
            ];

            $allLinks = collect($adminSections)->flatten(1);
            $activeAdminLink = $allLinks->first(fn ($link) => request()->routeIs($link['active'])) ?? $allLinks->first();
        @endphp

        <div class="admin-shell">
            <button class="admin-sidebar-mobile-toggle" onclick="document.getElementById('admin-sidebar').classList.toggle('open'); document.getElementById('admin-backdrop').classList.toggle('hidden')" aria-label="Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div id="admin-backdrop" class="admin-sidebar-backdrop hidden" onclick="document.getElementById('admin-sidebar').classList.remove('open'); this.classList.add('hidden')"></div>
            <aside id="admin-sidebar" class="admin-sidebar">
                <a href="{{ route('admin.dashboard') }}" class="admin-logo-wrap">
                    <img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios" class="admin-logo">
                </a>

                <div class="admin-sidebar-body">
                    <div class="mb-3 px-2">
                        <p class="text-xs font-black uppercase tracking-wider text-blue-700">Panel interno</p>
                    </div>

                    @foreach ($adminSections as $sectionName => $links)
                        <p class="mt-4 mb-1 px-2 text-xs font-black uppercase tracking-wider text-gray-400">{{ $sectionName }}</p>
                        <nav class="grid gap-1 mb-1">
                            @foreach ($links as $link)
                                <a href="{{ route($link['realRoute'] ?? $link['route']) }}" class="admin-link {{ request()->routeIs($link['active']) ? 'active' : '' }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $link['icon'] !!}</svg>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    @endforeach

                    <div class="admin-sidebar-divider mt-6 border-t pt-4">
                        <p class="text-xs font-semibold text-gray-950">{{ Auth::user()->name }}</p>
                        <p class="mt-0.5 truncate text-3xs text-gray-500">{{ Auth::user()->email }}</p>
                        <button onclick="const d=document.documentElement;d.classList.toggle('dark');localStorage.setItem('theme',d.classList.contains('dark')?'dark':'light')" class="admin-outline-link mt-2 w-full justify-center text-3xs">Modo oscuro</button>
                        <form method="POST" action="{{ route('logout') }}" class="mt-1.5">
                            @csrf
                            <button class="admin-outline-link w-full justify-center text-3xs border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700">Cerrar sesion</button>
                        </form>
                    </div>
                </div>
            </aside>

            <main class="min-w-0">
                <header class="border-b border-gray-200 bg-white px-4 py-3 sm:px-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="admin-title-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $activeAdminLink['icon'] !!}</svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase text-blue-800">@yield('eyebrow', 'Admin')</p>
                                <h2 class="truncate text-xl font-semibold leading-tight text-gray-900">@yield('page-title', 'Panel admin')</h2>
                                <p class="mt-0.5 text-sm text-gray-500">@yield('page-description', 'Control interno de la plataforma.')</p>
                            </div>
                        </div>
                        @yield('page-actions')
                    </div>
                </header>

                <div class="admin-content px-4 py-4 sm:px-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>