<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventario - Tus Envios</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 20px; }
        h2 { font-size: 16px; margin-bottom: 5px; }
        p.date { font-size: 9px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #d1d5db; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        td.right { text-align: right; }
        td.center { text-align: center; }
        .footer { margin-top: 20px; font-size: 8px; color: #999; text-align: center; }
        .badge-red { color: #dc2626; font-weight: bold; }
        .badge-amber { color: #d97706; font-weight: bold; }
        .badge-green { color: #059669; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Inventario - Tus Envios</h2>
    <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th>Categoria</th>
                <th class="center">Stock</th>
                <th class="right">Costo</th>
                <th class="right">Precio</th>
                <th class="right">Valor stock</th>
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku ?: '—' }}</td>
                    <td>{{ $product->category ?: '—' }}</td>
                    <td class="center">
                        <span class="{{ $product->stock <= 0 ? 'badge-red' : ($product->isLowStock() ? 'badge-amber' : 'badge-green') }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td class="right">{{ number_format($product->cost, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($product->price, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($product->stock * $product->price, 0, ',', '.') }}</td>
                    <td class="center">{{ $product->status === 'active' ? 'Activo' : 'Pausado' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">{{ $products->count() }} producto(s) · Tus Envios · tusenvios.com.co</p>
</body>
</html>
