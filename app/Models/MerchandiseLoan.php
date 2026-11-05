<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchandiseLoan extends Model
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'loan_number',
        'borrower_name',
        'borrower_phone',
        'borrower_document',
        'borrower_address',
        'loan_date',
        'due_date',
        'status',
        'total_items',
        'total_value',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'total_items' => 'integer',
        'total_value' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany(): BelongsTo
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MerchandiseLoanItem::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'partial']);
    }

    public function isOverdue(): bool
    {
        return $this->isActive() && $this->due_date && $this->due_date->isPast();
    }

    public function pendingUnits(): int
    {
        return (int) $this->items->sum(fn ($i) => $i->pendingUnits());
    }

    public function returnedUnits(): int
    {
        return (int) $this->items->sum('quantity_returned');
    }

    public function soldUnits(): int
    {
        return (int) $this->items->sum('quantity_sold');
    }

    public function statusBadgeClass(): string
    {
        if ($this->status === 'returned') return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        if ($this->status === 'sold') return 'bg-blue-100 text-blue-800 border-blue-200';
        if ($this->status === 'cancelled') return 'bg-gray-100 text-gray-700 border-gray-200';
        if ($this->isOverdue()) return 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse';
        if ($this->status === 'partial') return 'bg-amber-100 text-amber-800 border-amber-200';
        return 'bg-indigo-100 text-indigo-800 border-indigo-200';
    }

    public function statusLabel(): string
    {
        if ($this->status === 'returned') return 'Devuelto';
        if ($this->status === 'sold') return 'Liquidado (Venta)';
        if ($this->status === 'cancelled') return 'Anulado';
        if ($this->isOverdue()) return 'Vencido';
        if ($this->status === 'partial') return 'Parcial';
        return 'En préstamo';
    }
}
