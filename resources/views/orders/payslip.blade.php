<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Foodpanda Payslip & Receipt - #{{ $order->order_number }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .printable-slip {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                max-width: 100% !important;
                margin: 0 auto !important;
                border-radius: 0 !important;
            }
        }
        .barcode {
            background: repeating-linear-gradient(
                90deg,
                #000,
                #000 2px,
                #fff 2px,
                #fff 4px,
                #000 4px,
                #000 7px,
                #fff 7px,
                #fff 9px
            );
            height: 36px;
            width: 180px;
        }
        .digital-rubber-stamp {
            border: 3px dashed currentColor;
            padding: 6px 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 12px;
            display: inline-block;
            transform: rotate(-3deg);
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-100 min-h-screen py-6 px-3 sm:px-6 selection:bg-[#D70F64] selection:text-white"
      x-data="{ viewType: 'customer' }">

    <!-- Top Action Bar (No-Print) -->
    <div class="max-w-xl mx-auto mb-5 no-print flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-2">
            @if(Auth::check() && Auth::user()->isAdmin())
                <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1">
                    <span>&larr; Admin Orders</span>
                </a>
            @elseif(Auth::check() && Auth::user()->isRider())
                <a href="{{ route('rider.dashboard') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1">
                    <span>&larr; Rider Dashboard</span>
                </a>
            @else
                <a href="{{ route('customer.orders.show', $order) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1">
                    <span>&larr; Back to Order</span>
                </a>
            @endif

            <!-- Tab Switcher -->
            <div class="flex bg-slate-100 p-1 rounded-xl text-xs font-bold">
                <button type="button" @click="viewType = 'customer'" 
                        :class="viewType === 'customer' ? 'bg-[#D70F64] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                    🧾 Customer Receipt
                </button>
                <button type="button" @click="viewType = 'rider'" 
                        :class="viewType === 'rider' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                    🛵 Rider Delivery Slip
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button onclick="window.print()" class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-[#D70F64] to-[#E21B70] hover:from-[#c20d5a] hover:to-[#cb1864] text-white font-black text-xs rounded-xl shadow-md shadow-[#D70F64]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Slip Card -->
    <div class="printable-slip max-w-xl mx-auto bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xl">
        
        <!-- ================= CUSTOMER RECEIPT VIEW ================= -->
        <div x-show="viewType === 'customer'" class="divide-y divide-slate-100">
            
            <!-- Foodpanda Header -->
            <div class="bg-gradient-to-br from-[#D70F64] via-[#E21B70] to-[#FF2B85] p-6 sm:p-8 text-white text-center relative overflow-hidden">
                <div class="relative z-10 space-y-2">
                    <div class="inline-flex items-center gap-2 text-2xl font-black tracking-tight">
                        <span class="text-3xl">🐼</span>
                        <span>foodpanda</span>
                    </div>
                    <p class="text-xs font-semibold text-white/90 uppercase tracking-widest">Official Food Delivery Receipt &amp; Tax Invoice</p>
                    
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-2">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[11px] font-black tracking-wider uppercase border border-white/30">
                            ✓ CONFIRMED &amp; ACCEPTED
                        </span>

                        @if($order->payment_status === 'paid')
                            @if($order->payment_method === 'cod')
                                <span class="px-3 py-1 bg-emerald-400 text-emerald-950 rounded-full text-[11px] font-black tracking-wider uppercase shadow-md flex items-center gap-1">
                                    <span>💰</span> <span>PAID (CASH) • RIDER CONFIRMED</span>
                                </span>
                            @else
                                <span class="px-3 py-1 bg-emerald-400 text-emerald-950 rounded-full text-[11px] font-black tracking-wider uppercase shadow-md flex items-center gap-1">
                                    <span>💰</span> <span>PAID • ONLINE PREPAID</span>
                                </span>
                            @endif
                        @elseif($order->payment_method === 'cod')
                            <span class="px-3 py-1 bg-amber-400 text-amber-950 rounded-full text-[11px] font-black tracking-wider uppercase shadow-sm flex items-center gap-1">
                                <span>💵</span> <span>PAY ON DELIVERY (UNPAID)</span>
                            </span>
                        @else
                            <span class="px-3 py-1 bg-purple-300 text-purple-950 rounded-full text-[11px] font-black tracking-wider uppercase shadow-sm flex items-center gap-1">
                                <span>⏳</span> <span>PAYMENT PENDING VERIFICATION</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Authentic Rubber Stamp Area -->
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-center">
                @if($order->payment_status === 'paid')
                    @if($order->payment_method === 'cod')
                        <div class="digital-rubber-stamp text-emerald-700 border-emerald-600 bg-emerald-100/70 text-center shadow-xs">
                            <div>★ PAID (CASH) — OFFICIAL RECEIPT ★</div>
                            <div class="text-[10px] font-bold tracking-normal text-emerald-800 mt-0.5">
                                ငွေသားလက်ခံရရှိပြီး &bull; Rider Confirmed: {{ $order->updated_at ? $order->updated_at->format('M d, Y • h:i A') : '' }}
                            </div>
                        </div>
                    @else
                        <div class="digital-rubber-stamp text-emerald-700 border-emerald-600 bg-emerald-100/70 text-center shadow-xs">
                            <div>★ PAID — ONLINE VERIFIED SLIP ★</div>
                            <div class="text-[10px] font-bold tracking-normal text-emerald-800 mt-0.5">
                                အွန်လိုင်းငွေပေးချေပြီး &bull; Verified: {{ $order->updated_at ? $order->updated_at->format('M d, Y • h:i A') : '' }}
                            </div>
                        </div>
                    @endif
                @elseif($order->payment_method === 'cod')
                    <div class="digital-rubber-stamp text-amber-800 border-amber-600 bg-amber-100/70 text-center shadow-xs">
                        <div>★ CASH ON DELIVERY — PENDING PAYMENT ★</div>
                        <div class="text-[10px] font-bold tracking-normal text-amber-900 mt-0.5">
                            အစားအသောက်ရောက်ရှိချိန်တွင် Rider ထံ ငွေသား {{ number_format($order->total_amount) }} MMK ပေးချေရန်
                        </div>
                    </div>
                @else
                    <div class="digital-rubber-stamp text-purple-800 border-purple-600 bg-purple-100/70 text-center shadow-xs">
                        <div>★ PENDING SLIP VERIFICATION ★</div>
                        <div class="text-[10px] font-bold tracking-normal text-purple-900 mt-0.5">
                            Customer ငွေလွှဲပြေစာအား Admin မှ စစ်ဆေးဆဲ ဖြစ်ပါသည်
                        </div>
                    </div>
                @endif
            </div>

            <!-- Receipt Meta Info Box -->
            <div class="p-6 bg-white grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Order ID</span>
                    <span class="font-black text-[#D70F64] font-mono text-sm">#{{ $order->order_number }}</span>
                    <span class="text-slate-500 block text-[11px] mt-0.5">{{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : now()->format('M d, Y • h:i A') }}</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Customer</span>
                    <span class="font-black text-slate-900 text-sm">{{ $order->user->name ?? 'Customer' }}</span>
                    <span class="text-slate-600 block text-[11px] font-mono mt-0.5">📞 {{ $order->delivery_phone }}</span>
                </div>
                <div class="col-span-2 pt-2 border-t border-slate-200">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Delivery Destination</span>
                    <span class="font-medium text-slate-800 text-xs leading-relaxed block">
                        📍 {{ $order->delivery_address }} ({{ $order->delivery_township ?? 'Yangon' }})
                    </span>
                    @if($order->notes)
                        <div class="mt-1.5 text-[11px] font-semibold text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200">
                            📝 Special Note: {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Itemized Table -->
            <div class="p-6">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Order Items Breakdown</div>
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b-2 border-slate-100 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="pb-2">Dish</th>
                            <th class="pb-2 text-center">Qty</th>
                            <th class="pb-2 text-right">Price</th>
                            <th class="pb-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @php $calcSubtotal = 0; @endphp
                        @foreach($order->orderItems as $item)
                            @php
                                $unitPrice = $item->unit_price ?? ($item->menuItem ? $item->menuItem->price : 0);
                                $sub = $item->subtotal ?? ($unitPrice * $item->quantity);
                                $calcSubtotal += $sub;
                            @endphp
                            <tr>
                                <td class="py-3">
                                    <div class="font-bold text-slate-900">{{ $item->menuItem->name ?? 'Dish Item' }}</div>
                                    @if($item->menuItem && $item->menuItem->category)
                                        <div class="text-[10px] text-slate-400">{{ $item->menuItem->category->name }}</div>
                                    @endif
                                </td>
                                <td class="py-3 text-center font-black text-[#D70F64]">x{{ $item->quantity }}</td>
                                <td class="py-3 text-right font-mono text-slate-600">{{ number_format($unitPrice) }} MMK</td>
                                <td class="py-3 text-right font-bold text-slate-900 font-mono">{{ number_format($sub) }} MMK</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary Box -->
            @php
                $taxVal = $order->tax_amount > 0 ? $order->tax_amount : round($calcSubtotal * 0.05);
            @endphp
            <div class="p-6 bg-slate-50/80 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Items Subtotal</span>
                    <span class="font-bold text-slate-900 font-mono">{{ number_format($calcSubtotal) }} MMK</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span class="flex items-center gap-1">
                        <span>Commercial Tax (5%)</span>
                        <span class="text-[9px] px-1 py-0.2 bg-slate-200 text-slate-700 rounded font-bold uppercase">Tax</span>
                    </span>
                    <span class="font-bold text-slate-900 font-mono">+{{ number_format($taxVal) }} MMK</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Delivery Fee</span>
                    <span class="font-bold text-slate-900 font-mono">+{{ number_format($order->delivery_fee) }} MMK</span>
                </div>
                <div class="border-t-2 border-slate-200 pt-3 flex justify-between items-center">
                    <span class="font-black text-slate-900 text-sm uppercase tracking-wider">Grand Total</span>
                    <span class="font-black text-xl text-[#D70F64] font-mono">{{ number_format($order->total_amount) }} MMK</span>
                </div>

                <div class="mt-4 p-3 bg-white border border-slate-200 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Payment Method</span>
                        <span class="font-black text-slate-800 uppercase text-xs">
                            @if($order->payment_method === 'cod') 💵 Cash on Delivery (COD)
                            @elseif($order->payment_method === 'kbzpay') 📱 KBZPay Online
                            @elseif($order->payment_method === 'wavepay') 🌊 WavePay Online
                            @else {{ $order->payment_method }} @endif
                        </span>
                    </div>
                    <div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($order->payment_method === 'cod' ? 'bg-amber-100 text-amber-800' : 'bg-purple-100 text-purple-800') }}">
                            @if($order->payment_status === 'paid')
                                @if($order->payment_method === 'cod')
                                    ✓ PAID (CASH)
                                @else
                                    ✓ PAID (ONLINE)
                                @endif
                            @elseif($order->payment_method === 'cod')
                                💵 PAY ON DELIVERY (UNPAID)
                            @else
                                ⏳ PENDING VERIFICATION
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Receipt Footer & Barcode Simulation -->
            <div class="p-6 text-center space-y-3 bg-white">
                <div class="flex flex-col items-center justify-center">
                    <div class="barcode"></div>
                    <span class="text-[10px] font-mono text-slate-400 tracking-widest mt-1">*{{ $order->order_number }}*</span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium">Thank you for dining with us! For any customer support, please contact our helpline.</p>
                <div class="text-[10px] text-slate-400">Printed on {{ now()->format('Y-m-d H:i:s') }} &bull; Foodpanda Digital Slip System</div>
            </div>

        </div>

        <!-- ================= RIDER DISPATCH SLIP VIEW ================= -->
        <div x-show="viewType === 'rider'" class="divide-y divide-slate-100" style="display: none;">
            
            <!-- Rider Header -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 sm:p-8 text-white text-center border-b-4 border-[#D70F64]">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 text-xl font-black">
                        <span>🐼</span>
                        <span>food<span class="text-[#D70F64]">panda</span></span>
                        <span class="text-xs px-2 py-0.5 bg-[#D70F64] rounded text-white font-bold ml-1 uppercase">Rider Slip</span>
                    </div>
                    <p class="text-xs font-semibold text-slate-300">Delivery Dispatch &amp; Cash Collection Manifest</p>
                    <div class="font-mono text-sm font-black text-amber-400">Order #{{ $order->order_number }}</div>
                </div>
            </div>

            <!-- Earning & Collection Status -->
            <div class="p-6 bg-slate-50 space-y-3">
                <div class="p-3.5 bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-200 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase text-pink-700 tracking-wider block">Rider Delivery Fee Payout</span>
                        <span class="font-bold text-slate-800 text-xs">Rider: {{ $order->rider->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="text-lg font-black text-[#D70F64] font-mono">
                        +{{ number_format($order->delivery_fee) }} MMK
                    </div>
                </div>

                @if($order->payment_method === 'cod')
                    <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-2xl text-center">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 block">💵 Cash to Collect From Customer</span>
                        <div class="text-2xl font-black text-amber-700 font-mono my-1">{{ number_format($order->total_amount) }} MMK</div>
                        <span class="text-xs font-bold text-amber-800">⚠️ Collect exact cash from customer before handing over order.</span>
                    </div>
                @else
                    <div class="p-4 bg-emerald-50 border-2 border-emerald-300 rounded-2xl text-center">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 block">✅ Online Prepaid ({{ strtoupper($order->payment_method) }})</span>
                        <div class="text-2xl font-black text-emerald-600 font-mono my-1">0 MMK to Collect</div>
                        <span class="text-xs font-bold text-emerald-800">✓ Customer has already paid online. Do NOT collect cash!</span>
                    </div>
                @endif
            </div>

            <!-- Delivery Drop-off Details -->
            <div class="p-6 space-y-3 text-xs">
                <div class="font-black text-slate-900 text-xs uppercase tracking-wider">📍 Drop-off Location</div>
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-1.5">
                    <div class="font-black text-slate-900 text-sm">{{ $order->user->name ?? 'Customer' }}</div>
                    <div class="text-[#D70F64] font-black uppercase text-xs">Township: {{ $order->delivery_township ?? 'Yangon Region' }}</div>
                    <div class="text-slate-700 font-medium leading-relaxed">{{ $order->delivery_address }}</div>
                    <div class="pt-1">
                        <a href="tel:{{ $order->delivery_phone }}" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 text-white font-bold rounded-lg text-xs hover:bg-emerald-700 transition-colors">
                            <span>📞 Call Customer: {{ $order->delivery_phone }}</span>
                        </a>
                    </div>
                    @if($order->notes)
                        <div class="mt-2 text-xs font-semibold text-amber-800 bg-amber-50 p-2 rounded-lg border border-amber-200">
                            📝 Note: {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Verification Checklist -->
            <div class="p-6">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">🍽️ Kitchen Pick-up Verification ({{ $order->orderItems->sum('quantity') }} items)</div>
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden">
                    @foreach($order->orderItems as $item)
                        <div class="p-3 flex items-center justify-between text-xs hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded border border-slate-300 flex items-center justify-center text-[10px] text-emerald-600 font-bold">✓</span>
                                <span class="font-bold text-slate-900">{{ $item->menuItem->name ?? 'Dish' }}</span>
                            </div>
                            <span class="font-black text-[#D70F64] font-mono">x{{ $item->quantity }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 text-center bg-slate-50 text-[11px] text-slate-500">
                <span>Please ride safely. Remember to upload delivery proof photo upon completion! 📸</span>
            </div>

        </div>

    </div>

</body>
</html>
