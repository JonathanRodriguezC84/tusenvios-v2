@php
    // te-mobile-menu-server-color-v21-start
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
    // te-mobile-menu-server-color-v21-end

    $panelLogoOwner = Auth::user()->tenant ?: Auth::user()->affiliatedCompany;
    $panelLogoUrl = $panelLogoOwner?->logo_path
        ? \Illuminate\Support\Facades\Storage::url($panelLogoOwner->logo_path)
        : asset('images/logotusenvios.png') . '?v=20260521';

$items = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard',
            'show' => true,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5 12 3l9 7.5M5 10v10h14V10" />',
        ],
        [
            'label' => 'Crear guia',
            'route' => 'shipments.create',
            'active' => 'shipments.create',
            'show' => Auth::user()->canCreateShipments(),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />',
        ],
        [
            'label' => 'Mis guias',
            'route' => 'shipments.index',
            'active' => 'shipments.index',
            'show' => true,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h10a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 0 1 2-2Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6M9 13h6" />',
        ],
        [
            'label' => 'Productos',
            'route' => 'quick-products.index',
            'active' => 'quick-products.*',
            'show' => Auth::user()->tenant_id || Auth::user()->affiliated_company_id,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M6 7l1 13h10l1-13M9 7V5a3 3 0 0 1 6 0v2" />',
        ],
        [
            'label' => 'Mi marca',
            'route' => 'brand-settings.edit',
            'active' => 'brand-settings.*',
            'show' => Auth::user()->tenant_id || Auth::user()->affiliated_company_id,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h4a4 4 0 0 1 0 8h-4V4Zm0 8h5a4 4 0 0 1 0 8h-5v-8ZM7 4h5v16H7a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Z" />',
        ],
        [
            'label' => 'Configuracion',
            'route' => 'store-settings.edit',
            'active' => 'store-settings.*',
            'show' => Auth::user()->tenant_id || Auth::user()->affiliated_company_id,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 4.5 12 2l1.5 2.5 2.8.6-.9 2.7 1.9 2.2-2.5 1.4-.1 2.9-2.7-.8-2.7.8-.1-2.9L6.7 10l1.9-2.2-.9-2.7 2.8-.6Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />',
        ],
    ];

    $bottomItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard',
            'show' => true,
            'featured' => false,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5 12 3l9 7.5M5 10v10h14V10" />',
        ],
        [
            'label' => 'Guias',
            'route' => 'shipments.index',
            'active' => 'shipments.index',
            'show' => true,
            'featured' => false,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h10a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 0 1 2-2Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6M9 13h6" />',
        ],
        [
            'label' => 'Crear',
            'route' => 'shipments.create',
            'active' => 'shipments.create',
            'show' => Auth::user()->canCreateShipments(),
            'featured' => true,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />',
        ],
        [
            'label' => 'Productos',
            'route' => 'quick-products.index',
            'active' => 'quick-products.*',
            'show' => Auth::user()->tenant_id || Auth::user()->affiliated_company_id,
            'featured' => false,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M6 7l1 13h10l1-13M9 7V5a3 3 0 0 1 6 0v2" />',
        ],
        [
            'label' => 'Marca',
            'route' => 'brand-settings.edit',
            'active' => 'brand-settings.*',
            'show' => Auth::user()->tenant_id || Auth::user()->affiliated_company_id,
            'featured' => false,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h4a4 4 0 0 1 0 8h-4V4Zm0 8h5a4 4 0 0 1 0 8h-5v-8ZM7 4h5v16H7a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Z" />',
        ],
        [
            'label' => 'Config',
            'route' => 'store-settings.edit',
            'active' => 'store-settings.*',
            'show' => Auth::user()->tenant_id || Auth::user()->affiliated_company_id,
            'featured' => false,
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 4.5 12 2l1.5 2.5 2.8.6-.9 2.7 1.9 2.2-2.5 1.4-.1 2.9-2.7-.8-2.7.8-.1-2.9L6.7 10l1.9-2.2-.9-2.7 2.8-.6Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />',
        ],
    ];
@endphp

<style id="te-mobile-menu-server-color-v21">
    @media (max-width: 1023px) {
        .te-mobile-bottom-v21 a[data-route-create="1"] {
            background-color: {{ $mobileMenuColor }} !important;
            border-color: {{ $mobileMenuColor }} !important;
            color: #fff !important;
        }

        .te-mobile-bottom-v21 a[data-route-current="1"]:not([data-route-create="1"]),
        .te-mobile-side-v21 a[data-route-current="1"] {
            background-color: {{ $mobileMenuTint }} !important;
            color: {{ $mobileMenuColor }} !important;
        }

        .te-mobile-bottom-v21 a[data-route-current="1"]:not([data-route-create="1"]) svg,
        .te-mobile-side-v21 a[data-route-current="1"] svg {
            color: {{ $mobileMenuColor }} !important;
            stroke: currentColor !important;
        }

        .te-mobile-bottom-v21 a[data-route-create="1"] svg {
            color: #fff !important;
            stroke: currentColor !important;
        }
    }
</style>

