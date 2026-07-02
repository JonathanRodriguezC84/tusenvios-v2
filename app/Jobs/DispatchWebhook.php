<?php

namespace App\Jobs;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $webhookUrl,
        public Shipment $shipment,
        public string $newStatus,
    ) {}

    public function handle(): void
    {
        $statusLabels = [
            'created' => 'Por imprimir', 'printed' => 'Impresa',
            'delivered' => 'Entregada', 'cancelled' => 'Cancelada',
            'failed_delivery' => 'Novedad', 'on_route' => 'En camino',
            'returned' => 'Devuelta',
        ];

        $payload = [
            'event' => 'shipment.status_updated',
            'guide_number' => $this->shipment->guide_number,
            'barcode' => $this->shipment->barcodeValue(),
            'status' => $this->newStatus,
            'status_label' => $statusLabels[$this->newStatus] ?? $this->newStatus,
            'recipient_name' => $this->shipment->recipient_name . ' ' . $this->shipment->recipient_lastname,
            'recipient_phone' => $this->shipment->recipient_phone,
            'recipient_address' => $this->shipment->recipient_address,
            'tracking_url' => url("/track/{$this->shipment->guide_number}"),
            'updated_at' => now()->toIso8601String(),
        ];

        Http::timeout(10)
            ->retry(2, 1000)
            ->post($this->webhookUrl, $payload);
    }
}
