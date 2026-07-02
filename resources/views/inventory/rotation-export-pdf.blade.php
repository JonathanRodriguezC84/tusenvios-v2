<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rotacion de productos - Tus Envios</title>
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
    @php
        $actionLabels = ['agotado' => 'Agotado', 'reponer' => 'Reponer', 'moviendo' => 'En movimiento', 'quieto' => 'Quieto', 'pausado' => 'Pausado'];
    @endphp
    <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th>Categoria</th>
                <th class="center">Vendidas</th>
                <th class="right">Prom. diario</th>
                <th class="center">Stock</th>
                <th class="center">Minimo</th>
                <th class="center">Cobertura</th>
                <th class="right">Reposicion</th>
                <th class="right">Costo repos.</th>
                <th class="right">Valor stock</th>
                <th class="center">Accion</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php $p = $row['product']; @endphp
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->sku ?: '—' }}</td>
                    <td>{{ $p->category ?: '—' }}</td>
                    <td class="center">{{ $row['units'] }}</td>
                    <td class="right">{{ number_format($row['daily_average'], 2, ',', '.') }}</td>
                    <td class="center">{{ $p->stock }}</td>
                    <td class="center">{{ $p->stock_minimum }}</td>
                    <td class="center">{{ $row['days_of_stock'] ?? '∞' }}</td>
                    <td class="right">{{ $row['reorder_quantity'] }}</td>
                    <td class="right">${{ number_format((float) $row['reorder_cost'], 0, ',', '.') }}</td>
                    <td class="right">${{ number_format((float) $row['stock_value'], 0, ',', '.') }}</td>
                    <td class="center">{{ $actionLabels[$row['action']] ?? $row['action'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">{{ count($rows) }} producto(s) · Tus Envios · tusenvios.com.co</p>
</body>
</html>
