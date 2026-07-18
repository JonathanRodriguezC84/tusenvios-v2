<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <?php
            $brandColor = '#022a8c';
            $brandUser = auth()->user();
            if ($brandUser) {
                $brandOwner = $brandUser->affiliatedCompany ?: $brandUser->tenant;
                $brandData = $brandOwner?->brandData();
                $brandCandidate = $brandData['color'] ?? null;
                if (is_string($brandCandidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $brandCandidate)) {
                    $brandColor = strtolower($brandCandidate);
                }
            }
            $brandR = hexdec(substr($brandColor, 1, 2));
            $brandG = hexdec(substr($brandColor, 3, 2));
            $brandB = hexdec(substr($brandColor, 5, 2));
            $brandTint = "rgba({$brandR}, {$brandG}, {$brandB}, 0.10)";
            $brandText = (($brandR * 299 + $brandG * 587 + $brandB * 114) / 1000) > 165 ? '#111827' : '#ffffff';
        ?>
        <meta name="theme-color" content="<?php echo e($brandColor); ?>">
        <meta name="msapplication-TileColor" content="<?php echo e($brandColor); ?>">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Tus Envios">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        
        <style>
            @media (min-width: 1024px) {
                .app-content-frame { padding-left: 12rem !important; }
                .app-content-frame > header > div { max-width: none; padding: 0.75rem 1rem; }
                .app-content-frame main > div > div.mx-auto { max-width: none; padding-left: 1rem; padding-right: 1rem; }
                .app-content-frame main [class~="py-8"], .app-content-frame main [class~="py-6"] { padding-top: 1rem; padding-bottom: 1rem; }
                .app-content-frame [class~="p-5"] { padding: 1rem; }
            }
        </style>

        
        <style id="tus-envios-button-color">
            :root {
                --te-button-color: <?php echo e($brandColor); ?>;
                --te-button-hover: <?php echo e($brandColor); ?>;
                --te-button-soft: #eef3ff;
                --te-button-soft-text: <?php echo e($brandColor); ?>;
                --te-button-text: <?php echo e($brandText); ?>;
            }
            [class~="bg-blue-700"], [class~="bg-blue-800"] { background-color: var(--te-button-color) !important; }
            [class~="bg-blue-700"][class~="text-white"], [class~="bg-blue-800"][class~="text-white"] { color: var(--te-button-text) !important; }
            [class~="hover:bg-blue-800"]:hover, [class~="hover:bg-blue-700"]:hover, [class~="hover:bg-blue-500"]:hover { background-color: var(--te-button-hover) !important; }
            [class~="text-blue-700"], [class~="text-blue-800"] { color: var(--te-button-soft-text) !important; }
            [class~="border-blue-600"], [class~="border-blue-700"], [class~="focus:border-blue-700"]:focus, [class~="focus:ring-blue-700"]:focus { border-color: var(--te-button-color) !important; }
            [class~="bg-blue-50"], [class~="hover:bg-blue-50"]:hover { background-color: var(--te-button-soft) !important; }
        </style>

        <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260521v15">
        <link rel="manifest" href="/site-20260521v15.webmanifest">

        
        <script id="te-refresh-sw-v17">
            window.addEventListener('load', () => {
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistrations().then((r) => r.forEach((reg) => reg.update())).catch(() => {});
                }
            });
            window.addEventListener('pageshow', () => {
                document.querySelectorAll('meta[name="theme-color"], meta[name="msapplication-TileColor"]').forEach((meta) => {
                    meta.setAttribute('content', <?php echo json_encode($brandColor, 15, 512) ?>);
                });
            });
        </script>

        
        <style id="te-mobile-menu-color-v18">
            @media (max-width: 1023px) {
                html { --te-active-tint: color-mix(in srgb, <?php echo e($brandColor); ?> 12%, white); }
                .te-mobile-brand-active { background-color: var(--te-active-tint) !important; color: <?php echo e($brandColor); ?> !important; }
                .te-mobile-brand-featured { background-color: <?php echo e($brandColor); ?> !important; color: #fff !important; }
            }
        </style>

        
        <style id="te-app-fullheight-v01">
            @media (min-width: 1024px) {
                html, body { height: 100dvh; overflow: hidden; }
                .app-content-frame { height: 100dvh; display: flex; flex-direction: column; }
                .app-content-frame main { flex: 1; min-height: 0; overflow-y: auto; }
                .app-content-frame > header { flex-shrink: 0; }
            }
        </style>

        
        <script id="te-mobile-menu-color-v18">
            document.addEventListener('DOMContentLoaded', () => {
                const color = <?php echo json_encode($brandColor, 15, 512) ?>;

                document.documentElement.style.setProperty('--tenant-brand-color', color);

                const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
                const isActiveHref = (href) => {
                    try {
                        const url = new URL(href, window.location.origin);
                        const path = url.pathname.replace(/\/+$/, '') || '/';
                        if (path === currentPath) return true;
                        if (path === '/shipments' && currentPath.startsWith('/shipments') && !currentPath.includes('/create')) return true;
                        if (path === '/dashboard' && currentPath === '/') return true;
                    } catch (e) { return false; }
                    return false;
                };

                const applyMobileMenuColor = () => {
                    document.querySelectorAll('div.fixed.inset-x-0.bottom-0, aside.fixed.inset-y-0.left-0').forEach((area) => {
                        area.querySelectorAll('a[href]').forEach((link) => {
                            const label = (link.textContent || '').trim().toLowerCase();
                            const href = link.getAttribute('href') || '';
                            link.classList.remove('te-mobile-brand-active', 'te-mobile-brand-featured');
                            if (label === 'crear' || label === 'crear guia' || href.includes('/shipments/create')) {
                                link.classList.add('te-mobile-brand-featured');
                            } else if (isActiveHref(href) || /bg-(blue|red|orange|green|pink|purple|emerald|rose|sky|indigo)-50/.test(link.className)) {
                                link.classList.add('te-mobile-brand-active');
                            }
                        });
                    });
                };

                applyMobileMenuColor();
                document.addEventListener('click', () => window.setTimeout(applyMobileMenuColor, 80), true);
                window.addEventListener('pageshow', applyMobileMenuColor);
            });
        </script>

        
        <style id="te-force-mobile-menu-v20">
            :root { --tenant-brand-color: <?php echo e($brandColor); ?>; --tenant-brand-tint: <?php echo e($brandTint); ?>; }
            @media (max-width: 1023px) {
                nav div.fixed.inset-x-0.bottom-0 a[href*="/shipments/create"],
                nav div[class*="bottom-0"] a[href*="/shipments/create"],
                nav a[href*="/shipments/create"] { background-color: <?php echo e($brandColor); ?> !important; border-color: <?php echo e($brandColor); ?> !important; color: #ffffff !important; }
                nav div.fixed.inset-x-0.bottom-0 a[href*="/shipments/create"] svg,
                nav div[class*="bottom-0"] a[href*="/shipments/create"] svg { color: #ffffff !important; stroke: currentColor !important; }
                nav div.fixed.inset-x-0.bottom-0 a[href]:not([href*="/shipments/create"]).te-current-mobile-v20,
                nav div[class*="bottom-0"] a[href]:not([href*="/shipments/create"]).te-current-mobile-v20,
                nav aside a[href].te-current-mobile-v20 { background-color: <?php echo e($brandTint); ?> !important; color: <?php echo e($brandColor); ?> !important; }
                nav div.fixed.inset-x-0.bottom-0 a[href]:not([href*="/shipments/create"]).te-current-mobile-v20 svg,
                nav div[class*="bottom-0"] a[href]:not([href*="/shipments/create"]).te-current-mobile-v20 svg,
                nav aside a[href].te-current-mobile-v20 svg { color: <?php echo e($brandColor); ?> !important; stroke: currentColor !important; }
            }
        </style>
        <script id="te-force-mobile-menu-v20">
            (() => {
                const color = <?php echo json_encode($brandColor, 15, 512) ?>;
                const tint = <?php echo json_encode($brandTint, 15, 512) ?>;
                const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

                const isCurrent = (href) => {
                    try {
                        const path = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
                        if (path === currentPath) return true;
                        if (path === '/shipments' && currentPath === '/shipments') return true;
                        if (path === '/dashboard' && (currentPath === '/' || currentPath === '/dashboard')) return true;
                    } catch (e) { return false; }
                    return false;
                };

                const paint = () => {
                    document.documentElement.style.setProperty('--tenant-brand-color', color);
                    document.documentElement.style.setProperty('--tenant-brand-tint', tint);
                    document.querySelectorAll('nav a[href]').forEach((link) => {
                        const inMobileMenu = Boolean(link.closest('div.fixed.inset-x-0.bottom-0, div[class*="bottom-0"], aside'));
                        if (!inMobileMenu) return;
                        link.classList.toggle('te-current-mobile-v20', isCurrent(link.getAttribute('href') || ''));
                        if ((link.getAttribute('href') || '').includes('/shipments/create')) {
                            link.style.setProperty('background-color', color, 'important');
                            link.style.setProperty('border-color', color, 'important');
                            link.style.setProperty('color', '#ffffff', 'important');
                        }
                    });
                };
                paint();
                document.addEventListener('DOMContentLoaded', paint);
                window.addEventListener('pageshow', paint);
                window.setTimeout(paint, 250);
                window.setTimeout(paint, 900);
            })();
        </script>

        
        <script>
            (function(){var t=localStorage.getItem('theme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.classList.add('dark')}})();
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 text-gray-950 dark:bg-gray-900 dark:text-gray-100">
            <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="app-content-frame pb-20 pt-16 lg:pb-0 lg:pl-52 lg:pt-0">
                <?php if(isset($header)): ?>
                    <header class="border-b border-gray-200 bg-white shadow-sm">
                        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 lg:py-6">
                            <?php echo e($header); ?>

                        </div>
                    </header>
                <?php endif; ?>

                <main>
                    <?php echo e($slot); ?>

                </main>
            </div>
        </div>

        
        <style id="te-bottom-mobile-final-v22">
            @media (max-width: 1023px) {
                body nav [class*="bottom-0"] a[href*="/shipments/create"],
                body nav .fixed.bottom-0 a[href*="/shipments/create"],
                body nav .fixed.inset-x-0.bottom-0 a[href*="/shipments/create"],
                body [class*="bottom-0"] a[href*="/shipments/create"],
                body .fixed.bottom-0 a[href*="/shipments/create"],
                body .fixed.inset-x-0.bottom-0 a[href*="/shipments/create"],
                body nav [class*="bottom-0"] a:nth-child(3),
                body .fixed.inset-x-0.bottom-0 a:nth-child(3) {
                    background: <?php echo e($brandColor); ?> !important;
                    background-color: <?php echo e($brandColor); ?> !important;
                    border-color: <?php echo e($brandColor); ?> !important;
                    color: #ffffff !important;
                }
                body nav [class*="bottom-0"] a[href*="/shipments/create"] *,
                body .fixed.inset-x-0.bottom-0 a[href*="/shipments/create"] *,
                body nav [class*="bottom-0"] a:nth-child(3) *,
                body .fixed.inset-x-0.bottom-0 a:nth-child(3) * {
                    color: #ffffff !important; stroke: currentColor !important; fill: none !important;
                }
                body nav [class*="bottom-0"] a[aria-current="page"]:not([href*="/shipments/create"]),
                body nav [class*="bottom-0"] a.is-active:not([href*="/shipments/create"]),
                body nav [class*="bottom-0"] a.active:not([href*="/shipments/create"]),
                body .fixed.inset-x-0.bottom-0 a[aria-current="page"]:not([href*="/shipments/create"]) {
                    background-color: <?php echo e($brandTint); ?> !important;
                    color: <?php echo e($brandColor); ?> !important;
                }
            }
        </style>
        <script id="te-bottom-mobile-final-v22">
            (() => {
                const color = <?php echo json_encode($brandColor, 15, 512) ?>;
                const tint = <?php echo json_encode($brandTint, 15, 512) ?>;

                const paintBottomMenu = () => {
                    document.documentElement.style.setProperty('--tenant-brand-color', color);
                    document.documentElement.style.setProperty('--tenant-brand-tint', tint);

                    document.querySelectorAll('[class*="bottom-0"], .fixed.inset-x-0.bottom-0, .fixed.bottom-0').forEach((bar) => {
                        if (bar.querySelectorAll('a').length < 3) return;
                        Array.from(bar.querySelectorAll('a')).forEach((link, index) => {
                            const href = link.getAttribute('href') || '';
                            const text = (link.textContent || '').trim().toLowerCase();
                            const isCreate = href.includes('/shipments/create') || text === 'crear' || text.includes('crear');
                            if (isCreate || index === 2) {
                                link.style.setProperty('background', color, 'important');
                                link.style.setProperty('background-color', color, 'important');
                                link.style.setProperty('border-color', color, 'important');
                                link.style.setProperty('color', '#ffffff', 'important');
                                link.querySelectorAll('*').forEach((child) => {
                                    child.style.setProperty('color', '#ffffff', 'important');
                                    child.style.setProperty('stroke', 'currentColor', 'important');
                                });
                            }
                        });
                    });
                };

                paintBottomMenu();
                document.addEventListener('DOMContentLoaded', paintBottomMenu);
                window.addEventListener('load', paintBottomMenu);
                window.addEventListener('pageshow', paintBottomMenu);
                window.setTimeout(paintBottomMenu, 100);
                window.setTimeout(paintBottomMenu, 500);
                window.setTimeout(paintBottomMenu, 1500);
            })();
        </script>

        
        <style id="te-real-mobile-menu-v23">
            @media (max-width: 1023px) {
                .mobile-bottom-nav .mobile-bottom-link.is-featured,
                .mobile-bottom-nav .mobile-bottom-link[data-route-create="1"],
                .mobile-bottom-grid .mobile-bottom-link.is-featured,
                .mobile-bottom-grid .mobile-bottom-link[data-route-create="1"] {
                    background: <?php echo e($brandColor); ?> !important;
                    background-color: <?php echo e($brandColor); ?> !important;
                    border-color: <?php echo e($brandColor); ?> !important;
                    color: #ffffff !important;
                }
                .mobile-bottom-nav .mobile-bottom-link.is-featured *,
                .mobile-bottom-nav .mobile-bottom-link[data-route-create="1"] *,
                .mobile-bottom-grid .mobile-bottom-link.is-featured *,
                .mobile-bottom-grid .mobile-bottom-link[data-route-create="1"] * {
                    color: #ffffff !important; stroke: currentColor !important;
                }
                .mobile-bottom-nav .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]),
                .mobile-bottom-grid .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]) {
                    background: <?php echo e($brandTint); ?> !important;
                    background-color: <?php echo e($brandTint); ?> !important;
                    color: <?php echo e($brandColor); ?> !important;
                }
                .mobile-bottom-nav .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]) *,
                .mobile-bottom-grid .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]) * {
                    color: <?php echo e($brandColor); ?> !important; stroke: currentColor !important;
                }
            }
        </style>
        <script id="te-real-mobile-menu-v23">
            (() => {
                const color = <?php echo json_encode($brandColor, 15, 512) ?>;
                const tint = <?php echo json_encode($brandTint, 15, 512) ?>;

                const paintRealMobileMenu = () => {
                    document.documentElement.style.setProperty('--tenant-brand-color', color);
                    document.documentElement.style.setProperty('--tenant-brand-tint', tint);

                    document.querySelectorAll('.mobile-bottom-link.is-featured, .mobile-bottom-link[data-route-create="1"]').forEach((link) => {
                        link.style.setProperty('background', color, 'important');
                        link.style.setProperty('background-color', color, 'important');
                        link.style.setProperty('border-color', color, 'important');
                        link.style.setProperty('color', '#ffffff', 'important');
                        link.querySelectorAll('*').forEach((child) => {
                            child.style.setProperty('color', '#ffffff', 'important');
                            child.style.setProperty('stroke', 'currentColor', 'important');
                        });
                    });

                    document.querySelectorAll('.mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"])').forEach((link) => {
                        link.style.setProperty('background', tint, 'important');
                        link.style.setProperty('background-color', tint, 'important');
                        link.style.setProperty('color', color, 'important');
                        link.querySelectorAll('*').forEach((child) => {
                            child.style.setProperty('color', color, 'important');
                            child.style.setProperty('stroke', 'currentColor', 'important');
                        });
                    });
                };

                paintRealMobileMenu();
                document.addEventListener('DOMContentLoaded', paintRealMobileMenu);
                window.addEventListener('load', paintRealMobileMenu);
                window.addEventListener('pageshow', paintRealMobileMenu);
                window.setTimeout(paintRealMobileMenu, 100);
                window.setTimeout(paintRealMobileMenu, 500);
            })();
        </script>

        
        <style id="te-mobile-logout-v24">
            @media (max-width: 1023px) {
                .te-mobile-drawer-logout-v24 {
                    border-top: 1px solid #e5e7eb;
                    background: #ffffff;
                    padding: 0.75rem 1rem calc(env(safe-area-inset-bottom) + 4.6rem);
                }
                .te-mobile-drawer-logout-v24 button {
                    display: flex; width: 100%; align-items: center; justify-content: center; gap: 0.5rem;
                    border-radius: 0.5rem; border: 1px solid #d1d5db; background: #ffffff;
                    padding: 0.7rem 1rem; color: #111827; font-weight: 800; line-height: 1;
                }
                .te-mobile-drawer-logout-v24 button:active { border-color: <?php echo e($brandColor); ?>; color: <?php echo e($brandColor); ?>; }
            }
        </style>
        <script id="te-mobile-logout-v24">
            (() => {
                const addLogoutButton = () => {
                    if (document.querySelector('.te-mobile-drawer-logout-v24')) return;

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const sidePanel = document.querySelector('aside, .mobile-menu, .mobile-sidebar, [class*="mobile"][class*="menu"]');
                    if (!sidePanel) return;

                    const userBlock = Array.from(sidePanel.querySelectorAll('div, section, footer'))
                        .reverse()
                        .find((el) => /@/.test(el.textContent || ''));

                    const wrapper = document.createElement('div');
                    wrapper.className = 'te-mobile-drawer-logout-v24';
                    wrapper.innerHTML = `
                        <form method="POST" action="/logout">
                            <input type="hidden" name="_token" value="${csrf}">
                            <button type="submit" aria-label="Salir">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H3m0 0 4-4m-4 4 4 4M9 4h9a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H9" />
                                </svg>
                                Salir
                            </button>
                        </form>
                    `;

                    if (userBlock) { userBlock.insertAdjacentElement('afterend', wrapper); }
                    else { sidePanel.appendChild(wrapper); }
                };

                addLogoutButton();
                document.addEventListener('DOMContentLoaded', addLogoutButton);
                document.addEventListener('click', () => window.setTimeout(addLogoutButton, 80), true);
                window.addEventListener('pageshow', addLogoutButton);
                window.setTimeout(addLogoutButton, 500);
            })();
        </script>

        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }
        </script>
    </body>
</html><?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/layouts/app.blade.php ENDPATH**/ ?>