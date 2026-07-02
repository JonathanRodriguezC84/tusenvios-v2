<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'inventory_product_id',
        'tenant_id',
        'affiliated_company_id',
        'shipment_id',
        'type',
        'quantity_delta',
        'stock_after',
        'notes',
    ];

    protected $casts = [
        'quantity_delta' => 'integer',
        'stock_after' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
