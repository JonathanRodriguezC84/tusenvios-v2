<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Movimientos de inventario - Tus Envios</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; margin: 20px; }
        h2 { font-size: 15px; margin-bottom: 3px; }
        p.date { font-size: 9px; color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f3f4f6; text-align: left; padding: 5px 6px; font-size: 9px; text-transform: uppercase; border-bottom: 2px solid #d1d5db; }
        td { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        td.right { text-align: right; }
        td.center { text-align: center; }
        .footer { margin-top: 15px; font-size: 8px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h2>Movimientos de inventario - Tus Envios</h2>
    <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>SKU</th>
                <th>Tipo</th>
                <th class="right">Cantidad</th>
                <th class="right">Stock despues</th>
                <th>Guia</th>
                <th>Nota</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $movement)
                <tr>
                    <td>{{ optional($movement->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->product?->name ?? '—' }}</td>
                    <td>{{ $movement->product?->sku ?? '—' }}</td>
                    <td class="center">{{ $movement->type }}</td>
                    <td class="right">{{ $movement->quantity_delta }}</td>
                    <td class="right">{{ $movement->stock_after }}</td>
                    <td>{{ $movement->shipment?->guide_number ?? '—' }}</td>
                    <td>{{ $movement->notes ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">{{ $movements->count() }} movimiento(s) · Tus Envios · tusenvios.com.co</p>
</body>
</html>
