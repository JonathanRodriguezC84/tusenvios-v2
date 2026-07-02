@extends('layouts.admin')

@section('title', 'Resumen')
@section('eyebrow', 'Administracion')
@section('page-title', 'Resumen general')
@section('page-description', 'Indicadores rapidos de clientes, pagos y uso de la plataforma.')

@section('content')
    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">Clientes</p>
            <p class="mt-2 text-3xl font-black text-gray-950">{{ $metrics['clients'] }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $metrics['active_clients'] }} activos</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">Ingresos (MRR)</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-700">${{ number_format($revenue['mrr'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $revenue['trial_conversion'] }} en prueba</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">Guias del mes</p>
            <p class="mt-2 text-3xl font-black text-gray-950">{{ $metrics['shipments_month'] }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $metrics['users'] }} usuarios</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-black uppercase text-gray-500">Planes activos</p>
            <div class="mt-2 grid gap-1">
                @foreach ($planCounts as $planName => $count)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold {{ $planName === 'Fundador' ? 'text-amber-700' : 'text-gray-700' }}">{{ $planName }}</span>
                        <span class="font-black text-gray-950">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mt-5 grid gap-5 xl:grid-cols-2">
        <div class="admin-card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <p class="text-xs font-black uppercase text-gray-500">Clientes recientes</p>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($recentClients as $client)
                    <article class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate font-black text-gray-950">{{ $client->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $client->email ?: 'Sin correo' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-black text-gray-950">{{ $client->currentSubscription?->plan?->name ?: 'Sin plan' }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $client->shipments_count }} guias</p>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">Aun no hay clientes.</p>
                @endforelse
            </div>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <p class="text-xs font-black uppercase text-gray-500">Proximos pagos</p>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($dueSubscriptions as $subscription)
                    <article class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black text-gray-950">{{ $subscription->tenant?->name ?: 'Cliente eliminado' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $subscription->plan?->name ?: 'Sin plan' }}</p>
                            </div>
                            <p class="text-sm font-black text-gray-950 shrink-0">{{ $subscription->next_payment_at?->format('d/m/Y') ?: 'Sin fecha' }}</p>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">No hay pagos pendientes.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
