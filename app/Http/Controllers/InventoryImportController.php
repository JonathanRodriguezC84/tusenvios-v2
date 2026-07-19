<?php

namespace App\Http\Controllers;

use App\Models\InventoryProduct;
use Illuminate\Http\Request;

class InventoryImportController extends InventoryProductController
{
    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
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

    public function import(Request $request): \Illuminate\Http\RedirectResponse
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
}
