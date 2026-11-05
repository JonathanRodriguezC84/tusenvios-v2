<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remisión Préstamo {{ $loan->loan_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background: #f3f4f6; color: #111827; padding: 20px; font-size: 13px; }
        .page { max-width: 800px; margin: 0 auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 20px; }
        .brand-title { font-size: 20px; font-weight: 900; color: #1e3a8a; }
        .brand-info { font-size: 11px; color: #4b5563; margin-top: 4px; line-height: 1.4; }
        .doc-details { text-align: right; }
        .doc-title { font-size: 13px; font-weight: 900; text-transform: uppercase; color: #4b5563; letter-spacing: 0.5px; }
        .doc-number { font-size: 22px; font-weight: 900; color: #111827; margin: 4px 0; }
        .doc-badge { display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: bold; background: #e0e7ff; color: #3730a3; }
        
        .grid-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; padding: 14px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; }
        .info-col h4 { font-size: 10px; font-weight: 900; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; margin-bottom: 6px; }
        .info-col p { font-size: 12px; margin-bottom: 3px; color: #1f2937; }
        .info-col strong { color: #111827; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 12px; }
        th { background: #f3f4f6; color: #374151; font-weight: 800; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; padding: 8px 12px; text-align: left; border-bottom: 1px solid #d1d5db; }
        td { padding: 9px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .terms { font-size: 10px; color: #6b7280; line-height: 1.45; border-top: 1px dashed #d1d5db; padding-top: 14px; margin-bottom: 40px; }
        .terms strong { color: #374151; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 50px; }
        .sig-box { border-top: 1px solid #9ca3af; padding-top: 8px; text-align: center; font-size: 11px; }
        .sig-title { font-weight: bold; color: #111827; margin-bottom: 2px; }
        .sig-sub { color: #6b7280; font-size: 10px; }

        .print-btn-bar { max-width: 800px; margin: 0 auto 16px auto; display: flex; justify-content: flex-end; gap: 8px; }
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #374151; }

        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; padding: 0; }
            .print-btn-bar { display: none; }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar">
        <a href="{{ route('inventory.loans.show', $loan) }}" class="btn btn-secondary">← Volver al Sistema</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir / Guardar PDF</button>
    </div>

    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div>
                <div class="brand-title">{{ $brand['name'] ?? config('app.name', 'Tus Envíos') }}</div>
                <div class="brand-info">
                    @if (!empty($brand['phone'])) Tel: {{ $brand['phone'] }}<br> @endif
                    @if (!empty($brand['address'])) Dirección: {{ $brand['address'] }}<br> @endif
                    @if (!empty($brand['website'])) {{ $brand['website'] }} @endif
                </div>
            </div>
            <div class="doc-details">
                <div class="doc-title">Remisión de Entrega en Préstamo</div>
                <div class="doc-number">{{ $loan->loan_number }}</div>
                <div class="doc-badge">{{ $loan->statusLabel() }}</div>
            </div>
        </div>

        {{-- Borrower & Loan Info --}}
        <div class="grid-info">
            <div class="info-col">
                <h4>Receptor / Prestatario</h4>
                <p><strong>Nombre:</strong> {{ $loan->borrower_name }}</p>
                @if ($loan->borrower_document)
                    <p><strong>Documento / NIT:</strong> {{ $loan->borrower_document }}</p>
                @endif
                @if ($loan->borrower_phone)
                    <p><strong>Teléfono:</strong> {{ $loan->borrower_phone }}</p>
                @endif
                @if ($loan->borrower_address)
                    <p><strong>Ubicación:</strong> {{ $loan->borrower_address }}</p>
                @endif
            </div>
            <div class="info-col">
                <h4>Datos del Préstamo</h4>
                <p><strong>Fecha de Entrega:</strong> {{ $loan->loan_date->format('d/m/Y') }}</p>
                @if ($loan->due_date)
                    <p><strong>Fecha Límite Retorno:</strong> {{ $loan->due_date->format('d/m/Y') }}</p>
                @endif
                <p><strong>Responsable Emisión:</strong> {{ $loan->creator?->name ?? 'Bodega' }}</p>
                @if ($loan->notes)
                    <p><strong>Notas:</strong> {{ $loan->notes }}</p>
                @endif
            </div>
        </div>

        {{-- Products Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Producto / Descripción</th>
                    <th style="width: 80px;" class="text-center">Cantidad</th>
                    <th style="width: 110px;" class="text-right">Valor Unit.</th>
                    <th style="width: 120px;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loan->items as $index => $item)
                    <tr>
                        <td class="text-center text-gray-500">{{ $index + 1 }}</td>
                        <td>
                            <span class="font-bold">{{ $item->product?->name }}</span>
                            @if ($item->product?->sku)
                                <div style="font-size: 10px; color: #6b7280;">SKU: {{ $item->product->sku }}</div>
                            @endif
                            @if ($item->notes)
                                <div style="font-size: 10px; color: #4b5563; font-style: italic;">{{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="text-center font-bold">{{ $item->quantity_lent }} unds</td>
                        <td class="text-right">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right font-bold">${{ number_format($item->quantity_lent * $item->unit_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #111827;">
                    <td colspan="2" class="font-bold" style="padding-top: 10px;">TOTAL MERCANCÍA PRESTADA</td>
                    <td class="text-center font-bold" style="padding-top: 10px;">{{ $loan->total_items }} unds</td>
                    <td></td>
                    <td class="text-right font-bold" style="padding-top: 10px; font-size: 14px;">${{ number_format($loan->total_value, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Terms & Clause --}}
        <div class="terms">
            <strong>Condiciones de custodia y devolución:</strong> La persona o entidad que recibe la mercancía detallada en este documento declara haberla recibido en perfecto estado físico y operativo en calidad de préstamo o consignación temporal. Se compromete a custodiarla, responder por daño o pérdida, y a devolverla en o antes de la fecha límite pactada, o en su defecto a cancelar el valor total de reposición indicado.
        </div>

        {{-- Signature Blocks --}}
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-title">Entregado por (Bodega / Empresa)</div>
                <div class="sig-sub">{{ $loan->creator?->name ?? 'Representante Autorizado' }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Recibido a Conformidad</div>
                <div class="sig-sub">{{ $loan->borrower_name }} @if($loan->borrower_document) - CC/NIT: {{ $loan->borrower_document }} @endif</div>
            </div>
        </div>
    </div>
</body>
</html>
