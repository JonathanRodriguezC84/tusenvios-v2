@php
    $brand = $brandOwner->brandData();
    $logoUrl = $brand['logo_path'] ? Storage::url($brand['logo_path']) : null;
    $template = $brand['template'] ?? 'classic';

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

<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Vista previa de etiqueta</title>
        <style>
            @page { size: 100mm 150mm; margin: 0; }
            * { box-sizing: border-box; }
            body { margin: 0; background: #e5e7eb; }
            .actions { margin: 14px auto; text-align: center; }
            button { background: {{ $brand['color'] ?? '#0047D9' }}; border: 0; border-radius: 7px; color: #fff; cursor: pointer; font-size: 14px; font-weight: 800; padding: 10px 14px; }
            .label-sheet { margin: 0 auto 18px; }
            @media print {
                * {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
 body { background: #fff; } .actions { display: none; } .label-sheet { margin: 0; } }
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
        <div class="actions"><button onclick="window.print()">Imprimir vista previa</button></div>
        @include('brand-settings.partials.label-demo', ['brand' => $brand, 'logoUrl' => $logoUrl, 'template' => $template])
    </body>
</html>