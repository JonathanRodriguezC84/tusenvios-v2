<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrequentRecipient extends Model
{
    protected $fillable = [
        'tenant_id',
        'affiliated_company_id',
        'name',
        'lastname',
        'document',
        'phone',
        'alt_phone',
        'address',
        'neighborhood',
        'locality',
        'city',
        'notes',
        'use_count',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function affiliatedCompany()
    {
        return $this->belongsTo(AffiliatedCompany::class);
    }

    public static function getForUser($user)
    {
        return static::query()
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id);
                if ($user->affiliated_company_id) {
                    $query->orWhere('affiliated_company_id', $user->affiliated_company_id);
                }
            })
            ->orderByDesc('use_count')
            ->orderByDesc('updated_at')
            ->take(50)
            ->get();
    }

    public static function createFromShipment($shipment)
    {
        $ownerId = $shipment->affiliated_company_id
            ? ['affiliated_company_id' => $shipment->affiliated_company_id, 'tenant_id' => null]
            : ['tenant_id' => $shipment->tenant_id, 'affiliated_company_id' => null];

        $existing = static::query()
            ->where($ownerId)
            ->where('name', $shipment->recipient_name)
            ->when($shipment->recipient_phone, fn ($q) => $q->where('phone', $shipment->recipient_phone))
            ->first();

        if ($existing) {
            $existing->increment('use_count');
            $existing->update([
                'lastname' => $shipment->recipient_lastname ?? $existing->lastname,
                'phone' => $shipment->recipient_phone ?? $existing->phone,
                'address' => $shipment->recipient_address ?? $existing->address,
                'city' => $shipment->recipient_city ?? $existing->city,
                'locality' => $shipment->recipient_locality ?? $existing->locality,
                'neighborhood' => $shipment->recipient_neighborhood ?? $existing->neighborhood,
            ]);
            return $existing;
        }

        return static::create(array_merge($ownerId, [
            'name' => $shipment->recipient_name,
            'lastname' => $shipment->recipient_lastname,
            'document' => $shipment->recipient_document,
            'phone' => $shipment->recipient_phone,
            'alt_phone' => $shipment->recipient_alt_phone,
            'address' => $shipment->recipient_address,
            'neighborhood' => $shipment->recipient_neighborhood,
            'locality' => $shipment->recipient_locality,
            'city' => $shipment->recipient_city,
            'notes' => $shipment->recipient_notes,
            'use_count' => 1,
        ]));
    }
}