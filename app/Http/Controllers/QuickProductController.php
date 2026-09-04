<?php

namespace App\Http\Controllers;

use App\Models\QuickProduct;
use App\Models\Tenant;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QuickProductController extends Controller
{
public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('viewAny', QuickProduct::class);

        if (Auth::user()->canUseInventory()) {
            return redirect()->route('inventory.index');
        }

        $products = $this->queryForOwner()
            ->when(
                $request->filled('q'),
                fn ($query) => $query->where('name', 'like', '%'.$request->input('q').'%')
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('quick-products.index', compact('products'));
    }

    public function template(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('create', QuickProduct::class);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos');

        $headers = ['NOMBRE DE PRODUCTO', 'SKU', 'TIPO', 'COSTO', 'PRECIO VENTA', 'UND'];
        $descriptions = [
            'Nombre comercial con el que se identificará y mostrará el producto en el catálogo. Debe ser claro, específico y permitir identificar fácilmente el producto, modelo, variante o referencia.',
            'Código único utilizado para identificar y controlar internamente cada producto o variante. No debe repetirse entre productos.',
            'Clasificación o tipo de producto al que pertenece el artículo. Selecciona una de las opciones de la lista.',
            'Valor de adquisición o costo unitario del producto para la empresa. Se utiliza para calcular la rentabilidad y utilidad.',
            'Precio al que se ofrecerá el producto al cliente. Debe corresponder al precio de venta unitario definido para el catálogo.',
            'Cantidad actual de unidades disponibles en el inventario del producto.',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($descriptions, null, 'A2');

        $headerStyle = $sheet->getStyle('A1:F1');
        $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('022A8C');
        $headerStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $descStyle = $sheet->getStyle('A2:F2');
        $descStyle->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('6B7280'));
        $descStyle->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $example = ['Camiseta algodon', 'CAM-001', 'Mercancia', 15000, 25000, 100];
        $sheet->fromArray($example, null, 'A3');
        $sheet->getStyle('A3:F3')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('9CA3AF'));

        $firstDataRow = 3;
        $lastRow = 500;

        $options = ['Mercancia', 'Paquete', 'Documento'];
        $validation = $sheet->getDataValidation('C'.$firstDataRow.':C'.$lastRow);
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('"'.implode(',', $options).'"');
        $validation->setShowDropDown(true);

        $sheet->getColumnDimension('A')->setWidth(52);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(60);

        $fileName = 'plantilla-productos-rapidos.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'qp_template_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function import(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', QuickProduct::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:2048'],
        ]);

        $file = $validated['file'];
        $rows = [];

        try {
            if ($file->getClientOriginalExtension() === 'csv' || $file->getClientOriginalExtension() === 'txt') {
                $handle = fopen($file->getRealPath(), 'r');
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'No se pudo leer el archivo. Verifica que sea un Excel o CSV valido.']);
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'El archivo no tiene filas de datos.']);
        }

        $headers = array_map('strtolower', array_map('trim', (array) array_shift($rows)));
        $headerMap = [
            'nombre' => 'name',
            'nombre de producto' => 'name',
            'nombre del producto' => 'name',
            'nombre producto' => 'name',
            'producto' => 'name',
            'sku' => 'sku',
            'codigo' => 'sku',
            'codigo sku' => 'sku',
            'tipo' => 'package_type',
            'tipo de producto' => 'package_type',
            'tipo de paquete' => 'package_type',
            'costo' => 'cost',
            'costo unitario' => 'cost',
            'precio' => 'price',
            'precio venta' => 'price',
            'precio de venta' => 'price',
            'valor venta' => 'price',
            'stock' => 'stock',
            'unidades' => 'stock',
            'und' => 'stock',
            'cantidad' => 'stock',
            'inventario' => 'stock',
        ];
        $mapped = [];
        foreach ($headers as $index => $header) {
            $columnKey = $headerMap[$header] ?? null;
            if ($columnKey) {
                $mapped[$columnKey] = $index;
            }
        }

        $typeMap = [
            'mercad' => 'merchandise',
            'mercancia' => 'merchandise',
            'merchandise' => 'merchandise',
            'paquete' => 'package',
            'package' => 'package',
            'documento' => 'document',
            'document' => 'document',
        ];

        $created = 0;
        $skipped = 0;
        $errors = [];

        $ownerKeys = $this->ownerKeys();
        $existingSkus = QuickProduct::query()
            ->when(
                $ownerKeys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $ownerKeys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $ownerKeys['tenant_id'])->whereNull('affiliated_company_id')
            )
            ->pluck('sku')
            ->map(fn ($sku) => $sku ? strtoupper(trim($sku)) : null)
            ->filter()
            ->flip();

        $rowNumber = 1;
        DB::transaction(function () use ($rows, $mapped, $typeMap, $ownerKeys, &$created, &$skipped, &$errors, &$rowNumber, $existingSkus) {
            foreach ($rows as $row) {
                $rowNumber++;

                $get = fn ($key) => trim((string) ($row[$mapped[$key]] ?? ''));
                $name = $get('name');

                $isDescriptionRow = mb_strlen($name) > 40
                    && str_contains(mb_strtolower($name), 'nombre comercial')
                    && mb_strlen($get('sku')) > 40
                    && mb_strlen($get('package_type')) > 40;
                if ($isDescriptionRow) {
                    continue;
                }

                if ($name === '') {
                    if (implode('', array_map('trim', (array) $row)) === '') {
                        continue;
                    }
                    $errors[] = "Fila {$rowNumber}: falta el nombre del producto.";
                    continue;
                }

                if (mb_strlen($name) > 120) {
                    $errors[] = "Fila {$rowNumber}: el nombre supera 120 caracteres.";
                    continue;
                }

                $sku = $get('sku');
                if (mb_strlen($sku) > 100) {
                    $errors[] = "Fila {$rowNumber}: el SKU supera 100 caracteres.";
                    continue;
                }

                $skuKey = $sku !== '' ? strtoupper($sku) : null;
                if ($skuKey !== null && isset($existingSkus[$skuKey])) {
                    $skipped++;
                    $errors[] = "Fila {$rowNumber}: el SKU '{$sku}' ya existe y se omitio.";
                    continue;
                }

                $typeRaw = strtolower($get('package_type') ?: 'mercancia');
                $type = null;
                foreach ($typeMap as $pattern => $value) {
                    if (str_contains($typeRaw, $pattern)) {
                        $type = $value;
                        break;
                    }
                }
                $type = $type ?? 'merchandise';

                $cost = (float) str_replace(['$', ',', ' '], '', $get('cost')) ?: 0;
                $price = (float) str_replace(['$', ',', ' '], '', $get('price')) ?: 0;
                $stock = (int) str_replace([',', ' '], '', $get('stock')) ?: 0;

                if ($cost < 0 || $price < 0 || $stock < 0) {
                    $errors[] = "Fila {$rowNumber}: los valores no pueden ser negativos.";
                    continue;
                }

                QuickProduct::create(array_merge($ownerKeys, [
                    'name' => $name,
                    'sku' => $sku !== '' ? $sku : null,
                    'package_type' => $type,
                    'cost' => $cost,
                    'price' => $price,
                    'stock' => $stock,
                    'status' => 'active',
                ]));

                if ($skuKey !== null) {
                    $existingSkus[$skuKey] = true;
                }

                $created++;
            }
        });

        Audit::log('quick_product.import', null, "Importacion masiva: {$created} creados, {$skipped} omitidos, ".count($errors)." errores.");

        $message = "Importacion completada: {$created} producto(s) creado(s), {$skipped} omitido(s).";
        if ($errors) {
            $message .= ' Detalle: '.implode(' | ', array_slice($errors, 0, 10)).(count($errors) > 10 ? ' (y '. (count($errors) - 10) .' mas)' : '');
        }

        return redirect()
            ->route('quick-products.index')
            ->with('status', $message);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', QuickProduct::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:100'],
            'package_type' => ['required', 'in:package,document,merchandise'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['cost'] = $validated['cost'] ?? 0;
        $validated['price'] = $validated['price'] ?? 0;
        $validated['stock'] = $validated['stock'] ?? 0;

        $product = QuickProduct::query()->create(array_merge($validated, $this->ownerKeys(), [
            'status' => 'active',
        ]));

        Audit::log('quick_product.created', $product, "Producto rapido {$product->name} creado.");

        return redirect()
            ->route('quick-products.index')
            ->with('status', 'Producto rapido creado correctamente.');
    }

    public function update(Request $request, QuickProduct $quickProduct): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $quickProduct);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:100'],
            'package_type' => ['required', 'in:package,document,merchandise'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,paused'],
        ]);

        $validated['cost'] = $validated['cost'] ?? 0;
        $validated['price'] = $validated['price'] ?? 0;
        $validated['stock'] = $validated['stock'] ?? 0;

        $quickProduct->update($validated);

        Audit::log('quick_product.updated', $quickProduct, "Producto rapido {$quickProduct->name} actualizado.");

        return redirect()
            ->route('quick-products.index')
            ->with('status', 'Producto rapido actualizado correctamente.');
    }

    public function destroy(QuickProduct $quickProduct): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $quickProduct);

        Audit::log('quick_product.deleted', $quickProduct, "Producto rapido {$quickProduct->name} eliminado.");

        $quickProduct->delete();

        return redirect()
            ->route('quick-products.index')
            ->with('status', 'Producto rapido eliminado correctamente.');
    }

    private function owner()
    {
        $user = Auth::user();

        if ($user->role === 'affiliate' && $user->affiliatedCompany) {
            return $user->affiliatedCompany;
        }

        return $user->tenant ?: Tenant::query()->where('subdomain', 'demo-tus-envios')->first();
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

        return QuickProduct::query()
            ->when(
                $keys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $keys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $keys['tenant_id'])->whereNull('affiliated_company_id')
            );
    }

}

