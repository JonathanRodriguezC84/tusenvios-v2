<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickProduct extends Model
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'name',
        'package_type',
        'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }
}
