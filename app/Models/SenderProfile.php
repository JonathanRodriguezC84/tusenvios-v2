<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SenderProfile extends Model
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'label',
        'name',
        'phone',
        'address',
        'neighborhood',
        'locality',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
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
