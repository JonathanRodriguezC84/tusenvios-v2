<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Clientes frecuentes" description="Compradores que ya usaste en guias anteriores para crear nuevos envios mas rapido.">
            <x-slot name="eyebrow">Agenda comercial</x-slot>
            <x-slot name="actions">
                <a href="{{ route('recipients.export', request()->only(['search'])) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">Exportar Excel</a>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8">
        <section class="grid grid-cols-2 gap-2 md:grid-cols-2 xl:grid-cols-4 md:gap-4">
            <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm sm:p-5">
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-500 sm:text-xs">Clientes guardados</p>
                <p class="mt-2 text-2xl font-black te-stat-value text-gray-950 sm:text-3xl">{{ $summary['total'] }}</p>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 shadow-sm sm:p-5">
                <p class="text-[10px] font-black uppercase tracking-wider text-blue-700 sm:text-xs">Compradores repetidos</p>
                <p class="mt-2 text-2xl font-black te-stat-value text-blue-900 sm:text-3xl">{{ $summary['repeat'] }}</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 shadow-sm sm:p-5">
                <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700 sm:text-xs">Usos acumulados</p>
                <p class="mt-2 text-2xl font-black te-stat-value text-emerald-900 sm:text-3xl">{{ $summary['uses'] }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm sm:p-5">
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-500 sm:text-xs">Ciudad mas frecuente</p>
                <p class="mt-2 truncate text-2xl font-black te-stat-value text-gray-950 sm:text-3xl">{{ $summary['topCity'] ?: 'Sin datos' }}</p>
            </div>
        </section>

        <section class="mt-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('recipients.index') }}" class="flex flex-col gap-2 sm:flex-row">
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Buscar por nombre, telefono, ciudad o direccion..." class="min-h-10 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Buscar</button>
                <a href="{{ route('recipients.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50">Limpiar</a>
            </form>
        </section>

        <section class="mt-5 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            @if ($recipients->count())
                <div class="hidden grid-cols-[repeat(4,minmax(0,1fr))_90px] gap-2 border-b border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-gray-500 lg:grid">
                    <span>Cliente</span>
                    <span>Telefono</span>
                    <span>Departamento</span>
                    <span>Ciudad</span>
                    <span class="text-center">Crear</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($recipients as $recipient)
                        @php
                            $fullName = trim($recipient->name.' '.$recipient->lastname);
                            $cityName = $recipient->city ?: $recipient->locality;
                            $departmentName = $cityToDepartment[$cityName] ?? null;
                        @endphp
                        <article class="grid gap-2 px-5 py-2 lg:grid-cols-[repeat(4,minmax(0,1fr))_90px] lg:items-center">
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 lg:hidden">Cliente</p>
                                <p class="truncate text-sm font-black text-gray-950">{{ $fullName ?: 'Cliente sin nombre' }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 lg:hidden">Telefono</p>
                                <p class="truncate text-sm font-bold text-gray-800">{{ $recipient->phone ?: 'Sin telefono' }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 lg:hidden">Departamento</p>
                                <p class="truncate text-sm font-semibold text-gray-800">{{ $departmentName ?: 'Sin dato' }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 lg:hidden">Ciudad</p>
                                <p class="truncate text-sm font-semibold text-gray-800">{{ $cityName ?: 'Sin dato' }}</p>
                            </div>
                            <div>
                                @if (Auth::user()->canCreateShipments())
                                    <a href="{{ route('shipments.create', ['recipient' => $recipient->id]) }}" class="inline-flex w-full items-center justify-center rounded-md bg-blue-700 px-2.5 py-1.5 text-xs font-black text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($recipients->hasPages())
                    <div class="border-t border-gray-200 px-5 py-3">{{ $recipients->links('vendor.pagination.compact') }}</div>
                @endif
            @else
                <div class="px-6 py-16 text-center">
                    <p class="text-lg font-black text-gray-950">Aun no hay clientes frecuentes</p>
                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold text-gray-500">Cuando crees guias, el sistema ira guardando destinatarios para ayudarte a repetir datos rapidamente.</p>
                    @if (Auth::user()->canCreateShipments())
                        <a href="{{ route('shipments.create') }}" class="mt-5 inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Crear primera guia</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
