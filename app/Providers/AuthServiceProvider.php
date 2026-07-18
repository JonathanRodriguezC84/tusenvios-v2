<?php

namespace App\Providers;

use App\Models\Shipment;
use App\Policies\ShipmentPolicy;
use App\Policies\InventoryProductPolicy;
use App\Policies\QuickProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    }
}
