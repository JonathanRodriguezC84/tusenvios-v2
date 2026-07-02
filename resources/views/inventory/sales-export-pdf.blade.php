<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ventas por producto - Tus Envios</title>
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
    <h2>Ventas por producto - Tus Envios</h2>
    <p class="date">Generado: {{ now()->format('d/m/Y H:i') }} | Productos: {{ $totals['products'] }} | Unidades: {{ $totals['units'] }} | Venta: ${{ number_format((float) $totals['sales_value'], 0, ',', '.') }}</p>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th>Categoria</th>
                <th class="center">Unidades</th>
                <th class="center">Guias</th>
                <th class="right">Venta</th>
                <th class="right">Costo</th>
                <th class="right">Utilidad</th>
                <th class="right">Margen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['sku'] ?: '—' }}</td>
                    <td>{{ $row['category'] ?: '—' }}</td>
                    <td class="center">{{ $row['units'] }}</td>
                    <td class="center">{{ $row['shipments_count'] }}</td>
                    <td class="right">${{ number_format((float) $row['sales_value'], 0, ',', '.') }}</td>
                    <td class="right">${{ number_format((float) $row['cost_value'], 0, ',', '.') }}</td>
                    <td class="right">${{ number_format((float) $row['profit_value'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $row['margin_percent'], 1, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">{{ $totals['products'] }} producto(s) · Tus Envios · tusenvios.com.co</p>
</body>
</html>
