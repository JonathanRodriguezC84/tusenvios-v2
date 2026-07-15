@php
    $statusConfig = [
        'created' => ['label' => 'Guia creada', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => '#6b7280', 'bg' => '#f3f4f6', 'border' => '#d1d5db'],
        'printed' => ['label' => 'Etiqueta impresa', 'icon' => 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z', 'color' => '#6b7280', 'bg' => '#f3f4f6', 'border' => '#d1d5db'],
        'in_warehouse' => ['label' => 'Recibido en bodega', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 10l8 4m0-10v10', 'color' => '#d97706', 'bg' => '#fef3c7', 'border' => '#fcd34d'],
        'in_sorting' => ['label' => 'En clasificacion', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 10l8 4m0-10v10', 'color' => '#d97706', 'bg' => '#fef3c7', 'border' => '#fcd34d'],
        'assigned' => ['label' => 'Asignado para entrega', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 7a4 4 0 100-8 4 4 0 000 8zm6 14v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2', 'color' => '#4f46e5', 'bg' => '#eef2ff', 'border' => '#a5b4fc'],
        'on_route' => ['label' => 'En camino', 'icon' => 'M13 16h-1v-4h-1m4 0a4 4 0 11-8 0 4 4 0 018 0z', 'color' => '#2563eb', 'bg' => '#dbeafe', 'border' => '#93c5fd'],
        'delivered' => ['label' => 'Entregado', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#059669', 'bg' => '#d1fae5', 'border' => '#6ee7b7'],
        'failed_delivery' => ['label' => 'Novedad en entrega', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z', 'color' => '#dc2626', 'bg' => '#fee2e2', 'border' => '#fca5a5'],
        'rescheduled' => ['label' => 'Reprogramado', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#7c3aed', 'bg' => '#ede9fe', 'border' => '#c4b5fd'],
        'return_pending' => ['label' => 'En devolucion', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'color' => '#ea580c', 'bg' => '#fff7ed', 'border' => '#fdba74'],
        'returned' => ['label' => 'Devuelto', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'color' => '#6b7280', 'bg' => '#f3f4f6', 'border' => '#d1d5db'],
        'cancelled' => ['label' => 'Cancelado', 'icon' => 'M6 18L18 6M6 6l12 12', 'color' => '#6b7280', 'bg' => '#f3f4f6', 'border' => '#d1d5db'],
    ];

    $flow = ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route', 'delivered'];
    $currentStatus = $shipment->status;
    $currentConfig = $statusConfig[$currentStatus] ?? $statusConfig['created'];
    $isDelivered = $currentStatus === 'delivered';
    $hasIssue = in_array($currentStatus, ['failed_delivery', 'rescheduled', 'return_pending', 'returned', 'cancelled']);

    $brandOwner = $shipment->affiliatedCompany ?: $shipment->tenant;
    $brandData = $brandOwner?->brandData();
    $brandColor = '#022a8c';
    if ($brandData && is_string(($brandData['color'] ?? null)) && preg_match('/^#[0-9A-Fa-f]{6}$/', $brandData['color'])) {
        $brandColor = strtolower($brandData['color']);
    }
    $brandName = $brandOwner?->name ?? 'Tus Envios';
    $brandLogo = $brandOwner?->logo_path ? \Illuminate\Support\Facades\Storage::url($brandOwner->logo_path) : asset('images/logotusenvios.png') . '?v=20260521';
    $brandMessage = $brandData['message'] ?? 'Gracias por tu compra.';
    $brandWhatsapp = preg_replace('/\D+/', '', (string) ($brandData['whatsapp'] ?? $brandData['phone'] ?? ''));
    $trackingUrl = route('tracking.show', $shipment->guide_number);
    $whatsappText = rawurlencode("Hola, necesito ayuda con mi envio {$shipment->guide_number}. {$trackingUrl}");
    $whatsappUrl = $brandWhatsapp ? "https://wa.me/{$brandWhatsapp}?text={$whatsappText}" : null;

    $eventsByStatus = $shipment->events->groupBy('status');

    $estimatedDate = $shipment->estimated_delivery_date
        ? \Carbon\Carbon::parse($shipment->estimated_delivery_date)->locale('es')->isoFormat('D [de] MMMM')
        : null;

    $senderCity = $shipment->sender_locality ?? 'Bogota';
    $recipientCity = $shipment->recipient_city ?? $shipment->recipient_locality ?? '';
@endphp

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $brandColor }}">
    <title>{{ $shipment->guide_number }} | {{ $brandName }}</title>
    <meta name="description" content="Rastrea tu envio {{ $shipment->guide_number }} en {{ $brandName }}">
    <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=20260521v15">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=20260521v15">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .te-timeline-track { position: relative; padding-left: 36px; }
        .te-timeline-track::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #e5e7eb; }
        .te-timeline-track .te-step { position: relative; padding-bottom: 24px; }
        .te-timeline-track .te-step:last-child { padding-bottom: 0; }
        .te-timeline-track .te-step-dot { position: absolute; left: -36px; top: 2px; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .te-timeline-track .te-step-dot svg { width: 16px; height: 16px; }
        .te-timeline-track .te-step.is-active .te-step-dot { box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }
        .te-timeline-track .te-step.is-pending .te-step-dot { background: #f9fafb; border: 2px solid #d1d5db; }
        .te-timeline-track .te-step.is-pending .te-step-dot svg { color: #9ca3af; }
        .te-timeline-row { display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 16px; }
        @media (max-width: 639px) { .te-timeline-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-950">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('tracking.index') }}" class="flex items-center gap-3">
                <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="h-11 w-11 rounded-md object-contain">
                <span>
                    <span class="block text-base font-black leading-none">{{ $brandName }}</span>
                    <span class="block text-xs font-semibold text-gray-500">Seguimiento de envios</span>
                </span>
            </a>
            <a href="{{ route('tracking.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Nueva consulta
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
        {{-- Header card --}}
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 sm:px-6 sm:py-5" style="background: {{ $currentConfig['bg'] }}; border-bottom: 3px solid {{ $currentConfig['border'] }}">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase" style="color: {{ $currentConfig['color'] }}">Estado del envio</p>
                        <h1 class="mt-1 text-2xl font-black text-gray-950">{{ $currentConfig['label'] }}</h1>
                        <p class="mt-1 text-sm text-gray-600">{{ $shipment->guide_number }}</p>
                    </div>
                    @if($estimatedDate && !$isDelivered && !$hasIssue)
                        <div class="rounded-lg bg-white/80 px-4 py-2 text-center">
                            <p class="text-xs font-semibold uppercase text-gray-500">Entrega estimada</p>
                            <p class="text-sm font-bold text-gray-950">{{ $estimatedDate }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info grid --}}
            <div class="grid gap-px bg-gray-100 sm:grid-cols-3">
                <div class="bg-white p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Remitente</p>
                    <p class="mt-1 font-bold text-gray-950">{{ $shipment->sender_name }}</p>
                    <p class="text-sm text-gray-600">{{ $senderCity }}</p>
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Destinatario</p>
                    <p class="mt-1 font-bold text-gray-950">{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</p>
                    <p class="text-sm text-gray-600">{{ $recipientCity }}</p>
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500">Contenido</p>
                    <p class="mt-1 font-bold text-gray-950">{{ \Illuminate\Support\Str::limit($shipment->content_description ?? 'Paquete', 40) }}</p>
                    <p class="text-sm text-gray-600">{{ $shipment->pieces ?? 1 }} {{ ($shipment->pieces ?? 1) === 1 ? 'pieza' : 'piezas' }}{{ $shipment->weight_kg ? ' · ' . $shipment->weight_kg . ' kg' : '' }}</p>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-4 lg:grid-cols-[1fr_0.8fr]">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="h-14 w-14 rounded-lg border border-gray-200 bg-white object-contain p-1">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Vendido por</p>
                        <h2 class="mt-1 text-lg font-black text-gray-950">{{ $brandName }}</h2>
                        <p class="mt-1 text-sm font-semibold text-gray-600">{{ $brandMessage }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Necesitas ayuda?</p>
                <p class="mt-1 text-sm font-semibold text-gray-600">Ten a la mano tu numero de guia para que podamos ayudarte mas rapido.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black text-white shadow-sm" style="background: {{ $brandColor }}">
                            Escribir por WhatsApp
                        </a>
                    @endif
                    <button onclick="copyTrackingUrl(this, '{{ $trackingUrl }}', 'Enlace copiado', 'Copiar enlace')" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">
                        Copiar enlace
                    </button>
                </div>
            </div>
        </section>

        {{-- Progress bar --}}
        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-xs font-black uppercase tracking-wider text-gray-500">Progreso del envio</h2>
            <div class="mt-4">
                <div class="flex items-center justify-between">
                    @foreach($flow as $i => $step)
                        @php
                            $stepCfg = $statusConfig[$step];
                            $reached = false;
                            if ($isDelivered) { $reached = true; }
                            elseif ($hasIssue) { $reached = in_array($step, ['created', 'printed']); if ($currentStatus === 'failed_delivery') $reached = in_array($step, ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route']); }
                            else { $currentIndex = array_search($currentStatus, $flow); if ($currentIndex === false) $currentIndex = 0; $reached = $i <= $currentIndex; }
                            $isCurrent = $step === $currentStatus || (!$hasIssue && !$isDelivered && $i === ($currentIndex ?? 0));
                        @endphp
                        <div class="flex flex-1 flex-col items-center">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $reached ? 'bg-emerald-500 text-white' : ($isCurrent ? 'text-white' : 'bg-gray-100 text-gray-400') }}" {{ $isCurrent && !$reached ? 'style="background:' . $brandColor . '"' : '' }}>
                                @if($reached)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                @else
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stepCfg['icon'] }}" /></svg>
                                @endif
                            </div>
                            <span class="mt-1.5 text-center text-2xs font-semibold {{ $reached || $isCurrent ? 'text-gray-950' : 'text-gray-400' }}">{{ $stepCfg['label'] }}</span>
                        </div>
                        @if($i < count($flow) - 1)
                            <div class="h-0.5 flex-1 mt-4 {{ $reached ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Timeline --}}
        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-xs font-black uppercase tracking-wider text-gray-500">Historial de movimientos</h2>
            <div class="te-timeline-track mt-5">
                @forelse($shipment->events as $event)
                    @php
                        $evCfg = $statusConfig[$event->status] ?? $statusConfig['created'];
                        $isLatest = $loop->first;
                    @endphp
                    <div class="te-step {{ $isLatest ? 'is-active' : '' }}">
                        <div class="te-step-dot {{ $isLatest ? '' : '' }}" style="background: {{ $isLatest ? $evCfg['color'] : '#f9fafb' }}; border: 2px solid {{ $isLatest ? $evCfg['color'] : '#d1d5db' }}; color: {{ $isLatest ? '#fff' : '#9ca3af' }}">
                            @if($isLatest)
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $evCfg['icon'] }}" /></svg>
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $evCfg['icon'] }}" /></svg>
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-gray-950">{{ $evCfg['label'] }}</p>
                            <p class="text-sm text-gray-500">{{ $event->recorded_at->locale('es')->isoFormat('D [de] MMMM, YYYY h:mm A') }}</p>
                            @if($event->location)
                                <p class="text-sm text-gray-600">{{ $event->location }}</p>
                            @endif
                            @if($event->notes)
                                <p class="text-sm text-gray-600 italic">{{ $event->notes }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="te-step is-active">
                        <div class="te-step-dot" style="background: {{ $currentConfig['color'] }}; border: 2px solid {{ $currentConfig['border'] }}; color: #fff;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $currentConfig['icon'] }}" /></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-950">{{ $currentConfig['label'] }}</p>
                            <p class="text-sm text-gray-500">{{ $shipment->created_at->locale('es')->isoFormat('D [de] MMMM, YYYY h:mm A') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Share / help --}}
        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-bold text-gray-950">Compartir seguimiento</p>
                    <p class="text-sm text-gray-500">Envia el enlace a tu cliente para que vea el estado.</p>
                </div>
                <button onclick="copyTrackingUrl(this, '{{ $trackingUrl }}', 'Copiado!', 'Copiar enlace')" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Copiar enlace
                </button>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 bg-white mt-8">
        <div class="mx-auto max-w-5xl px-4 py-4 sm:px-6 text-center text-xs text-gray-500">
            Powered by <a href="https://tusenvios.com.co" class="font-semibold text-blue-700 hover:underline">Tus Envios</a>
        </div>
    </footer>
    <script>
        function copyTrackingUrl(button, url, copiedText, originalText) {
            const copy = navigator.clipboard
                ? navigator.clipboard.writeText(url)
                : new Promise((resolve) => {
                    const input = document.createElement('textarea');
                    input.value = url;
                    input.setAttribute('readonly', 'readonly');
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                    resolve();
                });

            copy.then(() => {
                button.textContent = copiedText;
                setTimeout(() => button.textContent = originalText, 2000);
            });
        }
    </script>
</body>
</html>
