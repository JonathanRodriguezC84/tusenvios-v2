@extends('layouts.admin')

@section('title', 'API Docs')
@section('eyebrow', 'Integraciones')
@section('page-title', 'Documentacion de la API')
@section('page-description', 'Endpoints disponibles para tenants, transportadoras y rastreo publico.')

@section('content')
    <style>
        .api-section { margin-bottom: 2rem; }
        .api-method { display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
        .api-badge { display: inline-block; border-radius: 4px; padding: 2px 8px; font-size: 0.7rem; font-weight: 700; color: #fff; text-transform: uppercase; }
        .api-badge.get { background: #059669; }
        .api-badge.post { background: #2563eb; }
        .api-badge.auth { background: #7c3aed; }
        .api-path { font-family: monospace; font-size: 0.9rem; font-weight: 600; color: #111827; }
        .api-block { background: #1e293b; color: #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; font-family: monospace; font-size: 0.8rem; line-height: 1.7; overflow-x: auto; white-space: pre-wrap; margin-top: 0.5rem; }
        .api-block .key { color: #facc15; }
        .api-block .str { color: #a5f3fc; }
        .api-block .num { color: #f9a8d4; }
        .api-block .comment { color: #6b7280; }
        .api-desc { color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; }
        .api-tag { display: inline-block; border-radius: 4px; padding: 1px 6px; font-size: 0.65rem; font-weight: 700; margin-left: 0.5rem; }
        .api-tag.tenant { background: #dbeafe; color: #1e40af; }
        .api-tag.carrier { background: #fef3c7; color: #92400e; }
        .api-tag.public { background: #d1fae5; color: #065f46; }
        .api-tag.admin { background: #ede9fe; color: #6d28d9; }
    </style>

    {{-- Tenant API --}}
    <div class="api-section admin-card p-5">
        <h3 class="text-sm font-black uppercase text-gray-500 flex items-center gap-2">
            <span class="api-tag tenant">TENANT</span> API para tiendas y e-commerce
        </h3>
        <p class="text-sm text-gray-500 mt-1">Autenticacion: <code>Authorization: Bearer {api_token}</code>. El token se genera desde Admin → Clientes → Ver cliente → Generar token.</p>

        <div class="mt-4 grid gap-4">
            @foreach ([
                ['method' => 'POST', 'path' => '/api/v1/my/shipments', 'desc' => 'Crear una guia nueva', 'body' => "{\n  \"recipient_name\": \"Juan Perez\",\n  \"recipient_lastname\": \"Gomez\",\n  \"recipient_phone\": \"3001234567\",\n  \"recipient_address\": \"Calle 80 #15-20\",\n  \"recipient_neighborhood\": \"Chapinero\",\n  \"recipient_locality\": \"Bogota\",\n  \"content_description\": \"Camiseta negra talla M x 1 - \$25.000\",\n  \"package_type\": \"package\",\n  \"pieces\": 1,\n  \"declared_value\": 25000,\n  \"collection_value\": 25000,\n  \"payment_method\": \"cod\"\n}", 'response' => "{\n  \"success\": true,\n  \"guide_number\": \"RCI2026000012\",\n  \"barcode\": \"RCI2026000012\",\n  \"label_url\": \"https://.../shipments/1/print\",\n  \"tracking_url\": \"https://.../track/RCI2026000012\"\n}"],
                ['method' => 'GET', 'path' => '/api/v1/my/shipments', 'desc' => 'Listar guias del tenant', 'body' => "?status=delivered&per_page=20", 'response' => "{\n  \"data\": [\n    {\n      \"id\": 1,\n      \"guide_number\": \"RCI2026000001\",\n      \"status\": \"delivered\",\n      \"recipient_name\": \"Juan Perez\",\n      \"created_at\": \"2026-06-01T10:00:00Z\"\n    }\n  ]\n}"],
                ['method' => 'GET', 'path' => '/api/v1/my/shipments/{id}', 'desc' => 'Ver detalle de una guia', 'body' => '', 'response' => "{\n  \"guide_number\": \"RCI2026000001\",\n  \"status\": \"delivered\",\n  \"recipient_name\": \"Juan Perez\",\n  \"label_url\": \"https://.../shipments/1/print\",\n  \"tracking_url\": \"https://.../track/RCI2026000001\"\n}"],
            ] as $ep)
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="api-method">
                        <span class="api-badge {{ strtolower($ep['method']) }}">{{ $ep['method'] }}</span>
                        <code class="api-path">{{ $ep['path'] }}</code>
                    </div>
                    <p class="api-desc">{{ $ep['desc'] }}</p>
                    @if ($ep['body'])
                        <p class="text-xs text-gray-400 mt-2">Body / Query:</p>
                        <div class="api-block">{{ $ep['body'] }}</div>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">Respuesta:</p>
                    <div class="api-block">{{ $ep['response'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Carrier API --}}
    <div class="api-section admin-card p-5">
        <h3 class="text-sm font-black uppercase text-gray-500 flex items-center gap-2">
            <span class="api-tag carrier">TRANSPORTADORA</span> API para mensajeros y transportadoras
        </h3>
        <p class="text-sm text-gray-500 mt-1">Autenticacion en 2 pasos: login con credenciales de mensajero (rol courier) + API Key → token Bearer para los demas endpoints.</p>

        <div class="mt-4 grid gap-4">
            @foreach ([
                ['method' => 'POST', 'path' => '/api/v1/auth/login', 'desc' => 'Autenticar mensajero. Requiere API Key (configurada en .env CARRIER_API_KEY)', 'body' => "{\n  \"email\": \"mensajero@transp.com\",\n  \"password\": \"su-contrasena\",\n  \"api_key\": \"CARRIER_API_KEY\"\n}", 'response' => "{\n  \"token\": \"1|abc123def456...\",\n  \"user\": { \"id\": 5, \"name\": \"Mensajero\", \"courier_company\": \"Servientrega\" }\n}"],
                ['method' => 'GET', 'path' => '/api/v1/shipments', 'desc' => 'Listar guias asignadas al mensajero', 'body' => "?status=assigned&page=1", 'response' => "{\n  \"data\": [\n    {\n      \"id\": 1,\n      \"guide_number\": \"RCI2026000001\",\n      \"recipient_name\": \"Juan Perez\",\n      \"recipient_phone\": \"3001234567\",\n      \"recipient_address\": \"Calle 80 #15-20\",\n      \"status\": \"assigned\"\n    }\n  ]\n}"],
                ['method' => 'POST', 'path' => '/api/v1/shipments/{id}/status', 'desc' => 'Actualizar estado (on_route, delivered, failed_delivery, rescheduled, return_pending, returned)', 'body' => "{\n  \"status\": \"delivered\",\n  \"notes\": \"Entregado al cliente en recepcion\"\n}", 'response' => "{ \"message\": \"Estado actualizado correctamente\" }"],
                ['method' => 'GET', 'path' => '/api/v1/scan/{guia}', 'desc' => 'Escanear guia por numero o codigo de barras', 'body' => '', 'response' => "{\n  \"guide_number\": \"RCI2026000001\",\n  \"status\": \"assigned\",\n  \"recipient_name\": \"Juan Perez\",\n  \"recipient_address\": \"Calle 80 #15-20\"\n}"],
            ] as $ep)
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="api-method">
                        <span class="api-badge {{ strtolower($ep['method']) }}">{{ $ep['method'] }}</span>
                        <code class="api-path">{{ $ep['path'] }}</code>
                    </div>
                    <p class="api-desc">{{ $ep['desc'] }}</p>
                    @if ($ep['body'])
                        <p class="text-xs text-gray-400 mt-2">Body / Query:</p>
                        <div class="api-block">{{ $ep['body'] }}</div>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">Respuesta:</p>
                    <div class="api-block">{{ $ep['response'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Public API --}}
    <div class="api-section admin-card p-5">
        <h3 class="text-sm font-black uppercase text-gray-500 flex items-center gap-2">
            <span class="api-tag public">PUBLICA</span> Sin autenticacion
        </h3>

        <div class="mt-4 grid gap-4">
            @foreach ([
                ['method' => 'GET', 'path' => '/api/v1/track/{guia}', 'desc' => 'Rastrear una guia. Devuelve estado actual e historial de eventos.', 'body' => '', 'response' => "{\n  \"guide_number\": \"RCI2026000001\",\n  \"status\": \"delivered\",\n  \"status_label\": \"Entregada\",\n  \"recipient_city\": \"Bogota\",\n  \"history\": [\n    { \"status\": \"created\", \"label\": \"Por imprimir\", \"date\": \"...\" },\n    { \"status\": \"delivered\", \"label\": \"Entregada\", \"date\": \"...\" }\n  ]\n}"],
                ['method' => 'GET', 'path' => '/api/v1/shipping-rates', 'desc' => 'Calcular tarifa de envio entre origen y destino', 'body' => "?origin=11001&destination=05001&weight=2&pieces=1&carrier=servientrega", 'response' => "{\n  \"carrier\": \"Servientrega\",\n  \"standard\": { \"price\": 7400, \"days\": \"2-4\" },\n  \"express\": { \"price\": 11100, \"days\": \"1-2\" }\n}"],
                ['method' => 'GET', 'path' => '/api/ping', 'desc' => 'Health check de la API', 'body' => '', 'response' => "{ \"status\": \"ok\", \"version\": \"1.0.0\" }"],
            ] as $ep)
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="api-method">
                        <span class="api-badge {{ strtolower($ep['method']) }}">{{ $ep['method'] }}</span>
                        <code class="api-path">{{ $ep['path'] }}</code>
                    </div>
                    <p class="api-desc">{{ $ep['desc'] }}</p>
                    @if ($ep['body'])
                        <p class="text-xs text-gray-400 mt-2">Query:</p>
                        <div class="api-block">{{ $ep['body'] }}</div>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">Respuesta:</p>
                    <div class="api-block">{{ $ep['response'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Webhooks --}}
    <div class="api-section admin-card p-5">
        <h3 class="text-sm font-black uppercase text-gray-500 flex items-center gap-2">
            <span class="api-tag admin">WEBHOOKS</span> Notificaciones salientes
        </h3>
        <p class="text-sm text-gray-500 mt-1">Cuando una guia cambia de estado, TusEnvios envia un POST a la URL configurada por el tenant.</p>

        <div class="mt-4 rounded-lg border border-gray-200 p-4">
            <p class="text-xs font-semibold text-gray-700">Payload enviado a la URL del webhook:</p>
            <div class="api-block mt-2">{
  "event": "shipment.status_updated",
  "guide_number": "RCI2026000012",
  "barcode": "RCI2026000012",
  "status": "delivered",
  "status_label": "Entregada",
  "recipient_name": "Juan Perez Gomez",
  "recipient_phone": "3001234567",
  "recipient_address": "Calle 80 #15-20",
  "tracking_url": "https://tusenvios.com.co/track/RCI2026000012",
  "updated_at": "2026-06-06T18:30:00-05:00"
}</div>
            <p class="mt-3 text-xs text-gray-500">Se reintenta 2 veces si falla (timeout 10s). Configurar en Admin → Clientes → Ver cliente → Webhook.</p>
        </div>
    </div>
@endsection
