<?php

namespace App\Services;

use App\Models\InventoryProduct;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    public function stockBadgeClass(InventoryProduct $product): string
    {
        if ($product->status === 'paused') {
            return 'is-paused';
        }

        if ($product->stock <= 0) {
            return 'is-out';
        }

        if ($product->isLowStock()) {
            return 'is-low';
        }

        return 'is-ok';
    }

    public function stockBadgeLabel(InventoryProduct $product): string
    {
        if ($product->status === 'paused') {
            return 'Pausado';
        }

        if ($product->stock <= 0) {
            return 'Agotado';
        }

        if ($product->isLowStock()) {
            return 'Stock bajo';
        }

        return 'Stock OK';
    }

    public function stockAlertLabel(InventoryProduct $product): string
    {
        if ($product->status === 'paused') {
            return 'Pausado';
        }

        if ($product->stock <= 0) {
            return 'Agotado';
        }

        if ($product->stock <= $product->stock_minimum) {
            return 'Stock bajo';
        }

        return 'Stock OK';
    }

    public function suggestedRestock(InventoryProduct $product): int
    {
        return max(1, (int) $product->stock_minimum - (int) $product->stock);
    }

    public function quickRestockButtonLabel(InventoryProduct $product): string
    {
        return $product->stock <= $product->stock_minimum ? 'Reponer' : 'Entrada';
    }

    public function movementTypeLabels(): array
    {
        return [
            'initial' => 'Inicial',
            'adjustment' => 'Ajuste',
            'shipment' => 'Guia',
            'restock' => 'Reposicion',
            'manual_in' => 'Entrada',
            'manual_out' => 'Salida',
            'status_change' => 'Estado',
        ];
    }

    public function statusLabels(): array
    {
        return [
            'active' => 'Activo',
            'paused' => 'Pausado',
        ];
    }

    public function themeVariables(): array
    {
        $color = '#022a8c';
        $user = auth()->user();

        if ($user) {
            $owner = $user->affiliatedCompany ?: $user->tenant;
            $brand = $owner?->brandData();
            $candidate = $brand['color'] ?? null;

            if (is_string($candidate) && preg_match('/^#[0-9A-Fa-f]{6}$/', $candidate)) {
                $color = strtolower($candidate);
            }
        }

        $red = hexdec(substr($color, 1, 2));
        $green = hexdec(substr($color, 3, 2));
        $blue = hexdec(substr($color, 5, 2));

        $text = (($red * 299 + $green * 587 + $blue * 114) / 1000) > 165 ? '#111827' : '#ffffff';

        return [
            'color' => $color,
            'text' => $text,
            'tint' => "rgba({$red}, {$green}, {$blue}, 0.08)",
            'border' => "rgba({$red}, {$green}, {$blue}, 0.28)",
            'soft' => "rgba({$red}, {$green}, {$blue}, 0.12)",
        ];
    }

    public function productReportSearch(InventoryProduct $product): string
    {
        return $product->sku ?: $product->name;
    }

    public function canDelete(InventoryProduct $product): bool
    {
        return $product->movements_count === 0;
    }
}
