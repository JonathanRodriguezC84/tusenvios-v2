<?php

namespace App\Providers;

use App\Models\Shipment;
use App\Policies\ShipmentPolicy;
use App\Policies\InventoryProductPolicy;
use App\Policies\QuickProductPolicy;
use App\Policies\UserPolicy;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Shipment::class => ShipmentPolicy::class,
        'App\Models\InventoryProduct' => InventoryProductPolicy::class,
        'App\Models\QuickProduct' => QuickProductPolicy::class,
        'App\Models\User' => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-admin', fn (User $user) => $user->isSuperAdmin());

        Gate::define('view-audit-logs', fn (User $user) => $user->isSuperAdmin() || $user->isTenantAdmin());

        Gate::define('use-inventory', fn (User $user) => $user->canUseInventory());

        Gate::define('scan-shipments', fn (User $user) => $user->canScanShipments());

        Gate::define('edit-shipments', fn (User $user) => $user->canEditShipments());
    }
}