<nav x-data="{ open: false }">
    <style id="te-logo-no-crop-v5">
        @media (min-width: 1024px) {
            aside > div:first-child,
            aside .te-panel-logo-area {
                height: auto !important;
                min-height: 92px !important;
                padding: 10px 16px !important;
                overflow: visible !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-bottom: 1px solid #e5e7eb !important;
                background: transparent !important;
            }

            aside > div:first-child a,
            aside .te-panel-logo-box {
                width: 148px !important;
                height: 54px !important;
                max-width: 148px !important;
                max-height: 54px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: visible !important;
            }

            aside > div:first-child img,
            aside .te-panel-logo-img {
                width: auto !important;
                height: auto !important;
                max-width: 148px !important;
                max-height: 54px !important;
                object-fit: contain !important;
                object-position: center !important;
                display: block !important;
            }
        }

        @media (max-width: 1023px) {
            .te-panel-logo-mobile-box {
                width: 150px !important;
                height: 44px !important;
                overflow: hidden !important;
            }

            .te-panel-logo-mobile-img,
            .fixed.inset-x-0.top-0 img {
                max-width: 150px !important;
                max-height: 44px !important;
                object-fit: contain !important;
                object-position: left center !important;
            }
        }
    </style>
    <style id="te-client-logo-area-v4">
        .te-panel-logo-area {
            min-height: 92px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e5e7eb;
            background: transparent;
        }

        .te-panel-logo-box {
            width: 148px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .te-panel-logo-img {
            display: block;
            width: auto;
            height: auto;
            max-width: 148px;
            max-height: 54px;
            object-fit: contain;
            object-position: center;
        }

        .te-panel-logo-mobile-box {
            width: 150px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            overflow: hidden;
        }

        .te-panel-logo-mobile-img {
            display: block;
            width: 100%;
            height: 100%;
            max-width: 150px;
            max-height: 44px;
            object-fit: contain;
            object-position: left center;
        }
    </style>
    <style>
        .te-mobile-drawer-logout-v24 {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .te-mobile-drawer-logout-v24 form,
        .te-mobile-drawer-logout-v24 button {
            display: none !important;
        }

        aside form[action*="logout"] > button[aria-label="Salir"]:not(.te-logout-button) {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .te-panel-logo-area,
        .te-panel-logo-box,
        .te-panel-logo-mobile-box,
        .te-panel-logo-area a,
        .te-panel-logo-mobile-box img,
        .te-panel-logo-img {
            background: transparent !important;
            box-shadow: none !important;
            filter: none !important;
        }

        .te-panel-logo-area img,
        .te-panel-logo-mobile-box img {
            background: transparent !important;
            box-shadow: none !important;
            filter: none !important;
        }

        @media (max-width: 1023px) {
            .te-mobile-side-v21 {
                padding-bottom: 64px !important;
            }

            .te-mobile-side-v21 > .flex-1 {
                flex: 0 1 auto !important;
                max-height: calc(100dvh - 92px - 136px) !important;
            }

            .te-mobile-side-v21 .te-sidebar-user-footer {
                margin-top: auto !important;
                padding-bottom: 76px !important;
                background: #ffffff !important;
            }

            .te-mobile-side-v21 .te-logout-button {
                display: flex !important;
                min-height: 44px !important;
            }
        }

        .mobile-bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 60;
            border-top: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 6px 8px calc(6px + env(safe-area-inset-bottom));
            box-shadow: 0 -8px 18px rgba(15, 23, 42, 0.06);
        }

        .mobile-bottom-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 4px;
            align-items: stretch;
        }

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
        }

        .mobile-bottom-link svg {
            width: 19px;
            height: 19px;
            flex: 0 0 auto;
        }

        .mobile-bottom-link.is-active {
            color: #022a8c;
            background: #eef4ff;
        }

        .mobile-bottom-link.is-featured {
            color: #ffffff;
            background: #022a8c;
        }

        @media (min-width: 1024px) {
            .mobile-bottom-nav {
                display: none;
            }
        }
    </style>
    <div class="fixed inset-x-0 top-0 z-40 border-b border-gray-200 bg-white px-4 py-2 lg:hidden" style="min-height:60px;">
        <div class="flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="te-panel-logo-mobile-box">
                <img src="{{ $panelLogoUrl }}" alt="{{ $panelLogoOwner?->name ?: 'Tus Envios' }}" style="display:block;width:auto;height:auto;max-width:150px;max-height:38px;object-fit:contain;">
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
        class="te-mobile-side-v21 fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition lg:translate-x-0"
        :class="{ 'translate-x-0': open }"
    >
        <div class="te-panel-logo-area">
            <a href="{{ route('dashboard') }}" class="te-panel-logo-box">
                <img src="{{ $panelLogoUrl }}" alt="{{ $panelLogoOwner?->name ?: 'Tus Envios' }}" class="block h-auto max-h-10 w-auto max-w-[140px] object-contain">
            </a>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4">
            <div class="space-y-1">
                @foreach ($items as $item)
                    @if ($item['show'])
                        <a
                            href="{{ route($item['route']) }}"
                            data-route-current="{{ request()->routeIs($item['active']) ? '1' : '0' }}" data-route-create="{{ ! empty($item['featured']) ? '1' : '0' }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($item['active']) ? 'bg-blue-50 text-blue-800' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}"
                        >
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="te-sidebar-user-footer border-t border-gray-200 p-4">
            <div class="mb-3">
                <p class="text-sm font-semibold text-gray-950">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
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

    <div class="mobile-bottom-nav">
        <div class="mobile-bottom-grid">
            @foreach ($bottomItems as $item)
                @if ($item['show'])
                    <a href="{{ route($item['route']) }}" data-route-current="{{ request()->routeIs($item['active']) ? '1' : '0' }}" data-route-create="{{ ! empty($item['featured']) ? '1' : '0' }}" class="mobile-bottom-link text-2xs font-black leading-none {{ $item['featured'] ? 'is-featured' : (request()->routeIs($item['active']) ? 'is-active' : '') }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</nav>
