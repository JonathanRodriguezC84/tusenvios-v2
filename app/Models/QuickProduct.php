<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickProduct extends Model
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'name',
        'sku',
        'package_type',
        'cost',
        'price',
        'stock',
        'status',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = mb_strtoupper(trim((string) $value), 'UTF-8');
    }

    public function setSkuAttribute(?string $value): void
    {
        $value = trim((string) $value);
        $this->attributes['sku'] = $value === '' ? null : mb_strtoupper($value, 'UTF-8');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }
}
