<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $order->order_number }}</title>
    <style>
        @page { margin: 34px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #15182A; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; }
        .header { width: 100%; border-bottom: 3px solid #4058E1; padding-bottom: 18px; margin-bottom: 22px; }
        .header td { vertical-align: middle; }
        .logo { width: 54px; height: 54px; object-fit: contain; }
        .brand { padding-left: 12px; }
        .brand strong { display: block; color: #4058E1; font-family: Georgia, serif; font-size: 21px; }
        .brand span { color: #667085; font-size: 9px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .invoice-title { color: #4058E1; font-family: Georgia, serif; font-size: 25px; font-weight: bold; text-align: right; }
        .invoice-number { color: #667085; text-align: right; }
        .meta { width: 100%; margin-bottom: 24px; }
        .meta td { width: 50%; vertical-align: top; }
        .meta-card { border: 1px solid #DDE2FF; border-radius: 8px; padding: 14px; }
        .meta-label { color: #FF5602; font-size: 8px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
        .meta-value { display: block; margin-top: 5px; font-size: 13px; font-weight: bold; }
        .meta-line { margin-top: 5px; color: #667085; }
        .status { display: inline-block; margin-top: 7px; border-radius: 20px; background: #F5F7FF; color: #4058E1; padding: 4px 9px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        table.items { width: 100%; border-collapse: collapse; }
        .items th { border-bottom: 2px solid #4058E1; padding: 9px 7px; color: #667085; font-size: 8px; letter-spacing: 1px; text-align: left; text-transform: uppercase; }
        .items td { border-bottom: 1px solid #DDE2FF; padding: 11px 7px; vertical-align: middle; }
        .items .money, .items th.money { text-align: right; white-space: nowrap; }
        .product-cell { width: 48%; }
        .product-image { display: inline-block; width: 60px; height: 60px; margin-right: 10px; vertical-align: middle; object-fit: contain; }
        .image-placeholder { display: inline-block; width: 60px; height: 60px; margin-right: 10px; background: #F5F7FF; vertical-align: middle; }
        .product-copy { display: inline-block; width: 185px; vertical-align: middle; }
        .product-name { display: block; font-weight: bold; }
        .product-meta { display: block; margin-top: 4px; color: #667085; font-size: 9px; }
        .totals { width: 265px; margin-left: auto; margin-top: 18px; border-collapse: collapse; }
        .totals td { padding: 7px 4px; }
        .totals .amount { text-align: right; }
        .totals .grand td { border-top: 2px solid #4058E1; color: #4058E1; font-size: 15px; font-weight: bold; padding-top: 10px; }
        .note { margin-top: 28px; border-left: 4px solid #FF5602; background: #FFF4EE; padding: 12px 14px; color: #4B5563; }
        .footer { position: fixed; right: 0; bottom: -18px; left: 0; color: #98A2B3; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="58%">
                @if ($logo)<img src="{{ $logo }}" alt="" class="logo">@endif
                <span class="brand"><strong>Tabarak Trading</strong><span>Wholesale catalogue</span></span>
            </td>
            <td>
                <div class="invoice-title">Order invoice</div>
                <div class="invoice-number">{{ $order->order_number }}</div>
            </td>
        </tr>
    </table>

    <table class="meta" cellspacing="10">
        <tr>
            <td>
                <div class="meta-card">
                    <span class="meta-label">Shop / owner</span>
                    <span class="meta-value">{{ $order->customer_name }}</span>
                    <div class="meta-line">{{ $order->customer_phone }}</div>
                    @if ($order->customer_address)
                        <div class="meta-line">{{ $order->customer_address }}</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="meta-card">
                    <span class="meta-label">Order details</span>
                    <span class="meta-value">{{ $order->submitted_at->format('M j, Y · g:i A') }}</span>
                    <span class="status">{{ $order->status->value }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="product-cell">Item</th>
                <th>Quantity</th>
                <th class="money">Unit price</th>
                <th class="money">Line total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td class="product-cell">
                        @if ($itemImages->get($item->id))
                            <img src="{{ $itemImages->get($item->id) }}" alt="" class="product-image">
                        @else
                            <span class="image-placeholder"></span>
                        @endif
                        <span class="product-copy">
                            <span class="product-name">{{ $item->product_name }}</span>
                            <span class="product-meta">{{ $item->product_sku ?: 'No SKU' }} · {{ $item->pack_label }}</span>
                        </span>
                    </td>
                    <td>{{ number_format($item->quantity) }}</td>
                    <td class="money">${{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="money">${{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="amount">${{ number_format((float) $order->subtotal, 2) }} USD</td></tr>
        <tr class="grand"><td>Total</td><td class="amount">${{ number_format((float) $order->total, 2) }} USD</td></tr>
    </table>

    <div class="note">This invoice records the order submitted to Tabarak Trading. The team will contact the customer at the U.S. phone number shown above.</div>
    <div class="footer">Tabarak Trading · {{ $order->order_number }} · Generated {{ now()->format('M j, Y') }}</div>
</body>
</html>
