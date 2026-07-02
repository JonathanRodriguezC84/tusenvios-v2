@extends('layouts.admin')

@section('title', 'WhatsApp')
@section('eyebrow', 'Configuracion')
@section('page-title', 'Notificaciones WhatsApp')
@section('page-description', 'Configuracion de WhatsApp Business API para notificaciones automaticas.')

@section('content')
    <section class="admin-card p-5 max-w-2xl">
        <h3 class="text-sm font-black uppercase text-gray-500">Estado</h3>
        <div class="mt-4 grid gap-3">
            @php
                $enabled = config('services.whatsapp.enabled');
                $hasToken = !empty(config('services.whatsapp.token'));
                $hasPhoneId = !empty(config('services.whatsapp.phone_number_id'));
                $isReady = $enabled && $hasToken && $hasPhoneId;
            @endphp

            <div class="flex items-center gap-3 rounded-lg border {{ $enabled ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }} p-4">
                <span class="text-2xl">{{ $enabled ? '🟢' : '⚪' }}</span>
                <div>
                    <p class="font-semibold text-gray-950">WhatsApp {{ $enabled ? 'Activado' : 'Desactivado' }}</p>
                    <p class="text-xs text-gray-500">Variable WHATSAPP_ENABLED en .env</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-lg border {{ $hasToken ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-4">
                <span class="text-2xl">{{ $hasToken ? '🟢' : '🟡' }}</span>
                <div>
                    <p class="font-semibold text-gray-950">Token de acceso {{ $hasToken ? 'configurado' : 'pendiente' }}</p>
                    <p class="text-xs text-gray-500">Variable WHATSAPP_TOKEN en .env</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-lg border {{ $hasPhoneId ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-4">
                <span class="text-2xl">{{ $hasPhoneId ? '🟢' : '🟡' }}</span>
                <div>
                    <p class="font-semibold text-gray-950">Phone Number ID {{ $hasPhoneId ? 'configurado' : 'pendiente' }}</p>
                    <p class="text-xs text-gray-500">Variable WHATSAPP_PHONE_ID en .env</p>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-card p-5 max-w-2xl mt-4">
        <h3 class="text-sm font-black uppercase text-gray-500">Notificaciones activas</h3>
        <p class="text-sm text-gray-500 mt-1">Cuando WhatsApp esta activado, se envian estas notificaciones automaticamente.</p>
        <div class="mt-4 divide-y divide-gray-100">
            @foreach ([
                ['evento' => 'Guia creada', 'destino' => 'Cliente final', 'template' => 'shipment_created', 'desc' => 'Numero de guia, ciudad destino y link de rastreo'],
                ['evento' => 'Guia en camino', 'destino' => 'Cliente final', 'template' => 'shipment_in_transit', 'desc' => 'Numero de guia y ciudad actual'],
                ['evento' => 'Guia entregada', 'destino' => 'Cliente final', 'template' => 'shipment_delivered', 'desc' => 'Confirmacion de entrega con numero de guia'],
                ['evento' => 'Stock bajo', 'destino' => 'Admin del negocio', 'template' => 'Texto libre', 'desc' => 'Alerta cuando un producto llega al stock minimo'],
                ['evento' => 'Resumen diario', 'destino' => 'Admin del negocio', 'template' => 'Texto libre', 'desc' => 'Guias creadas, entregadas, en transito e ingresos del dia'],
            ] as $n)
                <div class="py-3 flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-gray-950">{{ $n['evento'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $n['desc'] }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-3xs font-semibold text-gray-600">{{ $n['destino'] }}</span>
                        <p class="text-3xs text-gray-400 mt-0.5">{{ $n['template'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="admin-card p-5 max-w-2xl mt-4">
        <h3 class="text-sm font-black uppercase text-gray-500">Como activar</h3>
        <ol class="mt-3 grid gap-2 text-sm text-gray-600 list-decimal list-inside">
            <li>Crear una aplicacion en <a href="https://developers.facebook.com" target="_blank" class="text-blue-700 font-semibold">Meta for Developers</a></li>
            <li>Obtener el <strong>token de acceso</strong> y el <strong>Phone Number ID</strong></li>
            <li>Agregar al <code>.env</code>:
                <div class="mt-1 rounded bg-gray-100 p-2 font-mono text-xs text-gray-700">
                    WHATSAPP_ENABLED=true<br>
                    WHATSAPP_TOKEN=EAAx...<br>
                    WHATSAPP_PHONE_ID=123456789
                </div>
            </li>
            <li>Crear las plantillas (<code>shipment_created</code>, <code>shipment_in_transit</code>, <code>shipment_delivered</code>) en Meta</li>
        </ol>
    </section>
@endsection
