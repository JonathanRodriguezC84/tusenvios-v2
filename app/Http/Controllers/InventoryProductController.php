<?php

namespace App\Http\Controllers;

use App\Models\InventoryProduct;
use App\Models\InventoryMovement;
use App\Models\Category;
use App\Models\Tenant;
use App\Support\Audit;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryProductController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function index(Request $request)
    {
        $this->authorizeInventoryAccess();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,paused'],
            'stock' => ['nullable', 'in:ok,low,out'],
            'category' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'in:latest,name,alert,stock_low,stock_high,value,profit'],
        ]);

        $products = $this->sortedProducts($this->filteredProducts($filters), $filters)
            ->with(['movements' => fn ($query) => $query->latest()->limit(3)])
            ->withCount('movements')
            ->paginate(100)
            ->withQueryString();

        $activeProducts = $this->queryForOwner()->where('status', 'active');

        $metrics = [
            'products' => $this->queryForOwner()->count(),
            'active' => $this->queryForOwner()->where('status', 'active')->count(),
            'paused' => $this->queryForOwner()->where('status', 'paused')->count(),
            'ok_stock' => (clone $activeProducts)->whereColumn('stock', '>', 'stock_minimum')->count(),
            'low_stock' => (clone $activeProducts)->where('stock', '>', 0)->whereColumn('stock', '<=', 'stock_minimum')->count(),
            'out_stock' => (clone $activeProducts)->where('stock', '<=', 0)->count(),
            'units' => (clone $activeProducts)->sum('stock'),
            'cost_value' => (clone $activeProducts)->selectRaw('COALESCE(SUM(stock * cost), 0) as total')->value('total'),
            'sale_value' => (clone $activeProducts)->selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total'),
        ];
        $metrics['potential_profit'] = $metrics['sale_value'] - $metrics['cost_value'];

        $categories = Category::query()
            ->when(
                $this->ownerKeys()['affiliated_company_id'],
                fn ($q) => $q->where('affiliated_company_id', $this->ownerKeys()['affiliated_company_id']),
                fn ($q) => $q->where('tenant_id', $this->ownerKeys()['tenant_id'])->whereNull('affiliated_company_id')
            )
            ->orderBy('name')
            ->pluck('name');

        return view('inventory.index', [
            'products' => $products,
            'metrics' => $metrics,
            'filters' => $filters,
            'categories' => $categories,
            'theme' => $this->inventoryService->themeVariables(),
            'statusLabels' => $this->inventoryService->statusLabels(),
            'movementLabels' => $this->inventoryService->movementTypeLabels(),
            'inventoryService' => $this->inventoryService,
        ]);
    }

    public function movements(Request $request)
    {
        $this->authorizeInventoryAccess();

        $filters = $request->validate([
            'type' => ['nullable', 'in:initial,adjustment,shipment,restock,manual_in,manual_out,status_change'],
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $movementBaseQuery = $this->movementQuery($filters);
        $movementSummary = $this->movementSummary($movementBaseQuery);

        $movements = (clone $movementBaseQuery)
            ->with(['product', 'shipment'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('inventory.movements', compact('movements', 'filters', 'movementSummary'));
    }

    public function export(Request $request)
    {
        $this->authorizeInventoryAccess();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,paused'],
            'stock' => ['nullable', 'in:ok,low,out'],
            'category' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'in:latest,name,alert,stock_low,stock_high,value,profit'],
        ]);

        $fileName = 'inventario-tus-envios-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Producto', 'SKU', 'Categoria', 'Costo', 'Precio', 'Stock', 'Stock minimo', 'Estado', 'Alerta stock', 'Costo stock', 'Venta potencial', 'Utilidad potencial', 'Margen %', 'Fecha creacion']);

            $this->sortedProducts($this->filteredProducts($filters), $filters)
                ->chunk(200, function ($products) use ($handle) {
                    foreach ($products as $product) {
                        $costValue = (float) $product->stock * (float) $product->cost;
                        $saleValue = (float) $product->stock * (float) $product->price;
                        $profitValue = $saleValue - $costValue;
                        $margin = $saleValue > 0 ? round(($profitValue / $saleValue) * 100, 1) : 0;

                    fputcsv($handle, [
                        $product->name,
                        $product->sku,
                        $product->category,
                        $product->cost,
                        $product->price,
                        $product->stock,
                        $product->stock_minimum,
                        $product->status,
                        $this->inventoryService->stockAlertLabel($product),
                        $costValue,
                        $saleValue,
                        $profitValue,
                        $margin,
                        optional($product->created_at)->format('Y-m-d H:i:s'),
                    ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function template()
    {
        $this->authorizeInventoryAccess();

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Producto', 'SKU', 'Categoria', 'Costo', 'Precio', 'Stock', 'Stock minimo', 'Estado']);
            fputcsv($handle, ['Camiseta negra talla M', 'CAM-NEG-M', 'Ropa', '18000', '45000', '10', '2', 'active']);
            fputcsv($handle, ['Case iPhone 15 Pro', 'CASE-15PRO', 'Accesorios', '12000', '30000', '25', '5', 'active']);

            fclose($handle);
        }, 'plantilla-inventario-tus-envios.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $this->authorizeInventoryAccess();

        $validated = $request->validate([
            'inventory_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path = $validated['inventory_file']->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->withErrors(['inventory_file' => 'No se pudo leer el archivo.']);
        }

        $delimiter = $this->detectCsvDelimiter($handle);
        $headers = fgetcsv($handle, 0, $delimiter);
        $headers = $this->csvHeaders($headers ?: []);

        if (! collect($headers)->contains('name', 'producto')) {
            fclose($handle);

            return back()->withErrors([
                'inventory_file' => 'El CSV debe incluir la columna Producto. Descarga la plantilla para usar el formato correcto.',
            ]);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $skippedRows = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;
            $data = $this->csvRowData($headers, $row);

            if (! is_array($data) || blank($data['producto'] ?? null)) {
                $skipped++;
                $skippedRows[] = $rowNumber;
                continue;
            }

            $payload = [
                'name' => trim((string) ($data['producto'] ?? '')),
                'sku' => trim((string) ($data['sku'] ?? '')) ?: null,
                'category' => trim((string) ($data['categoria'] ?? '')) ?: null,
                'cost' => $this->csvMoney($data['costo'] ?? 0),
                'price' => $this->csvMoney($data['precio'] ?? 0),
                'stock' => max(0, (int) $this->csvMoney($data['stock'] ?? 0)),
                'stock_minimum' => max(0, (int) $this->csvMoney($data['stock_minimo'] ?? $data['stock_minimum'] ?? 0)),
                'status' => $this->csvStatus($data['estado'] ?? 'active'),
            ];

            if ($payload['name'] === '') {
                $skipped++;
                $skippedRows[] = $rowNumber;
                continue;
            }

            $query = $this->queryForOwner();
            $product = $this->findImportProduct($payload, $query);

            if (! $product) {
                $product = InventoryProduct::query()->create(array_merge($payload, $this->ownerKeys()));
                $created++;

                if ($product->stock > 0) {
                    $product->movements()->create(array_merge($this->ownerKeys(), [
                        'type' => 'initial',
                        'quantity_delta' => $product->stock,
                        'stock_after' => $product->stock,
                        'notes' => "Importacion CSV fila {$rowNumber}",
                    ]));
                }

                continue;
            }

            $previousStock = $product->stock;
            $product->update($payload);
            $updated++;

            $delta = $product->stock - $previousStock;
            if ($delta !== 0) {
                $product->movements()->create(array_merge($this->ownerKeys(), [
                    'type' => 'adjustment',
                    'quantity_delta' => $delta,
                    'stock_after' => $product->stock,
                    'notes' => "Actualizacion CSV fila {$rowNumber}",
                ]));
            }
        }

        fclose($handle);

        $skippedDetail = $skippedRows
            ? ' Filas omitidas: '.implode(', ', array_slice($skippedRows, 0, 8)).(count($skippedRows) > 8 ? '...' : '').'.'
            : '';

        return redirect()
            ->route('inventory.index')
            ->with('status', "Importacion lista: {$created} producto(s) creado(s), {$updated} actualizado(s), {$skipped} fila(s) omitida(s).{$skippedDetail}");
    }

    public function bulk(Request $request)
    {
        $this->authorizeInventoryAccess();

        $validated = $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['integer'],
            'bulk_action' => ['required', 'in:activate,pause,category,export'],
            'bulk_category' => ['nullable', 'string', 'max:80'],
        ], [
            'products.required' => 'Selecciona al menos un producto.',
        ]);

        $products = $this->queryForOwner()
            ->whereIn('id', $validated['products'])
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            return back()->withErrors(['products' => 'Selecciona al menos un producto valido.']);
        }

        if ($validated['bulk_action'] === 'export') {
            $fileName = 'inventario-seleccion-tus-envios-'.now()->format('Y-m-d-His').'.csv';

            return response()->streamDownload(function () use ($products) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, ['Producto', 'SKU', 'Categoria', 'Costo', 'Precio', 'Stock', 'Stock minimo', 'Estado', 'Alerta stock']);

                foreach ($products as $product) {
                    fputcsv($handle, [
                        $product->name,
                        $product->sku,
                        $product->category,
                        $product->cost,
                        $product->price,
                        $product->stock,
                        $product->stock_minimum,
                        $product->status,
                        $this->inventoryService->stockAlertLabel($product),
                    ]);
                }

                fclose($handle);
            }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($validated['bulk_action'] === 'category' && blank($validated['bulk_category'] ?? null)) {
            return back()->withErrors(['bulk_category' => 'Escribe la categoria que quieres aplicar.']);
        }

        DB::transaction(function () use ($products, $validated) {
            foreach ($products as $product) {
                if ($validated['bulk_action'] === 'category') {
                    $product->update(['category' => trim((string) $validated['bulk_category'])]);
                    Audit::log('inventory_product.bulk_category', $product, "Categoria masiva aplicada a {$product->name}.");
                    continue;
                }

                $status = $validated['bulk_action'] === 'activate' ? 'active' : 'paused';

                if ($product->status === $status) {
                    continue;
                }

                $previousStatus = $product->status;
                $product->update(['status' => $status]);

                $product->movements()->create(array_merge($this->ownerKeys(), [
                    'type' => 'status_change',
                    'quantity_delta' => 0,
                    'stock_after' => $product->stock,
                    'notes' => "Cambio masivo de {$previousStatus} a {$status}",
                ]));

                Audit::log('inventory_product.bulk_status', $product, "Estado masivo {$status} aplicado a {$product->name}.");
            }
        });

        return redirect()
            ->route('inventory.index')
            ->with('status', "Accion masiva aplicada a {$products->count()} producto(s).");
    }

    public function exportMovements(Request $request)
    {
        $this->authorizeInventoryAccess();

        $filters = $request->validate([
            'type' => ['nullable', 'in:initial,adjustment,shipment,restock,manual_in,manual_out,status_change'],
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $fileName = 'movimientos-inventario-tus-envios-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Fecha', 'Producto', 'SKU', 'Tipo', 'Cantidad', 'Stock despues', 'Guia', 'Nota']);

            $this->movementQuery($filters)
                ->with(['product', 'shipment'])
                ->latest()
                ->chunk(200, function ($movements) use ($handle) {
                    foreach ($movements as $movement) {
                        fputcsv($handle, [
                            optional($movement->created_at)->format('Y-m-d H:i:s'),
                            $movement->product?->name,
                            $movement->product?->sku,
                            $movement->type,
                            $movement->quantity_delta,
                            $movement->stock_after,
                            $movement->shipment?->guide_number,
                            $movement->notes,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportMovementsPdf(Request $request)
    {
        $this->authorizeInventoryAccess();

        $filters = $request->validate([
            'type' => ['nullable', 'in:initial,adjustment,shipment,restock,manual_in,manual_out,status_change'],
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $movements = $this->movementQuery($filters)
            ->with(['product', 'shipment'])
            ->latest()
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventory.movements-export-pdf', compact('movements'));

        return $pdf->download('movimientos-inventario-'.now()->format('Y-m-d').'.pdf');
    }

    public function create()
    {
        $this->authorizeInventoryAccess();

        $categories = Category::query()
            ->when(
                $this->ownerKeys()['affiliated_company_id'],
                fn ($q) => $q->where('affiliated_company_id', $this->ownerKeys()['affiliated_company_id']),
                fn ($q) => $q->where('tenant_id', $this->ownerKeys()['tenant_id'])->whereNull('affiliated_company_id')
            )
            ->orderBy('name')
            ->pluck('name');

        return view('inventory.create', [
            'theme' => $this->inventoryService->themeVariables(),
            'statusLabels' => $this->inventoryService->statusLabels(),
            'categories' => $categories,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $this->authorizeInventoryAccess();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,paused'],
            'stock' => ['nullable', 'in:ok,low,out'],
            'category' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'in:latest,name,alert,stock_low,stock_high,value,profit'],
            'export_format' => ['nullable', 'in:csv,pdf'],
        ]);

        $products = $this->sortedProducts($this->filteredProducts($filters), $filters)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventory.export-pdf', [
            'products' => $products,
            'filters' => $filters,
        ]);

        return $pdf->download('inventario-tus-envios-'.now()->format('Y-m-d').'.pdf');
    }

    public function store(Request $request)
    {
        $this->authorizeInventoryAccess();

        $validated = $request->validate($this->rules());
        $validated['stock'] = (int) $validated['stock'];
        $validated['stock_minimum'] = (int) $validated['stock_minimum'];

        if ($request->boolean('auto_sku') || empty($validated['sku'])) {
            $validated['sku'] = $this->generateSku();
        }

        $this->ensureSkuIsAvailable($validated['sku'] ?? null);
        $this->ensureNameCategoryIsAvailable($validated);

        $product = InventoryProduct::query()->create(array_merge($validated, $this->ownerKeys(), [
            'status' => 'active',
        ]));

        $this->ensureCategoryExists($validated['category'] ?? null);

        if ($product->stock !== 0) {
            $product->movements()->create(array_merge($this->ownerKeys(), [
                'type' => 'initial',
                'quantity_delta' => $product->stock,
                'stock_after' => $product->stock,
                'notes' => 'Stock inicial',
            ]));
        }

        Audit::log('inventory_product.created', $product, "Producto de inventario {$product->name} creado.");

        return redirect()
            ->route('inventory.index')
            ->with('status', 'Producto agregado al inventario.');
    }

    public function update(Request $request, InventoryProduct $inventoryProduct)
    {
        $this->authorizeInventoryAccess();
        $this->authorizeProduct($inventoryProduct);

        $rules = $this->rules();
        $hasFullData = $request->has('cost') || $request->has('price') || $request->has('stock_minimum');

        if ($request->wantsJson() && ! $hasFullData) {
            $validated = $request->validate([
                'stock' => ['required', 'integer', 'min:0', 'max:999999'],
                'name' => ['sometimes', 'string', 'max:120'],
                'sku' => ['sometimes', 'nullable', 'string', 'max:80'],
            ]);
        } else {
            $validated = $request->validate(array_merge($rules, [
                'status' => ['required', 'in:active,paused'],
            ]));

            if ($request->boolean('auto_sku')) {
                $validated['sku'] = $this->generateSku();
            }

            $this->ensureSkuIsAvailable($validated['sku'] ?? null, $inventoryProduct->id);
            $this->ensureNameCategoryIsAvailable($validated, $inventoryProduct->id);
        }

        $validated['stock'] = (int) $validated['stock'];
        $validated['stock_minimum'] = (int) ($validated['stock_minimum'] ?? $inventoryProduct->stock_minimum);

        $previousStock = $inventoryProduct->stock;
        $previousStatus = $inventoryProduct->status;
        $inventoryProduct->update($validated);

        $this->ensureCategoryExists($validated['category'] ?? null);

        $delta = $inventoryProduct->stock - $previousStock;
        if ($delta !== 0) {
            $inventoryProduct->movements()->create(array_merge($this->ownerKeys(), [
                'type' => 'adjustment',
                'quantity_delta' => $delta,
                'stock_after' => $inventoryProduct->stock,
                'notes' => 'Ajuste manual',
            ]));
        }

        if ($inventoryProduct->status !== $previousStatus) {
            $inventoryProduct->movements()->create(array_merge($this->ownerKeys(), [
                'type' => 'status_change',
                'quantity_delta' => 0,
                'stock_after' => $inventoryProduct->stock,
                'notes' => "Estado cambiado de {$previousStatus} a {$inventoryProduct->status}",
            ]));
        }

        Audit::log('inventory_product.updated', $inventoryProduct, "Producto de inventario {$inventoryProduct->name} actualizado.");

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'stock' => $inventoryProduct->stock]);
        }

        return redirect()
            ->route('inventory.index')
            ->with('status', 'Inventario actualizado correctamente.');
    }

    public function movement(Request $request, InventoryProduct $inventoryProduct)
    {
        $this->authorizeInventoryAccess();
        $this->authorizeProduct($inventoryProduct);

        $validated = $request->validate([
            'type' => ['required', 'in:manual_in,manual_out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:180'],
        ]);

        $quantity = (int) $validated['quantity'];
        $delta = $validated['type'] === 'manual_out' ? -1 * $quantity : $quantity;

        $updatedProduct = DB::transaction(function () use ($inventoryProduct, $validated, $quantity, $delta) {
            $lockedProduct = $this->queryForOwner()
                ->whereKey($inventoryProduct->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProduct->status !== 'active') {
                throw ValidationException::withMessages([
                    'quantity' => 'Reactiva el producto antes de registrar movimientos de stock.',
                ]);
            }

            if ($validated['type'] === 'manual_out' && $lockedProduct->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "No hay stock suficiente para {$lockedProduct->name}. Disponible: {$lockedProduct->stock}.",
                ]);
            }

            $lockedProduct->increment('stock', $delta);
            $lockedProduct->refresh();

            $lockedProduct->movements()->create(array_merge($this->ownerKeys(), [
                'type' => $validated['type'],
                'quantity_delta' => $delta,
                'stock_after' => $lockedProduct->stock,
                'notes' => $validated['notes'] ?: $this->movementDefaultNote($validated['type']),
            ]));

            return $lockedProduct;
        });

        Audit::log('inventory_product.movement', $updatedProduct, "Movimiento de inventario {$delta} para {$updatedProduct->name}.");

        return redirect()
            ->route('inventory.index')
            ->with('status', 'Movimiento de inventario registrado.');
    }

    private function movementDefaultNote(string $type): string
    {
        return [
            'manual_in' => 'Entrada manual',
            'manual_out' => 'Salida manual',
            'adjustment' => 'Ajuste positivo',
        ][$type] ?? 'Movimiento manual';
    }

    public function destroy(InventoryProduct $inventoryProduct)
    {
        $this->authorizeInventoryAccess();
        $this->authorizeProduct($inventoryProduct);

        if ($inventoryProduct->movements()->exists()) {
            $inventoryProduct->update(['status' => 'paused']);

            Audit::log('inventory_product.paused', $inventoryProduct, "Producto de inventario {$inventoryProduct->name} pausado para conservar historial.");

            return redirect()
                ->route('inventory.index')
                ->with('status', 'El producto tiene movimientos y fue pausado para conservar el historial.');
        }

        Audit::log('inventory_product.deleted', $inventoryProduct, "Producto de inventario {$inventoryProduct->name} eliminado.");

        $inventoryProduct->delete();

        return redirect()
            ->route('inventory.index')
            ->with('status', 'Producto eliminado del inventario.');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:80'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'stock_minimum' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }

    private function authorizeInventoryAccess(): void
    {
        abort_unless(Auth::user()->canUseInventory(), 403);
    }

    private function ownerKeys(): array
    {
        $user = Auth::user();

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

    private function queryForOwner()
    {
        $keys = $this->ownerKeys();

        return InventoryProduct::query()
            ->when(
                $keys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $keys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $keys['tenant_id'])->whereNull('affiliated_company_id')
            );
    }

    private function ensureCategoryExists(?string $category): void
    {
        if (blank($category)) {
            return;
        }

        $keys = $this->ownerKeys();

        Category::query()->firstOrCreate([
            'tenant_id' => $keys['tenant_id'],
            'affiliated_company_id' => $keys['affiliated_company_id'],
            'name' => trim($category),
        ]);
    }

    private function filteredProducts(array $filters)
    {
        return $this->queryForOwner()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when(($filters['stock'] ?? null) === 'ok', fn ($query) => $query->where('status', 'active')->whereColumn('stock', '>', 'stock_minimum'))
            ->when(($filters['stock'] ?? null) === 'low', fn ($query) => $query->where('status', 'active')->where('stock', '>', 0)->whereColumn('stock', '<=', 'stock_minimum'))
            ->when(($filters['stock'] ?? null) === 'out', fn ($query) => $query->where('status', 'active')->where('stock', '<=', 0));
    }

    private function sortedProducts($query, array $filters)
    {
        return match ($filters['sort'] ?? 'latest') {
            'name' => $query->orderBy('name'),
            'alert' => $query
                ->orderByRaw("CASE WHEN status = 'active' AND stock <= 0 THEN 0 WHEN status = 'active' AND stock <= stock_minimum THEN 1 WHEN status = 'paused' THEN 2 ELSE 3 END")
                ->orderBy('stock')
                ->orderBy('name'),
            'stock_low' => $query->orderBy('stock')->orderBy('name'),
            'stock_high' => $query->orderByDesc('stock')->orderBy('name'),
            'value' => $query->orderByRaw('(stock * cost) desc')->orderBy('name'),
            'profit' => $query->orderByRaw('(stock * (price - cost)) desc')->orderBy('name'),
            default => $query->latest(),
        };
    }

    private function generateSku(): string
    {
        $owner = $this->ownerKeys();
        $last = InventoryProduct::query()
            ->where($owner)
            ->where('sku', 'like', 'PROD-%')
            ->orderByDesc('id')
            ->first();

        $next = $last ? ((int) substr($last->sku, 5)) + 1 : 1;

        do {
            $sku = 'PROD-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $exists = InventoryProduct::query()->where($owner)->where('sku', $sku)->exists();
            $next++;
        } while ($exists);

        return $sku;
    }

    private function ensureSkuIsAvailable(?string $sku, ?int $ignoreProductId = null): void
    {
        $sku = trim((string) $sku);

        if ($sku === '') {
            return;
        }

        $exists = $this->queryForOwner()
            ->where('sku', $sku)
            ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'sku' => "Ya existe un producto con el SKU {$sku}.",
            ]);
        }
    }

    private function ensureNameCategoryIsAvailable(array $payload, ?int $ignoreProductId = null): void
    {
        if (filled($payload['sku'] ?? null)) {
            return;
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $category = trim((string) ($payload['category'] ?? ''));

        if ($name === '') {
            return;
        }

        $exists = $this->queryForOwner()
            ->where('name', $name)
            ->when(
                $category !== '',
                fn ($query) => $query->where('category', $category),
                fn ($query) => $query->where(function ($query) {
                    $query->whereNull('category')->orWhere('category', '');
                })
            )
            ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'Ya existe un producto sin SKU con el mismo nombre y categoria.',
            ]);
        }
    }

    private function csvMoney($value): float
    {
        $normalized = trim((string) $value);
        $normalized = str_replace(['$', ' ', "\xc2\xa0"], '', $normalized);

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return (float) $normalized;
    }

    private function detectCsvDelimiter($handle): string
    {
        $position = ftell($handle);
        $line = fgets($handle) ?: '';

        if ($position !== false) {
            fseek($handle, $position);
        }

        return collect([',', ';', "\t"])
            ->mapWithKeys(fn ($delimiter) => [$delimiter => count(str_getcsv($line, $delimiter))])
            ->sortDesc()
            ->keys()
            ->first() ?: ',';
    }

    private function csvStatus($value): string
    {
        $normalized = $this->normalizeCsvHeader($value);

        return [
            'active' => 'active',
            'activo' => 'active',
            'activa' => 'active',
            '1' => 'active',
            'si' => 'active',
            'yes' => 'active',
            'paused' => 'paused',
            'pausado' => 'paused',
            'pausada' => 'paused',
            'inactivo' => 'paused',
            'inactiva' => 'paused',
            '0' => 'paused',
            'no' => 'paused',
        ][$normalized] ?? 'active';
    }

    private function csvHeaders(array $headers): array
    {
        $seen = [];

        return collect($headers)
            ->map(fn ($header, $index) => [
                'index' => $index,
                'name' => $this->csvHeaderAlias($this->normalizeCsvHeader($header)),
            ])
            ->map(function ($header) use (&$seen) {
                $name = $header['name'];

                if ($name === '' || isset($seen[$name])) {
                    return null;
                }

                $seen[$name] = true;

                return $header;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function csvRowData(array $headers, array $row): ?array
    {
        if ($headers === []) {
            return null;
        }

        return collect($headers)
            ->mapWithKeys(fn ($header) => [$header['name'] => $row[$header['index']] ?? null])
            ->all();
    }

    private function normalizeCsvHeader($header): string
    {
        $normalized = strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $header)));
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);

        if (is_string($ascii) && $ascii !== '') {
            $normalized = strtolower($ascii);
        }

        $normalized = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', 'à', 'è', 'ì', 'ò', 'ù'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u', 'a', 'e', 'i', 'o', 'u'],
            $normalized
        );
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? $normalized;
        $normalized = trim($normalized, '_');

        return [
            'categor_a' => 'categoria',
            'stock_m_nimo' => 'stock_minimo',
            'stock_m_nimum' => 'stock_minimum',
            'fecha_creaci_n' => 'fecha_creacion',
        ][$normalized] ?? $normalized;
    }

    private function csvHeaderAlias(string $header): string
    {
        return [
            'nombre' => 'producto',
            'nombre_producto' => 'producto',
            'producto_nombre' => 'producto',
            'product' => 'producto',
            'item' => 'producto',
            'referencia' => 'sku',
            'codigo' => 'sku',
            'codigo_producto' => 'sku',
            'categoria_producto' => 'categoria',
            'category' => 'categoria',
            'costo_unitario' => 'costo',
            'precio_venta' => 'precio',
            'precio_unitario' => 'precio',
            'cantidad' => 'stock',
            'existencias' => 'stock',
            'inventario' => 'stock',
            'stock_min' => 'stock_minimo',
            'stock_minimum' => 'stock_minimo',
            'minimo' => 'stock_minimo',
            'estado_producto' => 'estado',
            'status' => 'estado',
        ][$header] ?? $header;
    }

    private function findImportProduct(array $payload, $query): ?InventoryProduct
    {
        if ($payload['sku']) {
            return (clone $query)->where('sku', $payload['sku'])->first();
        }

        return (clone $query)
            ->where('name', $payload['name'])
            ->when(
                filled($payload['category']),
                fn ($query) => $query->where('category', $payload['category']),
                fn ($query) => $query->where(function ($query) {
                    $query->whereNull('category')->orWhere('category', '');
                })
            )
            ->first();
    }

    private function movementQuery(array $filters)
    {
        $keys = $this->ownerKeys();

        return InventoryMovement::query()
            ->when(
                $keys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $keys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $keys['tenant_id'])->whereNull('affiliated_company_id')
            )
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        })
                        ->orWhereHas('shipment', fn ($query) => $query->where('guide_number', 'like', "%{$search}%"));
                });
            });
    }

    private function movementSummary($query): array
    {
        $row = (clone $query)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity_delta > 0 THEN quantity_delta ELSE 0 END), 0) as entries')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity_delta < 0 THEN ABS(quantity_delta) ELSE 0 END), 0) as exits')
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'shipment' THEN ABS(quantity_delta) ELSE 0 END), 0) as shipment_units")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'restock' THEN quantity_delta ELSE 0 END), 0) as restock_units")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'status_change' THEN 1 ELSE 0 END), 0) as status_changes")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'entries' => (int) ($row->entries ?? 0),
            'exits' => (int) ($row->exits ?? 0),
            'net' => (int) ($row->entries ?? 0) - (int) ($row->exits ?? 0),
            'shipment_units' => (int) ($row->shipment_units ?? 0),
            'restock_units' => (int) ($row->restock_units ?? 0),
            'status_changes' => (int) ($row->status_changes ?? 0),
        ];
    }

    private function authorizeProduct(InventoryProduct $inventoryProduct): void
    {
        $keys = $this->ownerKeys();

        abort_if(
            $keys['affiliated_company_id']
                ? $inventoryProduct->affiliated_company_id !== $keys['affiliated_company_id']
                : ($inventoryProduct->tenant_id !== $keys['tenant_id'] || $inventoryProduct->affiliated_company_id),
            403
        );
    }
}
