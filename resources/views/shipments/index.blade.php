@php
    $statusLabels = [
        'created' => 'Por imprimir',
        'printed' => 'Impresa',
        'in_warehouse' => 'Preparando',
        'in_sorting' => 'Preparando',
        'assigned' => 'Asignada',
        'on_route' => 'En camino',
        'delivered' => 'Entregada',
        'failed_delivery' => 'Con novedad',
        'rescheduled' => 'Reprogramada',
        'return_pending' => 'Devuelve',
        'returned' => 'Devuelta',
        'cancelled' => 'Cancelada',
    ];

    $statusGroupBadge = function (\App\Models\Shipment $shipment): array {
        return match (\App\Models\Shipment::statusGroupKey($shipment->status)) {
            'delivered' => ['bg-emerald-100 text-emerald-800', '#10b981'],
            'returned' => ['bg-orange-100 text-orange-700', '#f97316'],
            'cancelled' => ['bg-gray-200 text-gray-800', '#6b7280'],
            default => ['bg-blue-100 text-blue-800', '#3b82f6'],
        };
    };

    $toastMessages = [];
    if (session('status')) { $toastMessages[] = ['text' => session('status'), 'type' => 'success']; }

    $activeFilters = collect();
    if (!empty($filters['search'])) $activeFilters->push(['label' => 'Buscar: "'.$filters['search'].'"', 'route' => route('shipments.index', array_merge(request()->except(['search', 'page']), ['search' => '']))]);
    if (!empty($filters['product'])) $activeFilters->push(['label' => 'Producto: "'.$filters['product'].'"', 'route' => route('shipments.index', array_merge(request()->except(['product', 'page']), ['product' => '']))]);
    if (!empty($filters['date'])) $activeFilters->push(['label' => 'Fecha: '.\Carbon\Carbon::parse($filters['date'])->format('d/m/Y'), 'route' => route('shipments.index', array_merge(request()->except(['date', 'page']), ['date' => '']))]);

    $dailyTaskLabels = [
        'issues' => 'Tarea: Novedades por resolver',
        'pending_print' => 'Tarea: Guias por imprimir',
        'preparation' => 'Tarea: Preparacion y bodega',
        'route' => 'Tarea: En ruta sin cierre',
        'stale' => 'Tarea: Sin movimiento reciente',
    ];
    if (!empty($filters['task'])) $activeFilters->push(['label' => $dailyTaskLabels[$filters['task']] ?? 'Tarea: '.$filters['task'], 'route' => route('shipments.index', request()->except(['task', 'page']))]);

    @endphp

