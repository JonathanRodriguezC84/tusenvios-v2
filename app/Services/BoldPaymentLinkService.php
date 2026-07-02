<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BoldPaymentLinkService
{
    public function createLink(SubscriptionPayment $payment): array
    {
        $apiKey = config('services.bold.api_key');

        if (! $apiKey) {
            throw new RuntimeException('Falta configurar BOLD_API_KEY en el archivo .env.');
        }

        $expiresAt = now()->addDays(7);

        $payload = [
            'amount_type' => 'CLOSE',
            'amount' => [
                'currency' => $payment->currency,
                'total_amount' => $payment->amount,
                'tip_amount' => 0,
            ],
            'reference' => $payment->reference,
            'description' => 'Mensualidad Tus Envios - '.$payment->tenant->name,
            'expiration_date' => (int) ($expiresAt->getTimestamp() * 1000000000),
            'payment_methods' => ['CREDIT_CARD', 'PSE', 'BOTON_BANCOLOMBIA', 'NEQUI'],
            'payer_email' => $payment->tenant->email,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'x-api-key '.$apiKey,
            'Accept' => 'application/json',
        ])->post(rtrim(config('services.bold.base_url'), '/').'/online/link/v1', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Bold no pudo crear el link de pago. Respuesta: '.$response->body());
        }

        $data = $response->json();
        $boldPayload = $data['payload'] ?? [];

        $payment->update([
            'provider_link_id' => $boldPayload['payment_link'] ?? null,
            'payment_url' => $boldPayload['url'] ?? null,
            'expires_at' => $expiresAt,
            'provider_payload' => $data,
        ]);

        return $data;
    }

    public function syncStatus(SubscriptionPayment $payment): array
    {
        $apiKey = config('services.bold.api_key');

        if (! $apiKey) {
            throw new RuntimeException('Falta configurar BOLD_API_KEY en el archivo .env.');
        }

        if (! $payment->provider_link_id) {
            throw new RuntimeException('Este pago no tiene link de Bold asociado.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'x-api-key '.$apiKey,
            'Accept' => 'application/json',
        ])->get(rtrim(config('services.bold.base_url'), '/').'/online/link/v1/'.$payment->provider_link_id);

        if (! $response->successful()) {
            throw new RuntimeException('Bold no pudo consultar el pago. Respuesta: '.$response->body());
        }

        $data = $response->json();
        $payload = $data['payload'] ?? $data;
        $boldStatus = strtoupper($payload['status'] ?? 'ACTIVE');

        $status = match ($boldStatus) {
            'PAID' => 'paid',
            'PROCESSING' => 'processing',
            'REJECTED' => 'rejected',
            'CANCELLED' => 'cancelled',
            'EXPIRED' => 'expired',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'provider_transaction_id' => $payload['transaction_id'] ?? $payment->provider_transaction_id,
            'paid_at' => $status === 'paid' ? now() : $payment->paid_at,
            'provider_payload' => $data,
        ]);

        if ($status === 'paid' && $payment->subscription) {
            $baseDate = $payment->subscription->next_payment_at && $payment->subscription->next_payment_at->isFuture()
                ? $payment->subscription->next_payment_at
                : today();

            $payment->subscription->update([
                'status' => 'active',
                'starts_at' => $payment->subscription->starts_at ?: today(),
                'ends_at' => null,
                'next_payment_at' => $baseDate->copy()->addMonth(),
            ]);

            $payment->tenant->update(['status' => 'active']);
        }

        return $data;
    }
}