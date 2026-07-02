<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Mensajeria</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Mi ruta</h2>
            </div>
            <a href="{{ route('scan.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Escanear guia
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4">
                @forelse ($shipments as $shipment)
                    <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <a href="{{ route('shipments.show', $shipment) }}" class="text-lg font-bold text-blue-700 hover:text-blue-900 hover:underline">
                                    {{ $shipment->guide_number }}
                                </a>
                                <p class="mt-1 text-sm text-gray-500">{{ $shipment->affiliatedCompany?->name ?? 'Tus Envios' }}</p>
                                <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                    <div>
                                        <p class="text-gray-500">Destinatario</p>
                                        <p class="font-semibold text-gray-950">{{ $shipment->recipient_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Telefono</p>
                                        <p class="font-semibold text-gray-950">{{ $shipment->recipient_phone }}</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-gray-500">Direccion</p>
                                        <p class="font-semibold text-gray-950">{{ $shipment->recipient_address }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Zona</p>
                                        <p class="font-semibold text-gray-950">{{ $shipment->recipient_locality ?: ($shipment->zone ?? 'Sin zona') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Intentos</p>
                                        <p class="font-semibold text-gray-950">{{ $shipment->delivery_attempts }}</p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('courier-route.update', $shipment) }}" class="grid w-full gap-3 lg:max-w-xs">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                                    <option value="on_route" @selected($shipment->status === 'on_route')>En ruta</option>
                                    <option value="delivered">Entregado</option>
                                    <option value="failed_delivery">No entregado</option>
                                    <option value="rescheduled">Reprogramado</option>
                                    <option value="return_pending">Devuelto a bodega</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                                <textarea name="notes" rows="2" placeholder="Observaciones" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600"></textarea>
                                <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                                    Actualizar estado
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-gray-200 bg-white p-8 text-center text-gray-500 shadow-sm">
                        No tienes guias pendientes en ruta.
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

