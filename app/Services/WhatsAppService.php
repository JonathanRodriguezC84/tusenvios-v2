<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $token;
    protected string $phoneNumberId;
    protected bool $enabled;

    public function __construct(?array $whatsappConfig = null)
    {
        $this->apiUrl = $whatsappConfig['api_url'] ?? config('services.whatsapp.api_url', 'https://graph.facebook.com/v18.0');
        $this->token = $whatsappConfig['token'] ?? config('services.whatsapp.token', '');
        $this->phoneNumberId = $whatsappConfig['phone_number_id'] ?? config('services.whatsapp.phone_number_id', '');
        $this->enabled = $whatsappConfig['enabled'] ?? config('services.whatsapp.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->token) && !empty($this->phoneNumberId);
    }

    public function sendMessage(string $to, string $templateName, array $parameters = [], ?string $language = 'es'): array
    {
        if (!$this->isEnabled()) {
            Log::info('WhatsApp: disabled or not configured, skipping message', ['to' => $to, 'template' => $templateName]);
            return ['sent' => false, 'reason' => 'disabled'];
        }

        $components = $this->buildTemplateComponents($parameters);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
                'components' => $components,
            ],
        ];

        try {
            $response = Http::withToken($this->token)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                Log::info('WhatsApp: message sent', ['to' => $to, 'template' => $templateName]);
                return ['sent' => true, 'data' => $response->json()];
            }

            Log::error('WhatsApp: API error', ['status' => $response->status(), 'body' => $response->body()]);
            return ['sent' => false, 'error' => $response->json()];
        } catch (\Exception $e) {
            Log::error('WhatsApp: exception', ['message' => $e->getMessage()]);
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendTextMessage(string $to, string $text): array
    {
        if (!$this->isEnabled()) {
            return ['sent' => false, 'reason' => 'disabled'];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'text',
            'text' => ['body' => $text],
        ];

        try {
            $response = Http::withToken($this->token)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                Log::info('WhatsApp: text sent', ['to' => $to]);
                return ['sent' => true, 'data' => $response->json()];
            }

            return ['sent' => false, 'error' => $response->json()];
        } catch (\Exception $e) {
            Log::error('WhatsApp: exception', ['message' => $e->getMessage()]);
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendShipmentCreated(string $to, string $guideNumber, string $destination, string $trackingUrl): array
    {
        return $this->sendMessage($to, 'shipment_created', [
            ['type' => 'body', 'parameters' => [
                ['type' => 'text', 'text' => $guideNumber],
                ['type' => 'text', 'text' => $destination],
                ['type' => 'text', 'text' => $trackingUrl],
            ]],
        ]);
    }

    public function sendShipmentInTransit(string $to, string $guideNumber, string $currentCity): array
    {
        return $this->sendMessage($to, 'shipment_in_transit', [
            ['type' => 'body', 'parameters' => [
                ['type' => 'text', 'text' => $guideNumber],
                ['type' => 'text', 'text' => $currentCity],
            ]],
        ]);
    }

    public function sendShipmentDelivered(string $to, string $guideNumber): array
    {
        return $this->sendMessage($to, 'shipment_delivered', [
            ['type' => 'body', 'parameters' => [
                ['type' => 'text', 'text' => $guideNumber],
            ]],
        ]);
    }

    protected function buildTemplateComponents(array $parameters): array
    {
        $components = [];
        foreach ($parameters as $type => $params) {
            $component = [
                'type' => $type,
                'parameters' => [],
            ];
            foreach ($params as $param) {
                $component['parameters'][] = $param;
            }
            $components[] = $component;
        }
        return $components;
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10 && str_starts_with($phone, '3')) {
            $phone = '57' . $phone;
        }
        return $phone;
    }
}