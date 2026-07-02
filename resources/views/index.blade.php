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

@endphp

<x-app-layout>
    <style>
        .shipment-simple-head,
        .shipment-simple-row {
            display: grid;
            grid-template-columns: minmax(135px, .95fr) minmax(220px, 1.75fr) minmax(105px, .8fr) minmax(130px, .9fr) minmax(115px, .85fr) minmax(95px, .75fr) minmax(86px, .65fr) minmax(58px, .45fr);
            align-items: center;
            gap: 12px;
        }

        .shipment-simple-row {
            min-height: 62px;
            padding: 12px 20px;
        }

        .shipment-simple-row:hover {
            background: #f9fafb;
        }

        .shipment-mobile-label {
            display: none;
        }

        @media (max-width: 1023px) {
            .shipment-simple-head {
                display: none;
            }

            .shipment-simple-row {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 14px 16px;
            }

            .shipment-mobile-label {
                display: block;
                margin-bottom: 2px;
                font-size: 11px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
            }

            .shipment-simple-actions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Historial de envios</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Mis guias</h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Busca, imprime y revisa tus etiquetas.</p>
            </div>

            @if (Auth::user()->canCreateShipments())
                <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-md bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Crear guia
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('shipments.index') }}" class="grid gap-3 lg:grid-cols-[1fr_180px_170px_auto]">
                    <input
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        type="search"
                        placeholder="Buscar por guia, cliente, telefono o direccion"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                    >
                    <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        <option value="">Todos</option>
                        <option value="created" @selected(($filters['status'] ?? '') === 'created')>Por imprimir</option>
                        <option value="printed" @selected(($filters['status'] ?? '') === 'printed')>Impresa</option>
                        <option value="on_route" @selected(($filters['status'] ?? '') === 'on_route')>En camino</option>
                        <option value="delivered" @selected(($filters['status'] ?? '') === 'delivered')>Entregada</option>
                        <option value="failed_delivery" @selected(($filters['status'] ?? '') === 'failed_delivery')>Con novedad</option>
                        <option value="return_pending" @selected(($filters['status'] ?? '') === 'return_pending')>Devuelve</option>
                    </select>
                    <input
                        name="date"
                        value="{{ $filters['date'] ?? '' }}"
                        type="date"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                    >
                    <div class="flex gap-2">
                        <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                            Buscar
                        </button>
                        <a href="{{ route('shipments.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                            Limpiar
                        </a>
                    </div>
                </form>
            </section>

            <section class="mt-5 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Resultado</p>
                        <h3 class="text-lg font-semibold text-gray-950">{{ $shipments->total() }} guias</h3>
                        <p class="mt-1 text-sm text-gray-500">Mostrando {{ $shipments->firstItem() ?? 0 }} - {{ $shipments->lastItem() ?? 0 }}</p>
                    </div>
                </div>

                <div class="shipment-simple-head border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-bold uppercase text-gray-500">
                    <span>Fecha creacion</span>
                    <span>Nombre</span>
                    <span>Ciudad</span>
                    <span>Guia</span>
                    <span>Estado</span>
                    <span>Recaudo</span>
                    <span>Imprimir</span>
                    <span>Ver</span>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse ($shipments as $shipment)
                        @php
                            $citySummary = $shipment->recipient_city ?: ($shipment->recipient_locality ?: ($shipment->zone ?: 'Sin ciudad'));
                        @endphp
                        <article class="shipment-simple-row">
                            <div>
                                <p class="shipment-mobile-label">Fecha creacion</p>
                                <p class="text-sm font-semibold text-gray-700">{{ $shipment->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="shipment-mobile-label">Nombre</p>
                                <p class="truncate text-sm font-semibold uppercase text-gray-950">{{ $shipment->recipient_name }}</p>
                            </div>

                            <div>
                                <p class="shipment-mobile-label">Ciudad</p>
                                <p class="truncate text-sm font-semibold uppercase text-gray-700">{{ $citySummary }}</p>
                            </div>

                            <div>
                                <p class="shipment-mobile-label">Guia</p>
                                <p class="truncate text-sm font-semibold text-gray-950">{{ $shipment->guide_number }}</p>
                            </div>

                            <div>
                                <p class="shipment-mobile-label">Estado</p>
                                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$shipment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                                </span>
                            </div>

                            <div>
                                <p class="shipment-mobile-label">Recaudo</p>
                                <p class="text-sm font-semibold text-gray-950">${{ number_format($shipment->collection_value, 0, ',', '.') }}</p>
                            </div>

                            <div class="shipment-simple-actions">
                                <a href="{{ route('shipments.print', $shipment) }}" target="_blank" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    Imprimir
                                </a>
                            </div>

                            <div class="shipment-simple-actions">
                                <a href="{{ route('shipments.show', $shipment) }}" class="inline-flex items-center justify-center rounded-md bg-blue-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                                    Ver
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <p class="text-lg font-black text-gray-950">No hay guias para mostrar.</p>
                            <p class="mt-1 text-sm text-gray-500">Crea una guia o cambia los filtros de busqueda.</p>
                            @if (Auth::user()->canCreateShipments())
                                <a href="{{ route('shipments.create') }}" class="mt-4 inline-flex rounded-md bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                                    Crear guia
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $shipments->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
