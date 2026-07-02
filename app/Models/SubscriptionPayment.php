<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_subscription_id',
        'subscription_plan_id',
        'provider',
        'reference',
        'provider_link_id',
        'provider_transaction_id',
        'status',
        'amount',
        'currency',
        'payment_url',
        'provider_payload',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'provider_payload' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription()
    {
        return $this->belongsTo(TenantSubscription::class, 'tenant_subscription_id');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}