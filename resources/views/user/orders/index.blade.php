<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Orders — {{ config('app.name', 'FoodOrder') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-orange-500 selection:text-white min-h-screen">

    <!-- ===== NAVBAR ===== -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="text-xl font-black tracking-tight text-slate-900">Food<span class="text-orange-500">Order</span></span>
                </a>
                <div class="flex items-center gap-3">
                    <a href="/" class="text-sm font-semibold text-slate-600 hover:text-orange-500 transition-colors">&larr; Back to Menu</a>
                    <a href="/"
                       class="px-4 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-md shadow-orange-500/25 transition-all">
                        + Order တင်မည်
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 flex items-center gap-3">
                <span class="text-3xl">📦</span>
                ကျွန်ုပ်၏ Order များ
                <span class="text-base font-bold text-slate-400 ml-1">({{ $orders->count() }} ခု)</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1.5">သင်မှာယူခဲ့သော Order များ၏ အခြေအနေများကို ကြည့်ရှုနိုင်ပါသည်။</p>
        </div>

        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl px-5 py-4 shadow-sm">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($orders->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-3xl border border-slate-100 p-16 text-center shadow-sm">
                <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center text-5xl mb-6 mx-auto shadow-inner">📦</div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">Order မရှိသေးပါ</h3>
                <p class="text-slate-500 text-sm mb-8 max-w-sm mx-auto">သင်မှာယူထားသော အစားအစာ Order များ မရှိသေးပါ။ မနူးကြည့်ပြီး မှာယူလိုက်ပါ!</p>
                <a href="/" class="px-8 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all inline-block">
                    မနူးကြည့်မည်
                </a>
            </div>
        @else
            <div class="space-y-5">
                @foreach($orders as $order)
                    @php
                        $statusConfig = match($order->status) {
                            'pending'    => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'icon' => '⏳', 'label' => 'Pending'],
                            'confirmed'  => ['bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'icon' => '✓',  'label' => 'Confirmed'],
                            'preparing'  => ['bg' => 'bg-indigo-100',  'text' => 'text-indigo-700',  'icon' => '👨‍🍳', 'label' => 'Preparing'],
                            'delivering' => ['bg' => 'bg-purple-100',  'text' => 'text-purple-700',  'icon' => '🛵', 'label' => 'Delivering'],
                            'completed'  => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => '✅', 'label' => 'Completed'],
                            'cancelled'  => ['bg' => 'bg-red-100',     'text' => 'text-red-700',     'icon' => '❌', 'label' => 'Cancelled'],
                            default      => ['bg' => 'bg-slate-100',   'text' => 'text-slate-700',   'icon' => '📦', 'label' => ucfirst($order->status)],
                        };
                        $paymentConfig = match($order->payment_status ?? 'unpaid') {
                            'paid'                 => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'label' => '✓ Paid'],
                            'pending_verification' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => '🔍 Verifying'],
                            default                => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => '⏳ Unpaid'],
                        };
                        $isActive = in_array($order->status, ['pending', 'confirmed', 'preparing', 'delivering']);
                    @endphp

                    <div class="bg-white rounded-2xl border {{ $isActive ? 'border-orange-200' : 'border-slate-100' }} shadow-sm hover:shadow-md transition-all overflow-hidden">

                        <!-- Card Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 {{ $isActive ? 'bg-orange-50/60' : 'bg-slate-50/60' }} border-b border-slate-100">
                            <div class="flex items-center gap-3 flex-wrap">
                                <!-- Order Number -->
                                <span class="font-mono font-black text-slate-900 text-base tracking-tight">#{{ $order->order_number }}</span>

                                <!-- Live pulse for active orders -->
                                @if($isActive)
                                    <span class="flex items-center gap-1.5 text-xs font-bold text-orange-600">
                                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse inline-block"></span>
                                        Live Tracking
                                    </span>
                                @endif

                                <!-- Status Badge -->
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ $statusConfig['icon'] }} {{ $statusConfig['label'] }}
                                </span>

                                <!-- Payment Badge -->
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase {{ $paymentConfig['bg'] }} {{ $paymentConfig['text'] }}">
                                    {{ $paymentConfig['label'] }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400 font-medium">{{ $order->created_at->format('M d, Y • h:i A') }}</span>
                                <a href="{{ route('user.orders.show', $order) }}"
                                   class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-sm shadow-orange-500/20 transition-all whitespace-nowrap">
                                    အသေးစိတ် &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="px-5 py-4">
                            <div class="flex flex-col lg:flex-row gap-5">

                                <!-- Left: Items Ordered -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">မှာယူသောပစ္စည်းများ</p>
                                    <div class="space-y-2">
                                        @forelse($order->orderItems as $item)
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    @if($item->menuItem && $item->menuItem->image)
                                                        <img src="{{ asset($item->menuItem->image) }}"
                                                             alt="{{ $item->menuItem->name ?? 'Item' }}"
                                                             class="w-9 h-9 rounded-lg object-cover shrink-0 bg-slate-100">
                                                    @else
                                                        <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center text-xl shrink-0">🍽️</div>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-bold text-slate-900 truncate">
                                                            {{ $item->menuItem->name ?? 'Item (Removed)' }}
                                                        </p>
                                                        <p class="text-xs text-slate-400">
                                                            {{ number_format($item->unit_price) }} MMK × {{ $item->quantity }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-black text-slate-900 shrink-0">
                                                    {{ number_format($item->subtotal) }} <span class="text-xs text-orange-500 font-bold">MMK</span>
                                                </span>
                                            </div>
                                        @empty
                                            <p class="text-xs text-slate-400 italic">ပစ္စည်းအချက်အလက် မရှိပါ</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div class="hidden lg:block w-px bg-slate-100 self-stretch"></div>
                                <div class="block lg:hidden h-px bg-slate-100 w-full"></div>

                                <!-- Right: Delivery + Summary -->
                                <div class="lg:w-56 shrink-0 space-y-4">
                                    <!-- Delivery Info -->
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Delivery အချက်အလက်</p>
                                        <p class="text-xs font-semibold text-slate-700 flex items-start gap-1.5">
                                            <span>📍</span>
                                            <span class="leading-relaxed">{{ $order->delivery_township ?? 'Yangon' }}@if($order->delivery_address) — {{ Str::limit($order->delivery_address, 45) }}@endif</span>
                                        </p>
                                        <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-1">
                                            <span>📞</span>
                                            <span>{{ $order->delivery_phone }}</span>
                                        </p>
                                    </div>

                                    <!-- Price Breakdown -->
                                    <div class="bg-slate-50 rounded-xl p-3 space-y-1.5">
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <span>ပစ္စည်းစုစုပေါင်း</span>
                                            <span>{{ number_format($order->total_amount - ($order->delivery_fee ?? 0)) }} MMK</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <span>Delivery ဖိ</span>
                                            <span>{{ number_format($order->delivery_fee ?? 0) }} MMK</span>
                                        </div>
                                        <div class="border-t border-slate-200 pt-1.5 flex justify-between">
                                            <span class="text-xs font-black text-slate-900">ပေးရမည့်ငွေ</span>
                                            <span class="text-sm font-black text-orange-500">{{ number_format($order->total_amount) }} MMK</span>
                                        </div>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="flex items-center gap-2 text-xs text-slate-500 font-semibold">
                                        <span>
                                            @if($order->payment_method === 'cod') 💵
                                            @elseif($order->payment_method === 'kbzpay') 📱
                                            @else 🌊
                                            @endif
                                        </span>
                                        <span>{{ strtoupper($order->payment_method) }}</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Card Footer: Time ago -->
                        <div class="px-5 py-2.5 border-t border-slate-50 flex justify-between items-center">
                            <span class="text-[11px] text-slate-400 font-medium">{{ $order->created_at->diffForHumans() }}</span>
                            <a href="{{ route('user.orders.show', $order) }}"
                               class="text-xs font-bold text-orange-500 hover:text-orange-600 transition-colors">
                                Order Tracking ကြည့်မည် &rarr;
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </main>

</body>
</html>
