<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Centro de ayuda" description="Respuestas rapidas a las preguntas mas frecuentes.">
            <x-slot name="eyebrow">Soporte</x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-3 sm:p-5 lg:p-6">
        @if ($whatsapp)
            <div class="mb-4 flex flex-col gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2Z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-black text-gray-950">Necesitas ayuda?</h2>
                        <p class="text-xs font-semibold text-emerald-800">Escribenos por WhatsApp y te respondemos a la brevedad.</p>
                    </div>
                </div>
                <a href="https://wa.me/{{ $whatsapp }}?text={{ $waText }}" target="_blank" rel="noopener"
                   class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2Z"/></svg>
                    Hablar por WhatsApp
                </a>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="text-xs font-black uppercase tracking-wider text-gray-900">Preguntas frecuentes</h2>

            <div class="mt-3 divide-y divide-gray-100">
                @php
                    $faqs = [
                        ['q' => 'Como creo una guia de envio?', 'a' => 'Entra a "Nueva guia" desde el menu, diligencia los datos del destinatario y los productos, confirma el valor a recaudar y guarda. La guia queda lista para imprimir.'],
                        ['q' => 'Como imprimo una guia o etiqueta?', 'a' => 'Desde "Mis guias", busca la guia que necesitas y presiona el boton de imprimir de la fila. Tambien puedes usar la cola de impresion desde el acceso rapido "Por imprimir".'],
                        ['q' => 'Que significa cada estado de una guia?', 'a' => 'Por imprimir: aun no se imprime. En camino: la guia esta en operacion. Entregada: llego al destinatario. Devuelta: no se pudo entregar y regreso. Cancelada: fue anulada.'],
                        ['q' => 'El destinatario quiere rastrear su envio, que hago?', 'a' => 'Compartele el numero de guia. Puede rastrearlo desde la pagina publica de rastreo de Tus Envios sin necesidad de crear cuenta.'],
                        ['q' => 'Como agrego productos rapidos?', 'a' => 'En "Productos rapidos" guarda lo que mas envias (nombre, costo y precio de venta). Al crear una guia los encontraras en un clic, sin repetir datos.'],
                        ['q' => 'Como hago seguimiento de mis ingresos?', 'a' => 'El Dashboard resume guias creadas, entregadas, costo de productos, ingresos y utilidad. Puedes filtrar por hoy, ultimos 7 o 30 dias.'],
                        ['q' => 'El pago de mi plan no aparece registrado.', 'a' => 'Si pagaste por el enlace de pago y aun no se refleja, escribenos por WhatsApp con el comprobante y lo validamos a la brevedad.'],
                        ['q' => 'Como actualizo los datos de mi tienda o logo?', 'a' => 'Entra a "Configuracion > Tienda" para cambiar nombre, logo, colores y datos de contacto de tu marca.'],
                    ];
                @endphp

                @foreach ($faqs as $faq)
                    <div class="py-3">
                        <p class="text-sm font-bold text-gray-900">{{ $faq['q'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-gray-600">{{ $faq['a'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-gray-950">No encontraste lo que buscabas?</h2>
                <p class="mt-0.5 text-xs font-semibold text-gray-600">Envianos tu consulta por WhatsApp y te ayudamos directamente.</p>
            </div>
            @if ($whatsapp)
                <a href="https://wa.me/{{ $whatsapp }}?text={{ $waText }}" target="_blank" rel="noopener"
                   class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">
                    Contactar soporte
                </a>
            @endif
        </div>
    </div>
</x-app-layout>