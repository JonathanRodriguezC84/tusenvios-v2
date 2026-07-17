<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <?php
            $mobileThemeColor = '#022a8c';
            $mobileThemeUser = auth()->user();
            if ($mobileThemeUser) {
                $mobileThemeOwner = $mobileThemeUser->affiliatedCompany ?: $mobileThemeUser->tenant;
                $mobileThemeBrand = $mobileThemeOwner?->brandData();
                $mobileThemeCandidate = $mobileThemeBrand['color'] ?? null;
                if (is_string($mobileThemeCandidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $mobileThemeCandidate)) {
                    $mobileThemeColor = strtolower($mobileThemeCandidate);
                }
            }
        ?>
        <meta name="theme-color" content="<?php echo e($mobileThemeColor); ?>">
        <meta name="msapplication-TileColor" content="<?php echo e($mobileThemeColor); ?>">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <script id="te-mobile-theme-color-v16">
            document.addEventListener('DOMContentLoaded', () => {
                const color = <?php echo json_encode($mobileThemeColor, 15, 512) ?>;
                if (! /^#[0-9A-Fa-f]{6}$/.test(color)) {
                    return;
                }

                document.querySelectorAll('meta[name="theme-color"], meta[name="msapplication-TileColor"]').forEach((meta) => {
                    meta.setAttribute('content', color);
                });

                document.documentElement.style.setProperty('--tenant-brand-color', color);
            });
        </script><!-- te-mobile-theme-color-v16 -->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Tus Envios">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>
        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php
            $activeBrandColor = Auth::check() ? (Auth::user()->tenant?->brand_color ?: '#022a8c') : '#022a8c';

            $hex = ltrim($activeBrandColor, '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $activeBrandText = (($r * 299 + $g * 587 + $b * 114) / 1000) > 165 ? '#111827' : '#ffffff';
        ?>
    
        <style>
            @media (min-width: 1024px) {
                .app-content-frame {
                    padding-left: 12rem !important;
                }

                .app-content-frame > header > div {
                    max-width: none;
                    padding: 0.75rem 1rem;
                }

                .app-content-frame main > div > div.mx-auto {
                    max-width: none;
                    padding-left: 1rem;
                    padding-right: 1rem;
                }

                .app-content-frame main [class~="py-8"],
                .app-content-frame main [class~="py-6"] {
                    padding-top: 1rem;
                    padding-bottom: 1rem;
                }

                .app-content-frame [class~="p-5"] {
                    padding: 1rem;
                }
            }
        </style>
    
        <style id="tus-envios-button-color">
            :root {
                --te-button-color: <?php echo e($activeBrandColor ?? '#022a8c'); ?>;
                --te-button-hover: <?php echo e($activeBrandColor ?? '#022a8c'); ?>;
                --te-button-soft: #eef3ff;
                --te-button-soft-text: <?php echo e($activeBrandColor ?? '#022a8c'); ?>;
                --te-button-text: <?php echo e($activeBrandText ?? '#ffffff'); ?>;
            }

            [class~="bg-blue-700"],
            [class~="bg-blue-800"] {
                background-color: var(--te-button-color) !important;
            }

            [class~="bg-blue-700"][class~="text-white"],
            [class~="bg-blue-800"][class~="text-white"] {
                color: var(--te-button-text) !important;
            }

            [class~="hover:bg-blue-800"]:hover,
            [class~="hover:bg-blue-700"]:hover,
            [class~="hover:bg-blue-500"]:hover {
                background-color: var(--te-button-hover) !important;
            }

            [class~="text-blue-700"],
            [class~="text-blue-800"] {
                color: var(--te-button-soft-text) !important;
            }

            [class~="border-blue-600"],
            [class~="border-blue-700"],
            [class~="focus:border-blue-700"]:focus,
            [class~="focus:ring-blue-700"]:focus {
                border-color: var(--te-button-color) !important;
            }

            [class~="bg-blue-50"],
            [class~="hover:bg-blue-50"]:hover {
                background-color: var(--te-button-soft) !important;
            }
        </style>
            <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260521v15">
        <link rel="manifest" href="/site-20260521v15.webmanifest">
            <script id="te-refresh-sw-v17">
            window.addEventListener('load', () => {
                if (! ('serviceWorker' in navigator)) {
                    return;
                }

                navigator.serviceWorker.getRegistrations().then((registrations) => {
                    registrations.forEach((registration) => registration.update());
                }).catch(() => {});
            });

            window.addEventListener('pageshow', () => {
                const color = getComputedStyle(document.documentElement).getPropertyValue('--tenant-brand-color').trim();
                if (! /^#[0-9A-Fa-f]{6}$/.test(color)) {
                    return;
                }

                document.querySelectorAll('meta[name="theme-color"], meta[name="msapplication-TileColor"]').forEach((meta) => {
                    meta.setAttribute('content', color);
                });
            });
        </script><!-- te-refresh-sw-v17 -->
            <style id="te-mobile-menu-color-v18">
            @media (max-width: 1023px) {
                html {
                    --te-active-tint: color-mix(in srgb, var(--tenant-brand-color, #022a8c) 12%, white);
                }

                .te-mobile-brand-active {
                    background-color: var(--te-active-tint) !important;
                    color: var(--tenant-brand-color, #022a8c) !important;
                }

                .te-mobile-brand-featured {
                    background-color: var(--tenant-brand-color, #022a8c) !important;
                    color: #fff !important;
                }
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
                const normalize = (value) => (value || '').trim();
                const color = normalize(getComputedStyle(document.documentElement).getPropertyValue('--tenant-brand-color'))
                    || normalize(document.querySelector('meta[name="theme-color"]')?.getAttribute('content'))
                    || '#022a8c';

                if (! /^#[0-9A-Fa-f]{6}$/.test(color)) {
                    return;
                }

                document.documentElement.style.setProperty('--tenant-brand-color', color);

                const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
                const isActiveHref = (href) => {
                    try {
                        const url = new URL(href, window.location.origin);
                        const path = url.pathname.replace(/\/+$/, '') || '/';
                        if (path === currentPath) {
                            return true;
                        }
                        if (path === '/shipments' && currentPath.startsWith('/shipments')) {
                            return ! currentPath.includes('/create');
                        }
                        if (path === '/dashboard' && currentPath === '/') {
                            return true;
                        }
                    } catch (error) {
                        return false;
                    }

                    return false;
                };

                const applyMobileMenuColor = () => {
                    const mobileAreas = [
                        document.querySelector('div.fixed.inset-x-0.bottom-0'),
                        document.querySelector('aside.fixed.inset-y-0.left-0'),
                    ].filter(Boolean);

                    mobileAreas.forEach((area) => {
                        area.querySelectorAll('a[href]').forEach((link) => {
                            const label = normalize(link.textContent).toLowerCase();
                            const href = link.getAttribute('href') || '';

                            link.classList.remove('te-mobile-brand-active', 'te-mobile-brand-featured');

                            if (label === 'crear' || label === 'crear guia' || href.includes('/shipments/create')) {
                                link.classList.add('te-mobile-brand-featured');
                                return;
                            }

                            if (isActiveHref(href) || /bg-(blue|red|orange|green|pink|purple|emerald|rose|sky|indigo)-50/.test(link.className)) {
                                link.classList.add('te-mobile-brand-active');
                            }
                        });
                    });
                };

                applyMobileMenuColor();

                document.addEventListener('click', () => {
                    window.setTimeout(applyMobileMenuColor, 80);
                }, true);

                window.addEventListener('pageshow', applyMobileMenuColor);
            });
        </script><!-- te-mobile-menu-color-v18 -->
            <?php // te-force-mobile-menu-v20-start
            $forceMobileMenuColor = '#022a8c';
            $forceMobileMenuUser = auth()->user();
            if ($forceMobileMenuUser) {
                $forceMobileMenuOwner = $forceMobileMenuUser->affiliatedCompany ?: $forceMobileMenuUser->tenant;
                $forceMobileMenuBrand = $forceMobileMenuOwner?->brandData();
                $forceMobileMenuCandidate = $forceMobileMenuBrand['color'] ?? null;
                if (is_string($forceMobileMenuCandidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $forceMobileMenuCandidate)) {
                    $forceMobileMenuColor = strtolower($forceMobileMenuCandidate);
                }
            }

            $forceMobileMenuRed = hexdec(substr($forceMobileMenuColor, 1, 2));
            $forceMobileMenuGreen = hexdec(substr($forceMobileMenuColor, 3, 2));
            $forceMobileMenuBlue = hexdec(substr($forceMobileMenuColor, 5, 2));
            $forceMobileMenuTint = "rgba({$forceMobileMenuRed}, {$forceMobileMenuGreen}, {$forceMobileMenuBlue}, 0.10)";
        ?>
        <style id="te-force-mobile-menu-v20">
            :root {
                --tenant-brand-color: <?php echo e($forceMobileMenuColor); ?>;
                --tenant-brand-tint: <?php echo e($forceMobileMenuTint); ?>;
            }

            @media (max-width: 1023px) {
                nav div.fixed.inset-x-0.bottom-0 a[href*="/shipments/create"],
                nav div[class*="bottom-0"] a[href*="/shipments/create"],
                nav a[href*="/shipments/create"].bg-blue-600,
                nav a[href*="/shipments/create"].bg-red-600,
                nav a[href*="/shipments/create"].bg-orange-600,
                nav a[href*="/shipments/create"].bg-green-600,
                nav a[href*="/shipments/create"].bg-purple-600,
                nav a[href*="/shipments/create"].bg-pink-600,
                nav a[href*="/shipments/create"].bg-rose-600,
                nav a[href*="/shipments/create"].bg-indigo-600 {
                    background-color: <?php echo e($forceMobileMenuColor); ?> !important;
                    border-color: <?php echo e($forceMobileMenuColor); ?> !important;
                    color: #ffffff !important;
                }

                nav div.fixed.inset-x-0.bottom-0 a[href*="/shipments/create"] svg,
                nav div[class*="bottom-0"] a[href*="/shipments/create"] svg {
                    color: #ffffff !important;
                    stroke: currentColor !important;
                }

                nav div.fixed.inset-x-0.bottom-0 a[href]:not([href*="/shipments/create"]).te-current-mobile-v20,
                nav div[class*="bottom-0"] a[href]:not([href*="/shipments/create"]).te-current-mobile-v20,
                nav aside a[href].te-current-mobile-v20 {
                    background-color: <?php echo e($forceMobileMenuTint); ?> !important;
                    color: <?php echo e($forceMobileMenuColor); ?> !important;
                }

                nav div.fixed.inset-x-0.bottom-0 a[href]:not([href*="/shipments/create"]).te-current-mobile-v20 svg,
                nav div[class*="bottom-0"] a[href]:not([href*="/shipments/create"]).te-current-mobile-v20 svg,
                nav aside a[href].te-current-mobile-v20 svg {
                    color: <?php echo e($forceMobileMenuColor); ?> !important;
                    stroke: currentColor !important;
                }
            }
        </style>
        <script id="te-force-mobile-menu-v20">
            (() => {
                const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
                const color = <?php echo json_encode($forceMobileMenuColor, 15, 512) ?>;
                const tint = <?php echo json_encode($forceMobileMenuTint, 15, 512) ?>;

                const isCurrent = (href) => {
                    try {
                        const url = new URL(href, window.location.origin);
                        const path = url.pathname.replace(/\/+$/, '') || '/';
                        if (path === currentPath) return true;
                        if (path === '/shipments' && currentPath === '/shipments') return true;
                        if (path === '/dashboard' && (currentPath === '/' || currentPath === '/dashboard')) return true;
                    } catch (error) {}
                    return false;
                };

                const paint = () => {
                    document.documentElement.style.setProperty('--tenant-brand-color', color);
                    document.documentElement.style.setProperty('--tenant-brand-tint', tint);

                    document.querySelectorAll('nav a[href]').forEach((link) => {
                        const href = link.getAttribute('href') || '';
                        const inMobileMenu = Boolean(link.closest('div.fixed.inset-x-0.bottom-0, div[class*="bottom-0"], aside'));
                        if (! inMobileMenu) return;

                        link.classList.toggle('te-current-mobile-v20', isCurrent(href));

                        if (href.includes('/shipments/create')) {
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
        </script><!-- te-force-mobile-menu-v20 -->
        <script>
            (function(){var t=localStorage.getItem('theme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.classList.add('dark')}})();
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 text-gray-950 dark:bg-gray-900 dark:text-gray-100">
            <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="app-content-frame pb-20 pt-16 lg:pb-0 lg:pl-52 lg:pt-0">
                <!-- Page Heading -->
                <?php if(isset($header)): ?>
                    <header class="border-b border-gray-200 bg-white shadow-sm">
                        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 lg:py-6">
                            <?php echo e($header); ?>

                        </div>
                    </header>
                <?php endif; ?>

                <!-- Page Content -->
                <main>
                    <?php echo e($slot); ?>

                </main>
            </div>
        </div>
            <?php // te-bottom-mobile-final-v22-start
            $bottomFinalColor = '#022a8c';
            $bottomFinalUser = auth()->user();
            if ($bottomFinalUser) {
                $bottomFinalOwner = $bottomFinalUser->affiliatedCompany ?: $bottomFinalUser->tenant;
                $bottomFinalBrand = $bottomFinalOwner?->brandData();
                $bottomFinalCandidate = $bottomFinalBrand['color'] ?? null;
                if (is_string($bottomFinalCandidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $bottomFinalCandidate)) {
                    $bottomFinalColor = strtolower($bottomFinalCandidate);
                }
            }

            $bottomFinalR = hexdec(substr($bottomFinalColor, 1, 2));
            $bottomFinalG = hexdec(substr($bottomFinalColor, 3, 2));
            $bottomFinalB = hexdec(substr($bottomFinalColor, 5, 2));
            $bottomFinalTint = "rgba({$bottomFinalR}, {$bottomFinalG}, {$bottomFinalB}, 0.10)";
        ?>
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
                    background: <?php echo e($bottomFinalColor); ?> !important;
                    background-color: <?php echo e($bottomFinalColor); ?> !important;
                    border-color: <?php echo e($bottomFinalColor); ?> !important;
                    color: #ffffff !important;
                }

                body nav [class*="bottom-0"] a[href*="/shipments/create"] *,
                body .fixed.inset-x-0.bottom-0 a[href*="/shipments/create"] *,
                body nav [class*="bottom-0"] a:nth-child(3) *,
                body .fixed.inset-x-0.bottom-0 a:nth-child(3) * {
                    color: #ffffff !important;
                    stroke: currentColor !important;
                    fill: none !important;
                }

                body nav [class*="bottom-0"] a[aria-current="page"]:not([href*="/shipments/create"]),
                body nav [class*="bottom-0"] a.is-active:not([href*="/shipments/create"]),
                body nav [class*="bottom-0"] a.active:not([href*="/shipments/create"]),
                body .fixed.inset-x-0.bottom-0 a[aria-current="page"]:not([href*="/shipments/create"]) {
                    background-color: <?php echo e($bottomFinalTint); ?> !important;
                    color: <?php echo e($bottomFinalColor); ?> !important;
                }
            }
        </style>
        <script id="te-bottom-mobile-final-v22">
            (() => {
                const color = <?php echo json_encode($bottomFinalColor, 15, 512) ?>;
                const tint = <?php echo json_encode($bottomFinalTint, 15, 512) ?>;

                const paintBottomMenu = () => {
                    document.documentElement.style.setProperty('--tenant-brand-color', color);
                    document.documentElement.style.setProperty('--tenant-brand-tint', tint);

                    const bottomBars = Array.from(document.querySelectorAll('[class*="bottom-0"], .fixed.inset-x-0.bottom-0, .fixed.bottom-0'))
                        .filter((bar) => bar.querySelectorAll('a').length >= 3);

                    bottomBars.forEach((bar) => {
                        const links = Array.from(bar.querySelectorAll('a'));
                        links.forEach((link) => {
                            const href = link.getAttribute('href') || '';
                            const text = (link.textContent || '').trim().toLowerCase();
                            const isCreate = href.includes('/shipments/create') || text === 'crear' || text.includes('crear');

                            if (isCreate || links.indexOf(link) === 2) {
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
        </script><!-- te-bottom-mobile-final-v22 -->
            <?php // te-real-mobile-menu-v23-start
            $realMobileMenuColor = '#022a8c';
            $realMobileMenuUser = auth()->user();
            if ($realMobileMenuUser) {
                $realMobileMenuOwner = $realMobileMenuUser->affiliatedCompany ?: $realMobileMenuUser->tenant;
                $realMobileMenuBrand = $realMobileMenuOwner?->brandData();
                $realMobileMenuCandidate = $realMobileMenuBrand['color'] ?? null;
                if (is_string($realMobileMenuCandidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $realMobileMenuCandidate)) {
                    $realMobileMenuColor = strtolower($realMobileMenuCandidate);
                }
            }

            $realMobileMenuR = hexdec(substr($realMobileMenuColor, 1, 2));
            $realMobileMenuG = hexdec(substr($realMobileMenuColor, 3, 2));
            $realMobileMenuB = hexdec(substr($realMobileMenuColor, 5, 2));
            $realMobileMenuTint = "rgba({$realMobileMenuR}, {$realMobileMenuG}, {$realMobileMenuB}, 0.10)";
        ?>
        <style id="te-real-mobile-menu-v23">
            @media (max-width: 1023px) {
                .mobile-bottom-nav .mobile-bottom-link.is-featured,
                .mobile-bottom-nav .mobile-bottom-link[data-route-create="1"],
                .mobile-bottom-grid .mobile-bottom-link.is-featured,
                .mobile-bottom-grid .mobile-bottom-link[data-route-create="1"] {
                    background: <?php echo e($realMobileMenuColor); ?> !important;
                    background-color: <?php echo e($realMobileMenuColor); ?> !important;
                    border-color: <?php echo e($realMobileMenuColor); ?> !important;
                    color: #ffffff !important;
                }

                .mobile-bottom-nav .mobile-bottom-link.is-featured *,
                .mobile-bottom-nav .mobile-bottom-link[data-route-create="1"] *,
                .mobile-bottom-grid .mobile-bottom-link.is-featured *,
                .mobile-bottom-grid .mobile-bottom-link[data-route-create="1"] * {
                    color: #ffffff !important;
                    stroke: currentColor !important;
                }

                .mobile-bottom-nav .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]),
                .mobile-bottom-grid .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]) {
                    background: <?php echo e($realMobileMenuTint); ?> !important;
                    background-color: <?php echo e($realMobileMenuTint); ?> !important;
                    color: <?php echo e($realMobileMenuColor); ?> !important;
                }

                .mobile-bottom-nav .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]) *,
                .mobile-bottom-grid .mobile-bottom-link[data-route-current="1"]:not([data-route-create="1"]) * {
                    color: <?php echo e($realMobileMenuColor); ?> !important;
                    stroke: currentColor !important;
                }
            }
        </style>
        <script id="te-real-mobile-menu-v23">
            (() => {
                const color = <?php echo json_encode($realMobileMenuColor, 15, 512) ?>;
                const tint = <?php echo json_encode($realMobileMenuTint, 15, 512) ?>;

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
        </script><!-- te-real-mobile-menu-v23 -->
            <?php // te-mobile-logout-v24-start
            $mobileLogoutColor = '#022a8c';
            $mobileLogoutUser = auth()->user();
            if ($mobileLogoutUser) {
                $mobileLogoutOwner = $mobileLogoutUser->affiliatedCompany ?: $mobileLogoutUser->tenant;
                $mobileLogoutBrand = $mobileLogoutOwner?->brandData();
                $mobileLogoutCandidate = $mobileLogoutBrand['color'] ?? null;
                if (is_string($mobileLogoutCandidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $mobileLogoutCandidate)) {
                    $mobileLogoutColor = strtolower($mobileLogoutCandidate);
                }
            }
        ?>
        <style id="te-mobile-logout-v24">
            @media (max-width: 1023px) {
                .te-mobile-drawer-logout-v24 {
                    border-top: 1px solid #e5e7eb;
                    background: #ffffff;
                    padding: 0.75rem 1rem calc(env(safe-area-inset-bottom) + 4.6rem);
                }

                .te-mobile-drawer-logout-v24 button {
                    display: flex;
                    width: 100%;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background: #ffffff;
                    padding: 0.7rem 1rem;
                    color: #111827;
                    font-weight: 800;
                    line-height: 1;
                }

                .te-mobile-drawer-logout-v24 button:active {
                    border-color: <?php echo e($mobileLogoutColor); ?>;
                    color: <?php echo e($mobileLogoutColor); ?>;
                }
            }
        </style>
        <script id="te-mobile-logout-v24">
            (() => {
                const addLogoutButton = () => {
                    if (document.querySelector('.te-mobile-drawer-logout-v24')) {
                        return;
                    }

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const sidePanel = document.querySelector('aside, .mobile-menu, .mobile-sidebar, [class*="mobile"][class*="menu"]');
                    if (! sidePanel) {
                        return;
                    }

                    const userBlock = Array.from(sidePanel.querySelectorAll('div, section, footer'))
                        .reverse()
                        .find((element) => /@/.test(element.textContent || ''));

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

                    if (userBlock) {
                        userBlock.insertAdjacentElement('afterend', wrapper);
                    } else {
                        sidePanel.appendChild(wrapper);
                    }
                };

                addLogoutButton();
                document.addEventListener('DOMContentLoaded', addLogoutButton);
                document.addEventListener('click', () => window.setTimeout(addLogoutButton, 80), true);
                window.addEventListener('pageshow', addLogoutButton);
                window.setTimeout(addLogoutButton, 500);
            })();
        </script><!-- te-mobile-logout-v24 -->

        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }
        </script>
    </body>
</html>
<?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/layouts/app.blade.php ENDPATH**/ ?>