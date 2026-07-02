<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Shipment $shipment,
        public string $event,
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $phone = $this->shipment->recipient_phone;
        if (!$phone) {
            Log::warning('WhatsApp: no recipient phone', ['shipment_id' => $this->shipment->id]);
            return;
        }

        $guideNumber = $this->shipment->guide_number ?? $this->shipment->id;
        $trackingUrl = route('tracking.show', $guideNumber);

        match ($this->event) {
            'created' => $whatsapp->sendShipmentCreated($phone, $guideNumber, $this->shipment->destination_city ?? '', $trackingUrl),
            'in_transit' => $whatsapp->sendShipmentInTransit($phone, $guideNumber, $this->shipment->current_city ?? ''),
            'delivered' => $whatsapp->sendShipmentDelivered($phone, $guideNumber),
            default => Log::warning('WhatsApp: unknown event', ['event' => $this->event]),
        };
    }
}