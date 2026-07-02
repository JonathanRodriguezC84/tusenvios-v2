<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Categorias - Tus Envios</title>
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
    </style>
</head>
<body>
    <h2>Categorias de inventario - Tus Envios</h2>
    <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="center">Productos</th>
                <th class="center">Activos</th>
                <th class="center">Pausados</th>
                <th class="center">Unidades</th>
                <th class="center">Stock bajo</th>
                <th class="center">Agotados</th>
                <th class="right">Costo activo</th>
                <th class="right">Venta activa</th>
                <th class="right">Utilidad</th>
                <th class="right">Margen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['category'] }}</td>
                    <td class="center">{{ $row['products'] }}</td>
                    <td class="center">{{ $row['active'] }}</td>
                    <td class="center">{{ $row['paused'] }}</td>
                    <td class="center">{{ $row['units'] }}</td>
                    <td class="center">{{ $row['low_stock'] }}</td>
                    <td class="center">{{ $row['out_stock'] }}</td>
                    <td class="right">{{ number_format((float) $row['cost_value'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $row['sale_value'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $row['profit_value'], 0, ',', '.') }}</td>
                    <td class="right">{{ $row['margin_percent'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">{{ $totals['products'] }} producto(s) en {{ $totals['categories'] }} categoria(s) · Tus Envios · tusenvios.com.co</p>
</body>
</html>
