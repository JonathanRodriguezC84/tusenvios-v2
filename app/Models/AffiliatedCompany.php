<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliatedCompany extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'guide_prefix',
        'document_number',
        'contact_name',
        'email',
        'phone',
        'default_payment_method',
        'allows_cod',
        'cod_commission_percent',
        'credit_limit',
        'billing_notes',
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
    ];

    protected $casts = [
        'allows_cod' => 'boolean',
        'cod_commission_percent' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function settlements()
    {
        return $this->hasMany(AffiliateSettlement::class);
    }

    public function senderProfiles()
    {
        return $this->hasMany(SenderProfile::class);
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
            'logo_path' => $this->logo_path ?: $this->tenant?->logo_path,
            'color' => $this->brand_color ?: $this->tenant?->brand_color ?: '#0047D9',
            'whatsapp' => $this->brand_whatsapp ?: $this->tenant?->brand_whatsapp,
            'instagram' => $this->brand_instagram ?: $this->tenant?->brand_instagram,
            'facebook' => $this->brand_facebook ?: $this->tenant?->brand_facebook,
            'tiktok' => $this->brand_tiktok ?: $this->tenant?->brand_tiktok,
            'website' => $this->brand_website ?: $this->tenant?->brand_website,
            'message' => $this->brand_message ?: $this->tenant?->brand_message ?: 'Gracias por tu compra.',
            'phone' => $this->brand_phone ?: $this->phone ?: $this->tenant?->brand_phone ?: $this->tenant?->phone,
            'address' => $this->brand_address ?: $this->tenant?->brand_address,
            'neighborhood' => $this->brand_neighborhood ?: $this->tenant?->brand_neighborhood,
            'locality' => $this->brand_locality ?: $this->tenant?->brand_locality,
'template' => $this->label_template ?: $this->tenant?->label_template ?: 'classic',
            'default_print_format' => $this->default_print_format ?: $this->tenant?->default_print_format ?: '100x150',
            'notify_low_stock' => $this->notify_low_stock,
        ];
    }
}
