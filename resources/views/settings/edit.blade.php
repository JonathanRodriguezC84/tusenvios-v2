<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Cuenta" title="Configuracion" description="Accesos rapidos a tu marca, perfil y preferencias." />
    </x-slot>
    <div class="p-4 h-full overflow-y-auto">
        <div class="max-w-2xl mx-auto space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Accesos rapidos</h3>
                <div class="space-y-2 text-sm">
                    <a href="{{ route('brand-settings.edit') }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50">
                        <span>Mi marca</span>
                        <span class="text-blue-700 font-semibold">Configurar →</span>
                    </a>
                    <a href="{{ route('store-settings.edit') }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50">
                        <span>Tienda</span>
                        <span class="text-blue-700 font-semibold">Configurar →</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50">
                        <span>Perfil</span>
                        <span class="text-blue-700 font-semibold">Editar →</span>
                    </a>
                </div>
            </div>

            @if (Auth::user()->isSuperAdmin())
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-1">Notificaciones WhatsApp</h3>
                <p class="text-xs text-gray-500 mb-3">Configura los mensajes automaticos via WhatsApp Business API.</p>
                <div class="space-y-2 text-xs text-gray-500">
                    <p><strong>Estado:</strong> <span class="{{ config('services.whatsapp.enabled') ? 'text-emerald-600' : 'text-amber-600' }} font-semibold">{{ config('services.whatsapp.enabled') ? 'Activado' : 'Desactivado' }}</span></p>
                    <p>Variables en <code>.env</code>: WHATSAPP_API_URL, WHATSAPP_TOKEN, WHATSAPP_PHONE_ID</p>
                    <p>Se usa para: alertas de stock bajo, cambios de estado de envios, resumen diario.</p>
                </div>
            </div>
            @endif

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-1">API para transportadoras</h3>
                <p class="text-xs text-gray-500 mb-3">Endpoints REST para que las transportadoras integren sus sistemas.</p>
                <div class="space-y-2 text-xs text-gray-500">
                    <p><strong>URL base:</strong> <code>/api/v1</code></p>
                    <p><strong>Auth:</strong> Bearer Token (configurar en <code>.env</code> como <code>CARRIER_API_KEY</code>)</p>
                    <p class="mt-2"><strong>Endpoints:</strong></p>
                    <ul class="list-disc pl-4 space-y-1">
                        <li><code>POST /api/v1/auth/login</code> - Inicio de sesion</li>
                        <li><code>GET /api/v1/shipments</code> - Envios asignados</li>
                        <li><code>POST /api/v1/shipments/{id}/status</code> - Actualizar estado</li>
                        <li><code>GET /api/v1/scan/{guia}</code> - Escanear guia</li>
                    </ul>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-1">Pasarela de pagos</h3>
                <p class="text-xs text-gray-500 mb-3">Integracion con Bold para procesar pagos de suscripciones.</p>
                <div class="space-y-2 text-xs text-gray-500">
                    <p><strong>Proveedor actual:</strong> Bold</p>
                    <p><strong>Estado:</strong> <span class="text-emerald-600 font-semibold">Configurado</span></p>
                    <p>Los pagos se gestionan desde <strong>Admin → Suscripciones</strong>.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
