<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected array $config;

    public function __construct()
    {
        $this->config = [
            'whatsapp_enabled' => config('services.whatsapp.enabled', false),
            'whatsapp_api_url' => config('services.whatsapp.api_url', ''),
            'whatsapp_token' => config('services.whatsapp.token', ''),
            'whatsapp_phone_id' => config('services.whatsapp.phone_id', ''),
            'email_enabled' => config('services.email.enabled', false),
        ];
    }

    public function sendLowStockAlert(string $productName, int $currentStock, int $minimum, ?string $phone = null): void
    {
        $message = "⚠️ *Alerta de inventario*\n{$productName} tiene {$currentStock} unidades (min: {$minimum})";

        if ($this->config['whatsapp_enabled'] && $phone) {
            $this->sendWhatsApp($phone, $message);
        } else {
            Log::info("[NotificationService] WhatsApp desactivado. Simulando envio a {$phone}: {$message}");
        }
    }

    public function sendShipmentStatusUpdate(string $guideNumber, string $status, ?string $phone = null): void
    {
        $statusLabels = [
            'on_route' => 'en camino',
            'delivered' => 'entregada',
            'failed_delivery' => 'con novedad',
        ];
        $label = $statusLabels[$status] ?? $status;
        $message = "📦 *Guia {$guideNumber}*\nEstado actualizado: {$label}";

        if ($this->config['whatsapp_enabled'] && $phone) {
            $this->sendWhatsApp($phone, $message);
        } else {
            Log::info("[NotificationService] WhatsApp desactivado. Simulando envio a {$phone}: {$message}");
        }
    }

    public function sendDailySummary(string $adminPhone, array $metrics): void
    {
        $message = "📊 *Resumen diario*\n"
            . "Envios hoy: {$metrics['shipments_today']}\n"
            . "Entregadas: {$metrics['delivered_today']}\n"
            . "En transito: {$metrics['in_transit']}\n"
            . "Ingresos: \$".number_format($metrics['revenue_today'], 0, ',', '.');

        if ($this->config['whatsapp_enabled']) {
            $this->sendWhatsApp($adminPhone, $message);
        } else {
            Log::info("[NotificationService] WhatsApp desactivado. Simulando envio a {$adminPhone}: {$message}");
        }
    }

    private function sendWhatsApp(string $phone, string $message): void
    {
        $url = trim($this->config['whatsapp_api_url'], '/') . '/' . $this->config['whatsapp_phone_id'] . '/messages';

        try {
            \Illuminate\Support\Facades\Http::withToken($this->config['whatsapp_token'])
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Error WhatsApp: {$e->getMessage()}");
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function isEnabled(): bool
    {
        return $this->config['whatsapp_enabled'];
    }
}
