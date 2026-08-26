<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $sale->invoice_number }}</title>
    <style>
        @page {
            size: 5.5in 8.5in;
            margin: 10mm 9mm 13mm;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: #000;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.2pt;
            line-height: 1.35;
        }

        table { width: 100%; border-collapse: collapse; }
        p { margin: 0; }

        .brand-table td {
            padding: 0 0 5mm;
            vertical-align: top;
        }

        .brand-mark {
            display: block;
            width: 48mm;
            height: auto;
            margin-bottom: 1.5mm;
        }

        .brand-caption {
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 1.35pt;
            text-transform: uppercase;
        }

        .invoice-heading {
            width: 42mm;
            border: 1.5pt solid #000;
            padding: 3mm 3.5mm !important;
            text-align: right;
        }

        .invoice-heading .document-type {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1.2pt;
        }

        .invoice-heading .number {
            margin-top: 1mm;
            font-size: 8pt;
            font-weight: bold;
        }

        .invoice-heading .status {
            margin-top: 1.5mm;
            padding-top: 1.5mm;
            border-top: 0.75pt solid #000;
            font-size: 6.5pt;
            letter-spacing: 0.7pt;
            text-transform: uppercase;
        }

        .section-title {
            margin: 0 0 2mm;
            padding-bottom: 1mm;
            border-bottom: 1pt solid #000;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1pt;
            text-transform: uppercase;
        }

        .meta-table { margin-bottom: 4mm; }
        .meta-table td {
            width: 50%;
            padding: 0.8mm 2.5mm 0.8mm 0;
            vertical-align: top;
        }

        .label {
            display: inline-block;
            min-width: 18mm;
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 0.4pt;
            text-transform: uppercase;
        }

        .items-table { margin-top: 1mm; }
        .items-table thead { display: table-header-group; }
        .items-table tr { page-break-inside: avoid; }

        .items-table th {
            padding: 2mm 1.4mm;
            border-top: 1.2pt solid #000;
            border-bottom: 1.2pt solid #000;
            background: #e8e8e8;
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 0.35pt;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 2mm 1.4mm;
            border-bottom: 0.5pt solid #aaa;
            vertical-align: top;
        }

        .items-table .code { width: 18%; }
        .items-table .product { width: 40%; }
        .items-table .quantity { width: 10%; text-align: center; }
        .items-table .money { width: 16%; text-align: right; white-space: nowrap; }
        .product-name { font-weight: bold; }
        .product-reference { margin-top: 0.5mm; color: #444; font-size: 6.5pt; }

        .summary-table {
            width: 62mm;
            margin: 4mm 0 0 auto;
            page-break-inside: avoid;
        }

        .summary-table td { padding: 1mm 0 1mm 3mm; }
        .summary-table .summary-label {
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 0.35pt;
            text-transform: uppercase;
        }
        .summary-table .summary-value { text-align: right; white-space: nowrap; }
        .summary-table .grand-total td {
            padding-top: 2mm;
            border-top: 1.5pt solid #000;
            font-size: 11pt;
            font-weight: bold;
        }

        .payment-note {
            margin-top: 4mm;
            padding: 2.5mm 0;
            border-top: 0.75pt solid #000;
            border-bottom: 0.75pt solid #000;
            page-break-inside: avoid;
            font-size: 7pt;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -9mm;
            left: 0;
            border-top: 0.5pt solid #777;
            padding-top: 1.5mm;
            color: #333;
            font-size: 6pt;
            text-align: center;
        }

        .page-number:before { content: counter(page); }
    </style>
</head>
<body>
    @php
        $customerName = $sale->customer_name_snapshot ?: $sale->customer?->name ?: 'Cliente general';
        $customerDocument = $sale->customer_document_snapshot ?: $sale->customer?->dni_ruc ?: 'No registrado';
        $statusLabel = match ($sale->status) {
            'cancelled' => 'Anulada',
            'draft' => 'Borrador',
            default => 'Emitida',
        };
    @endphp

    <div class="footer">
        Documento generado por LEO AutoParts - Página <span class="page-number"></span>
    </div>

    <table class="brand-table">
        <tr>
            <td>
                <img class="brand-mark" src="{{ $logoDataUri }}" alt="LEO AutoParts">
                <p class="brand-caption">Repuestos automotrices</p>
            </td>
            <td class="invoice-heading">
                <p class="document-type">FACTURA</p>
                <p class="number">{{ $sale->invoice_number }}</p>
                <p class="status">{{ $statusLabel }}</p>
            </td>
        </tr>
    </table>

    <p class="section-title">Datos de la operación</p>
    <table class="meta-table">
        <tr>
            <td><span class="label">Cliente</span>{{ $customerName }}</td>
            <td><span class="label">Fecha</span>{{ $sale->sale_date?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><span class="label">DNI / RUC</span>{{ $customerDocument }}</td>
            <td><span class="label">Vendedor</span>{{ $sale->user?->name ?? 'No registrado' }}</td>
        </tr>
        <tr>
            <td><span class="label">Pago</span>{{ $sale->paymentMethod?->name ?? 'No registrado' }}</td>
            <td><span class="label">Moneda</span>USD</td>
        </tr>
    </table>

    <p class="section-title">Detalle</p>
    <table class="items-table">
        <thead>
            <tr>
                <th class="code">Código</th>
                <th class="product">Producto</th>
                <th class="quantity">Cant.</th>
                <th class="money">P. unit.</th>
                <th class="money">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->saleDetails as $detail)
                <tr>
                    <td class="code">{{ $detail->product?->code ?? 'N/D' }}</td>
                    <td class="product">
                        <p class="product-name">{{ $detail->product?->name ?? 'Producto no disponible' }}</p>
                        @if ($detail->product?->brand || $detail->product?->model)
                            <p class="product-reference">{{ trim(($detail->product?->brand ?? '').' '.($detail->product?->model ?? '')) }}</p>
                        @endif
                    </td>
                    <td class="quantity">{{ number_format($detail->quantity, 0) }}</td>
                    <td class="money">$ {{ number_format($detail->price, 2) }}</td>
                    <td class="money">$ {{ number_format($detail->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-label">Subtotal</td>
            <td class="summary-value">$ {{ number_format($sale->subtotal, 2) }}</td>
        </tr>
        @if ((float) $sale->discount_total > 0)
            <tr>
                <td class="summary-label">Descuento</td>
                <td class="summary-value">- $ {{ number_format($sale->discount_total, 2) }}</td>
            </tr>
        @endif
        @if ((float) $sale->tax_total > 0)
            <tr>
                <td class="summary-label">Impuestos</td>
                <td class="summary-value">$ {{ number_format($sale->tax_total, 2) }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td>Total</td>
            <td class="summary-value">$ {{ number_format($sale->total, 2) }}</td>
        </tr>
        <tr>
            <td class="summary-label">Recibido</td>
            <td class="summary-value">$ {{ number_format($sale->amount_received, 2) }}</td>
        </tr>
        @if ((float) $sale->change > 0)
            <tr>
                <td class="summary-label">Cambio</td>
                <td class="summary-value">$ {{ number_format($sale->change, 2) }}</td>
            </tr>
        @endif
        @if ((float) $sale->balance > 0)
            <tr>
                <td class="summary-label">Saldo</td>
                <td class="summary-value">$ {{ number_format($sale->balance, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="payment-note">
        <strong>Gracias por elegir LEO AutoParts.</strong>
        Conserve esta factura como constancia de la operación.
    </div>
</body>
</html>
