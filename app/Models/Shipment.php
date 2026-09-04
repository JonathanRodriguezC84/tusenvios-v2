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

    /**
     * Agrupacion de estados visible para el plan Emprende: los 12 estados
     * internos se muestran en 3 grupos. El flujo interno (STATUS_FLOW y la
     * linea de tiempo) conserva los estados reales.
     */
    public const STATUS_GROUPS = [
        'on_route' => ['label' => 'En camino', 'statuses' => ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route', 'failed_delivery', 'rescheduled']],
        'delivered' => ['label' => 'Entregada', 'statuses' => ['delivered']],
        'returned' => ['label' => 'Devuelta', 'statuses' => ['return_pending', 'returned']],
        'cancelled' => ['label' => 'Cancelada', 'statuses' => ['cancelled']],
    ];

    /**
     * Estados que componen la jornada de Tareas Diarias (guia pendiente).
     */
    public const DAILY_PENDING_STATUSES = [
        'created', 'printed', 'in_warehouse', 'in_sorting', 'assigned',
        'on_route', 'failed_delivery', 'rescheduled', 'return_pending',
    ];

    public static function statusGroupKey(string $status): string
    {
        foreach (self::STATUS_GROUPS as $key => $group) {
            if (in_array($status, $group['statuses'], true)) {
                return $key;
            }
        }

        return $status;
    }

    public static function statusGroupLabel(string $status): string
    {
        foreach (self::STATUS_GROUPS as $group) {
            if (in_array($status, $group['statuses'], true)) {
                return $group['label'];
            }
        }

        return $status;
    }

    public static function statusesForFilter($status): array
    {
        if (isset(self::STATUS_GROUPS[$status])) {
            return self::STATUS_GROUPS[$status]['statuses'];
        }

        return [(string) $status];
    }

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
        if (in_array($status, self::STATUS_FLOW[$this->status] ?? [], true)) {
            return true;
        }

        // Plan Emprende: la vista solo muestra 3 grupos. Permite saltar
        // directamente entre grupos (ej. En camino -> Entregada / Devuelta),
        // pero una guia entregada o devuelta queda cerrada.
        $fromGroup = self::statusGroupKey($this->status);

        if ($fromGroup === 'on_route' && self::statusGroupKey($status) !== 'on_route') {
            return true;
        }

        // Volver a "Por imprimir" desde cualquier guia en operacion no cerrada.
        if ($status === 'created' && $fromGroup === 'on_route' && $this->status !== 'created') {
            return true;
        }

        return false;
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
