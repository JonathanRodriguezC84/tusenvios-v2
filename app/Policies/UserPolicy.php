<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manageTenants(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageAffiliates(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTenantAdmin();
    }

    public function manageUsers(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTenantAdmin();
    }

    public function impersonate(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
