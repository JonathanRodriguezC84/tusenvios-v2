<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'price',
        'coverage_keywords',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
