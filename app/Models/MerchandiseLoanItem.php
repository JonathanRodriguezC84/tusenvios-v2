<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchandiseLoanItem extends Model
{
    protected $fillable = [
        'merchandise_loan_id',
        'inventory_product_id',
        'quantity_lent',
        'quantity_returned',
        'quantity_sold',
        'unit_price',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'quantity_lent' => 'integer',
        'quantity_returned' => 'integer',
        'quantity_sold' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(MerchandiseLoan::class, 'merchandise_loan_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function pendingUnits(): int
    {
        return max(0, $this->quantity_lent - $this->quantity_returned - $this->quantity_sold);
    }
}
