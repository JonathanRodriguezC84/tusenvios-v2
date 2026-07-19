<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovementRequest;
use App\Models\InventoryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMovementController extends InventoryProductController
{
    public function movements(Request $request): \Illuminate\View\View
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

    public function movement(StoreMovementRequest $request, InventoryProduct $inventoryProduct): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeProduct($inventoryProduct);

        $validated = $request->validated();

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

        \App\Support\Audit::log('inventory_product.movement', $updatedProduct, "Movimiento de inventario {$delta} para {$updatedProduct->name}.");

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

    public function exportMovements(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
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

    public function exportMovementsPdf(Request $request): \Illuminate\Http\Response
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
}
