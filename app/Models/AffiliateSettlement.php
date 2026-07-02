<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateSettlement extends Model
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'created_by',
        'paid_by',
        'settlement_number',
        'date_from',
        'date_to',
        'shipments_count',
        'shipping_total',
        'collection_total',
        'commission_total',
        'net_collection',
        'total_to_invoice',
        'status',
        'notes',
        'closed_at',
        'paid_at',
        'payment_reference',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'shipping_total' => 'decimal:2',
        'collection_total' => 'decimal:2',
        'commission_total' => 'decimal:2',
        'net_collection' => 'decimal:2',
        'total_to_invoice' => 'decimal:2',
        'closed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function items()
    {
        return $this->hasMany(AffiliateSettlementItem::class);
    }
}
