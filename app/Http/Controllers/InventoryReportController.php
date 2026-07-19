<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\InventoryProduct;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryReportController extends Controller
{
    public function sales(Request $request): \Illuminate\View\View
    {
        $this->authorize('use-inventory');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:sales,profit,margin,units,shipments,name'],
        ]);

        ['rows' => $rows, 'totals' => $totals] = $this->salesRows($filters);
        $categories = $this->inventoryCategories();

        return view('inventory.sales-report', compact('rows', 'totals', 'filters', 'categories'));
    }

    public function exportSales(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('use-inventory');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:sales,profit,margin,units,shipments,name'],
        ]);

        ['rows' => $rows] = $this->salesRows($filters);
        $fileName = 'ventas-productos-inventario-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Producto', 'SKU', 'Categoria', 'Unidades', 'Guias', 'Venta', 'Costo estimado', 'Utilidad estimada', 'Margen %', 'Guias relacionadas']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['sku'],
                    $row['category'],
                    $row['units'],
                    $row['shipments_count'],
                    $row['sales_value'],
                    $row['cost_value'],
                    $row['profit_value'],
                    $row['margin_percent'],
                    collect($row['shipments'])->pluck('guide_number')->implode(' | '),
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportSalesPdf(Request $request): \Illuminate\Http\Response
    {
        $this->authorize('use-inventory');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:sales,profit,margin,units,shipments,name'],
        ]);

        ['rows' => $rows, 'totals' => $totals] = $this->salesRows($filters);

        $pdf = Pdf::loadView('inventory.sales-export-pdf', compact('rows', 'totals'));

        return $pdf->download('ventas-productos-'.now()->format('Y-m-d').'.pdf');
    }

    public function rotation(Request $request): \Illuminate\View\View
    {
        $this->authorize('use-inventory');

        $filters = $this->validateRotationFilters($request);
        $days = (int) ($filters['days'] ?? 30);
        ['rows' => $rows, 'totals' => $totals] = $this->rotationRows($filters);

        $categories = $this->inventoryCategories();

        return view('inventory.rotation-report', compact('rows', 'totals', 'filters', 'categories', 'days'));
    }

    public function exportRotation(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('use-inventory');

        $filters = $this->validateRotationFilters($request);
        $days = (int) ($filters['days'] ?? 30);
        ['rows' => $rows] = $this->rotationRows($filters);
        $fileName = 'rotacion-inventario-'.$days.'-dias-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Producto', 'SKU', 'Categoria', 'Estado', 'Vendidas', 'Promedio diario', 'Stock', 'Stock minimo', 'Cobertura dias', 'Reposicion sugerida', 'Costo reposicion', 'Valor stock', 'Venta potencial', 'Accion']);

            foreach ($rows as $row) {
                $product = $row['product'];

                fputcsv($handle, [
                    $product->name,
                    $product->sku,
                    $product->category,
                    $product->status,
                    $row['units'],
                    round((float) $row['daily_average'], 2),
                    $product->stock,
                    $product->stock_minimum,
                    $row['days_of_stock'] ?? 'Sin ventas',
                    $row['reorder_quantity'],
                    $row['reorder_cost'],
                    $row['stock_value'],
                    $row['sale_value'],
                    $row['action'],
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportRotationPdf(Request $request): \Illuminate\Http\Response
    {
        $this->authorize('use-inventory');

        $filters = $this->validateRotationFilters($request);
        $days = (int) ($filters['days'] ?? 30);
        ['rows' => $rows] = $this->rotationRows($filters);

        $pdf = Pdf::loadView('inventory.rotation-export-pdf', compact('rows', 'days'));

        return $pdf->download('rotacion-inventario-'.$days.'-dias-'.now()->format('Y-m-d').'.pdf');
    }

    public function categories(Request $request): \Illuminate\View\View
    {
        $this->authorize('use-inventory');

        $filters = $this->validateCategoryFilters($request);
        ['rows' => $rows, 'totals' => $totals] = $this->categoryRows($filters);

        return view('inventory.category-report', compact('rows', 'totals', 'filters'));
    }

    public function exportCategories(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('use-inventory');

        $filters = $this->validateCategoryFilters($request);
        ['rows' => $rows] = $this->categoryRows($filters);
        $fileName = 'categorias-inventario-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Categoria', 'Productos', 'Activos', 'Pausados', 'Unidades', 'Stock bajo', 'Agotados', 'Costo activo', 'Venta activa', 'Utilidad potencial', 'Margen %']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['category'],
                    $row['products'],
                    $row['active'],
                    $row['paused'],
                    $row['units'],
                    $row['low_stock'],
                    $row['out_stock'],
                    $row['cost_value'],
                    $row['sale_value'],
                    $row['profit_value'],
                    $row['margin_percent'],
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportCategoriesPdf(Request $request): \Illuminate\Http\Response
    {
        $this->authorize('use-inventory');

        $filters = $this->validateCategoryFilters($request);
        ['rows' => $rows, 'totals' => $totals] = $this->categoryRows($filters);

        $pdf = Pdf::loadView('inventory.category-export-pdf', compact('rows', 'totals'));

        return $pdf->download('categorias-inventario-'.now()->format('Y-m-d').'.pdf');
    }

    private function salesRows(array $filters): array
    {
        $rows = [];
        $uniqueShipmentIds = [];

        Shipment::query()
            ->visibleTo(auth()->user())
            ->whereNotNull('inventory_snapshot')
            ->where('status', '!=', 'cancelled')
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->get()
            ->each(function (Shipment $shipment) use (&$rows, &$uniqueShipmentIds) {
                $uniqueShipmentIds[$shipment->id] = true;

                foreach (($shipment->inventory_snapshot ?? []) as $item) {
                    $key = (string) ($item['id'] ?? $item['sku'] ?? $item['name'] ?? 'sin_producto');
                    $quantity = (int) ($item['quantity'] ?? 1);
                    $price = (float) ($item['price'] ?? 0);
                    $cost = array_key_exists('cost', $item)
                        ? (float) $item['cost']
                        : (float) InventoryProduct::query()->where('id', $item['id'] ?? null)->value('cost');

                    $rows[$key] ??= [
                        'name' => $item['name'] ?? 'Producto',
                        'sku' => $item['sku'] ?? '',
                        'category' => $item['category'] ?? '',
                        'units' => 0,
                        'sales_value' => 0,
                        'cost_value' => 0,
                        'shipments' => [],
                    ];

                    $rows[$key]['units'] += $quantity;
                    $rows[$key]['sales_value'] += $quantity * $price;
                    $rows[$key]['cost_value'] += $quantity * $cost;
                    $rows[$key]['shipments'][$shipment->id] = [
                        'id' => $shipment->id,
                        'guide_number' => $shipment->guide_number,
                    ];
                }
            });

        $search = Str::lower(trim((string) ($filters['q'] ?? '')));
        $category = trim((string) ($filters['category'] ?? ''));

        $rows = collect($rows)
            ->map(function ($row) {
                $row['shipments_count'] = count($row['shipments']);
                $row['shipments'] = array_values($row['shipments']);
                $row['profit_value'] = $row['sales_value'] - $row['cost_value'];
                $row['margin_percent'] = $row['sales_value'] > 0
                    ? round(($row['profit_value'] / $row['sales_value']) * 100, 1)
                    : 0;

                return $row;
            })
            ->when($search !== '', function ($rows) use ($search) {
                return $rows->filter(function ($row) use ($search) {
                    $haystack = Str::lower(implode(' ', [
                        $row['name'],
                        $row['sku'],
                        $row['category'],
                    ]));

                    return Str::contains($haystack, $search);
                });
            })
            ->when($category !== '', fn ($rows) => $rows->where('category', $category))
            ->when(($filters['sort'] ?? 'sales') === 'name', fn ($rows) => $rows->sortBy('name'))
            ->when(($filters['sort'] ?? 'sales') === 'sales', fn ($rows) => $rows->sortByDesc('sales_value'))
            ->when(($filters['sort'] ?? 'sales') === 'profit', fn ($rows) => $rows->sortByDesc('profit_value'))
            ->when(($filters['sort'] ?? 'sales') === 'margin', fn ($rows) => $rows->sortByDesc('margin_percent'))
            ->when(($filters['sort'] ?? 'sales') === 'units', fn ($rows) => $rows->sortByDesc('units'))
            ->when(($filters['sort'] ?? 'sales') === 'shipments', fn ($rows) => $rows->sortByDesc('shipments_count'))
            ->values();

        $totals = [
            'units' => $rows->sum('units'),
            'sales_value' => $rows->sum('sales_value'),
            'cost_value' => $rows->sum('cost_value'),
            'profit_value' => $rows->sum('profit_value'),
            'products' => $rows->count(),
            'shipments' => count($uniqueShipmentIds),
            'lines' => $rows->sum('shipments_count'),
        ];

        return [
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    private function categoryRows(array $filters): array
    {
        $search = Str::lower(trim((string) ($filters['q'] ?? '')));

        $rows = $this->queryProductsForOwner()
            ->get()
            ->groupBy(fn (InventoryProduct $product) => filled($product->category) ? $product->category : 'Sin categoria')
            ->map(function ($products, string $category) {
                $activeProducts = $products->where('status', 'active');
                $saleValue = $activeProducts->sum(fn ($product) => (float) $product->stock * (float) $product->price);
                $costValue = $activeProducts->sum(fn ($product) => (float) $product->stock * (float) $product->cost);
                $profitValue = $saleValue - $costValue;

                return [
                    'category' => $category,
                    'products' => $products->count(),
                    'active' => $activeProducts->count(),
                    'paused' => $products->where('status', 'paused')->count(),
                    'units' => $activeProducts->sum('stock'),
                    'low_stock' => $activeProducts->filter(fn ($product) => $product->stock <= $product->stock_minimum)->count(),
                    'out_stock' => $activeProducts->where('stock', '<=', 0)->count(),
                    'cost_value' => $costValue,
                    'sale_value' => $saleValue,
                    'profit_value' => $profitValue,
                    'margin_percent' => $saleValue > 0 ? round(($profitValue / $saleValue) * 100, 1) : 0,
                ];
            })
            ->when($search !== '', fn ($rows) => $rows->filter(fn ($row) => Str::contains(Str::lower($row['category']), $search)))
            ->when(($filters['sort'] ?? 'sale_value') === 'sale_value', fn ($rows) => $rows->sortByDesc('sale_value'))
            ->when(($filters['sort'] ?? 'sale_value') === 'profit_value', fn ($rows) => $rows->sortByDesc('profit_value'))
            ->when(($filters['sort'] ?? 'sale_value') === 'units', fn ($rows) => $rows->sortByDesc('units'))
            ->when(($filters['sort'] ?? 'sale_value') === 'alerts', fn ($rows) => $rows->sortByDesc(fn ($row) => $row['low_stock'] + $row['out_stock']))
            ->when(($filters['sort'] ?? 'sale_value') === 'name', fn ($rows) => $rows->sortBy('category'))
            ->values();

        $totals = [
            'categories' => $rows->count(),
            'products' => $rows->sum('products'),
            'units' => $rows->sum('units'),
            'low_stock' => $rows->sum('low_stock'),
            'out_stock' => $rows->sum('out_stock'),
            'cost_value' => $rows->sum('cost_value'),
            'sale_value' => $rows->sum('sale_value'),
            'profit_value' => $rows->sum('profit_value'),
        ];

        return [
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    private function validateCategoryFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:sale_value,profit_value,units,alerts,name'],
        ]);
    }

    private function inventoryCategories()
    {
        return $this->queryProductsForOwner()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    private function rotationRows(array $filters): array
    {
        $days = (int) ($filters['days'] ?? 30);
        $salesByKey = $this->salesUnitsByProductKey($days);
        $search = Str::lower(trim((string) ($filters['q'] ?? '')));

        $rows = $this->queryProductsForOwner()
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->orderBy('name')
            ->get()
            ->map(function (InventoryProduct $product) use ($salesByKey, $days) {
                $units = 0;

                foreach ($this->productKeys($product) as $key) {
                    if (array_key_exists($key, $salesByKey)) {
                        $units = (int) $salesByKey[$key];
                        break;
                    }
                }

                $dailyAverage = $days > 0 ? $units / $days : 0;
                $daysOfStock = $dailyAverage > 0 ? (int) floor($product->stock / $dailyAverage) : null;
                $targetStock = max($product->stock_minimum, (int) ceil($dailyAverage * $days));
                $reorderQuantity = max(0, $targetStock - $product->stock);

                return [
                    'product' => $product,
                    'units' => $units,
                    'daily_average' => $dailyAverage,
                    'days_of_stock' => $daysOfStock,
                    'reorder_quantity' => $reorderQuantity,
                    'reorder_cost' => $reorderQuantity * (float) $product->cost,
                    'action' => $this->rotationAction($product, $units),
                    'stock_value' => (float) $product->stock * (float) $product->cost,
                    'sale_value' => (float) $product->stock * (float) $product->price,
                ];
            })
            ->when($search !== '', function ($rows) use ($search) {
                return $rows->filter(function ($row) use ($search) {
                    $product = $row['product'];
                    $haystack = Str::lower(implode(' ', [
                        $product->name,
                        $product->sku,
                        $product->category,
                    ]));

                    return Str::contains($haystack, $search);
                });
            })
            ->when($filters['action'] ?? null, function ($rows, $action) {
                return $action === 'comprar'
                    ? $rows->where('reorder_quantity', '>', 0)
                    : $rows->where('action', $action);
            })
            ->when(($filters['sort'] ?? 'velocity') === 'velocity', fn ($rows) => $rows->sortByDesc('daily_average'))
            ->when(($filters['sort'] ?? 'velocity') === 'units', fn ($rows) => $rows->sortByDesc('units'))
            ->when(($filters['sort'] ?? 'velocity') === 'reorder', fn ($rows) => $rows->sortByDesc('reorder_quantity'))
            ->when(($filters['sort'] ?? 'velocity') === 'stock', fn ($rows) => $rows->sortBy('stock_value'))
            ->when(($filters['sort'] ?? 'velocity') === 'name', fn ($rows) => $rows->sortBy(fn ($row) => $row['product']->name))
            ->values();

        $totals = [
            'products' => $rows->count(),
            'units' => $rows->sum('units'),
            'stock_units' => $rows->sum(fn ($row) => $row['product']->stock),
            'stock_value' => $rows->sum('stock_value'),
            'reorder_units' => $rows->sum('reorder_quantity'),
            'reorder_cost' => $rows->sum('reorder_cost'),
            'restock' => $rows->whereIn('action', ['agotado', 'reponer'])->count(),
            'quiet' => $rows->where('action', 'quieto')->count(),
        ];

        return [
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    private function validateRotationFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'action' => ['nullable', 'in:comprar,agotado,reponer,moviendo,quieto,pausado'],
            'days' => ['nullable', 'integer', 'in:7,30,60,90'],
            'sort' => ['nullable', 'in:velocity,units,reorder,stock,name'],
        ]);
    }

    private function salesUnitsByProductKey(int $days): array
    {
        $salesByKey = [];

        Shipment::query()
            ->visibleTo(auth()->user())
            ->whereNotNull('inventory_snapshot')
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->get()
            ->each(function (Shipment $shipment) use (&$salesByKey) {
                foreach (($shipment->inventory_snapshot ?? []) as $item) {
                    $quantity = (int) ($item['quantity'] ?? 1);
                    $key = $this->snapshotKey($item);

                    if ($key) {
                        $salesByKey[$key] = ($salesByKey[$key] ?? 0) + $quantity;
                    }
                }
            });

        return $salesByKey;
    }

    private function rotationAction(InventoryProduct $product, int $units): string
    {
        if ($product->status === 'paused') {
            return 'pausado';
        }

        if ($product->stock <= 0) {
            return 'agotado';
        }

        if ($product->stock <= $product->stock_minimum) {
            return 'reponer';
        }

        if ($units <= 0) {
            return 'quieto';
        }

        return 'moviendo';
    }

    private function snapshotKey(array $item): ?string
    {
        if (isset($item['id'])) {
            return 'id:'.$item['id'];
        }

        if (filled($item['sku'] ?? null)) {
            return 'sku:'.Str::lower($item['sku']);
        }

        if (filled($item['name'] ?? null)) {
            return 'name:'.Str::lower($item['name']);
        }

        return null;
    }

    private function productKeys(InventoryProduct $product): array
    {
        return collect([
            'id:'.$product->id,
            filled($product->sku) ? 'sku:'.Str::lower($product->sku) : null,
            filled($product->name) ? 'name:'.Str::lower($product->name) : null,
        ])->filter()->values()->all();
    }

    private function queryProductsForOwner()
    {
        $keys = $this->ownerKeys();

        return InventoryProduct::query()
            ->when(
                $keys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $keys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $keys['tenant_id'])->whereNull('affiliated_company_id')
            );
    }

    private function ownerKeys(): array
    {
        $user = auth()->user();

        if ($user->role === 'affiliate' && $user->affiliated_company_id) {
            return [
                'tenant_id' => $user->tenant_id,
                'affiliated_company_id' => $user->affiliated_company_id,
            ];
        }

        $tenantId = $user->tenant_id ?: Tenant::query()->where('subdomain', 'demo-tus-envios')->value('id');

        return [
            'tenant_id' => $tenantId,
            'affiliated_company_id' => null,
        ];
    }
}
