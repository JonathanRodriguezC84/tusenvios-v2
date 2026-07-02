<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryProduct extends Model
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'name',
        'sku',
        'category',
        'cost',
        'price',
        'stock',
        'stock_minimum',
        'status',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimum' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_minimum;
    }
}
