<?php
    $mobileMenuColor = '#022a8c';
    $mobileMenuUser = Auth::user();
    if ($mobileMenuUser) {
        $mobileMenuOwner = $mobileMenuUser->affiliatedCompany ?: $mobileMenuUser->tenant;
        $mobileMenuBrand = $mobileMenuOwner?->brandData();
        $mobileMenuCandidate = $mobileMenuBrand['color'] ?? null;
        if (is_string($mobileMenuCandidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $mobileMenuCandidate)) {
            $mobileMenuColor = strtolower($mobileMenuCandidate);
        }
    }

    $mobileMenuRed = hexdec(substr($mobileMenuColor, 1, 2));
    $mobileMenuGreen = hexdec(substr($mobileMenuColor, 3, 2));
    $mobileMenuBlue = hexdec(substr($mobileMenuColor, 5, 2));
    $mobileMenuTint = "rgba({$mobileMenuRed}, {$mobileMenuGreen}, {$mobileMenuBlue}, 0.10)";

    $panelLogoOwner = Auth::user()->tenant ?: Auth::user()->affiliatedCompany;
    $panelLogoUrl = $panelLogoOwner?->logo_path
        ? \Illuminate\Support\Facades\Storage::url($panelLogoOwner->logo_path)
        : asset('images/logotusenvios.png') . '?v=20260521';

    $isAdmin = Auth::user()->isSuperAdmin();
    $isAdminOnly = $isAdmin && !Auth::user()->tenant_id;

    $trialInfo = null;
    $userIsTenant = Auth::user()->tenant_id || Auth::user()->affiliated_company_id;
    if ($userIsTenant && !Auth::user()->isSuperAdmin()) {
        $trialTenant = Auth::user()->tenant ?: Auth::user()->affiliatedCompany?->tenant;
        $trialSub = $trialTenant?->currentSubscription;
        if ($trialSub && $trialSub->isTrial()) {
            $trialInfo = [
                'remaining' => $trialSub->trialGuidesRemaining(),
                'total' => (int) $trialSub->trial_guide_limit,
            ];
        }
    }
    $userIsTenant = Auth::user()->tenant_id || Auth::user()->affiliated_company_id;
    $canInventory = Auth::user()->canUseInventory();
    $canShipments = Auth::user()->canCreateShipments();

    $isActive = function ($patterns) {
        $patterns = is_array($patterns) ? $patterns : [$patterns];
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) return true;
        }
        return false;
    };

    $menuSections = [
        [
            'label' => 'Dashboard',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5 12 3l9 7.5M5 10v10h14V10" />',
            'active' => ['dashboard'],
            'show' => true,
            'route' => 'dashboard',
            'children' => [],
        ],
        [
            'label' => 'Crear guia',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />',
            'active' => ['shipments.create'],
            'show' => $canShipments,
            'route' => 'shipments.create',
            'children' => [],
        ],
        [
            'label' => 'Envios',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0zM13 5v7a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1h8a1 1 0 011 1zm2 3h2l3 4v1a1 1 0 01-1 1h-4m-5 0h5" />',
            'active' => ['shipments.*'],
            'show' => true,
            'children' => [
                ['label' => 'Mis guias', 'route' => 'shipments.index', 'active' => 'shipments.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />', 'show' => true],
                ['label' => 'Calculadora', 'route' => 'shipping-rates.index', 'active' => 'shipping-rates.*', 'icon' => '...', 'show' => $isAdmin],
            ],
        ],
        [
            'label' => 'Productos',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7.5 12 3l8 4.5-8 4.5-8-4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12l8 4.5 8-4.5M4 16.5l8 4.5 8-4.5" />',
            'active' => ['quick-products.*'],
            'show' => $userIsTenant && !$canInventory,
            'children' => [
                ['label' => 'Productos rapidos', 'route' => 'quick-products.index', 'active' => 'quick-products.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M6 7l1 13h10l1-13M9 7V5a3 3 0 0 1 6 0v2" />', 'show' => $userIsTenant],
            ],
        ],
        [
            'label' => 'Inventario',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7.5 12 3l8 4.5-8 4.5-8-4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12l8 4.5 8-4.5M4 16.5l8 4.5 8-4.5" />',
            'active' => ['inventory.*', 'inventory.create', 'inventory.reports.*', 'inventory.movements'],
            'show' => $canInventory,
            'children' => [
                ['label' => 'Agregar producto', 'route' => 'inventory.create', 'active' => 'inventory.create', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />', 'show' => $canInventory],
                ['label' => 'Listado', 'route' => 'inventory.index', 'active' => 'inventory.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />', 'show' => $canInventory],
                ['label' => 'Movimientos', 'route' => 'inventory.movements', 'active' => 'inventory.movements', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />', 'show' => $canInventory],
                ['label' => 'Ventas', 'route' => 'inventory.reports.sales', 'active' => 'inventory.reports.sales', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 0 4.5 6h.75m13.5 0h.75a.75.75 0 0 0 .75-.75V4.5M4.5 6v9.75a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V6" />', 'show' => $canInventory],
                ['label' => 'Rotacion', 'route' => 'inventory.reports.rotation', 'active' => 'inventory.reports.rotation', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c-.017.22-.032.441-.046.662M4.5 12l3 3m-3-3-3 3" />', 'show' => $canInventory],
                ['label' => 'Categorias', 'route' => 'inventory.reports.categories', 'active' => 'inventory.reports.categories', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h.008v.008H6V6Z" />', 'show' => $canInventory],
            ],
        ],
        [
            'label' => 'Configuracion',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 4.5 12 2l1.5 2.5 2.8.6-.9 2.7 1.9 2.2-2.5 1.4-.1 2.9-2.7-.8-2.7.8-.1-2.9L6.7 10l1.9-2.2-.9-2.7 2.8-.6Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />',
            'active' => ['brand-settings.*', 'store-settings.*', 'profile.*'],
            'show' => $userIsTenant,
            'children' => [
                ['label' => 'Mi marca', 'route' => 'brand-settings.edit', 'active' => 'brand-settings.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h4a4 4 0 0 1 0 8h-4V4Zm0 8h5a4 4 0 0 1 0 8h-5v-8ZM7 4h5v16H7a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Z" />', 'show' => $userIsTenant],
                ['label' => 'Tienda', 'route' => 'store-settings.edit', 'active' => 'store-settings.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .415.336.75.75.75Z" />', 'show' => $userIsTenant],
                ['label' => 'Mi perfil', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />', 'show' => true],
            ],
        ],
    ];

    if ($isAdmin) {
        $menuSections[] = [
            'label' => 'Administracion',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />',
            'active' => ['admin.*', 'subscriptions.*'],
            'show' => true,
            'children' => [
                ['label' => 'Panel admin', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5 12 3l9 7.5M5 10v10h14V10" />', 'show' => true],
                ['label' => 'Clientes', 'route' => 'admin.clients', 'active' => 'admin.clients.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 12 21c-2.173 0-4.2-.598-5.93-1.637m0 0a9.37 9.37 0 0 0 2.625-.372M6 19.372v-.008m6.001-1.437a4.125 4.125 0 0 0-5.497-5.498 4.125 4.125 0 0 0 0 5.498m0 0v.003m0 0c.587.176 1.2.272 1.832.272M12 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />', 'show' => true],
                ['label' => 'Suscripciones', 'route' => 'admin.subscriptions', 'active' => 'admin.subscriptions.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />', 'show' => true],
                ['label' => 'Actividad', 'route' => 'admin.activity', 'active' => 'admin.activity', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16V9m4 7V6m4 10v-4" />', 'show' => true],
            ],
        ];
        $menuSections[] = [
            'label' => 'Configuracion',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
            'active' => ['admin.plans', 'brand-settings.*', 'store-settings.*'],
            'show' => true,
            'children' => [
                ['label' => 'Planes', 'route' => 'admin.plans', 'active' => 'admin.plans', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />', 'show' => true],
            ],
        ];
    }

    if ($isAdminOnly) {
        $menuSections = collect($menuSections)->filter(fn ($s) => in_array($s['label'], ['Administracion', 'Configuracion']))->values()->all();
        $bottomItems = [];
    }

    if (!$isAdminOnly) {
        $bottomItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'show' => true, 'featured' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5 12 3l9 7.5M5 10v10h14V10" />'],
            ['label' => 'Guias', 'route' => 'shipments.index', 'active' => 'shipments.index', 'show' => true, 'featured' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'],
            ['label' => 'Crear', 'route' => 'shipments.create', 'active' => 'shipments.create', 'show' => $canShipments, 'featured' => true, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />'],
            ['label' => 'Tienda', 'route' => 'store-settings.edit', 'active' => 'store-settings.*', 'show' => $userIsTenant, 'featured' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />'],
            ['label' => 'Fletes', 'route' => 'shipping-rates.index', 'active' => 'shipping-rates.*', 'show' => $isAdmin, 'featured' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
            ['label' => 'Invent.', 'route' => 'inventory.index', 'active' => 'inventory.*', 'show' => $canInventory, 'featured' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7.5 12 3l8 4.5-8 4.5-8-4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12l8 4.5 8-4.5M4 16.5l8 4.5 8-4.5" />'],
            ['label' => 'Perfil', 'route' => 'profile.edit', 'active' => 'profile.*', 'show' => true, 'featured' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />'],
        ];
    }
?>

<style id="te-mobile-menu-server-color-v21">
    @media (max-width: 1023px) {
        .te-mobile-bottom-v21 a[data-route-create="1"] {
            background-color: <?php echo e($mobileMenuColor); ?> !important;
            border-color: <?php echo e($mobileMenuColor); ?> !important;
            color: #fff !important;
        }
        .te-mobile-bottom-v21 a[data-route-current="1"]:not([data-route-create="1"]),
        .te-mobile-side-v21 a[data-route-current="1"] {
            background-color: <?php echo e($mobileMenuTint); ?> !important;
            color: <?php echo e($mobileMenuColor); ?> !important;
        }
        .te-mobile-bottom-v21 a[data-route-current="1"]:not([data-route-create="1"]) svg,
        .te-mobile-side-v21 a[data-route-current="1"] svg {
            color: <?php echo e($mobileMenuColor); ?> !important;
            stroke: currentColor !important;
        }
        .te-mobile-bottom-v21 a[data-route-create="1"] svg {
            color: #fff !important;
            stroke: currentColor !important;
        }
    }
</style>

<style>
    .te-menu-child-link-v01 {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 0.375rem;
        padding: 0.35rem 0.5rem 0.35rem 1.8rem;
        color: #6b7280;
        transition: all 0.15s ease;
    }
    .te-menu-child-link-v01:hover {
        background: #f9fafb;
        color: #111827;
    }
    .te-menu-child-link-v01.is-active {
        background: var(--te-button-soft, #eef3ff);
        color: var(--te-button-soft-text, #022a8c);
    }
    .te-menu-child-link-v01 svg {
        width: 0.9rem;
        height: 0.9rem;
        flex: 0 0 auto;
    }
    .te-menu-section-label-v01 {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 0.5rem;
        color: #9ca3af;
    }
    .te-menu-section-label-v01:not(:first-child) {
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }
</style>

<nav x-data="{ open: false }">
    <style id="te-logo-no-crop-v5">
        @media (min-width: 1024px) {
            aside > div:first-child, aside .te-panel-logo-area { height: auto !important; min-height: 126px !important; padding: 16px 16px !important; overflow: visible !important; display: flex !important; align-items: center !important; justify-content: center !important; border-bottom: 1px solid #e5e7eb !important; background: transparent !important; }
            aside > div:first-child a, aside .te-panel-logo-box { width: 12rem !important; max-width: 12rem !important; max-height: 6.5rem !important; display: flex !important; align-items: center !important; justify-content: center !important; overflow: visible !important; }
            aside > div:first-child img, aside .te-panel-logo-img { width: auto !important; height: auto !important; max-width: 12rem !important; max-height: 6.5rem !important; object-fit: contain !important; background: transparent !important; box-shadow: none !important; display: block !important; }
        }
        @media (max-width: 1023px) {
            .te-panel-logo-mobile-box { width: 9.5rem !important; height: auto !important; min-height: 2.75rem !important; overflow: visible !important; }
            .te-panel-logo-mobile-img, .fixed.inset-x-0.top-0 img { max-width: 9.5rem !important; max-height: 2.75rem !important; object-fit: contain !important; object-position: left center !important; }
        }
    </style>
    <style id="te-client-logo-area-v4">
        .te-panel-logo-area { min-height: 126px; padding: 16px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #e5e7eb; background: transparent; }
        .te-panel-logo-box { width: 12rem; min-height: 3.5rem; max-width: 12rem; max-height: 6.5rem; display: flex; align-items: center; justify-content: center; overflow: visible; }
        .te-panel-logo-img { display: block; width: auto; height: auto; max-width: 12rem; max-height: 6.5rem; object-fit: contain; object-position: center; }
        .te-panel-logo-mobile-box { width: 9.5rem; min-height: 2.75rem; display: flex; align-items: center; justify-content: flex-start; overflow: visible; }
        .te-panel-logo-mobile-img { display: block; width: 100%; height: 100%; max-width: 9.5rem; max-height: 2.75rem; object-fit: contain; object-position: left center; }
    </style>
    <style>
        .te-mobile-drawer-logout-v24 { display: none !important; height: 0 !important; margin: 0 !important; padding: 0 !important; overflow: hidden !important; }
        .te-mobile-drawer-logout-v24 form, .te-mobile-drawer-logout-v24 button { display: none !important; }
        aside form[action*="logout"] > button[aria-label="Salir"]:not(.te-logout-button) { display: none !important; height: 0 !important; margin: 0 !important; padding: 0 !important; overflow: hidden !important; }
        .te-panel-logo-area, .te-panel-logo-box, .te-panel-logo-mobile-box, .te-panel-logo-area a, .te-panel-logo-mobile-box img, .te-panel-logo-img { background: transparent !important; box-shadow: none !important; filter: none !important; }
        .te-panel-logo-area img, .te-panel-logo-mobile-box img { background: transparent !important; box-shadow: none !important; filter: none !important; }
        @media (max-width: 1023px) {
            .te-mobile-side-v21 { padding-bottom: 64px !important; }
            .te-mobile-side-v21 > .flex-1 { flex: 0 1 auto !important; max-height: calc(100dvh - 126px - 136px) !important; }
            .te-mobile-side-v21 .te-sidebar-user-footer { margin-top: auto !important; padding-bottom: 76px !important; background: #ffffff !important; }
            .te-mobile-side-v21 .te-logout-button { display: flex !important; min-height: 44px !important; }
        }
        .mobile-bottom-nav { position: fixed; left: 0; right: 0; bottom: 0; z-index: 60; border-top: 1px solid #e5e7eb; background: #ffffff; padding: 6px 8px calc(6px + env(safe-area-inset-bottom)); box-shadow: 0 -8px 18px rgba(15, 23, 42, 0.06); }
        .mobile-bottom-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(44px, 1fr)); gap: 4px; align-items: stretch; }
        .mobile-bottom-link {
            min-width: 0;
            min-height: 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            border-radius: 8px;
            color: #6b7280;
            text-align: center;
            text-decoration: none;
            font-size: 10px;
            font-weight: 700;
        }
        .mobile-bottom-link svg { width: 19px; height: 19px; flex: 0 0 auto; }
        .mobile-bottom-link.is-active { color: #022a8c; background: #eef4ff; }
        .mobile-bottom-link.is-featured { color: #ffffff; background: #022a8c; }
        @media (min-width: 1024px) { .te-mobile-side-v21 { width: 12rem !important; } .mobile-bottom-nav { display: none; } }
    </style>

    <div class="fixed inset-x-0 top-0 z-40 border-b border-gray-200 bg-white px-4 py-2 lg:hidden" style="min-height:60px;">
        <div class="flex items-center justify-between">
            <a href="<?php echo e(route('dashboard')); ?>" class="te-panel-logo-mobile-box">
                <img src="<?php echo e($panelLogoUrl); ?>" alt="<?php echo e($panelLogoOwner?->name ?: 'Tus Envios'); ?>" style="display:block;width:auto;height:auto;max-width:150px;max-height:38px;object-fit:contain;">
            </a>
            <button @click="open = !open" class="rounded-md border border-gray-300 p-2 text-gray-700" aria-label="Abrir menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-40 bg-black/30 lg:hidden" @click="open = false"></div>

    <aside
        class="te-mobile-side-v21 fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition lg:w-52 lg:translate-x-0"
        :class="{ 'translate-x-0': open }"
    >
        <div class="te-panel-logo-area">
            <a href="<?php echo e(route('dashboard')); ?>" class="te-panel-logo-box">
                <img src="<?php echo e($panelLogoUrl); ?>" alt="<?php echo e($panelLogoOwner?->name ?: 'Tus Envios'); ?>" class="block h-auto max-h-10 w-auto max-w-[150px] object-contain">
            </a>
        </div>

        <div class="flex-1 overflow-y-auto px-2 py-3 lg:px-2">
            <div class="space-y-0.5">
                <?php $__currentLoopData = $menuSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!($section['show'] ?? true)): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php
                        $hasChildren = !empty($section['children']);
                        $isSectionActive = $hasChildren
                            ? $isActive($section['active'])
                            : $isActive($section['active']);
                        $sectionHasVisibleChildren = $hasChildren
                            ? collect($section['children'])->first(fn ($c) => $c['show'] ?? false)
                            : false;
                    ?>

                    <?php if(! $hasChildren): ?>
                        <a href="<?php echo e(route($section['route'])); ?>"
                            data-route-current="<?php echo e($isSectionActive ? '1' : '0'); ?>"
                            class="flex items-center gap-3 rounded-md px-3 py-2 text-xs font-semibold transition lg:gap-2 lg:px-2 <?php echo e($isSectionActive ? 'bg-blue-50 text-blue-800' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950'); ?>">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><?php echo $section['icon']; ?></svg>
                            <span><?php echo e($section['label']); ?></span>
                        </a>
                    <?php elseif($sectionHasVisibleChildren): ?>
                        <?php
                            $anyChildActive = collect($section['children'])->contains(fn ($c) => ($c['show'] ?? false) && $isActive($c['active']));
                        ?>
                        <div class="pt-1">
                            <div class="flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-black uppercase tracking-wider text-gray-400 lg:gap-2 lg:px-2">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><?php echo $section['icon']; ?></svg>
                                <span><?php echo e($section['label']); ?></span>
                            </div>
                            <div class="mt-0.5 space-y-0.5">
                                <?php $__currentLoopData = $section['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(!($child['show'] ?? true)): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <?php $childActive = $isActive($child['active']); ?>
                                    <a href="<?php echo e(route($child['route'])); ?>"
                                        data-route-current="<?php echo e($childActive ? '1' : '0'); ?>"
                                        class="te-menu-child-link-v01 text-xs font-semibold <?php echo e($childActive ? 'is-active' : ''); ?>">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><?php echo $child['icon']; ?></svg>
                                        <span class="truncate"><?php echo e($child['label']); ?></span>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="te-sidebar-user-footer border-t border-gray-200 p-4 lg:p-3">
            <?php if($trialInfo): ?>
                <div class="mb-3 rounded-lg bg-blue-50 border border-blue-200 p-2.5">
                    <p class="text-xs font-semibold text-blue-800">Prueba gratis</p>
                    <p class="text-sm font-bold text-blue-900 mt-0.5"><?php echo e($trialInfo['remaining']); ?> / <?php echo e($trialInfo['total']); ?> guias</p>
                    <div class="mt-1.5 bg-blue-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full transition-all" style="width:<?php echo e(($trialInfo['total'] - $trialInfo['remaining']) / $trialInfo['total'] * 100); ?>%"></div>
                    </div>
                    <?php if($trialInfo['remaining'] <= 3): ?>
                        <p class="text-xs font-semibold text-blue-700 mt-1">Activa tu plan mensual</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <p class="text-sm font-semibold text-gray-950"><?php echo e(Auth::user()->name); ?></p>
                <p class="truncate text-xs text-gray-500"><?php echo e(Auth::user()->email); ?></p>
            </div>
            <button
                x-data="{
                    dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                    toggle() { this.dark = !this.dark; localStorage.setItem('theme', this.dark ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', this.dark); }
                }"
                x-init="$watch('dark', v => document.documentElement.classList.toggle('dark', v))"
                @click="toggle()"
                class="flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 mb-2"
            >
                <svg x-show="!dark" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="dark" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span x-text="dark ? 'Modo claro' : 'Modo oscuro'"></span>
            </button>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-3">
                <?php echo csrf_field(); ?>
                <button aria-label="Salir" class="te-logout-button flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6h3a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3M10 8l-4 4 4 4M6 12h10" />
                    </svg>
                    <span>Salir</span>
                </button>
            </form>
        </div>
    </aside>

    <div x-show="open" x-cloak class="fixed inset-y-0 left-64 z-50 w-[calc(100%-16rem)] lg:hidden" @click="open = false"></div>

</nav>

    <nav class="mobile-bottom-nav fixed bottom-0 inset-x-0 z-40 border-t border-gray-200 bg-white lg:hidden" style="padding:6px 8px calc(6px + env(safe-area-inset-bottom));background:#ffffff;border-top:1px solid #e5e7eb;">
        <div class="mobile-bottom-grid">
            <?php $i = 0; ?>
            <?php $__currentLoopData = $bottomItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!($item['show'] ?? true)): ?> <?php continue; ?> <?php endif; ?>
                <?php $i++; $isCurrent = request()->routeIs($item['active']); ?>
                <a href="<?php echo e(route($item['route'])); ?>" class="mobile-bottom-link <?php echo e(($item['featured'] ?? false) ? 'is-featured' : ''); ?>" data-route-current="<?php echo e($isCurrent ? '1' : '0'); ?>" data-route-create="<?php echo e(($item['featured'] ?? false) ? '1' : '0'); ?>">
                    <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?php echo $item['icon']; ?></svg>
                    <span><?php echo e($item['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </nav>
<?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>