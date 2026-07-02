<?php

namespace App\Notifications;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WhatsAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $event,
        public string $guideNumber = '',
        public string $destination = '',
        public string $trackingUrl = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'guide_number' => $this->guideNumber,
            'destination' => $this->destination,
            'tracking_url' => $this->trackingUrl,
            'sent_via' => 'whatsapp',
        ];
    }

    public function toWhatsApp(object $notifiable, WhatsAppService $whatsapp): array
    {
        $phone = $notifiable->phone ?? $notifiable->recipient_phone ?? '';
        if (!$phone) {
            return ['sent' => false, 'reason' => 'no_phone'];
        }

        return match ($this->event) {
            'created' => $whatsapp->sendShipmentCreated($phone, $this->guideNumber, $this->destination, $this->trackingUrl),
            'in_transit' => $whatsapp->sendShipmentInTransit($phone, $this->guideNumber, $this->destination),
            'delivered' => $whatsapp->sendShipmentDelivered($phone, $this->guideNumber),
            default => ['sent' => false, 'reason' => 'unknown_event'],
        };
    }
}