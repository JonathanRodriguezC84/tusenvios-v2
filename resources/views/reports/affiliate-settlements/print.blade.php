<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Liquidacion {{ $settlement->settlement_number }}</title>
        <style>
            @page {
                margin: 14mm;
                size: letter;
            }

            * {
                box-sizing: border-box;
            }

            body {
                background: #e5e7eb;
                color: #111827;
                font-family: "Inter", Arial, Helvetica, sans-serif;
                margin: 0;
            }

            .actions {
                margin: 16px auto;
                text-align: center;
            }

            .actions button,
            .actions a {
                background: #1d4ed8;
                border: 0;
                border-radius: 6px;
                color: #ffffff;
                display: inline-block;
                font-size: 14px;
                font-weight: 700;
                margin: 0 4px;
                padding: 10px 14px;
                text-decoration: none;
            }

            .actions a {
                background: #ffffff;
                border: 1px solid #d1d5db;
                color: #374151;
            }

            .page {
                background: #ffffff;
                margin: 0 auto 24px;
                max-width: 216mm;
                min-height: 279mm;
                padding: 14mm;
            }

            header {
                align-items: flex-start;
                border-bottom: 2px solid #111827;
                display: flex;
                justify-content: space-between;
                gap: 18px;
                padding-bottom: 14px;
            }

            img {
                height: 42px;
                object-fit: contain;
                width: auto;
            }

            h1,
            h2,
            p {
                margin: 0;
            }

            h1 {
                font-size: 24px;
                line-height: 1.1;
            }

            h2 {
                font-size: 15px;
                margin-bottom: 8px;
            }

            .muted {
                color: #6b7280;
                font-size: 12px;
                margin-top: 4px;
            }

            .status {
                border-radius: 999px;
                display: inline-block;
                font-size: 12px;
                font-weight: 700;
                margin-top: 8px;
                padding: 5px 10px;
            }

            .status-paid {
                background: #dbeafe;
                color: #1e40af;
            }

            .status-closed {
                background: #fef3c7;
                color: #92400e;
            }

            .grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(4, 1fr);
                margin-top: 18px;
            }

            .box {
                border: 1px solid #d1d5db;
                padding: 10px;
            }

            .box .label {
                color: #6b7280;
                display: block;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .box .value {
                display: block;
                font-size: 14px;
                font-weight: 800;
                margin-top: 4px;
                overflow-wrap: anywhere;
            }

            .totals {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(3, 1fr);
                margin-top: 18px;
            }

            .total .value {
                font-size: 20px;
            }

            table {
                border-collapse: collapse;
                font-size: 12px;
                margin-top: 18px;
                width: 100%;
            }

            th,
            td {
                border-bottom: 1px solid #e5e7eb;
                padding: 8px 6px;
                text-align: left;
                vertical-align: top;
            }

            th {
                background: #f3f4f6;
                color: #374151;
                font-size: 11px;
                text-transform: uppercase;
            }

            .money {
                text-align: right;
                white-space: nowrap;
            }

            .notes {
                border-top: 1px solid #d1d5db;
                color: #4b5563;
                font-size: 11px;
                margin-top: 18px;
                padding-top: 10px;
            }

            @media print {
                body {
                    background: #ffffff;
                }

                .actions {
                    display: none;
                }

                .page {
                    margin: 0;
                    min-height: auto;
                    padding: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="actions">
            <button onclick="window.print()">Imprimir / guardar PDF</button>
            <a href="{{ route('reports.affiliate-settlements.show', $settlement) }}">Volver</a>
        </div>

        <main class="page">
            <header>
                <div>
                    <img src="/pwa-icon.svg" alt="Tus Envios">
                    <p class="muted">Tus Envios</p>
                </div>
                <div>
                    <h1>Liquidacion {{ $settlement->settlement_number }}</h1>
                    <p class="muted">{{ $settlement->affiliatedCompany?->name ?? 'Sin afiliada' }}</p>
                    <span class="status {{ $settlement->status === 'paid' ? 'status-paid' : 'status-closed' }}">
                        {{ $settlement->status === 'paid' ? 'Pagada' : 'Pendiente de pago' }}
                    </span>
                </div>
            </header>

            <section class="grid">
                <div class="box">
                    <span class="label">Periodo</span>
                    <span class="value">{{ $settlement->date_from->format('d/m/Y') }} - {{ $settlement->date_to->format('d/m/Y') }}</span>
                </div>
                <div class="box">
                    <span class="label">Guias</span>
                    <span class="value">{{ $settlement->shipments_count }}</span>
                </div>
                <div class="box">
                    <span class="label">Cerrada por</span>
                    <span class="value">{{ $settlement->creator?->name ?? 'Sistema' }}</span>
                </div>
                <div class="box">
                    <span class="label">Fecha cierre</span>
                    <span class="value">{{ $settlement->closed_at?->format('d/m/Y H:i') }}</span>
                </div>
                <div class="box">
                    <span class="label">Pagada por</span>
                    <span class="value">{{ $settlement->payer?->name ?? 'Pendiente' }}</span>
                </div>
                <div class="box">
                    <span class="label">Fecha pago</span>
                    <span class="value">{{ $settlement->paid_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</span>
                </div>
                <div class="box" style="grid-column: span 2;">
                    <span class="label">Referencia pago</span>
                    <span class="value">{{ $settlement->payment_reference ?: 'Sin referencia' }}</span>
                </div>
            </section>

            <section class="totals">
                <div class="box total">
                    <span class="label">Envios</span>
                    <span class="value">${{ number_format($settlement->shipping_total, 0, ',', '.') }}</span>
                </div>
                <div class="box total">
                    <span class="label">Recaudo neto comercio</span>
                    <span class="value">${{ number_format($settlement->net_collection, 0, ',', '.') }}</span>
                </div>
                <div class="box total">
                    <span class="label">Total operacion</span>
                    <span class="value">${{ number_format($settlement->total_to_invoice, 0, ',', '.') }}</span>
                </div>
            </section>

            <table>
                <thead>
                    <tr>
                        <th>Guia</th>
                        <th>Destinatario</th>
                        <th>Tarifa</th>
                        <th>Estado</th>
                        <th class="money">Envio</th>
                        <th class="money">Recaudo</th>
                        <th class="money">Comision</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settlement->items as $item)
                        <tr>
                            <td>{{ $item->guide_number }}</td>
                            <td>{{ $item->recipient_name }}</td>
                            <td>{{ $item->delivery_zone_name ?? 'Manual' }}</td>
                            <td>{{ $item->status }}</td>
                            <td class="money">${{ number_format($item->shipping_value, 0, ',', '.') }}</td>
                            <td class="money">${{ number_format($item->collection_value, 0, ',', '.') }}</td>
                            <td class="money">${{ number_format($item->commission_value, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($settlement->notes)
                <p class="notes">{{ $settlement->notes }}</p>
            @endif
        </main>
    </body>
</html>

