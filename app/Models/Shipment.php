<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;
    public const STATUS_FLOW = [
        'created' => ['printed', 'cancelled'],
        'printed' => ['in_warehouse'],
        'in_warehouse' => ['in_sorting', 'assigned', 'on_route'],
        'in_sorting' => ['assigned', 'on_route'],
        'assigned' => ['on_route'],
        'on_route' => ['delivered', 'failed_delivery', 'rescheduled', 'return_pending', 'cancelled'],
        'failed_delivery' => ['rescheduled', 'return_pending', 'on_route'],
        'rescheduled' => ['on_route', 'return_pending'],
        'return_pending' => ['returned'],
        'delivered' => [],
        'returned' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'created_by',
        'courier_id',
        'guide_number',
        'status',
        'service_type',
        'estimated_delivery_date',
        'sender_name',
        'sender_document',
        'sender_phone',
        'sender_address',
        'sender_neighborhood',
        'sender_locality',
        'sender_notes',
        'recipient_name',
        'recipient_lastname',
        'recipient_document',
        'recipient_phone',
        'recipient_alt_phone',
        'recipient_address',
        'recipient_neighborhood',
        'recipient_locality',
        'recipient_city',
        'recipient_notes',
        'package_type',
        'pieces',
        'weight_kg',
        'content_description',
        'inventory_snapshot',
        'declared_value',
        'shipping_value',
        'payment_method',
        'collection_value',
        'zone',
        'delivery_zone_id',
        'delivery_attempts',
        'issue_reason',
    ];

    protected $casts = [
        'estimated_delivery_date' => 'date',
        'weight_kg' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'inventory_snapshot' => 'array',
        'shipping_value' => 'decimal:2',
        'collection_value' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }

    public function settlementItems()
    {
        return $this->hasMany(AffiliateSettlementItem::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function barcodeValue(): string
    {
        return str_replace('-', '', $this->guide_number);
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'created';
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'created';
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::STATUS_FLOW[$this->status] ?? [], true);
    }

    public function nextScanStatusFor(User $user): ?string
    {
        if ($this->status === 'printed' && in_array($user->role, ['superadmin', 'tenant_admin', 'warehouse'], true)) {
            return 'in_warehouse';
        }

        if (in_array($this->status, ['in_warehouse', 'in_sorting', 'assigned'], true) && in_array($user->role, ['superadmin', 'tenant_admin', 'courier'], true)) {
            return 'on_route';
        }

        if ($this->status === 'on_route' && in_array($user->role, ['superadmin', 'tenant_admin', 'courier'], true)) {
            return 'delivered';
        }

        if ($this->status === 'return_pending' && in_array($user->role, ['superadmin', 'tenant_admin', 'warehouse'], true)) {
            return 'returned';
        }

        return null;
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->role === 'affiliate' && $user->affiliated_company_id) {
            return $query->where('affiliated_company_id', $user->affiliated_company_id);
        }

        if ($user->role === 'courier') {
            return $query->where('courier_id', $user->id);
        }

        if ($user->tenant_id) {
            return $query->where('tenant_id', $user->tenant_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role === 'affiliate') {
            return $this->affiliated_company_id && $this->affiliated_company_id === $user->affiliated_company_id;
        }

        if ($user->role === 'courier') {
            return $this->courier_id && $this->courier_id === $user->id;
        }

        return $this->tenant_id && $this->tenant_id === $user->tenant_id;
    }
}
