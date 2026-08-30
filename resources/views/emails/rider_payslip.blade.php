<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dispatch & Delivery Slip #{{ $order->order_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0f172a;
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
            border: 1px solid #334155;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 28px 24px;
            color: #ffffff;
            border-bottom: 4px solid #D70F64;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .brand {
            font-size: 20px;
            font-weight: 900;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .brand span.panda {
            color: #D70F64;
        }
        .rider-badge {
            background: #D70F64;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 24px;
        }
        .earnings-card {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            border: 2px solid #fbcfe8;
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .earnings-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #9d174d;
            letter-spacing: 0.5px;
        }
        .earnings-amount {
            font-size: 22px;
            font-weight: 900;
            color: #D70F64;
            font-family: monospace;
        }
        .destination-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .dest-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }
        .customer-name {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .customer-phone {
            display: inline-block;
            background: #16a34a;
            color: #ffffff !important;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            margin: 6px 0 10px 0;
        }
        .address-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            line-height: 1.4;
        }
        .collection-box {
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 20px;
            text-align: center;
        }
        .collection-box.cod {
            background: #fffbeb;
            border: 2px solid #fde047;
            color: #854d0e;
        }
        .collection-box.paid {
            background: #f0fdf4;
            border: 2px solid #86efac;
            color: #166534;
        }
        .collection-amount {
            font-size: 24px;
            font-weight: 900;
            font-family: monospace;
            margin: 6px 0;
        }
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .checklist-table th {
            background: #f1f5f9;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            color: #475569;
            text-align: left;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .checklist-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .cta-btn {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #D70F64 0%, #E21B70 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 20px;
            border-radius: 14px;
            font-weight: 900;
            font-size: 14px;
            box-shadow: 0 4px 14px rgba(215, 15, 100, 0.35);
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 18px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="brand">
                    <span>🐼</span>
                    <span>Food<span class="panda">Order</span></span>
                    <span style="font-size: 14px; font-weight: 600; color: #94a3b8; margin-left: 4px;">Rider Dispatch</span>
                </div>
                <div class="rider-badge">Delivery Slip</div>
            </div>
            <div style="font-size: 14px; color: #e2e8f0; margin-top: 4px;">
                Order #<strong style="color: #ffffff; font-family: monospace;">{{ $order->order_number }}</strong>
                &bull; {{ $order->created_at ? $order->created_at->format('h:i A') : now()->format('h:i A') }}
            </div>
        </div>

        <div class="content">
            <!-- Rider Earning Card -->
            <div class="earnings-card">
                <div>
                    <div class="earnings-label">🛵 Your Delivery Fee Earning</div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 600;">Assigned to: <strong>{{ $rider->name ?? ($order->rider->name ?? 'Rider') }}</strong></div>
                </div>
                <div class="earnings-amount">
                    +{{ number_format($order->delivery_fee) }} MMK
                </div>
            </div>

            <!-- Payment Collection Banner for Rider -->
            @if($order->payment_method === 'cod')
                <div class="collection-box cod">
                    <div style="font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">
                        💵 Cash on Delivery (COD) Collection Required
                    </div>
                    <div class="collection-amount" style="color: #b45309;">
                        {{ number_format($order->total_amount) }} MMK
                    </div>
                    <div style="font-size: 12px; font-weight: 700;">
                        ⚠️ Please collect this exact amount from the customer upon delivery.
                    </div>
                </div>
            @else
                <div class="collection-box paid">
                    <div style="font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">
                        ✅ Prepaid Online ({{ strtoupper($order->payment_method) }})
                    </div>
                    <div class="collection-amount" style="color: #16a34a;">
                        0 MMK (Already Paid)
                    </div>
                    <div style="font-size: 12px; font-weight: 700;">
                        ✓ Customer has paid online. Do NOT collect food cash from customer!
                    </div>
                </div>
            @endif

            <!-- Customer Destination & Phone Box -->
            <div class="destination-card">
                <span class="dest-title">📍 Customer Delivery Details</span>
                <div class="customer-name">{{ $order->user->name ?? 'Customer' }}</div>
                
                @if($order->delivery_phone)
                    <div>
                        <a href="tel:{{ $order->delivery_phone }}" class="customer-phone">
                            📞 Call Customer: {{ $order->delivery_phone }}
                        </a>
                    </div>
                @endif

                <div class="address-box">
                    <div style="color: #D70F64; font-weight: 800; font-size: 11px; text-transform: uppercase; margin-bottom: 2px;">
                        Township: {{ $order->delivery_township ?? 'Yangon Region' }}
                    </div>
                    <div>{{ $order->delivery_address }}</div>
                    @if($order->notes)
                        <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #cbd5e1; color: #b45309; font-weight: 700; font-size: 12px;">
                            📝 Note from Customer: {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kitchen Dish Checklist -->
            <div style="margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #475569; letter-spacing: 0.5px;">
                    🍽️ Kitchen Dish Verification Checklist ({{ $order->orderItems->sum('quantity') }} items)
                </span>
            </div>
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th>Dish / Item</th>
                        <th style="text-align: center; width: 60px;">Qty</th>
                        <th style="text-align: right; width: 80px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->orderItems as $item)
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;">
                                {{ $item->menuItem->name ?? 'Dish Item' }}
                            </td>
                            <td style="text-align: center; font-weight: 900; color: #D70F64;">
                                x{{ $item->quantity }}
                            </td>
                            <td style="text-align: right; font-size: 11px; font-weight: 700; color: #16a34a;">
                                [ ✓ Prepared ]
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Rider Portal CTA Button -->
            <div style="margin-top: 24px;">
                <a href="{{ route('rider.dashboard') }}" class="cta-btn">
                    <span>🛵 Open Rider Dashboard &amp; Complete Delivery</span>
                </a>
            </div>

            <!-- View / Print Web Payslip Link -->
            <div style="text-align: center; margin-top: 14px;">
                <a href="{{ route('orders.payslip', $order) }}" style="font-size: 12px; color: #D70F64; font-weight: 700; text-decoration: underline;">
                    🧾 View &amp; Print Full FoodOrder Delivery Slip
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 4px 0; font-weight: 700;">FoodOrder Rider Delivery Fleet</p>
            <p style="margin: 0; font-size: 10px;">Please ride safely and remember to take a delivery proof photo upon handover.</p>
        </div>
    </div>
</body>
</html>
