<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuickProduct;
use App\Models\InventoryProduct;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function syncStock(Request $request): \Illuminate\Http\JsonResponse
    {
        $tenant = $request->__tenant;

        $validated = $request->validate([
            'stocks' => ['required', 'array'],
            'stocks.*.sku' => ['required', 'string'],
            'stocks.*.stock' => ['required', 'integer', 'min:0'],
        ]);

        $updated = 0;

        foreach ($validated['stocks'] as $item) {
            $sku = strtoupper(trim($item['sku']));
            $stock = (int) $item['stock'];

            // 1. Intentar en QuickProduct (Productos Rápidos)
            $product = QuickProduct::query()
                ->where('tenant_id', $tenant->id)
                ->where('sku', $sku)
                ->first();

            if ($product) {
                $product->update(['stock' => $stock]);
                $updated++;
                continue;
            }

            // 2. Intentar en InventoryProduct (Inventario Completo)
            $invProduct = InventoryProduct::query()
                ->where('tenant_id', $tenant->id)
                ->where('sku', $sku)
                ->first();

            if ($invProduct) {
                $invProduct->update(['stock' => $stock]);
                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'updated_count' => $updated,
        ]);
    }

    public function syncCatalog(Request $request): \Illuminate\Http\JsonResponse
    {
        $tenant = $request->__tenant;

        $validated = $request->validate([
            'products' => ['required', 'array'],
            'products.*.sku' => ['required', 'string', 'max:100'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.stock' => ['required', 'integer', 'min:0'],
            'products.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $created = 0;
        $updated = 0;

        foreach ($validated['products'] as $item) {
            $sku = strtoupper(trim($item['sku']));
            $name = trim($item['name']);
            $stock = (int) $item['stock'];
            $price = isset($item['price']) ? (float) $item['price'] : 0;

            $product = QuickProduct::query()
                ->where('tenant_id', $tenant->id)
                ->where('sku', $sku)
                ->first();

            if ($product) {
                $product->update(['stock' => $stock, 'name' => $name, 'price' => $price]);
                $updated++;
                continue;
            }

            $invProduct = InventoryProduct::query()
                ->where('tenant_id', $tenant->id)
                ->where('sku', $sku)
                ->first();

            if ($invProduct) {
                $invProduct->update(['stock' => $stock]);
                $updated++;
                continue;
            }

            QuickProduct::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'sku' => $sku,
                'stock' => $stock,
                'price' => $price,
                'cost' => 8000,
                'package_type' => 'sobre',
                'status' => 'active',
            ]);
            $created++;
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'updated' => $updated,
        ]);
    }
}
