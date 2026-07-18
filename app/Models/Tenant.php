<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;
    protected $attributes = [
        'daily_tasks_enabled' => true,
    ];

    protected $fillable = [
        'name',
        'legal_name',
        'document_number',
        'email',
        'phone',
        'subdomain',
        'guide_prefix',
        'status',
        'logo_path',
        'brand_color',
        'brand_whatsapp',
        'brand_instagram',
        'brand_facebook',
        'brand_tiktok',
        'brand_website',
        'brand_message',
        'brand_phone',
        'brand_address',
        'brand_neighborhood',
        'brand_locality',
'label_template',
        'default_print_format',
        'notify_low_stock',
        'daily_tasks_enabled',
        'api_token',
        'webhook_url',
        'webhook_events',
        'balance',
    ];

    public function affiliatedCompanies()
    {
        return $this->hasMany(AffiliatedCompany::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function subscriptionPayments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(TenantSubscription::class)->latestOfMany();
    }

    public function subscriptionIsActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (! $this->relationLoaded('currentSubscription')) {
            $this->load('currentSubscription');
        }

        return ! $this->currentSubscription || $this->currentSubscription->isUsable();
    }

    public function quickProducts()
    {
        return $this->hasMany(QuickProduct::class);
    }

    public function inventoryProducts()
    {
        return $this->hasMany(InventoryProduct::class);
    }

    public function brandData(): array
    {
        return [
            'name' => $this->name,
            'logo_path' => $this->logo_path,
            'color' => $this->brand_color ?: '#0047D9',
            'whatsapp' => $this->brand_whatsapp,
            'instagram' => $this->brand_instagram,
            'facebook' => $this->brand_facebook,
            'tiktok' => $this->brand_tiktok,
            'website' => $this->brand_website,
            'message' => $this->brand_message ?: 'Gracias por tu compra.',
            'phone' => $this->brand_phone ?: $this->phone,
            'address' => $this->brand_address,
            'neighborhood' => $this->brand_neighborhood,
            'locality' => $this->brand_locality,
'template' => $this->label_template ?: 'classic',
            'default_print_format' => $this->default_print_format ?: '100x150',
            'notify_low_stock' => $this->notify_low_stock,
        ];
    }
}
