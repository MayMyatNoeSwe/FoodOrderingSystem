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
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-orange-500 selection:text-white">

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
                <div class="flex items-center gap-4">
                    <a href="/" class="text-sm font-semibold text-slate-600 hover:text-orange-500 transition-colors">&larr; Back to Menu</a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900">📦 ကျွန်ုပ်၏ Order များ (My Orders)</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">သင်မှာယူခဲ့သော Order များ၏ အခြေအနေများကို ကြည့်ရှုနိုင်ပါသည်။</p>
            </div>
            <a href="/" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                + Order အသစ်တင်မည်
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-100 p-12 text-center shadow-sm">
                <div class="text-5xl mb-4">📦</div>
                <h3 class="text-xl font-black text-slate-900 mb-2">Order မရှိသေးပါ</h3>
                <p class="text-slate-500 text-sm mb-6">သင်မှာယူထားသော အစားအစာ Order များ မရှိသေးပါ။</p>
                <a href="/" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all inline-block">
                    မနူးကြည့်မည်
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-900 text-base">Order #{{ $order->order_number }}</span>
                                <span class="text-xs text-slate-400">• {{ $order->created_at->format('M d, Y • h:i A') }}</span>
                            </div>

                            <p class="text-xs text-slate-500 font-medium">
                                📍 {{ $order->delivery_township ?? 'Yangon' }} — {{ Str::limit($order->delivery_address, 40) }}
                            </p>

                            <div class="flex items-center gap-2 pt-1">
                                <!-- Order Status -->
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider
                                    @if($order->status === 'pending') bg-amber-100 text-amber-700
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-700
                                    @elseif($order->status === 'completed') bg-green-100 text-green-700
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ strtoupper($order->status) }}
                                </span>

                                <!-- Payment Status -->
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider
                                    @if($order->payment_status === 'paid') bg-green-100 text-green-700
                                    @elseif($order->payment_status === 'pending_verification') bg-purple-100 text-purple-700
                                    @else bg-orange-100 text-orange-700 @endif">
                                    {{ str_replace('_', ' ', strtoupper($order->payment_status)) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex sm:flex-col items-end justify-between sm:justify-center border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                            <span class="font-black text-orange-500 text-lg">{{ number_format($order->total_amount) }} MMK</span>
                            <a href="{{ route('user.orders.show', $order) }}"
                               class="mt-1 px-4 py-2 bg-slate-100 hover:bg-orange-500 hover:text-white font-bold text-xs rounded-xl text-slate-700 transition-all">
                                အသေးစိတ်ကြည့်မည် &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </main>

</body>
</html>