<x-app-layout>
    @vite(['resources/css/shipments.css', 'resources/js/shipments.js'])

    <script id="sh-toast-data" type="application/json">{{ json_encode($toastMessages) }}</script>

    <x-slot name="header">
        <x-page-header eyebrow="{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM') }}" title="Mis guias" description="Busca, imprime y revisa tus etiquetas.">
            <x-slot name="actions">
                <a href="{{ route('shipments.export.pdf', request()->only(['search', 'product', 'status', 'task', 'date'])) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">Exportar PDF</a>
                <a href="{{ route('shipments.export', request()->only(['search', 'product', 'status', 'task', 'date'])) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">Exportar CSV</a>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-3 lg:p-5 h-full flex flex-col">
        {{-- Filtros --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-3 lg:p-4 shrink-0">
            <form method="GET" action="{{ route('shipments.index') }}" class="flex flex-wrap gap-2 items-center">
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Buscar por guia, cliente, telefono..." class="flex-1 min-w-[200px] rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <input name="product" value="{{ $filters['product'] ?? '' }}" type="search" placeholder="Buscar por producto..." class="min-w-[180px] rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <select name="status" class="rounded-lg border border-gray-300 py-1.5 pl-3 pr-8 text-sm bg-white">
                    <option value="">Todos los estados</option>
                    @foreach (\App\Models\Shipment::STATUS_GROUPS as $groupKey => $group)
                        <option value="{{ $groupKey }}" @selected(in_array($filters['status'] ?? '', $group['statuses'], true) || ($filters['status'] ?? '') === $groupKey)>{{ $group['label'] }}</option>
                    @endforeach
                </select>
                <input name="date" value="{{ $filters['date'] ?? '' }}" type="date" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                <button class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 min-h-[36px]">Filtrar</button>
                <a href="{{ route('shipments.index') }}" class="bg-white inline-flex items-center rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 min-h-[36px]">Limpiar</a>
            </form>
        </div>

        <nav class="mt-3 shrink-0" aria-label="Accesos rapidos por estado">
            <div class="grid grid-cols-3 md:grid-cols-6 gap-1.5">
                @foreach ($shipmentSummary['shortcuts'] as $shortcut)
                    @php
                        $shortcutTone = match ($shortcut['tone']) {
                            'red' => $shortcut['active'] ? 'border-red-300 bg-red-700 text-white' : 'border-red-200 bg-white text-red-800 hover:bg-red-50',
                            'amber' => $shortcut['active'] ? 'border-amber-300 bg-amber-600 text-white' : 'border-amber-200 bg-white text-amber-800 hover:bg-amber-50',
                            'emerald' => $shortcut['active'] ? 'border-emerald-300 bg-emerald-700 text-white' : 'border-emerald-200 bg-white text-emerald-800 hover:bg-emerald-50',
                            'blue' => $shortcut['active'] ? 'border-blue-300 bg-blue-700 text-white' : 'border-blue-200 bg-white text-blue-800 hover:bg-blue-50',
                            'slate' => $shortcut['active'] ? 'border-slate-300 bg-slate-700 text-white' : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50',
                            default => $shortcut['active'] ? 'border-transparent text-white' : 'border-gray-200 bg-white text-gray-800 hover:bg-gray-50',
                        };
                    @endphp
                    <a href="{{ $shortcut['route'] }}" class="group flex w-full items-center justify-between gap-2 rounded-lg border px-2 py-1.5 shadow-sm transition {{ $shortcutTone }}" @if($shortcut['active']) aria-current="page" style="background: var(--te-button-color, #022a8c);" @endif>
                        <span class="min-w-0">
                            <span class="block truncate text-xs font-black sm:text-sm">{{ $shortcut['label'] }}</span>
                            <span class="block truncate text-[10px] font-semibold sm:text-xs {{ $shortcut['active'] ? 'text-white/80' : 'text-gray-500 group-hover:text-gray-700' }}">{{ $shortcut['description'] }}</span>
                        </span>
                        <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-black sm:text-xs {{ $shortcut['active'] ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800' }}">{{ $shortcut['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>

        @if ($activeFilters->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-1.5 shrink-0">
                @foreach ($activeFilters as $chip)
                    <a href="{{ $chip['route'] }}" class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-2.5 py-0.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">
                        {{ $chip['label'] }}
                        <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Barra de acciones --}}
        <div class="mt-3 shrink-0 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                @if ($shipments->total() > 0)
                    <label class="flex items-center gap-1.5 text-sm font-semibold text-gray-600 cursor-pointer hover:text-gray-900 select-none">
                        <input type="checkbox" id="select-all-shipments" class="rounded border-gray-300 text-blue-700 focus:ring-blue-600" onchange="document.querySelectorAll('.shipment-checkbox').forEach(c=>c.checked=this.checked); updateSelectionLabel()">
                        <span id="selection-label">Todo</span>
                    </label>
                @else
                    <p class="text-sm font-semibold text-gray-700">{{ $shipments->total() }} guia(s)</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($shipments->total() > 0)
                    <form id="bulk-status-form" method="POST" action="{{ route('shipments.bulk-status') }}" class="flex flex-wrap items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="" id="bulk-status-input">
                        <div id="bulk-shipment-ids"></div>
                        <select id="bulk-status-select" class="rounded-lg border-2 border-blue-600 bg-blue-50 px-5 py-2 text-sm font-bold text-blue-800 focus:border-blue-700 focus:ring-2 focus:ring-blue-300 cursor-pointer shadow-sm min-w-[180px] flex-1 sm:flex-none">
                            <option value="">Cambiar estado</option>
                            <option value="created">Por imprimir</option>
                            <option value="on_route">En camino</option>
                            <option value="delivered">Entregada</option>
                            <option value="returned">Devuelta</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                        <button type="button" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm" onclick="submitBulkStatus()">Aplicar</button>
                    </form>
                    <button type="button" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm whitespace-nowrap" onclick="submitBulkPrint()">Imprimir seleccionadas</button>
                @endif
            </div>
        </div>

        {{-- Lista tipo tabla --}}
        <div class="mt-3 flex-1 min-h-0 overflow-y-auto">
            <form id="bulk-print-form" method="POST" action="{{ route('shipments.bulk-print') }}" target="_blank" class="hidden">@csrf</form>

            <style>
                .sh-list-header, .sh-list-row {
                    display: grid;
                    grid-template-columns: 32px minmax(90px,0.9fr) minmax(120px,1.5fr) minmax(70px,0.7fr) minmax(85px,0.85fr) minmax(75px,0.7fr) minmax(65px,0.6fr) 138px;
                    align-items: center;
                    gap: 6px;
                }
                .sh-list-row {
                    min-height: 44px;
                    padding: 6px 12px 6px 9px;
                    border-bottom: 1px solid #f3f4f6;
                    transition: background 0.1s;
                }
                .sh-list-row:hover { background: #f9fafb; }
                .dark .sh-list-row:hover { background: #1e293b; }
                .sh-list-cell {
                    display: flex;
                    align-items: center;
                    min-width: 0;
                }
                .sh-mobile-row { display: none; }

                @media (max-width: 1023px) {
                    .sh-list-header, .sh-desktop-row { display: none; }
                    .sh-mobile-row { display: flex; align-items: center; }
                    .shipment-checkbox, #select-all-shipments { width: 1rem !important; height: 1rem !important; min-width: 1rem !important; min-height: 1rem !important; max-width: 1rem !important; max-height: 1rem !important; flex: 0 0 1rem !important; appearance: auto; }
                }
            </style>

            @if ($shipments->count())
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    {{-- Cabecera --}}
                    <div class="sh-list-header bg-gray-50 px-3 py-2 text-xs font-bold uppercase text-gray-500 border-b border-gray-200">
                        <span class="sh-list-cell"></span>
                        <span class="sh-list-cell">Guia</span>
                        <span class="sh-list-cell">Cliente</span>
                        <span class="sh-list-cell">Destino</span>
                        <span class="sh-list-cell">Estado</span>
                        <span class="sh-list-cell">Valor</span>
                        <span class="sh-list-cell">Fecha</span>
                        <span class="sh-list-cell">Accion</span>
                    </div>

                    {{-- Filas --}}
                    @foreach ($shipments as $shipment)
                        @php
                            $city = $shipment->recipient_city ?: ($shipment->recipient_locality ?: ($shipment->zone ?: '—'));
                            $trackingUrl = route('tracking.show', $shipment->guide_number);
                            [$badgeCls, $badgeColor] = $statusGroupBadge($shipment);
                            $units = !empty($shipment->inventory_snapshot) ? collect($shipment->inventory_snapshot)->sum(fn ($i) => (int) ($i['quantity'] ?? 0)) : 0;
                            $barColor = $badgeColor;
                            $recipientFirstName = trim($shipment->recipient_name) ?: 'cliente';
                            $customerMessage = implode(' ', [
                                "Hola {$recipientFirstName}, te compartimos el seguimiento de tu envio {$shipment->guide_number}.",
                                'Estado actual: '.($statusLabels[$shipment->status] ?? $shipment->status).'.',
                                'Puedes revisarlo aqui: '.$trackingUrl,
                            ]);
                        @endphp
                        {{-- Desktop --}}
                        <div class="sh-list-row sh-desktop-row" style="border-left:3px solid {{ $barColor }}">
                            <div class="sh-list-cell">
                                <input type="checkbox" value="{{ $shipment->id }}" data-shipment-status="{{ $shipment->status }}" class="shipment-checkbox rounded border-gray-300 text-blue-700 w-4 h-4 shrink-0 aspect-square">
                            </div>
                            <div class="sh-list-cell">
                                <p class="text-sm font-semibold text-gray-950 font-mono truncate">{{ $shipment->guide_number }}</p>
                                @if ($units)<span class="ml-1.5 text-xs font-semibold text-emerald-600 shrink-0">({{ $units }})</span>@endif
                            </div>
                            <div class="sh-list-cell" style="flex-direction:column;align-items:stretch">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</p>
                                @if ($shipment->recipient_phone)<p class="text-xs text-gray-500 truncate">{{ $shipment->recipient_phone }}</p>@endif
                            </div>
                            <div class="sh-list-cell">
                                <p class="text-sm font-semibold text-gray-700 truncate">{{ $city }}</p>
                            </div>
                            <div class="sh-list-cell">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold {{ $badgeCls }}">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $barColor }}"></span>
                                    {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                                </span>
                            </div>
                            <div class="sh-list-cell">
                                <p class="text-sm font-semibold text-gray-950">${{ number_format($shipment->collection_value, 0, ',', '.') }}</p>
                            </div>
                            <div class="sh-list-cell" style="flex-direction:column;align-items:stretch">
                                <p class="text-sm font-semibold text-gray-700">{{ $shipment->created_at->format('d/m/y') }}</p>
                                <p class="text-xs text-gray-400">{{ $shipment->created_at->format('H:i') }}</p>
                            </div>
                            <div class="sh-list-cell" style="gap:3px">
                                <button type="button" data-customer-message="{{ $customerMessage }}" onclick="copyCustomerMessage(this)" title="Copiar mensaje al cliente" class="flex items-center justify-center rounded-md border border-blue-200 bg-blue-50 w-8 h-8 text-blue-700 hover:bg-blue-100 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-5l-5 4v-4Z" /></svg>
                                </button>
                                <button type="button" data-tracking-url="{{ $trackingUrl }}" onclick="copyTrackingLink(this)" title="Copiar seguimiento" class="flex items-center justify-center rounded-md border border-gray-300 bg-white w-8 h-8 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 0 1 6.364 6.364l-1.768 1.768a4.5 4.5 0 0 1-6.364 0m1.06-9.192a4.5 4.5 0 0 0-6.364 0L4.35 9.396a4.5 4.5 0 0 0 6.364 6.364" /></svg>
                                </button>
                                <a href="{{ route('shipments.print', $shipment) }}" onclick="event.preventDefault(); window.open(this.href, 'print{{ $shipment->id }}', 'width=800,height=600,scrollbars=yes,resizable=yes')" title="Imprimir" class="flex items-center justify-center rounded-md border border-gray-300 bg-white w-8 h-8 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2m2 4h6a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2zm8-12V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4h10z" /></svg>
                                </a>
                                <a href="{{ route('shipments.show', $shipment) }}" title="Detalle" class="flex items-center justify-center rounded-md bg-blue-700 w-8 h-8 text-white hover:bg-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                </a>
                            </div>
                        </div>
                        {{-- Mobile --}}
                        <div class="sh-list-row sh-mobile-row" style="border-left:3px solid {{ $barColor }}">
                            <div class="sh-list-cell">
                                <input type="checkbox" value="{{ $shipment->id }}" data-shipment-status="{{ $shipment->status }}" class="shipment-checkbox rounded border-gray-300 text-blue-700 w-4 h-4 shrink-0 aspect-square">
                            </div>
                            <div class="sh-list-cell" style="flex-direction:column;align-items:stretch;gap:4px;flex:1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-950 font-mono">{{ $shipment->guide_number }}</p>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold {{ $badgeCls }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $barColor }}"></span>
                                        {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                                    <div>
                                        <p class="font-semibold text-gray-500">Cliente</p>
                                        <p class="font-semibold text-gray-900 truncate">{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</p>
                                        @if ($shipment->recipient_phone)<p class="text-gray-500 truncate">{{ $shipment->recipient_phone }}</p>@endif
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-500">Destino</p>
                                        <p class="font-semibold text-gray-700 truncate">{{ $city }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-500">Valor</p>
                                        <p class="font-semibold text-gray-950">${{ number_format($shipment->collection_value, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-500">Fecha</p>
                                        <p class="font-semibold text-gray-700">{{ $shipment->created_at->format('d/m/y') }}</p>
                                        <p class="text-gray-400">{{ $shipment->created_at->format('H:i') }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-1 sm:grid-cols-4">
                                    <button type="button" data-customer-message="{{ $customerMessage }}" onclick="copyCustomerMessage(this)" class="flex items-center justify-center gap-1 rounded border border-blue-200 bg-blue-50 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-5l-5 4v-4Z" /></svg>
                                        Mensaje
                                    </button>
                                    <button type="button" data-tracking-url="{{ $trackingUrl }}" onclick="copyTrackingLink(this)" class="flex-1 flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 0 1 6.364 6.364l-1.768 1.768a4.5 4.5 0 0 1-6.364 0m1.06-9.192a4.5 4.5 0 0 0-6.364 0L4.35 9.396a4.5 4.5 0 0 0 6.364 6.364" /></svg>
                                        Seguimiento
                                    </button>
                                    <a href="{{ route('shipments.print', $shipment) }}" onclick="event.preventDefault(); window.open(this.href, 'print{{ $shipment->id }}', 'width=800,height=600,scrollbars=yes,resizable=yes')" class="flex-1 flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2m2 4h6a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2zm8-12V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4h10z" /></svg>
                                        Imprimir
                                    </a>
                                    <a href="{{ route('shipments.show', $shipment) }}" class="flex-1 flex items-center justify-center gap-1 rounded bg-blue-700 py-2 text-xs font-bold text-white hover:bg-blue-800">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        Detalle
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-20 text-center px-6 h-full">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    </div>
                    <p class="text-lg font-bold text-gray-950">No hay guias</p>
                    <p class="mt-1 text-sm text-gray-500 max-w-xs">Crea tu primer envio o ajusta los filtros para encontrar lo que buscas.</p>
                    @if (Auth::user()->canCreateShipments())
                        <a href="{{ route('shipments.create') }}" class="mt-6 rounded-xl bg-blue-700 px-6 py-3 text-sm font-bold text-white hover:bg-blue-800 shadow-sm transition-colors">Crear guia</a>
                    @endif
                </div>
            @endif

            @if ($shipments->hasPages())
                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                    {{ $shipments->links('vendor.pagination.compact') }}
                </div>
            @endif
        </div>
    </div>
    <script>
        function submitBulkStatus() {
            const select = document.getElementById('bulk-status-select');
            const status = select.value;
            if (!status) {
                alert('Selecciona un estado.');
                return;
            }

            const ids = selectedShipmentIds();
            if (ids.length === 0) {
                alert('Selecciona al menos una guia.');
                return;
            }

            const container = document.getElementById('bulk-shipment-ids');
            container.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'shipment_ids[]';
                input.value = id;
                container.appendChild(input);
            });

            document.getElementById('bulk-status-input').value = status;
            document.getElementById('bulk-status-form').submit();
        }

        function selectedShipmentIds() {
            const ids = [];
            const seen = new Set();
            document.querySelectorAll('.shipment-checkbox:checked').forEach(c => {
                if (!seen.has(c.value)) {
                    seen.add(c.value);
                    ids.push(c.value);
                }
            });
            return ids;
        }

        function submitBulkPrint() {
            const ids = selectedShipmentIds();
            if (ids.length === 0) {
                alert('Selecciona al menos una guia.');
                return;
            }

            const form = document.getElementById('bulk-print-form');
            form.querySelectorAll('input[name="shipment_ids[]"]').forEach(input => input.remove());
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'shipment_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            form.submit();
        }

        function updateSelectionLabel() {
            const ids = selectedShipmentIds();
            const label = document.getElementById('selection-label');
            label.textContent = ids.length > 0 ? 'Guias seleccionadas ' + ids.length : 'Todo';
        }

        function copyTextToClipboard(text) {
            if (!text) return Promise.resolve();

            return navigator.clipboard
                ? navigator.clipboard.writeText(text)
                : new Promise((resolve) => {
                    const input = document.createElement('textarea');
                    input.value = text;
                    input.setAttribute('readonly', 'readonly');
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                    resolve();
                });
        }

        function showCopiedState(button, label = 'Copiado') {
            copyTextToClipboard(button.dataset.trackingUrl || button.dataset.customerMessage || '').then(() => {
                const original = button.innerHTML;
                button.innerHTML = '<span class="text-xs font-bold">' + label + '</span>';
                button.disabled = true;
                setTimeout(() => {
                    button.innerHTML = original;
                    button.disabled = false;
                }, 1600);
            });
        }

        function copyTrackingLink(button) {
            if (!button.dataset.trackingUrl) return;
            showCopiedState(button, 'Copiado');
        }

        function copyCustomerMessage(button) {
            if (!button.dataset.customerMessage) return;
            showCopiedState(button, 'Mensaje copiado');
        }

        document.querySelectorAll('.shipment-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectionLabel);
        });
    </script>
</x-app-layout>
