<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'status',
        'start_mode',
        'trial_guide_limit',
        'trial_guide_used',
        'starts_at',
        'ends_at',
        'next_payment_at',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'next_payment_at' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isTrial(): bool
    {
        return $this->start_mode === 'trial_guides' && $this->trial_guide_limit > 0;
    }

    public function trialGuidesRemaining(): int
    {
        return max(0, (int) $this->trial_guide_limit - (int) $this->trial_guide_used);
    }

    public function canCreateTrialGuide(): bool
    {
        return $this->isTrial() && $this->trialGuidesRemaining() > 0;
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->isTrial()) {
            return true;
        }

        if ($this->next_payment_at && $this->next_payment_at->isPast() && ! $this->next_payment_at->isToday()) {
            return false;
        }

        return ! $this->ends_at || $this->ends_at->isToday() || $this->ends_at->isFuture();
    }

    public function markGuideCreated(): void
    {
        if (! $this->isTrial()) {
            return;
        }

        $this->increment('trial_guide_used');
    }
}