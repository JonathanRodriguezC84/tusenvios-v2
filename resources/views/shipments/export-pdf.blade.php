<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Guias - Tus Envios</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; margin: 15px; }
        h2 { font-size: 14px; margin-bottom: 3px; }
        p.date { font-size: 8px; color: #666; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f3f4f6; text-align: left; padding: 4px 5px; font-size: 8px; text-transform: uppercase; border-bottom: 2px solid #d1d5db; }
        td { padding: 3px 5px; border-bottom: 1px solid #e5e7eb; font-size: 8px; }
        td.right { text-align: right; }
        td.center { text-align: center; }
        .footer { margin-top: 12px; font-size: 7px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h2>Guias - Tus Envios</h2>
    <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Guia</th>
                <th>Cliente</th>
                <th>Destinatario</th>
                <th>Telefono</th>
                <th>Direccion</th>
                <th>Zona</th>
                <th>Tarifa</th>
                <th>Estado</th>
                <th class="right">Valor envio</th>
                <th class="right">Valor recaudo</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shipments as $shipment)
                @php
                    $settlement = $shipment->settlementItems->first()?->settlement;
                    $settlementNumber = $settlement?->settlement_number ?? ($shipment->affiliated_company_id ? 'Pendiente' : 'No aplica');
                    $settlementStatus = $settlement ? ($settlement->status === 'paid' ? 'Pagada' : 'Pendiente de pago') : ($shipment->affiliated_company_id ? 'Pendiente' : 'No aplica');
                @endphp
                <tr>
                    <td>{{ $shipment->guide_number }}</td>
                    <td>{{ $shipment->affiliatedCompany?->name ?? 'RCI' }}</td>
                    <td>{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</td>
                    <td>{{ $shipment->recipient_phone }}</td>
                    <td>{{ $shipment->recipient_address }}</td>
                    <td>{{ $shipment->zone }}</td>
                    <td>{{ $shipment->deliveryZone?->name ?? 'Manual' }}</td>
                    <td>{{ $statusLabels[$shipment->status] ?? $shipment->status }}</td>
                    <td class="right">${{ number_format((float) $shipment->shipping_value, 0, ',', '.') }}</td>
                    <td class="right">${{ number_format((float) $shipment->collection_value, 0, ',', '.') }}</td>
                    <td>{{ $shipment->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">{{ $shipments->count() }} guia(s) · Tus Envios · tusenvios.com.co</p>
</body>
</html>
