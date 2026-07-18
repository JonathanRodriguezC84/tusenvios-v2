<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->accountIsActive();
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $user->accountIsActive() && $shipment->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->canCreateShipments();
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $user->canEditShipments() && $shipment->isVisibleTo($user) && $shipment->canBeEdited();
    }

    public function cancel(User $user, Shipment $shipment): bool
    {
        return $user->canEditShipments() && $shipment->isVisibleTo($user) && $shipment->canBeCancelled();
    }

    public function updateStatus(User $user, Shipment $shipment): bool
    {
        return $user->canScanShipments() && $shipment->isVisibleTo($user);
    }

    public function bulkUpdateStatus(User $user): bool
    {
        return $user->canScanShipments() || $user->canEditShipments();
    }

    public function assignCourier(User $user, Shipment $shipment): bool
    {
        return $user->canEditShipments() && $shipment->isVisibleTo($user);
    }

    public function print(User $user, Shipment $shipment): bool
    {
        return $user->accountIsActive() && $shipment->isVisibleTo($user);
    }

    public function export(User $user): bool
    {
        return $user->accountIsActive();
    }
}
