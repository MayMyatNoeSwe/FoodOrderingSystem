<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Details #{{ $order->order_number }} — {{ config('app.name', 'FoodOrder') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-orange-500 selection:text-white" x-data="{ imgModal: false, imgSrc: '' }">

    <!-- Clear Cart on Order Complete -->
    <script>
        localStorage.removeItem('foodorder_cart');
    </script>

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

        <!-- Flash Success Notification -->
        @if (session('success'))
            <div class="mb-8 p-4 bg-green-500 text-white rounded-2xl shadow-xl shadow-green-500/20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎉</span>
                    <div>
                        <h3 class="font-black text-base">{{ session('success') }}</h3>
                        <p class="text-xs text-green-100 mt-0.5">သင်၏ Order ကို လက်ခံရရှိပါပြီ။</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header Status Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-600 rounded-full">Order #{{ $order->order_number }}</span>
                        <span class="text-xs text-slate-400 font-medium">{{ $order->created_at->format('M d, Y • h:i A') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">Order အသေးစိတ်</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Status Badge -->
                    <span class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider
                        @if($order->status === 'pending') bg-amber-100 text-amber-700 border border-amber-200
                        @elseif($order->status === 'confirmed') bg-blue-100 text-blue-700 border border-blue-200
                        @elseif($order->status === 'completed') bg-green-100 text-green-700 border border-green-200
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-700 border border-red-200
                        @else bg-slate-100 text-slate-700 @endif">
                        Order Status: {{ strtoupper($order->status) }}
                    </span>

                    <!-- Payment Status Badge -->
                    <span class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider
                        @if($order->payment_status === 'paid') bg-green-100 text-green-700 border border-green-200
                        @elseif($order->payment_status === 'pending_verification') bg-purple-100 text-purple-700 border border-purple-200
                        @else bg-orange-100 text-orange-700 border border-orange-200 @endif">
                        Payment: {{ str_replace('_', ' ', strtoupper($order->payment_status)) }}
                    </span>
                </div>
            </div>

            <!-- Grid Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <!-- Delivery Info -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">📍 Delivery အချက်အလက်</p>
                    <p class="font-bold text-slate-800">{{ $order->delivery_township ?? 'Yangon' }}</p>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $order->delivery_address }}</p>
                    <p class="text-xs font-bold text-slate-800 mt-2">📞 {{ $order->delivery_phone }}</p>
                </div>

                <!-- Payment Method Info -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">💳 ငွေပေးချေမှု</p>
                    <p class="font-black text-slate-900 uppercase text-base mb-1">
                        @if($order->payment_method === 'cod') 💵 Cash on Delivery
                        @elseif($order->payment_method === 'kbzpay') 📱 KBZPay
                        @elseif($order->payment_method === 'wavepay') 🌊 WavePay
                        @else {{ $order->payment_method }} @endif
                    </p>
                    @if($order->payment_screenshot)
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs text-slate-500 font-medium">Screenshot:</span>
                            <button @click="imgModal = true; imgSrc = '{{ asset($order->payment_screenshot) }}'"
                                    class="text-xs font-bold text-orange-500 underline hover:text-orange-600 cursor-pointer">
                                ကြည့်မည် 🔍
                            </button>
                        </div>
                    @else
                        <p class="text-xs text-slate-500">ပစ္စည်းရောက်မှ ငွေချေပါမည်</p>
                    @endif
                </div>

                <!-- Notes -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">📝 မှတ်ချက်</p>
                    <p class="text-xs text-slate-700 leading-relaxed italic">
                        {{ $order->notes ? '"'.$order->notes.'"' : 'မရှိပါ' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Items Table Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 mb-8">
            <h2 class="text-lg font-black text-slate-900 mb-6">မှာယူထားသော အစားအစာများ</h2>

            <div class="divide-y divide-slate-100">
                @foreach ($order->orderItems as $item)
                    <div class="py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-slate-100 rounded-xl overflow-hidden shrink-0">
                                <img src="{{ $item->menuItem?->image_url ?? asset('images/hero_food.png') }}"
                                     alt="{{ $item->menuItem?->name }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">{{ $item->menuItem?->name ?? 'Menu Item' }}</h3>
                                <p class="text-xs text-slate-400 font-medium mt-0.5">
                                    {{ number_format($item->unit_price) }} MMK &times; {{ $item->quantity }} ခု
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="font-black text-slate-900 text-sm">{{ number_format($item->subtotal) }} MMK</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cost Summary Footer -->
            <div class="border-t border-slate-100 pt-6 mt-6 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-900">{{ number_format($order->total_amount - $order->delivery_fee) }} MMK</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Delivery Fee</span>
                    <span class="font-bold text-slate-900">{{ number_format($order->delivery_fee) }} MMK</span>
                </div>
                <div class="border-t border-slate-100 pt-3 flex justify-between items-center">
                    <span class="font-black text-slate-900 text-base">စုစုပေါင်း ကျသင့်ငွေ</span>
                    <span class="font-black text-orange-500 text-xl">{{ number_format($order->total_amount) }} MMK</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="w-full sm:w-auto px-8 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-black text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all text-center">
                ← မနူးစာမျက်နှာသို့ ပြန်သွားမည်
            </a>
        </div>

    </main>

    <!-- Screenshot Modal -->
    <div x-show="imgModal" x-transition class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-3xl p-4 max-w-lg w-full relative shadow-2xl" @click.outside="imgModal = false">
            <button @click="imgModal = false" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 flex items-center justify-center">✕</button>
            <p class="font-bold text-slate-900 text-sm mb-3">ငွေချေထားသော Screenshot</p>
            <img :src="imgSrc" alt="Payment Screenshot" class="w-full h-auto rounded-2xl border border-slate-100 max-h-[70vh] object-contain">
        </div>
    </div>

</body>
</html>
