<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Guia {{ $shipment->guide_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        .label { border: 2px solid #000; padding: 15px; max-width: 280px; margin: 0 auto; }
        .brand { text-align: center; margin-bottom: 10px; font-weight: bold; font-size: 13px; }
        .guide { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 10px; }
        .field { margin-bottom: 8px; }
        .field-label { font-size: 8px; text-transform: uppercase; color: #666; }
        .field-value { font-size: 11px; font-weight: bold; }
        .qr { text-align: center; margin: 10px 0; }
        .footer { font-size: 7px; text-align: center; margin-top: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="label">
        <div class="brand">{{ $shipment->affiliatedCompany?->name ?? $shipment->tenant?->name ?? 'Tus Envios' }}</div>
        <div class="guide">{{ $shipment->guide_number }}</div>

        <div class="field">
            <div class="field-label">Destinatario</div>
            <div class="field-value">{{ strtoupper(trim($shipment->recipient_name . ' ' . $shipment->recipient_lastname)) }}</div>
        </div>

        <div class="field">
            <div class="field-label">Direccion</div>
            <div class="field-value">{{ strtoupper($shipment->recipient_address) }}</div>
        </div>

        <div class="field">
            <div class="field-label">Ciudad / Barrio</div>
            <div class="field-value">{{ strtoupper($shipment->recipient_city ?: $shipment->recipient_locality) }} / {{ strtoupper($shipment->recipient_neighborhood ?? '') }}</div>
        </div>

        <div class="field">
            <div class="field-label">Telefono</div>
            <div class="field-value">{{ $shipment->recipient_phone }}</div>
        </div>

        <div class="field">
            <div class="field-label">Producto</div>
            <div class="field-value">{{ strtoupper($shipment->content_description ?: '—') }}</div>
        </div>

        <div class="field">
            <div class="field-label">Piezas</div>
            <div class="field-value">{{ $shipment->pieces }}</div>
        </div>

        @if ($shipment->collection_value > 0)
        <div class="field">
            <div class="field-label">Recaudo</div>
            <div class="field-value">${{ number_format($shipment->collection_value, 0, ',', '.') }}</div>
        </div>
        @endif

        <div class="qr">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&margin=8&data={{ $shipment->guide_number }}" width="100" height="100">
        </div>

        <div class="footer">TUSENVIOS.COM.CO · {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>
