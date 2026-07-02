@extends('layouts.admin')

@section('title', 'Actividad')
@section('eyebrow', 'Monitoreo')
@section('page-title', 'Actividad de guias')
@section('page-description', 'Ultimas guias creadas en la plataforma.')

@section('page-actions')
    <a href="{{ route('admin.activity.export', request()->query()) }}" class="admin-outline-link">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Exportar CSV
    </a>
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    <section class="admin-card p-4">
        <form method="GET" action="{{ route('admin.activity') }}" class="grid gap-3 lg:grid-cols-[1fr_170px_170px_140px_140px_auto]">
            <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Buscar guia, cliente o destinatario" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            <select name="tenant_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todas las tiendas</option>
                @foreach ($tenants as $t)
                    <option value="{{ $t->id }}" @selected(($filters['tenant_id'] ?? '') == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todos los estados</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            <div class="flex gap-2">
                <button class="admin-btn">Buscar</button>
                <a href="{{ route('admin.activity') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700">Limpiar</a>
            </div>
        </form>
    </section>

    <div class="admin-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-xs font-black uppercase text-gray-500">
                    <th class="px-4 py-3">Guia</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Destinatario</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($shipments as $shipment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('shipments.show', $shipment) }}" target="_blank" class="font-bold text-blue-800 hover:text-blue-900">{{ $shipment->guide_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $shipment->tenant?->name ?? 'Sin cliente' }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</p>
                            @if ($shipment->recipient_phone)<p class="text-xs text-gray-500">{{ $shipment->recipient_phone }}</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $shipment->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : ($shipment->status === 'cancelled' ? 'bg-gray-100 text-gray-600' : ($shipment->status === 'failed_delivery' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                                {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $shipment->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500">No hay actividad para los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $shipments->links() }}
    </div>
@endsection
