<?php

namespace App\Notifications;

use App\Models\InventoryProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public InventoryProduct $product,
        public string $alertType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeLabel = $this->alertType === 'out' ? 'AGOTADO' : 'STOCK BAJO';
        $stock = $this->product->stock;
        $min = $this->product->stock_minimum;

        return (new MailMessage)
            ->subject("[{$typeLabel}] {$this->product->name}")
            ->greeting("Alerta de {$typeLabel}")
            ->line("El producto **{$this->product->name}** tiene {$stock} unidades en stock (minimo: {$min}).")
            ->when($this->product->sku, fn ($msg) => $msg->line("SKU: {$this->product->sku}"))
            ->when($this->product->category, fn ($msg) => $msg->line("Categoria: {$this->product->category}"))
            ->action('Ver inventario', url('/inventory'))
            ->salutation('Tus Envios - Inventario');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'stock' => $this->product->stock,
            'stock_minimum' => $this->product->stock_minimum,
            'alert_type' => $this->alertType,
            'category' => $this->product->category,
        ];
    }
}