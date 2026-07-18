<?php

namespace App\Policies;

use App\Models\QuickProduct;
use App\Models\User;

class QuickProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->accountIsActive() && !$user->canUseInventory();
    }

    public function create(User $user): bool
    {
        return $user->accountIsActive() && !$user->canUseInventory();
    }

    public function update(User $user, QuickProduct $quickProduct): bool
    {
        return $user->accountIsActive() && !$user->canUseInventory() && $this->isOwner($user, $quickProduct);
    }

    public function delete(User $user, QuickProduct $quickProduct): bool
    {
        return $user->accountIsActive() && !$user->canUseInventory() && $this->isOwner($user, $quickProduct);
    }

    private function isOwner(User $user, QuickProduct $product): bool
    {
        if ($user->role === 'affiliate' && $user->affiliated_company_id) {
            return $product->affiliated_company_id === $user->affiliated_company_id;
        }
        return $product->tenant_id === $user->tenant_id && !$product->affiliated_company_id;
    }
}
