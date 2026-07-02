<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateSettlementItem extends Model
{
    protected $fillable = [
        'affiliate_settlement_id',
        'shipment_id',
        'guide_number',
        'recipient_name',
        'status',
        'payment_method',
        'delivery_zone_name',
        'shipping_value',
        'collection_value',
        'commission_value',
    ];

    protected $casts = [
        'shipping_value' => 'decimal:2',
        'collection_value' => 'decimal:2',
        'commission_value' => 'decimal:2',
    ];

    public function settlement()
    {
        return $this->belongsTo(AffiliateSettlement::class, 'affiliate_settlement_id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
