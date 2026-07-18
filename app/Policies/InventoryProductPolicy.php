<?php

namespace App\Policies;

use App\Models\User;

class InventoryProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->accountIsActive() && $user->canUseInventory();
    }

    public function create(User $user): bool
    {
        return $user->accountIsActive() && $user->canUseInventory();
    }

    public function update(User $user): bool
    {
        return $user->accountIsActive() && $user->canUseInventory();
    }

    public function delete(User $user): bool
    {
        return $user->accountIsActive() && $user->canUseInventory();
    }

    public function export(User $user): bool
    {
        return $user->accountIsActive() && $user->canUseInventory();
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function movement(User $user): bool
    {
        return $user->accountIsActive() && $user->canUseInventory();
    }

    public function kardex(User $user): bool
    {
        return $user->accountIsActive() && $user->canUseInventory();
    }
}
