<?php

namespace App\Console\Commands;

use App\Models\InventoryProduct;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'inventory:check-low-stock {--tenant= : Tenant ID especifico}';
    protected $description = 'Revisa productos con stock bajo y envia notificaciones';

    public function handle()
    {
        $products = InventoryProduct::query()
            ->where('status', 'active')
            ->when($this->option('tenant'), fn ($q, $id) => $q->where('tenant_id', $id))
            ->get()
            ->filter(function ($product) {
                return $product->stock <= $product->stock_minimum;
            });

        if ($products->isEmpty()) {
            $this->info('No hay productos con stock bajo.');
            return 0;
        }

        $grouped = $products->groupBy('tenant_id');

        foreach ($grouped as $tenantId => $tenantProducts) {
            $tenant = Tenant::find($tenantId);
            if (! $tenant || ! $tenant->notify_low_stock) {
                $this->line("Tenant {$tenantId}: notificaciones desactivadas.");
                continue;
            }

            $admins = User::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('role', ['superadmin', 'tenant_admin', 'warehouse'])
                ->whereNotNull('email')
                ->get();

            if ($admins->isEmpty()) {
                $this->warn("Tenant {$tenantId}: no hay admins para notificar.");
                continue;
            }

            foreach ($tenantProducts as $product) {
                $alertType = $product->stock <= 0 ? 'out' : 'low';

                foreach ($admins as $admin) {
                    $admin->notify(new LowStockNotification($product, $alertType));
                }

                $this->line("Notificado: {$product->name} (stock: {$product->stock}, minimo: {$product->stock_minimum})");
            }
        }

        $this->info("Notificaciones enviadas para {$products->count()} producto(s).");
        return 0;
    }
}