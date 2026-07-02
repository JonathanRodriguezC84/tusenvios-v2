<!doctype html>
@php
    $printFormat = $printFormat ?? [
        'label' => 'Etiqueta 100 x 150 mm',
        'short_label' => '100 x 150',
        'page' => '100mm 150mm',
        'width' => '100mm',
        'height' => '150mm',
        'scale' => '1',
        'padding' => '0',
    ];
@endphp
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Impresion de guias</title>
        <style>
            @page { size: {{ $printFormat['page'] }}; margin: 0; }
            * { box-sizing: border-box; }
            body { margin: 0; background: #e5e7eb; color: #050505; font-family: "Inter", Arial, Helvetica, sans-serif; }
            .actions { margin: 14px auto; text-align: center; }
            button { background: #022a8c; border: 0; border-radius: 7px; color: #fff; cursor: pointer; font-size: 14px; font-weight: 800; padding: 10px 14px; }
            .print-page {
                align-items: center;
                background: #ffffff;
                break-after: page;
                display: flex;
                height: {{ $printFormat['height'] }};
                justify-content: center;
                margin: 0 auto 18px;
                overflow: hidden;
                padding: {{ $printFormat['padding'] }};
                page-break-after: always;
                width: {{ $printFormat['width'] }};
            }
            .print-page:last-child { page-break-after: auto; break-after: auto; }
            .print-label {
                flex: 0 0 auto;
                transform: scale({{ $printFormat['scale'] }});
                transform-origin: center center;
            }
            @media print {
                * {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
 body { background: #fff; } .actions { display: none; } .print-page { margin: 0; } }
            {!! file_get_contents(resource_path('views/brand-settings/partials/label-demo.css')) !!}
        
            .social-item {
                align-items: center;
                display: inline-flex;
                gap: 1mm;
                white-space: nowrap;
            }

            .social-svg,
            .social-fallback {
                display: inline-block;
                flex: 0 0 3.4mm;
                height: 3.4mm;
                width: 3.4mm;
            }

            .social-fallback {
                align-items: center;
                background: #111827;
                border-radius: 999px;
                color: #ffffff;
                display: inline-flex;
                font-size: 5px;
                font-weight: 900;
                justify-content: center;
            }
        </style>
    </head>
    <body>
        <div class="actions"><button onclick="window.print()">Imprimir {{ $shipments->count() }} guias en {{ $printFormat['short_label'] }}</button></div>

        @foreach ($shipments as $shipment)
            @php
                $brand = ($shipment->affiliatedCompany ?: $shipment->tenant)?->brandData() ?? [
                    'name' => 'Tus Envios',
                    'logo_path' => null,
                    'color' => '#022a8c',
                    'whatsapp' => null,
                    'instagram' => null,
                    'facebook' => null,
                    'tiktok' => null,
                    'website' => 'tusenvios.com.co',
                    'message' => 'Gracias por tu compra.',
        'phone' => '',
        'address' => '',
        'neighborhood' => '',
        'locality' => '',
                    'template' => 'classic',
                ];
                $template = in_array($brand['template'] ?? 'classic', ['classic', 'modern', 'advance'], true) ? $brand['template'] : 'classic';
                $logoUrl = $brand['logo_path'] ? Storage::url($brand['logo_path']) : null;
                $socialLinks = collect([
                    ['type' => 'whatsapp', 'label' => 'WhatsApp', 'value' => $brand['whatsapp'] ?? null],
                    ['type' => 'instagram', 'label' => 'Instagram', 'value' => $brand['instagram'] ?? null],
                    ['type' => 'facebook', 'label' => 'Facebook', 'value' => $brand['facebook'] ?? null],
                    ['type' => 'tiktok', 'label' => 'TikTok', 'value' => $brand['tiktok'] ?? null],
                ])->filter(fn ($item) => filled($item['value']))->values();
            
    if (! function_exists('tusEnviosSocialIconSvg')) {
        function tusEnviosSocialIconSvg(string $type): string
        {
            return match (strtolower($type)) {
                'whatsapp', 'wa' => '<svg class="social-svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#25D366"/><path fill="#fff" d="M17.3 14.1c-.3-.1-1.7-.8-1.9-.9-.3-.1-.5-.1-.7.1-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1-.3-.1-1.2-.4-2.2-1.4-.8-.7-1.4-1.6-1.5-1.9-.2-.3 0-.4.1-.6l.4-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5 0-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.7-.7 1.9-1.3.2-.6.2-1.2.2-1.3-.1-.2-.3-.3-.6-.4Z"/></svg>',
                'instagram', 'ig' => '<svg class="social-svg" viewBox="0 0 24 24" aria-hidden="true"><defs><linearGradient id="ig-te" x1="3" y1="21" x2="21" y2="3"><stop stop-color="#FEDA75"/><stop offset=".35" stop-color="#FA7E1E"/><stop offset=".7" stop-color="#D62976"/><stop offset="1" stop-color="#4F5BD5"/></linearGradient></defs><circle cx="12" cy="12" r="12" fill="url(#ig-te)"/><path fill="#fff" d="M12 7.2c1.6 0 1.8 0 2.4.1.6 0 .9.1 1.1.2.3.1.5.2.7.4.2.2.3.4.4.7.1.2.2.5.2 1.1.1.6.1.8.1 2.4s0 1.8-.1 2.4c0 .6-.1.9-.2 1.1-.1.3-.2.5-.4.7-.2.2-.4.3-.7.4-.2.1-.5.2-1.1.2-.6.1-.8.1-2.4.1s-1.8 0-2.4-.1c-.6 0-.9-.1-1.1-.2-.3-.1-.5-.2-.7-.4-.2-.2-.3-.4-.4-.7-.1-.2-.2-.5-.2-1.1-.1-.6-.1-.8-.1-2.4s0-1.8.1-2.4c0-.6.1-.9.2-1.1.1-.3.2-.5.4-.7.2-.2.4-.3.7-.4.2-.1.5-.2 1.1-.2.6-.1.8-.1 2.4-.1Zm0-1.1c-1.6 0-1.9 0-2.5.1-.7 0-1.1.1-1.5.3-.4.2-.8.4-1.1.7-.3.3-.5.7-.7 1.1-.2.4-.3.8-.3 1.5-.1.6-.1.9-.1 2.5s0 1.9.1 2.5c0 .7.1 1.1.3 1.5.2.4.4.8.7 1.1.3.3.7.5 1.1.7.4.2.8.3 1.5.3.6.1.9.1 2.5.1s1.9 0 2.5-.1c.7 0 1.1-.1 1.5-.3.4-.2.8-.4 1.1-.7.3-.3.5-.7.7-1.1.2-.4.3-.8.3-1.5.1-.6.1-.9.1-2.5s0-1.9-.1-2.5c0-.7-.1-1.1-.3-1.5-.2-.4-.4-.8-.7-1.1-.3-.3-.7-.5-1.1-.7-.4-.2-.8-.3-1.5-.3-.6-.1-.9-.1-2.5-.1Zm0 3a3.1 3.1 0 1 0 0 6.2 3.1 3.1 0 0 0 0-6.2Zm0 5.1a2 2 0 1 1 0-4.1 2 2 0 0 1 0 4.1Zm3.9-5.2a.7.7 0 1 1-1.5 0 .7.7 0 0 1 1.5 0Z"/></svg>',
                'facebook', 'fb' => '<svg class="social-svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#1877F2"/><path fill="#fff" d="M13.6 20v-7h2.3l.4-2.7h-2.7V8.6c0-.8.2-1.3 1.4-1.3h1.4V4.9c-.7-.1-1.4-.1-2.1-.1-2.1 0-3.6 1.3-3.6 3.7v1.9H8.3V13h2.4v7h2.9Z"/></svg>',
                'tiktok', 'tk' => '<svg class="social-svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#000"/><path fill="#fff" d="M15 5.1c.4 1.4 1.3 2.3 2.7 2.4v2.3c-1 0-1.9-.3-2.7-.8v4.5c0 2.3-1.5 4.2-4 4.2-2.2 0-3.8-1.5-3.8-3.6 0-2.3 1.8-3.8 4.2-3.5v2.4c-.9-.3-1.8.3-1.8 1.2 0 .8.6 1.3 1.4 1.3.9 0 1.5-.5 1.5-1.7V5.1H15Z"/></svg>',
                default => '<span class="social-fallback">'.e(strtoupper(substr($type, 0, 2))).'</span>',
            };
        }
    }
@endphp

            <main class="print-page">
            <article class="print-label label-sheet label-{{ $template }}">
                @if ($template === 'advance')
                    <section class="label-barcode"><p class="label-guide">{{ $shipment->guide_number }}</p><div class="label-code">{!! \App\Support\Code39Barcode::svg($shipment->barcodeValue(), 72) !!}</div></section>
                @endif

                <section class="label-top">
                    <div class="label-logo">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $brand['name'] }}">
                        @else
                            <span>{{ strtoupper(substr($brand['name'] ?? 'TE', 0, 2)) }}</span>
                        @endif
                    </div>
                    <div class="label-sender">
                        <p class="label-company">{{ $shipment->sender_name }}</p>
                        <p>{{ data_get($brand, 'address') ?: $shipment->sender_address }}</p>
                        <p>{{ trim((data_get($brand, 'neighborhood') ?: ($shipment->sender_neighborhood ?: 'SIN BARRIO')).' / '.(data_get($brand, 'locality') ?: ($shipment->sender_locality ?: 'SIN CIUDAD')), ' /') }}</p>
                        <p>{{ data_get($brand, 'phone') ?: ($shipment->sender_phone ?: 'NO REGISTRADO') }}</p>
                        <p class="label-message">{{ $brand['message'] ?: 'GRACIAS POR TU COMPRA' }}</p>
                    </div>
                </section>

                <section class="label-socials">
                    @forelse ($socialLinks as $social)
                        <span>
    <i class="{{ data_get($social, 'type', strtolower(data_get($social, 'icon', 'social'))) }}" aria-label="{{ $social['label'] ?? ($social['icon'] ?? 'Red social') }}">
        @php($socialType = $social['type'] ?? strtolower($social['icon'] ?? ''))
        @if ($socialType === 'whatsapp' || $socialType === 'wa')
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.57 2 2.13 6.35 2.13 11.71c0 1.72.47 3.39 1.35 4.86L2 22l5.6-1.43a10.1 10.1 0 0 0 4.44.99c5.47 0 9.91-4.35 9.91-9.7C21.95 6.35 17.51 2 12.04 2Zm5.75 13.73c-.24.67-1.38 1.27-1.95 1.35-.5.08-1.14.11-1.84-.11-.43-.13-.98-.32-1.68-.62-2.95-1.25-4.88-4.15-5.03-4.34-.15-.2-1.2-1.56-1.2-2.98s.76-2.12 1.03-2.41c.27-.29.59-.36.79-.36h.57c.18.01.43-.07.67.5.24.56.82 1.95.9 2.09.07.15.12.32.02.51-.1.2-.15.31-.3.48-.15.17-.32.38-.45.51-.15.15-.31.32-.13.61.18.29.8 1.29 1.72 2.08 1.18 1.03 2.18 1.35 2.49 1.5.31.15.49.13.67-.08.18-.2.77-.88.98-1.18.2-.29.41-.24.69-.15.29.1 1.83.85 2.14 1 .31.15.51.22.59.34.08.13.08.74-.16 1.41Z"/></svg>
        @elseif ($socialType === 'instagram' || $socialType === 'ig')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
        @elseif ($socialType === 'facebook' || $socialType === 'fb')
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8.4V6.7c0-.8.5-1 1.1-1H17V2.4c-.9-.1-1.8-.2-2.7-.2-2.7 0-4.5 1.6-4.5 4.5v1.7H7v3.7h2.8V22H14v-9.9h2.8l.5-3.7H14Z"/></svg>
        @else
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.6 3.2c.5 2 1.7 3.2 3.7 3.4v3.6a7 7 0 0 1-3.7-1.1v5.8c0 4-2.6 6.4-6.3 6.4-3 0-5.5-2-5.5-5.1 0-3.5 3-5.7 6.5-5.1v3.8c-1.4-.5-2.6.2-2.6 1.3 0 1 .8 1.6 1.7 1.6 1.1 0 1.9-.7 1.9-2.3V3.2h4.3Z"/></svg>
        @endif
    </i>{{ data_get($social, 'value') }}
</span>
                    @empty
                        <span>SIN REDES REGISTRADAS</span>
                    @endforelse
                </section>

                @if ($template === 'classic')
                    <section class="label-barcode"><p class="label-guide">{{ $shipment->guide_number }}</p><div class="label-code">{!! \App\Support\Code39Barcode::svg($shipment->barcodeValue(), 72) !!}</div></section>
                @endif

                <section class="label-recipient-row">
                    <div class="label-recipient">
                        <div><span>NOMBRE</span><strong>{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</strong></div>
                        <div><span>DIRECCION</span><strong class="big">{{ $shipment->recipient_address }}</strong></div>
                        <div><span>BARRIO / LOCALIDAD</span><strong>{{ $shipment->recipient_neighborhood ?: 'SIN BARRIO' }} / {{ $shipment->recipient_locality ?: 'SIN LOCALIDAD' }}</strong></div>
                        <div><span>TELEFONO</span><strong>{{ $shipment->recipient_phone }}</strong></div>
                    </div>
                    <div class="label-qr">{!! \App\Support\QrCode::svg($shipment->barcodeValue(), 3) !!}</div>
                </section>

                <section class="label-notes"><span>OBSERVACIONES</span><strong>{{ $shipment->recipient_notes ?: 'SIN OBSERVACIONES' }}</strong></section>

                @if ($template === 'modern')
                    <section class="label-barcode"><p class="label-guide">{{ $shipment->guide_number }}</p><div class="label-code">{!! \App\Support\Code39Barcode::svg($shipment->barcodeValue(), 72) !!}</div></section>
                @endif

                <section class="label-metas">
                    <div><span>ZONA</span><strong>{{ $shipment->zone ?: 'SIN ZONA' }}</strong></div>
                    <div><span>PIEZAS</span><strong>{{ $shipment->pieces }}</strong></div>
                    <div><span>RECAUDO</span><strong>${{ number_format($shipment->collection_value, 0, ',', '.') }}</strong></div>
                </section>
                <section class="label-footer">
                    <span>TUSENVIOS.COM.CO</span>
                    <span>{{ $shipment->barcodeValue() }}</span>
                    <span>{{ now()->format('Y-m-d H:i') }}</span>
                </section>
            </article>
            </main>
        @endforeach
    </body>
</html>
