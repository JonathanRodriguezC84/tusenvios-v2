<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $shipmentIds = DB::table('inventory_movements')
            ->where('type', 'shipment')
            ->whereNotNull('shipment_id')
            ->distinct()
            ->pluck('shipment_id');

        foreach ($shipmentIds as $shipmentId) {
            $shipment = DB::table('shipments')->where('id', $shipmentId)->first();

            if (! $shipment || $shipment->inventory_snapshot) {
                continue;
            }

            $movements = DB::table('inventory_movements')
                ->join('inventory_products', 'inventory_products.id', '=', 'inventory_movements.inventory_product_id')
                ->where('inventory_movements.shipment_id', $shipmentId)
                ->where('inventory_movements.type', 'shipment')
                ->select([
                    'inventory_products.id',
                    'inventory_products.name',
                    'inventory_products.sku',
                    'inventory_products.category',
                    'inventory_products.cost',
                    'inventory_products.price',
                    'inventory_movements.quantity_delta',
                    'inventory_movements.stock_after',
                ])
                ->get();

            if ($movements->isEmpty()) {
                continue;
            }

            $snapshot = $movements->map(fn ($movement) => [
                'id' => $movement->id,
                'name' => $movement->name,
                'sku' => $movement->sku,
                'category' => $movement->category,
                'cost' => (float) $movement->cost,
                'price' => (float) $movement->price,
                'quantity' => abs((int) $movement->quantity_delta),
                'stock_after' => (int) $movement->stock_after,
            ])->values()->all();

            DB::table('shipments')
                ->where('id', $shipmentId)
                ->update(['inventory_snapshot' => json_encode($snapshot)]);
        }
    }

    public function down(): void
    {
        // Backfilled snapshots are useful historical data, so this migration is intentionally not destructive.
    }
};
