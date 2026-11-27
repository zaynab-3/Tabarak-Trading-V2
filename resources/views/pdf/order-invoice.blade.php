<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        @page { size: letter portrait; margin: 24px 28px 34px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #111;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            line-height: 1.35;
        }
        table { border-collapse: collapse; }
        .header { width: 100%; margin-bottom: 12px; }
        .header td { vertical-align: top; }
        .brand-block { width: 61%; }
        .logo-wrap { display: inline-block; width: 78px; vertical-align: top; }
        .logo { width: 70px; height: 70px; object-fit: contain; }
        .company-copy { display: inline-block; width: 320px; vertical-align: top; padding-top: 1px; }
        .company-name { margin: 0 0 4px; font-size: 16px; font-weight: 700; }
        .company-line { margin: 1px 0; }
        .invoice-panel { width: 39%; border: 1.5px solid #111; }
        .invoice-panel td { padding: 5px 7px; border-bottom: 1px solid #111; }
        .invoice-panel tr:last-child td { border-bottom: 0; }
        .invoice-panel .invoice-heading { font-size: 15px; font-weight: 700; text-align: center; }
        .invoice-panel .label { font-weight: 700; }
        .invoice-panel .value { text-align: right; }

        .party-table { width: 100%; margin-bottom: 9px; table-layout: fixed; }
        .party-table td { width: 50%; vertical-align: top; padding-right: 5px; }
        .party-table td:last-child { padding-right: 0; padding-left: 5px; }
        .party-box { border: 1px solid #111; min-height: 82px; }
        .party-title { border-bottom: 1px solid #111; padding: 3px 6px; font-weight: 700; background: #f4f4f4; }
        .party-content { padding: 6px; min-height: 61px; }
        .party-name { font-weight: 700; margin-bottom: 2px; }
        .party-address { white-space: pre-line; min-height: 26px; }
        .party-phone { margin-top: 3px; }

        .meta-table { width: 100%; margin-bottom: 10px; table-layout: fixed; }
        .meta-table th, .meta-table td { border: 1px solid #111; padding: 4px 6px; }
        .meta-table th { background: #f4f4f4; font-size: 8px; text-transform: uppercase; letter-spacing: .35px; }
        .meta-table td { min-height: 22px; text-align: center; }
        .payment-cell { white-space: nowrap; }
        .check-box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #111;
            margin: 0 3px 0 6px;
            vertical-align: -1px;
            text-align: center;
            line-height: 8px;
            font-size: 8px;
            font-weight: 700;
        }
        .check-box.first { margin-left: 0; }

        .items { width: 100%; table-layout: fixed; }
        .items thead { display: table-header-group; }
        .items th, .items td { border: 1px solid #111; padding: 4px 5px; vertical-align: top; }
        .items th { background: #ededed; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .items td { font-size: 8.8px; }
        .items .num { width: 5%; text-align: center; }
        .items .code { width: 17%; }
        .items .description { width: 39%; }
        .items .qty { width: 9%; text-align: center; }
        .items .price { width: 14%; text-align: right; white-space: nowrap; }
        .items .amount { width: 16%; text-align: right; white-space: nowrap; }
        .item-name { font-weight: 700; }
        .item-pack { color: #444; margin-top: 2px; }

        .summary { width: 100%; margin-top: 8px; }
        .summary td { vertical-align: top; }
        .summary-left { width: 61%; padding-right: 15px; }
        .summary-right { width: 39%; }
        .totals { width: 100%; }
        .totals td { border: 1px solid #111; padding: 5px 7px; }
        .totals .label { font-weight: 700; background: #f4f4f4; }
        .totals .amount { text-align: right; font-weight: 700; }
        .totals .grand td { font-size: 11px; }

        .signatures { margin-top: 14px; page-break-inside: avoid; }
        .signature-row { width: 100%; margin-top: 9px; }
        .signature-row td { width: 50%; padding-right: 16px; vertical-align: bottom; }
        .signature-row td:last-child { padding-right: 0; padding-left: 16px; }
        .line { display: inline-block; width: 165px; height: 13px; border-bottom: 1px solid #111; vertical-align: bottom; }
        .acknowledgement { margin-top: 8px; font-size: 8px; }

        .policy { margin-top: 14px; border-top: 1.5px solid #111; padding-top: 7px; page-break-inside: avoid; }
        .policy-title { margin-bottom: 4px; font-weight: 700; font-size: 10px; }
        .policy-line { margin: 2px 0; }
        .policy-line:before { content: "• "; }
        .legal-note { margin-top: 5px; font-size: 7px; color: #444; }

        .footer {
            position: fixed;
            bottom: -22px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #555;
        }
    </style>
</head>
<body>
    @php
        $shipAddress = filled($order->shipping_address) ? $order->shipping_address : $order->billing_address;
        $policyLines = collect(preg_split('/\r\n|\r|\n/', (string) $company['policy']))
            ->map(fn ($line) => trim($line))
            ->filter();
        $paymentMethod = $order->payment_method?->value;
    @endphp

    <table class="header">
        <tr>
            <td class="brand-block">
                <span class="logo-wrap">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="" class="logo">
                    @endif
                </span>
                <span class="company-copy">
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-line">{{ $company['address_line_1'] }}</div>
                    <div class="company-line">{{ $company['address_line_2'] }}</div>
                    <div class="company-line">Phone: {{ $company['phone'] }}</div>
                    <div class="company-line">Email: {{ $company['email'] }}</div>
                </span>
            </td>
            <td>
                <table class="invoice-panel">
                    <tr><td colspan="2" class="invoice-heading">INVOICE</td></tr>
                    <tr><td class="label">Invoice #</td><td class="value">{{ $order->order_number }}</td></tr>
                    <tr><td class="label">Print Date</td><td class="value">{{ $printedAt->format('m/d/Y') }}</td></tr>
                    <tr><td class="label">Order Date</td><td class="value">{{ $order->submitted_at?->timezone('America/Detroit')->format('m/d/Y') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="party-table">
        <tr>
            <td>
                <div class="party-box">
                    <div class="party-title">Bill To</div>
                    <div class="party-content">
                        <div class="party-name">{{ $order->customer_name }}</div>
                        <div class="party-address">{{ $order->billing_address ?: ' ' }}</div>
                        <div class="party-phone">Phone: {{ $order->customer_phone }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="party-box">
                    <div class="party-title">Ship To</div>
                    <div class="party-content">
                        <div class="party-name">{{ $order->customer_name }}</div>
                        <div class="party-address">{{ $shipAddress ?: ' ' }}</div>
                        <div class="party-phone">Phone: {{ $order->customer_phone }}</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <thead>
            <tr>
                <th>Purchase Order #</th>
                <th>Delivery Date</th>
                <th>Payment Method</th>
                <th>Check #</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $order->purchase_order_number ?: '—' }}</td>
                <td>{{ $order->delivery_date?->format('m/d/Y') ?: '—' }}</td>
                <td class="payment-cell">
                    <span class="check-box first">{{ $paymentMethod === 'cash' ? 'X' : '' }}</span> Cash
                    <span class="check-box">{{ $paymentMethod === 'check' ? 'X' : '' }}</span> Check
                </td>
                <td>{{ $order->check_number ?: '—' }}</td>
            </tr>
        </tbody>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="num">#</th>
                <th class="code">Item Code</th>
                <th class="description">Description</th>
                <th class="qty">Qty</th>
                <th class="price">Unit Price</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td class="code">{{ $item->product_sku ?: '—' }}</td>
                    <td class="description">
                        <div class="item-name">{{ $item->product_name }}</div>
                        @if ($item->pack_label)
                            <div class="item-pack">{{ $item->pack_label }}</div>
                        @endif
                    </td>
                    <td class="qty">{{ number_format($item->quantity) }}</td>
                    <td class="price">${{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="amount">${{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="summary-left"></td>
            <td class="summary-right">
                <table class="totals">
                    <tr><td class="label">Sub-total</td><td class="amount">${{ number_format((float) $order->subtotal, 2) }}</td></tr>
                    <tr class="grand"><td class="label">Total</td><td class="amount">${{ number_format((float) $order->total, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="signatures">
        <div class="acknowledgement">By signing below, the receiving party acknowledges receipt of the merchandise and the Company Policy printed on this invoice.</div>
        <table class="signature-row">
            <tr>
                <td><strong>Received By:</strong> <span class="line"></span></td>
                <td><strong>Signature:</strong> <span class="line"></span></td>
            </tr>
        </table>
        <table class="signature-row">
            <tr>
                <td><strong>Delivered By:</strong> <span class="line"></span></td>
                <td><strong>Signature:</strong> <span class="line"></span></td>
            </tr>
        </table>
    </div>

    <div class="policy">
        <div class="policy-title">Note / Company Policy</div>
        @foreach ($policyLines as $line)
            <div class="policy-line">{{ $line }}</div>
        @endforeach
        <div class="legal-note">Policy language is printed for business records and is subject to applicable law.</div>
    </div>

    <div class="footer">{{ $company['name'] }} · Invoice {{ $order->order_number }} · Printed {{ $printedAt->format('m/d/Y') }}</div>
</body>
</html>
