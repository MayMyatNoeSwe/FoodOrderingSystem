<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt & Payslip #{{ $order->order_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.5;
        }
        .wrapper {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #D70F64 0%, #E21B70 50%, #FF2B85 100%);
            padding: 32px 28px;
            color: #ffffff;
            text-align: center;
        }
        .header .brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.5px;
            text-transform: lowercase;
            margin-bottom: 6px;
        }
        .header .brand span.logo-icon {
            font-size: 26px;
        }
        .header .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.35);
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 8px;
        }
        .content {
            padding: 28px;
        }
        .greeting-box {
            background: #fdf2f8;
            border-left: 4px solid #D70F64;
            padding: 16px 20px;
            border-radius: 0 12px 12px 0;
            margin-bottom: 24px;
        }
        .greeting-box h2 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 800;
            color: #9d174d;
        }
        .greeting-box p {
            margin: 0;
            font-size: 13px;
            color: #475569;
        }
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .meta-grid td {
            padding: 14px 16px;
            font-size: 12px;
            vertical-align: top;
        }
        .meta-grid .label {
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        .meta-grid .val {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table th {
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .item-name {
            font-weight: 700;
            color: #0f172a;
        }
        .item-qty {
            font-weight: 800;
            color: #D70F64;
        }
        .price-col {
            text-align: right;
            font-family: monospace;
            font-weight: 600;
        }
        .summary-card {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 6px 0;
            color: #475569;
        }
        .summary-row.total {
            border-top: 2px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 8px;
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
        }
        .summary-row.total .total-amount {
            color: #D70F64;
            font-size: 18px;
        }
        .payment-stamp {
            margin-top: 16px;
            padding: 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            text-align: center;
            font-size: 12px;
            font-weight: 800;
            color: #166534;
        }
        .cta-container {
            text-align: center;
            margin: 28px 0 10px 0;
        }
        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #D70F64 0%, #E21B70 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 14px;
            box-shadow: 0 4px 14px rgba(215, 15, 100, 0.35);
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
        .footer a {
            color: #D70F64;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <span class="logo-icon">🐼</span>
                <span>FoodOrder</span>
            </div>
            <div style="font-size: 14px; font-weight: 600; opacity: 0.95;">Official Order Receipt & Tax Invoice</div>
            <div class="badge">✓ Order Confirmed &amp; Accepted</div>
        </div>

        <!-- Body Content -->
        <div class="content">
            <!-- Greeting Box -->
            <div class="greeting-box">
                <h2>Thank you for your order, {{ $order->user->name ?? 'Valued Customer' }}! 🍽️</h2>
                <p>The restaurant has accepted your order and our kitchen team has started preparing your fresh gourmet dishes.</p>
            </div>

            <!-- Order Metadata Table -->
            <table class="meta-grid">
                <tr>
                    <td style="width: 50%; border-right: 1px solid #e2e8f0;">
                        <span class="label">Order Reference #</span>
                        <span class="val" style="color: #D70F64;">{{ $order->order_number }}</span>
                    </td>
                    <td style="width: 50%;">
                        <span class="label">Order Date &amp; Time</span>
                        <span class="val">{{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : now()->format('M d, Y • h:i A') }}</span>
                    </td>
                </tr>
                <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="border-right: 1px solid #e2e8f0;">
                        <span class="label">Delivery Contact</span>
                        <span class="val">📞 {{ $order->delivery_phone }}</span>
                    </td>
                    <td>
                        <span class="label">Estimated Delivery</span>
                        <span class="val" style="color: #16a34a;">⚡ 20 - 35 mins</span>
                    </td>
                </tr>
                <tr style="border-top: 1px solid #e2e8f0;">
                    <td colspan="2">
                        <span class="label">Delivery Address</span>
                        <span class="val" style="font-weight: 500; font-size: 12px; color: #334155;">📍 {{ $order->delivery_address }} ({{ $order->delivery_township ?? 'Yangon' }})</span>
                        @if($order->notes)
                            <div style="margin-top: 6px; font-size: 11px; color: #d97706; font-weight: 600;">
                                📝 Note: {{ $order->notes }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">Item Description</th>
                        <th style="text-align: center; width: 60px;">Qty</th>
                        <th style="text-align: right; width: 90px;">Price</th>
                        <th style="text-align: right; width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $subtotalCalc = 0;
                    @endphp
                    @foreach($order->orderItems as $item)
                        @php
                            $unitPrice = $item->unit_price ?? ($item->menuItem ? $item->menuItem->price : 0);
                            $itemSubtotal = $item->subtotal ?? ($unitPrice * $item->quantity);
                            $subtotalCalc += $itemSubtotal;
                        @endphp
                        <tr>
                            <td>
                                <div class="item-name">{{ $item->menuItem->name ?? 'Dish Item' }}</div>
                                @if($item->menuItem && $item->menuItem->category)
                                    <div style="font-size: 11px; color: #94a3b8;">{{ $item->menuItem->category->name }}</div>
                                @endif
                            </td>
                            <td style="text-align: center;" class="item-qty">x{{ $item->quantity }}</td>
                            <td class="price-col" style="color: #64748b;">{{ number_format($unitPrice) }} MMK</td>
                            <td class="price-col" style="color: #0f172a; font-weight: 800;">{{ number_format($itemSubtotal) }} MMK</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary Card -->
            @php
                $taxAmount = $order->tax_amount > 0 ? $order->tax_amount : round($subtotalCalc * 0.05);
            @endphp
            <div class="summary-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #64748b;">Subtotal</td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace;">{{ number_format($subtotalCalc) }} MMK</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #64748b;">Commercial Tax (5%)</td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace;">+{{ number_format($taxAmount) }} MMK</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #64748b;">Standard Delivery Fee</td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace;">+{{ number_format($order->delivery_fee) }} MMK</td>
                    </tr>
                    <tr style="border-top: 2px solid #e2e8f0;">
                        <td style="padding: 12px 0 4px 0; font-size: 15px; font-weight: 900; color: #0f172a; text-transform: uppercase;">Grand Total</td>
                        <td style="padding: 12px 0 4px 0; text-align: right; font-size: 18px; font-weight: 900; color: #D70F64; font-family: monospace;">
                            {{ number_format($order->total_amount) }} MMK
                        </td>
                    </tr>
                </table>

                <div class="payment-stamp">
                    💳 Payment Channel: 
                    <strong style="text-transform: uppercase;">
                        @if($order->payment_method === 'cod') Cash on Delivery (COD)
                        @elseif($order->payment_method === 'kbzpay') KBZPay (Online Digital Wallet)
                        @elseif($order->payment_method === 'wavepay') WavePay (Online Digital Wallet)
                        @else {{ $order->payment_method }} @endif
                    </strong>
                    &bull; 
                    <span>
                        @if($order->payment_status === 'paid')
                            <span style="color: #16a34a;">PAID &amp; VERIFIED ✓</span>
                        @elseif($order->payment_method === 'cod')
                            <span style="color: #ea580c;">PAY CASH UPON ARRIVAL 💵</span>
                        @else
                            <span style="color: #9333ea;">VERIFIED BY ADMIN ✓</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="cta-container">
                <a href="{{ route('customer.orders.show', $order) }}" class="cta-btn">
                    <span>🛵 Track Your Live Delivery Status</span>
                </a>
            </div>

            <!-- View / Print Web Payslip Link -->
            <div style="text-align: center; margin-top: 14px;">
                <a href="{{ route('orders.payslip', $order) }}" style="font-size: 12px; color: #D70F64; font-weight: 700; text-decoration: underline;">
                    🧾 View &amp; Print Official Printable Tax Invoice
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px 0; font-weight: 700; color: #64748b;">{{ config('app.name', 'Food Ordering System') }} &bull; Fast, Fresh &amp; Delicious</p>
            <p style="margin: 0 0 6px 0;">Need help with your order? Reply to this email or visit our <a href="{{ route('customer.help') }}">Help &amp; Complaints Support</a> center.</p>
            <p style="margin: 0; font-size: 10px; color: #cbd5e1;">Generated automatically upon order acceptance. Keep this receipt for your records.</p>
        </div>
    </div>
</body>
</html>
