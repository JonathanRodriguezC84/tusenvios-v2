@php
    $statusStyles = [
        'created' => 'bg-gray-100 text-gray-800',
        'printed' => 'bg-gray-100 text-gray-800',
        'in_warehouse' => 'bg-amber-100 text-amber-800',
        'in_sorting' => 'bg-amber-100 text-amber-800',
        'assigned' => 'bg-indigo-100 text-indigo-800',
        'on_route' => 'bg-blue-100 text-blue-800',
        'delivered' => 'bg-emerald-100 text-emerald-800',
        'failed_delivery' => 'bg-blue-100 text-blue-900',
        'rescheduled' => 'bg-purple-100 text-purple-800',
        'return_pending' => 'bg-orange-100 text-orange-800',
        'returned' => 'bg-blue-100 text-blue-900',
        'cancelled' => 'bg-gray-200 text-gray-800',
    ];

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

    $timelineSteps = [
        ['label' => 'Impresa', 'icon' => 'M7 7h10M7 11h10M7 15h6'],
        ['label' => 'Preparada', 'icon' => 'M20 7 12 3 4 7v10l8 4 8-4V7ZM12 13V3'],
        ['label' => 'En camino', 'icon' => 'M3 7h11v8H3V7Zm11 3h3l3 3v2h-6v-5ZM6 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z'],
        ['label' => 'Entregada', 'icon' => 'm5 13 4 4L19 7'],
    ];

    $timelinePositions = [
        'created' => 0,
        'printed' => 1,
        'in_warehouse' => 2,
        'in_sorting' => 2,
        'assigned' => 3,
        'on_route' => 3,
        'failed_delivery' => 3,
        'rescheduled' => 3,
        'return_pending' => 3,
        'returned' => 3,
        'delivered' => 4,
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Tu negocio hoy</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Tus Envios</h2>
            </div>

            @if (Auth::user()->canCreateShipments())
                <a href="{{ route('shipments.create') }}" class="inline-flex h-11 items-center justify-center rounded-md bg-blue-700 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Crear guia
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($onboarding['show'])
                <section class="mb-5 rounded-lg border border-blue-100 bg-blue-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-blue-700">Primeros pasos</p>
                            <h3 class="mt-1 text-lg font-black text-gray-950">Deja lista tu cuenta para imprimir</h3>
                        </div>
                        <p class="rounded-full bg-white px-4 py-2 text-sm font-bold text-blue-800">{{ $onboarding['completed'] }}/{{ $onboarding['total'] }} completo</p>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach ($onboarding['steps'] as $step)
                            <a href="{{ $step['route'] }}" class="rounded-md border {{ $step['done'] ? 'border-emerald-200 bg-white' : 'border-blue-100 bg-white' }} p-4">
                                <p class="text-sm font-black text-gray-950">{{ $step['done'] ? 'Listo' : $loop->iteration.'. '.$step['label'] }}</p>
                                <p class="mt-1 text-sm leading-5 text-gray-600">{{ $step['description'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('shipments.index', ['status' => 'created']) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:bg-gray-50">
                    <p class="text-sm font-semibold text-gray-500">Por imprimir</p>
                    <p class="mt-3 text-3xl font-black text-gray-950">{{ $metrics['pending_print'] }}</p>
                </a>
                <a href="{{ route('shipments.index', ['status' => 'on_route']) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:bg-gray-50">
                    <p class="text-sm font-semibold text-gray-500">En camino</p>
                    <p class="mt-3 text-3xl font-black text-gray-950">{{ $metrics['on_route'] }}</p>
                </a>
                <a href="{{ route('shipments.index', ['status' => 'delivered']) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:bg-gray-50">
                    <p class="text-sm font-semibold text-gray-500">Entregadas hoy</p>
                    <p class="mt-3 text-3xl font-black text-gray-950">{{ $metrics['delivered_today'] }}</p>
                </a>
                <a href="{{ route('shipments.index', ['status' => 'failed_delivery']) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:bg-gray-50">
                    <p class="text-sm font-semibold text-gray-500">Con novedad</p>
                    <p class="mt-3 text-3xl font-black text-gray-950">{{ $metrics['issues'] }}</p>
                </a>
            </section>

            <!-- DASHBOARD_RESUMEN_PRODUCTOS_UNIDADES_V01 -->
            <section class="mt-5 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <style>
                        .recent-shipments-head,
                        .recent-shipments-row {
                            display: grid;
                            grid-template-columns: minmax(135px, .95fr) minmax(170px, 1.2fr) minmax(120px, .8fr) minmax(230px, 1.65fr) minmax(90px, .55fr) minmax(105px, .75fr) minmax(120px, .85fr);
                            gap: 14px;
                            align-items: start;
                        }

                        .recent-shipments-mobile-label {
                            display: none;
                        }

                        @media (max-width: 767px) {
                            .recent-shipments-head {
                                display: none;
                            }

                            .recent-shipments-row {
                                display: grid;
                                grid-template-columns: 1fr;
                                gap: 12px;
                            }

                            .recent-shipments-mobile-label {
                                display: block;
                            }
                        }
                    </style>

                    <div class="flex flex-col gap-3 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">Resumen</p>
                            <h3 class="text-lg font-semibold text-gray-950">Tus envios recientes</h3>
                        </div>
                        <a href="{{ route('shipments.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Ver todas
                        </a>
                    </div>

                    <div class="recent-shipments-head border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-bold uppercase text-gray-500">
                        <span>Fecha creacion</span>
                        <span>Nombre</span>
                        <span>Estado</span>
                        <span>Producto</span>
                        <span>Unidades</span>
                        <span>Precio</span>
                        <span>Guia</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse ($shipments as $shipment)
                            @php
                                $productSummary = trim((string) ($shipment->content_description ?: 'Sin producto'));
                                $rawProductItems = array_values(array_filter(preg_split('/\s*\+\s*/', $productSummary) ?: [], fn ($item) => trim($item) !== ''));
                                $productRows = [];

                                foreach ($rawProductItems as $rawProductItem) {
                                    $rawProductItem = trim($rawProductItem);
                                    $productName = preg_replace('/\s*-\s*\$?\s*[\d\.,]+(?:\s*COP)?\s*$/i', '', $rawProductItem) ?? $rawProductItem;
                                    $units = 1;

                                    if (preg_match('/\bX\s*(\d+)\b/i', $productName, $matches)) {
                                        $units = (int) $matches[1];
                                        $productName = preg_replace('/\s*X\s*\d+\b/i', '', $productName) ?? $productName;
                                    }

                                    $productRows[] = [
                                        'name' => trim($productName) !== '' ? trim($productName) : 'Sin producto',
                                        'units' => trim($productSummary) === 'Sin producto' ? 0 : $units,
                                    ];
                                }

                                if ($productRows === []) {
                                    $productRows[] = ['name' => 'Sin producto', 'units' => 0];
                                }

                                $priceValue = $shipment->collection_value ?: ($shipment->shipping_value ?: 0);
                            @endphp
                            <a href="{{ route('shipments.show', $shipment) }}" class="recent-shipments-row px-5 py-4 hover:bg-gray-50">
                                <div>
                                    <p class="recent-shipments-mobile-label text-xs font-bold uppercase text-gray-500">Fecha creacion</p>
                                    <p class="truncate text-sm font-normal text-gray-700">{{ optional($shipment->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="recent-shipments-mobile-label text-xs font-bold uppercase text-gray-500">Nombre</p>
                                    <p class="truncate text-sm font-normal uppercase text-gray-950">{{ $shipment->recipient_name }}</p>
                                </div>
                                <div>
                                    <p class="recent-shipments-mobile-label text-xs font-bold uppercase text-gray-500">Estado</p>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$shipment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                                    </span>
                                </div>
                                <div>
                                    <p class="recent-shipments-mobile-label text-xs font-semibold uppercase text-gray-500">Producto</p>
                                    <div class="grid gap-1 text-sm font-normal text-gray-700">
                                        @foreach ($productRows as $productRow)
                                            <p class="truncate">{{ $productRow['name'] }}</p>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <p class="recent-shipments-mobile-label text-xs font-semibold uppercase text-gray-500">Unidades</p>
                                    <div class="grid gap-1 text-sm font-normal text-gray-950">
                                        @foreach ($productRows as $productRow)
                                            <p class="truncate">{{ $productRow['units'] }}</p>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <p class="recent-shipments-mobile-label text-xs font-bold uppercase text-gray-500">Precio</p>
                                    <p class="truncate text-sm font-normal text-gray-950">${{ number_format((float) $priceValue, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="recent-shipments-mobile-label text-xs font-bold uppercase text-gray-500">Guia</p>
                                    <p class="truncate text-sm font-normal text-gray-950">{{ $shipment->guide_number }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center">
                                <p class="font-semibold text-gray-950">Todavia no hay guias.</p>
                                <p class="mt-1 text-sm text-gray-500">Crea la primera guia para empezar a ver actividad aqui.</p>
                                @if (Auth::user()->canCreateShipments())
                                    <a href="{{ route('shipments.create') }}" class="mt-4 inline-flex rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                                        Crear guia
                                    </a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </section>
        </div>
    </div>
</x-app-layout>
