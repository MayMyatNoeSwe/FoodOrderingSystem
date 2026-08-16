<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Details #{{ $order->order_number }} — {{ config('app.name', 'FoodOrder') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <!-- Theme Initialization (Prevents FOUC) -->
    <script>
        if (localStorage.getItem('foodorder_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Order Tracker JS -->
    <script>
        window.initOrderTracker = function(initialStatus, initialPaymentStatus, initialNotes, jsonUrl) {
            return {
                imgModal: false,
                imgSrc: '',
                currentStatus: initialStatus,
                currentPaymentStatus: initialPaymentStatus,
                currentNotes: initialNotes,
                justApproved: false,
                darkMode: localStorage.getItem('foodorder_theme') === 'dark',
                toggleTheme: function() {
                    this.darkMode = !this.darkMode;
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('foodorder_theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('foodorder_theme', 'light');
                    }
                },
                init: function() {
                    var self = this;
                    localStorage.removeItem('foodorder_cart');
                    setInterval(function() {
                        fetch(jsonUrl)
                            .then(function(res) { return res.json(); })
                            .then(function(data) {
                                if (data.status) {
                                    if ((data.status === 'confirmed' || data.status === 'preparing') && self.currentStatus === 'pending') {
                                        self.justApproved = true;
                                    }
                                    self.currentStatus = data.status;
                                    self.currentPaymentStatus = data.payment_status;
                                    if (data.notes !== undefined) {
                                        self.currentNotes = data.notes;
                                    }
                                }
                            })
                            .catch(function() {});
                    }, 3000);
                }
            };
        };
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 selection:bg-orange-500 selection:text-white"
    x-data="initOrderTracker({{ json_encode($order->status) }}, {{ json_encode($order->payment_status) }}, {{ json_encode($order->notes ?? '') }}, '{{ route('user.orders.json_status', $order) }}')">

    <!-- ===== NAVBAR ===== -->
    <x-storefront-navbar />

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- ===== LIVE NOTIFICATION BANNER (State Dependent) ===== -->
        <!-- 1. PENDING STATE BANNER -->
        <div x-show="currentStatus === 'pending'" x-transition class="mb-8 p-5 bg-amber-500 text-white rounded-3xl shadow-xl shadow-amber-500/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-amber-400">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-2xl shrink-0 animate-bounce">
                    ⏳
                </div>
                <div>
                    <h3 class="font-black text-base">Notification sent to Admin.</h3>
                    <p class="text-xs text-amber-100 mt-0.5 leading-relaxed">Your order status will update automatically here as soon as the Admin approves it.</p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-xl text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                <span>Live Checking...</span>
            </div>
        </div>

        <!-- 2. APPROVED / CONFIRMED BANNER -->
        <div x-show="currentStatus === 'confirmed' || currentStatus === 'preparing'" x-transition class="mb-8 p-5 bg-emerald-600 text-white rounded-3xl shadow-xl shadow-emerald-600/20 flex items-center gap-4 border border-emerald-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                🎉
            </div>
            <div>
                <h3 class="font-black text-lg">Order Confirmed by Admin!</h3>
                <p class="text-xs text-emerald-100 mt-0.5">Our kitchen has started preparing your food.</p>
            </div>
        </div>

        <!-- 3. DISPATCHED / DELIVERING BANNER -->
        <div x-show="currentStatus === 'delivering'" x-transition class="mb-8 p-5 bg-purple-600 text-white rounded-3xl shadow-xl shadow-purple-600/20 flex items-center gap-4 border border-purple-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                🛵
            </div>
            <div>
                <h3 class="font-black text-lg">Order is on the way!</h3>
                <p class="text-xs text-purple-100 mt-0.5">Our delivery rider is heading to your location.</p>
            </div>
        </div>

        <!-- 4. COMPLETED BANNER -->
        <div x-show="currentStatus === 'completed'" x-transition class="mb-8 p-5 bg-blue-600 text-white rounded-3xl shadow-xl shadow-blue-600/20 flex items-center gap-4 border border-blue-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                ✅
            </div>
            <div>
                <h3 class="font-black text-lg">Order Completed!</h3>
                <p class="text-xs text-blue-100 mt-0.5">Thank you for ordering with us. Enjoy your meal!</p>
            </div>
        </div>

        <!-- 5. CANCELLED / REJECTED BANNER -->
        <div x-show="currentStatus === 'cancelled'" x-transition class="mb-8 p-5 bg-red-600 text-white rounded-3xl shadow-xl shadow-red-600/20 flex items-center gap-4 border border-red-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                ❌
            </div>
            <div>
                <h3 class="font-black text-lg">Order Cancelled</h3>
                <p class="text-xs text-red-100 mt-0.5" x-text="currentNotes && currentNotes.trim() !== '' ? currentNotes : 'The order was cancelled by the administrator.'"></p>
            </div>
        </div>

        <!-- Header Status Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-600 rounded-full">Order #{{ $order->order_number }}</span>
                        <span class="text-xs text-slate-400 font-medium">{{ $order->created_at->format('M d, Y • h:i A') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">Order Details</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Dynamic Status Badge -->
                    <span class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                        :class="{
                            'bg-amber-100 text-amber-700 border border-amber-200': currentStatus === 'pending',
                            'bg-emerald-100 text-emerald-700 border border-emerald-200': currentStatus === 'confirmed' || currentStatus === 'preparing',
                            'bg-purple-100 text-purple-700 border border-purple-200': currentStatus === 'delivering',
                            'bg-blue-100 text-blue-700 border border-blue-200': currentStatus === 'completed',
                            'bg-red-100 text-red-700 border border-red-200': currentStatus === 'cancelled'
                        }">
                        Order Status: <span x-text="currentStatus.toUpperCase()"></span>
                    </span>

                    <!-- Dynamic Payment Status Badge -->
                    <span class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                        :class="{
                            'bg-green-100 text-green-700 border border-green-200': currentPaymentStatus === 'paid',
                            'bg-purple-100 text-purple-700 border border-purple-200': currentPaymentStatus === 'pending_verification',
                            'bg-orange-100 text-orange-700 border border-orange-200': currentPaymentStatus === 'unpaid'
                        }">
                        Payment: <span x-text="currentPaymentStatus.replace('_', ' ').toUpperCase()"></span>
                    </span>
                </div>
            </div>

            <!-- Grid Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <!-- Delivery Info -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">📍 Delivery Info</p>
                    <p class="font-bold text-slate-800">{{ $order->delivery_township ?? 'Yangon' }}</p>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $order->delivery_address }}</p>
                    <p class="text-xs font-bold text-slate-800 mt-2">📞 {{ $order->delivery_phone }}</p>
                </div>

                <!-- Payment Method Info -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">💳 Payment Method</p>
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
                                View 🔍
                            </button>
                        </div>
                    @else
                        <p class="text-xs text-slate-500">Pay on delivery (COD)</p>
                    @endif
                </div>

                <!-- Notes -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">📝 Notes</p>
                    <p class="text-xs text-slate-700 leading-relaxed italic" x-text="currentNotes && currentNotes.trim() !== '' ? '&quot;' + currentNotes + '&quot;' : 'None'">
                        {{ $order->notes ? '"'.$order->notes.'"' : 'None' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Items Table Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 mb-8">
            <h2 class="text-lg font-black text-slate-900 mb-6">Ordered Items</h2>

            <div class="overflow-x-auto rounded-2xl border border-slate-100 mb-6">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3 text-center">Quantity</th>
                            <th class="px-4 py-3 text-right">Unit Price</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800 font-medium">
                        @foreach ($order->orderItems as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-slate-100 rounded-xl overflow-hidden shrink-0">
                                            <img src="{{ $item->menuItem?->image_url ?? asset('images/hero_food.png') }}"
                                                 alt="{{ $item->menuItem?->name }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-sm">{{ $item->menuItem?->name ?? 'Menu Item' }}</h3>
                                            <p class="text-xs text-slate-400 font-medium">{{ $item->menuItem?->category?->name ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold font-mono text-slate-700">
                                    <span class="px-2.5 py-1 bg-slate-100 rounded-lg">{{ $item->quantity }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-600">
                                    {{ number_format($item->unit_price) }} MMK
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-slate-900">
                                    {{ number_format($item->subtotal) }} MMK
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                    <span class="font-black text-slate-900 text-base">Total Amount Payable</span>
                    <span class="font-black text-orange-500 text-xl">{{ number_format($order->total_amount) }} MMK</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="w-full sm:w-auto px-8 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-black text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all text-center">
                ← Back to Menu
            </a>
            <a href="{{ route('user.orders.index') }}" class="w-full sm:w-auto px-8 py-3.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-black text-sm rounded-2xl transition-all text-center">
                📦 View My Orders
            </a>
        </div>

    </main>

    <!-- Screenshot Modal -->
    <div x-show="imgModal" x-transition class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-3xl p-4 max-w-lg w-full relative shadow-2xl" @click.outside="imgModal = false">
            <button @click="imgModal = false" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 flex items-center justify-center">✕</button>
            <p class="font-bold text-slate-900 text-sm mb-3">Payment Screenshot</p>
            <img :src="imgSrc" alt="Payment Screenshot" class="w-full h-auto rounded-2xl border border-slate-100 max-h-[70vh] object-contain">
        </div>
    </div>

</body>
</html>
