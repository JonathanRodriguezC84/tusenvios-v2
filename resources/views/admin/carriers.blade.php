@extends('layouts.admin')

@section('title', 'Transportadoras')
@section('eyebrow', 'Integraciones')
@section('page-title', 'Transportadoras')
@section('page-description', 'API para que las transportadoras se conecten y gestionen envios.')

@section('content')
    <div class="grid gap-5 xl:grid-cols-3">
        {{-- TRANSPORTADORAS --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">Transportadoras activas</h3>
            <div class="mt-4 grid gap-3">
                @foreach ($carriers as $code => $carrier)
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="font-bold text-gray-950">{{ $carrier['name'] }}</p>
                        <div class="mt-2 grid grid-cols-2 gap-1 text-xs">
                            <span class="text-gray-500">Tarifa base</span>
                            <span class="font-semibold text-right">${{ number_format($carrier['base_rate'], 0) }}</span>
                            <span class="text-gray-500">Por kilo</span>
                            <span class="font-semibold text-right">${{ number_format($carrier['per_kg'], 0) }}</span>
                            <span class="text-gray-500">Por pieza</span>
                            <span class="font-semibold text-right">${{ number_format($carrier['per_piece'], 0) }}</span>
                        </div>
                        <div class="mt-2 flex gap-1">
                            @foreach ($carrier['zone_multipliers'] as $zone => $mult)
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-3xs font-semibold text-gray-600">{{ $zone }} x{{ $mult }}</span>
                            @endforeach
                        </div>
                        <div class="mt-2 flex gap-2 text-3xs text-gray-500">
                            <span>Estandar: {{ $carrier['standard_days'] }}d</span>
                            <span>Express: {{ $carrier['express_days'] }}d</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">Editar en <code>config/shipping.php</code></p>
        </section>

        {{-- API ENDPOINTS --}}
        <section class="admin-card p-5 xl:col-span-2">
            <h3 class="text-sm font-black uppercase text-gray-500">API: como se conecta una transportadora</h3>

            <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs font-semibold text-blue-800 mb-2">1. Autenticacion</p>
                <p class="text-sm text-blue-700">Cada transportadora recibe un usuario (rol <code>courier</code>) con su correo y contrasena. Usa ese usuario + la API Key para obtener un token.</p>
                <code class="block mt-2 rounded bg-white px-3 py-2 text-xs font-mono text-gray-700">POST /api/v1/auth/login
{
  "email": "mensajero@transp.com",
  "password": "su-contrasena",
  "api_key": "{{ $rawKey ? 'TU_API_KEY_AQUI' : '(configurar CARRIER_API_KEY en .env)' }}"
}
→ { "token": "1|abc123...", "user": {...} }</code>
            </div>

            <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-semibold text-emerald-800 mb-2">2. Consultar guias asignadas</p>
                <p class="text-sm text-emerald-700">El mensajero ve solo las guias que tiene asignadas. Usa el token del paso 1.</p>
                <code class="block mt-2 rounded bg-white px-3 py-2 text-xs font-mono text-gray-700">GET /api/v1/shipments?status=assigned
Authorization: Bearer {token}
→ { "data": [{"guide_number":"RCI...", "recipient_name":"...", ...}] }</code>
            </div>

            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs font-semibold text-amber-800 mb-2">3. Actualizar estado de una guia</p>
                <p class="text-sm text-amber-700">Cuando el mensajero entrega o reporta novedad, actualiza el estado.</p>
                <code class="block mt-2 rounded bg-white px-3 py-2 text-xs font-mono text-gray-700">POST /api/v1/shipments/{id}/status
Authorization: Bearer {token}
{
  "status": "delivered",
  "notes": "Entregado al cliente"
}
→ { "message": "Estado actualizado correctamente" }</code>
                <p class="mt-2 text-xs text-amber-600 font-semibold">
                    Estados validos: <code>on_route</code> <code>delivered</code> <code>failed_delivery</code> <code>rescheduled</code> <code>return_pending</code> <code>returned</code>
                </p>
            </div>

            <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <p class="text-xs font-semibold text-gray-700 mb-2">4. Escanear guia</p>
                <p class="text-sm text-gray-600">Consulta rapida por numero de guia o codigo de barras.</p>
                <code class="block mt-2 rounded bg-white px-3 py-2 text-xs font-mono text-gray-700">GET /api/v1/scan/{guia}
Authorization: Bearer {token}
→ { "guide_number":"RCI...", "status":"assigned", "recipient_address":"..." }</code>
            </div>
        </section>
    </div>

    {{-- URL Y API KEY --}}
    <section class="admin-card p-5 mt-5">
        <h3 class="text-sm font-black uppercase text-gray-500">Datos para compartir con la transportadora</h3>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
            <div class="flex items-center gap-3 rounded-lg border border-gray-200 p-3">
                <span class="text-xs font-semibold uppercase text-gray-500 w-20">URL Base</span>
                <code class="rounded bg-gray-100 px-3 py-1.5 font-mono text-gray-700">{{ $baseUrl }}/api/v1</code>
            </div>
            <div class="flex items-center gap-3 rounded-lg border border-gray-200 p-3">
                <span class="text-xs font-semibold uppercase text-gray-500 w-20">API Key</span>
                <code class="rounded bg-gray-100 px-3 py-1.5 font-mono text-gray-700" id="apiKeyDisplay">{{ $apiKey ?: 'No configurada — editar .env' }}</code>
                @if($rawKey)
                    <button type="button" onclick="toggleApiKey()" class="text-xs text-blue-600 hover:underline shrink-0" id="apiKeyToggle">Mostrar</button>
                    <script>
                        function toggleApiKey() {
                            const display = document.getElementById('apiKeyDisplay');
                            const toggle = document.getElementById('apiKeyToggle');
                            if (display.dataset.revealed === '1') {
                                display.textContent = '{{ $apiKey }}';
                                display.dataset.revealed = '0';
                                toggle.textContent = 'Mostrar';
                            } else {
                                display.textContent = '{{ $rawKey }}';
                                display.dataset.revealed = '1';
                                toggle.textContent = 'Ocultar';
                            }
                        }
                    </script>
                @endif
            </div>
        </div>
    </section>
@endsection
