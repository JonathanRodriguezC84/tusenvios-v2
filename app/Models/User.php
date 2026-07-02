<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isTenantAdmin(): bool
    {
        return $this->role === 'tenant_admin';
    }

    public function canManageTenants(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageAffiliates(): bool
    {
        return $this->isSuperAdmin() || $this->isTenantAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin() || $this->isTenantAdmin();
    }

    public function canMarkAffiliateSettlementsPaid(): bool
    {
        return $this->isSuperAdmin() || $this->isTenantAdmin();
    }

    public function canCreateShipments(): bool
    {
        if (! $this->accountIsActive()) {
            return false;
        }

        return in_array($this->role, ['superadmin', 'tenant_admin', 'affiliate', 'warehouse'], true);
    }

    public function canUseInventory(): bool
    {
        if (! $this->accountIsActive()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $tenant = $this->tenant ?: $this->affiliatedCompany?->tenant;

        if (! $tenant) {
            return false;
        }

        $subscription = $tenant->currentSubscription()->with('plan')->first();
        $planCode = $subscription?->plan?->code;

        return $planCode === 'fundador';
    }

    public function canEditShipments(): bool
    {
        if (! $this->accountIsActive()) {
            return false;
        }

        return in_array($this->role, ['superadmin', 'tenant_admin', 'warehouse'], true);
    }

    public function canScanShipments(): bool
    {
        if (! $this->accountIsActive()) {
            return false;
        }

        return in_array($this->role, ['superadmin', 'tenant_admin', 'warehouse', 'courier'], true);
    }

    public function accountIsActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->tenant && ! $this->tenant->subscriptionIsActive()) {
            return false;
        }

        if ($this->affiliatedCompany && $this->affiliatedCompany->status !== 'active') {
            return false;
        }

        return true;
    }
}
