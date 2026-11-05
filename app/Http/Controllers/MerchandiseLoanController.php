<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use App\Models\MerchandiseLoan;
use App\Models\MerchandiseLoanItem;
use App\Models\QuickProduct;
use App\Models\FrequentRecipient;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MerchandiseLoanController extends InventoryProductController
{
    public function index(Request $request): \Illuminate\View\View
    {
        $this->authorizeLoanAccess();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,partial,returned,sold,cancelled,overdue'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = $this->loanQuery($filters);

        $loans = (clone $query)
            ->with(['items.product', 'items.quickProduct', 'creator'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $baseOwnerQuery = $this->loanBaseQuery();

        $allActive = (clone $baseOwnerQuery)->whereIn('status', ['active', 'partial'])->with('items')->get();

        $pendingUnits = 0;
        $pendingValue = 0;
        foreach ($allActive as $l) {
            foreach ($l->items as $item) {
                $rem = $item->pendingUnits();
                $pendingUnits += $rem;
                $pendingValue += $rem * (float) $item->unit_price;
            }
        }

        $metrics = [
            'total' => (clone $baseOwnerQuery)->count(),
            'active' => (clone $baseOwnerQuery)->whereIn('status', ['active', 'partial'])->count(),
            'overdue' => (clone $baseOwnerQuery)->whereIn('status', ['active', 'partial'])->whereDate('due_date', '<', now()->toDateString())->count(),
            'returned' => (clone $baseOwnerQuery)->where('status', 'returned')->count(),
            'sold' => (clone $baseOwnerQuery)->where('status', 'sold')->count(),
            'pending_units' => $pendingUnits,
            'pending_value' => $pendingValue,
        ];

        return view('inventory.loans.index', [
            'loans' => $loans,
            'metrics' => $metrics,
            'filters' => $filters,
            'theme' => $this->inventoryService->themeVariables(),
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        $this->authorizeLoanAccess();

        $owner = $this->ownerKeys();

        // 1. QuickProducts del tenant
        $quickProducts = QuickProduct::query()
            ->when(
                $owner['affiliated_company_id'],
                fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
            )
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'cost', 'stock'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'key' => 'quick_' . $p->id,
                'type' => 'quick',
                'name' => $p->name,
                'sku' => $p->sku,
                'price' => (float) $p->price,
                'cost' => (float) $p->cost,
                'stock' => (int) $p->stock,
                'badge' => 'Rápido',
            ]);

        // 2. InventoryProducts del tenant (si existen)
        $invProducts = InventoryProduct::query()
            ->when(
                $owner['affiliated_company_id'],
                fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
            )
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'cost', 'stock'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'key' => 'inv_' . $p->id,
                'type' => 'inventory',
                'name' => $p->name,
                'sku' => $p->sku,
                'price' => (float) $p->price,
                'cost' => (float) $p->cost,
                'stock' => (int) $p->stock,
                'badge' => 'Inventario',
            ]);

        $products = $quickProducts->concat($invProducts)->values();

        return view('inventory.loans.create', [
            'products' => $products,
            'theme' => $this->inventoryService->themeVariables(),
            'defaultLoanDate' => now()->toDateString(),
            'defaultDueDate' => now()->addDays(7)->toDateString(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeLoanAccess();

        $validated = $request->validate([
            'borrower_name' => ['required', 'string', 'max:150'],
            'borrower_phone' => ['nullable', 'string', 'max:40'],
            'borrower_document' => ['nullable', 'string', 'max:50'],
            'borrower_address' => ['nullable', 'string', 'max:255'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:loan_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.product_type' => ['required', 'in:quick,inventory'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Debes agregar al menos un producto al préstamo.',
            'items.min' => 'Debes agregar al menos un producto al préstamo.',
            'items.*.quantity.min' => 'La cantidad mínima por producto es 1.',
        ]);

        $owner = $this->ownerKeys();

        $loan = DB::transaction(function () use ($validated, $owner) {
            $loanNumber = $this->generateLoanNumber();

            $totalItems = 0;
            $totalValue = 0;

            $itemsData = [];

            foreach ($validated['items'] as $itemInput) {
                $productId = (int) $itemInput['product_id'];
                $productType = $itemInput['product_type'];
                $quantity = (int) $itemInput['quantity'];

                if ($productType === 'quick') {
                    $product = QuickProduct::query()
                        ->when(
                            $owner['affiliated_company_id'],
                            fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                            fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
                        )
                        ->whereKey($productId)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => 'Uno de los productos rápidos seleccionados no existe o no pertenece a tu cuenta.',
                        ]);
                    }

                    if ($product->status !== 'active') {
                        throw ValidationException::withMessages([
                            'items' => "El producto {$product->name} se encuentra inactivo.",
                        ]);
                    }

                    if ($product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Stock insuficiente para {$product->name}. Disponible en bodega: {$product->stock}.",
                        ]);
                    }

                    // Descontar stock de producto rápido
                    $product->decrement('stock', $quantity);

                    $totalItems += $quantity;
                    $lineTotal = $quantity * (float) $product->price;
                    $totalValue += $lineTotal;

                    $itemsData[] = [
                        'inventory_product_id' => null,
                        'quick_product_id' => $product->id,
                        'product_type' => 'quick',
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity_lent' => $quantity,
                        'quantity_returned' => 0,
                        'quantity_sold' => 0,
                        'unit_price' => $product->price,
                        'unit_cost' => $product->cost,
                        'notes' => $itemInput['notes'] ?? null,
                    ];
                } else {
                    $product = $this->queryForOwner()
                        ->whereKey($productId)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => 'Uno de los productos de inventario no pertenece a tu cuenta.',
                        ]);
                    }

                    if ($product->status !== 'active') {
                        throw ValidationException::withMessages([
                            'items' => "El producto {$product->name} se encuentra pausado.",
                        ]);
                    }

                    if ($product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Stock insuficiente para {$product->name}. Disponible en bodega: {$product->stock}.",
                        ]);
                    }

                    // Descontar stock y registrar movimiento
                    $product->decrement('stock', $quantity);
                    $product->refresh();

                    $product->movements()->create(array_merge($owner, [
                        'type' => 'loan_out',
                        'quantity_delta' => -1 * $quantity,
                        'stock_after' => $product->stock,
                        'notes' => "Préstamo {$loanNumber} a {$validated['borrower_name']}",
                    ]));

                    $totalItems += $quantity;
                    $lineTotal = $quantity * (float) $product->price;
                    $totalValue += $lineTotal;

                    $itemsData[] = [
                        'inventory_product_id' => $product->id,
                        'quick_product_id' => null,
                        'product_type' => 'inventory',
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity_lent' => $quantity,
                        'quantity_returned' => 0,
                        'quantity_sold' => 0,
                        'unit_price' => $product->price,
                        'unit_cost' => $product->cost,
                        'notes' => $itemInput['notes'] ?? null,
                    ];
                }
            }

            $loan = MerchandiseLoan::create([
                'tenant_id' => $owner['tenant_id'],
                'affiliated_company_id' => $owner['affiliated_company_id'],
                'loan_number' => $loanNumber,
                'borrower_name' => trim($validated['borrower_name']),
                'borrower_phone' => trim($validated['borrower_phone'] ?? ''),
                'borrower_document' => trim($validated['borrower_document'] ?? ''),
                'borrower_address' => trim($validated['borrower_address'] ?? ''),
                'loan_date' => $validated['loan_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => 'active',
                'total_items' => $totalItems,
                'total_value' => $totalValue,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $data) {
                $loan->items()->create($data);
            }

            return $loan;
        });

        Audit::log('merchandise_loan.created', $loan, "Préstamo de mercancía {$loan->loan_number} registrado para {$loan->borrower_name}.");

        return redirect()
            ->route('inventory.loans.show', $loan)
            ->with('status', "Préstamo {$loan->loan_number} creado con éxito.");
    }

    public function storeFromShipment(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeLoanAccess();

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_lastname' => ['nullable', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'recipient_alt_phone' => ['nullable', 'string', 'max:50'],
            'recipient_email' => ['nullable', 'string', 'max:150'],
            'recipient_address' => ['nullable', 'string', 'max:255'],
            'recipient_department' => ['nullable'],
            'recipient_locality' => ['nullable', 'string', 'max:120'],
            'recipient_city' => ['nullable', 'string', 'max:120'],
            'recipient_neighborhood' => ['nullable', 'string', 'max:120'],
            'recipient_notes' => ['nullable', 'string', 'max:1000'],
            'inventory_items' => ['nullable'],
        ], [
            'recipient_name.required' => 'El nombre del cliente es obligatorio para registrar el préstamo.',
            'recipient_phone.required' => 'El teléfono del cliente es obligatorio para registrar el préstamo.',
        ]);

        $owner = $this->ownerKeys();

        // 1. Decodificar items de productos
        $rawItems = $request->input('inventory_items');
        if (is_string($rawItems)) {
            $rawItems = json_decode($rawItems, true) ?: [];
        }
        if (!is_array($rawItems)) {
            $rawItems = [];
        }

        if (empty($rawItems)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['inventory_items' => 'Debes agregar al menos un producto frecuente o de inventario para registrar el préstamo.']);
        }

        // 2. Registrar o actualizar al cliente en FrequentRecipient
        $fullName = trim($validated['recipient_name'] . ' ' . ($validated['recipient_lastname'] ?? ''));
        $phone = trim($validated['recipient_phone']);

        $recipientData = [
            'name' => trim($validated['recipient_name']),
            'lastname' => trim($validated['recipient_lastname'] ?? ''),
            'phone' => $phone,
            'alt_phone' => trim($validated['recipient_alt_phone'] ?? ''),
            'address' => trim($validated['recipient_address'] ?? ''),
            'neighborhood' => trim($validated['recipient_neighborhood'] ?? ''),
            'locality' => trim($validated['recipient_locality'] ?? ''),
            'city' => trim($validated['recipient_city'] ?? ''),
            'notes' => trim($validated['recipient_notes'] ?? ''),
        ];

        try {
            $existingRecipient = FrequentRecipient::query()
                ->when(
                    $owner['affiliated_company_id'],
                    fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                    fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
                )
                ->where('name', $recipientData['name'])
                ->when($phone, fn ($q) => $q->where('phone', $phone))
                ->first();

            if ($existingRecipient) {
                $existingRecipient->increment('use_count');
                $existingRecipient->update(array_filter($recipientData));
            } else {
                FrequentRecipient::create(array_merge(
                    $owner['affiliated_company_id']
                        ? ['affiliated_company_id' => $owner['affiliated_company_id'], 'tenant_id' => null]
                        : ['tenant_id' => $owner['tenant_id'], 'affiliated_company_id' => null],
                    $recipientData,
                    ['use_count' => 1]
                ));
            }
        } catch (\Throwable $e) {
            // Continuar sin bloquear el préstamo si falla recipient
        }

        // 3. Crear el préstamo y descontar stock
        $loan = DB::transaction(function () use ($validated, $fullName, $phone, $rawItems, $owner) {
            $loanNumber = $this->generateLoanNumber();

            $totalItems = 0;
            $totalValue = 0;
            $itemsData = [];

            foreach ($rawItems as $item) {
                $productId = (int) ($item['id'] ?? 0);
                $productType = $item['type'] ?? 'quick';
                $quantity = max(1, (int) ($item['quantity'] ?? 1));

                if ($productType === 'quick' && $productId > 0) {
                    $product = QuickProduct::query()
                        ->when(
                            $owner['affiliated_company_id'],
                            fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                            fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
                        )
                        ->whereKey($productId)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'inventory_items' => "Uno de los productos rápidos seleccionados ya no está disponible.",
                        ]);
                    }

                    if ($product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'inventory_items' => "Stock insuficiente para {$product->name}. Disponible en bodega: {$product->stock}.",
                        ]);
                    }

                    $product->decrement('stock', $quantity);

                    $totalItems += $quantity;
                    $lineTotal = $quantity * (float) $product->price;
                    $totalValue += $lineTotal;

                    $itemsData[] = [
                        'inventory_product_id' => null,
                        'quick_product_id' => $product->id,
                        'product_type' => 'quick',
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity_lent' => $quantity,
                        'quantity_returned' => 0,
                        'quantity_sold' => 0,
                        'unit_price' => $product->price,
                        'unit_cost' => $product->cost,
                        'notes' => null,
                    ];
                } elseif ($productType === 'inventory' && $productId > 0) {
                    $product = $this->queryForOwner()
                        ->whereKey($productId)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'inventory_items' => "Uno de los productos de inventario ya no está disponible.",
                        ]);
                    }

                    if ($product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'inventory_items' => "Stock insuficiente para {$product->name}. Disponible en bodega: {$product->stock}.",
                        ]);
                    }

                    $product->decrement('stock', $quantity);
                    $product->refresh();

                    $product->movements()->create(array_merge($owner, [
                        'type' => 'loan_out',
                        'quantity_delta' => -1 * $quantity,
                        'stock_after' => $product->stock,
                        'notes' => "Préstamo {$loanNumber} a {$fullName}",
                    ]));

                    $totalItems += $quantity;
                    $lineTotal = $quantity * (float) $product->price;
                    $totalValue += $lineTotal;

                    $itemsData[] = [
                        'inventory_product_id' => $product->id,
                        'quick_product_id' => null,
                        'product_type' => 'inventory',
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity_lent' => $quantity,
                        'quantity_returned' => 0,
                        'quantity_sold' => 0,
                        'unit_price' => $product->price,
                        'unit_cost' => $product->cost,
                        'notes' => null,
                    ];
                }
            }

            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'inventory_items' => "Debes seleccionar al menos un producto válido para registrar el préstamo.",
                ]);
            }

            $fullAddress = trim(implode(', ', array_filter([
                $validated['recipient_address'] ?? null,
                $validated['recipient_neighborhood'] ?? null,
                $validated['recipient_locality'] ?? null,
                $validated['recipient_city'] ?? null,
            ])));

            $loan = MerchandiseLoan::create([
                'tenant_id' => $owner['tenant_id'],
                'affiliated_company_id' => $owner['affiliated_company_id'],
                'loan_number' => $loanNumber,
                'borrower_name' => $fullName,
                'borrower_phone' => $phone,
                'borrower_document' => null,
                'borrower_address' => $fullAddress ?: null,
                'loan_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'active',
                'total_items' => $totalItems,
                'total_value' => $totalValue,
                'notes' => !empty($validated['recipient_notes']) ? trim($validated['recipient_notes']) : "Registrado desde checkout Crear Guía",
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $data) {
                $loan->items()->create($data);
            }

            return $loan;
        });

        Audit::log('merchandise_loan.created', $loan, "Préstamo {$loan->loan_number} registrado para {$loan->borrower_name} desde Crear Guía.");

        return redirect()
            ->route('inventory.loans.index')
            ->with('status', "¡Préstamo {$loan->loan_number} registrado con éxito! Cliente guardado y stock descontado.");
    }

    public function show(MerchandiseLoan $loan): \Illuminate\View\View
    {
        $this->authorizeLoan($loan);

        $loan->load(['items.product', 'items.quickProduct', 'creator', 'tenant', 'affiliatedCompany']);

        return view('inventory.loans.show', [
            'loan' => $loan,
            'theme' => $this->inventoryService->themeVariables(),
        ]);
    }

    public function print(MerchandiseLoan $loan): \Illuminate\View\View
    {
        $this->authorizeLoan($loan);

        $loan->load(['items.product', 'items.quickProduct', 'creator', 'tenant', 'affiliatedCompany']);

        $user = Auth::user();
        $owner = $user->affiliatedCompany ?: $user->tenant;
        $brand = $owner?->brandData() ?? [];

        return view('inventory.loans.print', [
            'loan' => $loan,
            'brand' => $brand,
            'theme' => $this->inventoryService->themeVariables(),
        ]);
    }

    public function returnItems(Request $request, MerchandiseLoan $loan): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeLoan($loan);

        if (!$loan->isActive()) {
            throw ValidationException::withMessages([
                'returns' => 'Este préstamo ya no se encuentra activo.',
            ]);
        }

        $validated = $request->validate([
            'returns' => ['required', 'array', 'min:1'],
            'returns.*.item_id' => ['required', 'integer'],
            'returns.*.quantity' => ['required', 'integer', 'min:0'],
            'return_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $owner = $this->ownerKeys();

        DB::transaction(function () use ($loan, $validated, $owner) {
            $anyProcessed = false;

            foreach ($validated['returns'] as $input) {
                $qty = (int) $input['quantity'];
                if ($qty <= 0) continue;

                $item = $loan->items()->where('id', $input['item_id'])->firstOrFail();
                $pending = $item->pendingUnits();

                if ($qty > $pending) {
                    throw ValidationException::withMessages([
                        'returns' => "No puedes devolver {$qty} unidades de {$item->displayName()}. Pendientes: {$pending}.",
                    ]);
                }

                // Devolver stock según tipo de producto
                if ($item->product_type === 'quick' || $item->quick_product_id) {
                    $quickProduct = QuickProduct::query()
                        ->when(
                            $owner['affiliated_company_id'],
                            fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                            fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
                        )
                        ->whereKey($item->quick_product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($quickProduct) {
                        $quickProduct->increment('stock', $qty);
                    }
                } else {
                    $product = $this->queryForOwner()
                        ->whereKey($item->inventory_product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($product) {
                        $product->increment('stock', $qty);
                        $product->refresh();

                        $note = "Devolución préstamo {$loan->loan_number} de {$loan->borrower_name}";
                        if (!empty($validated['return_notes'])) {
                            $note .= " (" . trim($validated['return_notes']) . ")";
                        }

                        $product->movements()->create(array_merge($owner, [
                            'type' => 'loan_return',
                            'quantity_delta' => $qty,
                            'stock_after' => $product->stock,
                            'notes' => $note,
                        ]));
                    }
                }

                $item->increment('quantity_returned', $qty);
                $anyProcessed = true;
            }

            if (!$anyProcessed) {
                throw ValidationException::withMessages([
                    'returns' => 'Debes indicar al menos una cantidad mayor a cero para devolver.',
                ]);
            }

            $this->recalculateLoanStatus($loan);
        });

        Audit::log('merchandise_loan.returned', $loan, "Devolución registrada en préstamo {$loan->loan_number}.");

        return redirect()
            ->route('inventory.loans.show', $loan)
            ->with('status', 'Devolución de mercancía registrada exitosamente en bodega.');
    }

    public function settleItems(Request $request, MerchandiseLoan $loan): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeLoan($loan);

        if (!$loan->isActive()) {
            throw ValidationException::withMessages([
                'settle' => 'Este préstamo ya no se encuentra activo.',
            ]);
        }

        $validated = $request->validate([
            'settle' => ['required', 'array', 'min:1'],
            'settle.*.item_id' => ['required', 'integer'],
            'settle.*.quantity' => ['required', 'integer', 'min:0'],
            'settle_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($loan, $validated) {
            $anyProcessed = false;

            foreach ($validated['settle'] as $input) {
                $qty = (int) $input['quantity'];
                if ($qty <= 0) continue;

                $item = $loan->items()->where('id', $input['item_id'])->firstOrFail();
                $pending = $item->pendingUnits();

                if ($qty > $pending) {
                    throw ValidationException::withMessages([
                        'settle' => "No puedes liquidar {$qty} unidades de {$item->displayName()}. Pendientes: {$pending}.",
                    ]);
                }

                $item->increment('quantity_sold', $qty);
                $anyProcessed = true;
            }

            if (!$anyProcessed) {
                throw ValidationException::withMessages([
                    'settle' => 'Debes indicar al menos una cantidad mayor a cero para liquidar.',
                ]);
            }

            if (!empty($validated['settle_notes'])) {
                $loan->notes = trim(($loan->notes ? $loan->notes . "\n" : "") . "[Liquidado: " . now()->format('d/m/Y H:i') . "] " . $validated['settle_notes']);
                $loan->save();
            }

            $this->recalculateLoanStatus($loan);
        });

        Audit::log('merchandise_loan.settled', $loan, "Liquidación por venta registrada en préstamo {$loan->loan_number}.");

        return redirect()
            ->route('inventory.loans.show', $loan)
            ->with('status', 'Mercancía liquidada como venta exitosamente.');
    }

    public function cancel(MerchandiseLoan $loan): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeLoan($loan);

        if (!$loan->isActive()) {
            throw ValidationException::withMessages([
                'cancel' => 'Solo se pueden anular préstamos activos.',
            ]);
        }

        $hasReturnsOrSales = $loan->items->contains(fn ($i) => $i->quantity_returned > 0 || $i->quantity_sold > 0);
        if ($hasReturnsOrSales) {
            throw ValidationException::withMessages([
                'cancel' => 'No se puede anular un préstamo con devoluciones o ventas parciales procesadas.',
            ]);
        }

        $owner = $this->ownerKeys();

        DB::transaction(function () use ($loan, $owner) {
            foreach ($loan->items as $item) {
                if ($item->product_type === 'quick' || $item->quick_product_id) {
                    $quickProduct = QuickProduct::query()
                        ->when(
                            $owner['affiliated_company_id'],
                            fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                            fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
                        )
                        ->whereKey($item->quick_product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($quickProduct) {
                        $quickProduct->increment('stock', $item->quantity_lent);
                    }
                } else {
                    $product = $this->queryForOwner()
                        ->whereKey($item->inventory_product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($product) {
                        $product->increment('stock', $item->quantity_lent);
                        $product->refresh();

                        $product->movements()->create(array_merge($owner, [
                            'type' => 'loan_return',
                            'quantity_delta' => $item->quantity_lent,
                            'stock_after' => $product->stock,
                            'notes' => "Anulación de préstamo {$loan->loan_number}",
                        ]));
                    }
                }
            }

            $loan->update(['status' => 'cancelled']);
        });

        Audit::log('merchandise_loan.cancelled', $loan, "Préstamo {$loan->loan_number} anulado y stock restituido.");

        return redirect()
            ->route('inventory.loans.show', $loan)
            ->with('status', "Préstamo {$loan->loan_number} anulado y stock devuelto a bodega.");
    }

    protected function recalculateLoanStatus(MerchandiseLoan $loan): void
    {
        $loan->load('items');
        $totalPending = 0;
        $totalSold = 0;
        $totalReturned = 0;

        foreach ($loan->items as $item) {
            $totalPending += $item->pendingUnits();
            $totalSold += $item->quantity_sold;
            $totalReturned += $item->quantity_returned;
        }

        if ($totalPending === 0) {
            if ($totalSold > 0 && $totalReturned === 0) {
                $loan->status = 'sold';
            } else {
                $loan->status = 'returned';
            }
        } else {
            if ($totalSold > 0 || $totalReturned > 0) {
                $loan->status = 'partial';
            } else {
                $loan->status = 'active';
            }
        }

        $loan->save();
    }

    protected function loanBaseQuery()
    {
        $keys = $this->ownerKeys();

        return MerchandiseLoan::query()
            ->when(
                $keys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $keys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $keys['tenant_id'])->whereNull('affiliated_company_id')
            );
    }

    protected function loanQuery(array $filters)
    {
        return $this->loanBaseQuery()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('loan_number', 'like', "%{$search}%")
                        ->where('borrower_name', 'like', "%{$search}%")
                        ->orWhere('borrower_phone', 'like', "%{$search}%")
                        ->orWhere('borrower_document', 'like', "%{$search}%")
                        ->orWhereHas('items', fn ($q) => $q->where('product_name', 'like', "%{$search}%")->orWhere('product_sku', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status === 'overdue') {
                    $query->whereIn('status', ['active', 'partial'])->whereDate('due_date', '<', now()->toDateString());
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('loan_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('loan_date', '<=', $date));
    }

    protected function generateLoanNumber(): string
    {
        $owner = $this->ownerKeys();

        $last = MerchandiseLoan::query()
            ->when(
                $owner['affiliated_company_id'],
                fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
            )
            ->where('loan_number', 'like', 'PREST-%')
            ->orderByDesc('id')
            ->first();

        $next = $last ? ((int) substr($last->loan_number, 6)) + 1 : 1;

        do {
            $number = 'PREST-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $exists = MerchandiseLoan::query()
                ->when(
                    $owner['affiliated_company_id'],
                    fn ($q) => $q->where('affiliated_company_id', $owner['affiliated_company_id']),
                    fn ($q) => $q->where('tenant_id', $owner['tenant_id'])->whereNull('affiliated_company_id')
                )
                ->where('loan_number', $number)
                ->exists();
            $next++;
        } while ($exists);

        return $number;
    }

    protected function authorizeLoanAccess(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->accountIsActive()) {
            abort(403, 'Acceso no autorizado.');
        }

        if (! $user->isSuperAdmin() && ! $user->tenant_id && ! $user->affiliated_company_id) {
            abort(403, 'Debes pertenecer a una tienda para gestionar préstamos.');
        }
    }

    protected function authorizeLoan(MerchandiseLoan $loan): void
    {
        $this->authorizeLoanAccess();

        $keys = $this->ownerKeys();

        abort_if(
            $keys['affiliated_company_id']
                ? $loan->affiliated_company_id !== $keys['affiliated_company_id']
                : ($loan->tenant_id !== $keys['tenant_id'] || $loan->affiliated_company_id),
            403
        );
    }
}
