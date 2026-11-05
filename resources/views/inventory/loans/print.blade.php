<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remisión {{ $loan->loan_number }} - Préstamo de Mercancía</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #111827;
            background-color: #fff;
            padding: 24px;
            font-size: 12px;
            line-height: 1.4;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .brand-title { font-size: 20px; font-weight: 800; color: #1e3a8a; }
        .brand-info { font-size: 11px; color: #4b5563; margin-top: 4px; }
        .doc-details { text-align: right; }
        .doc-title { font-size: 14px; font-weight: 800; text-transform: uppercase; color: #1e3a8a; }
        .doc-number { font-size: 18px; font-weight: 900; color: #111827; margin: 2px 0; }
        .doc-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 9999px;
            text-transform: uppercase;
            border: 1px solid #d1d5db;
        }
        .grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
            background: #f9fafb;
            padding: 14px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .info-col h4 {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }
        .info-col p { margin-bottom: 3px; font-size: 11px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 10px;
            border-bottom: 1px solid #d1d5db;
            text-align: left;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .terms {
            font-size: 10px;
            color: #4b5563;
            background: #f9fafb;
            border-left: 3px solid #1e3a8a;
            padding: 10px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
        }
        .sig-box {
            border-top: 1px solid #111827;
            padding-top: 6px;
            text-align: center;
        }
        .sig-title { font-weight: 800; font-size: 11px; }
        .sig-sub { font-size: 10px; color: #4b5563; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Print Action Bar (hidden on print) --}}
        <div class="no-print" style="margin-bottom: 16px; display: flex; justify-content: flex-end; gap: 8px;">
            <button onclick="window.print()" style="background: #1e3a8a; color: white; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
                🖨️ Imprimir
            </button>
            <button onclick="window.close()" style="background: #e5e7eb; color: #374151; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
                Cerrar
            </button>
        </div>

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
                            <span class="font-bold">{{ $item->displayName() }}</span>
                            @if ($item->displaySku())
                                <div style="font-size: 10px; color: #6b7280;">SKU: {{ $item->displaySku() }}</div>
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
