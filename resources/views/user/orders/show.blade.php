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
        window.initOrderTracker = function(initialStatus, initialPaymentStatus, initialNotes, initialRiderName, initialRiderPhone, initialDeliveryProofPhoto, jsonUrl) {
            return {
                imgModal: false,
                imgSrc: '',
                imgTitle: 'Payment Screenshot',
                currentStatus: initialStatus,
                currentPaymentStatus: initialPaymentStatus,
                currentNotes: initialNotes,
                currentRiderName: initialRiderName,
                currentRiderPhone: initialRiderPhone,
                currentDeliveryProofPhoto: initialDeliveryProofPhoto,
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
                                    if (data.rider_name !== undefined) {
                                        self.currentRiderName = data.rider_name;
                                    }
                                    if (data.rider_phone !== undefined) {
                                        self.currentRiderPhone = data.rider_phone;
                                    }
                                    if (data.delivery_proof_photo !== undefined) {
                                        self.currentDeliveryProofPhoto = data.delivery_proof_photo;
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
    x-data="initOrderTracker({{ json_encode($order->status) }}, {{ json_encode($order->payment_status) }}, {{ json_encode($order->notes ?? '') }}, {{ json_encode($order->rider ? $order->rider->name : null) }}, {{ json_encode($order->rider ? ($order->rider->phone_number ?? $order->rider->phone ?? null) : null) }}, {{ json_encode($order->delivery_proof_photo ? asset($order->delivery_proof_photo) : null) }}, '{{ route('customer.orders.json_status', $order) }}')">

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

        <!-- 2. APPROVED / CONFIRMED - WAITING FOR RIDER PICKUP -->
        <div x-show="(currentStatus === 'confirmed' || currentStatus === 'preparing') && !currentRiderName" x-transition class="mb-8 p-5 bg-emerald-600 text-white rounded-3xl shadow-xl shadow-emerald-600/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-emerald-500">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                    👨‍🍳
                </div>
                <div>
                    <h3 class="font-black text-lg">Order Confirmed by Admin!</h3>
                    <p class="text-xs text-emerald-100 mt-0.5">Kitchen is preparing your food. Waiting for a nearby rider to pick up...</p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2 bg-white/20 px-3.5 py-1.5 rounded-xl text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                <span>Rider Pickup Pool Active</span>
            </div>
        </div>

        <!-- 3. RIDER ASSIGNED (PREPARING / HEADING TO PICKUP) -->
        <div x-show="(currentStatus === 'confirmed' || currentStatus === 'preparing') && currentRiderName" x-transition class="mb-8 p-5 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-3xl shadow-xl shadow-orange-500/25 flex items-center justify-between gap-4 border border-orange-400">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                    🛵
                </div>
                <div>
                    <h3 class="font-black text-lg">Rider Assigned: <span x-text="currentRiderName"></span></h3>
                    <p class="text-xs text-orange-100 mt-0.5">Your rider has accepted this order and is heading to the kitchen for pickup!</p>
                </div>
            </div>
            <template x-if="currentRiderPhone">
                <a :href="'tel:' + currentRiderPhone" class="shrink-0 px-4 py-2 bg-white text-orange-600 font-black text-xs rounded-xl shadow-md flex items-center gap-1.5 hover:bg-orange-50 transition-colors">
                    <span>📞 Call Rider</span>
                </a>
            </template>
        </div>

        <!-- 4. DISPATCHED / DELIVERING BANNER -->
        <div x-show="currentStatus === 'delivering'" x-transition class="mb-8 p-5 bg-purple-600 text-white rounded-3xl shadow-xl shadow-purple-600/20 flex items-center justify-between gap-4 border border-purple-500">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0 animate-pulse">
                    🛵
                </div>
                <div>
                    <h3 class="font-black text-lg">Order is Out for Delivery!</h3>
                    <p class="text-xs text-purple-100 mt-0.5" x-text="currentRiderName ? 'Rider ' + currentRiderName + ' is heading to your address.' : 'Our delivery rider is heading to your location.'"></p>
                </div>
            </div>
            <template x-if="currentRiderPhone">
                <a :href="'tel:' + currentRiderPhone" class="shrink-0 px-4 py-2 bg-white text-purple-700 font-black text-xs rounded-xl shadow-md flex items-center gap-1.5 hover:bg-purple-50 transition-colors">
                    <span>📞 Call Rider</span>
                </a>
            </template>
        </div>

        <!-- 5. COMPLETED BANNER -->
        <div x-show="currentStatus === 'completed'" x-transition class="mb-8 p-5 bg-blue-600 text-white rounded-3xl shadow-xl shadow-blue-600/20 flex items-center gap-4 border border-blue-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                ✅
            </div>
            <div>
                <h3 class="font-black text-lg">Order Completed &amp; Delivered!</h3>
                <p class="text-xs text-blue-100 mt-0.5">အစားအသောက် ပို့ဆောင်မှု ပြီးစီးပါပြီ။ Thank you for ordering with us. Enjoy your meal!</p>
            </div>
        </div>

        <!-- 6. CANCELLED / REJECTED BANNER -->
        <div x-show="currentStatus === 'cancelled'" x-transition class="mb-8 p-5 bg-red-600 text-white rounded-3xl shadow-xl shadow-red-600/20 flex items-center gap-4 border border-red-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                ❌
            </div>
            <div>
                <h3 class="font-black text-lg">Order Cancelled</h3>
                <p class="text-xs text-red-100 mt-0.5" x-text="currentNotes && currentNotes.trim() !== '' ? currentNotes : 'The order was cancelled by the administrator.'"></p>
            </div>
        </div>

        <!-- ===== PROOF OF DELIVERY PHOTO CARD (DISPLAYED WHEN ORDER IS COMPLETED) ===== -->
        <div x-show="currentStatus === 'completed' && currentDeliveryProofPhoto" x-transition class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-slate-900 rounded-3xl border-2 border-emerald-400 dark:border-emerald-700/80 p-6 sm:p-7 mb-8 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30 shrink-0">
                        📸
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-slate-900 dark:text-white text-base">Delivery Proof Photo (ရောက်ရှိမှု အတည်ပြု ဓာတ်ပုံ)</h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-600 text-white font-black text-[10px] shadow-sm">✓ Photo Verified</span>
                        </div>
                        <p class="text-xs text-emerald-800 dark:text-emerald-300 font-medium mt-0.5">
                            သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိပြီး ရိုက်ကူးအတည်ပြုထားသော သက်သေဓာတ်ပုံ
                        </p>
                    </div>
                </div>

                <button type="button" @click="imgTitle = 'Delivery Proof Photo (သုံးစွဲသူထံ ရောက်ရှိမှု အတည်ပြု ဓာတ်ပုံ)'; imgSrc = currentDeliveryProofPhoto; imgModal = true;"
                        class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                    <span>🔍</span>
                    <span>View Full Photo</span>
                </button>
            </div>

            <div class="mt-4 pt-4 border-t border-emerald-200/60 dark:border-slate-800 flex items-center gap-4">
                <div @click="imgTitle = 'Delivery Proof Photo (သုံးစွဲသူထံ ရောက်ရှိမှု အတည်ပြု ဓာတ်ပုံ)'; imgSrc = currentDeliveryProofPhoto; imgModal = true;"
                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border-2 border-emerald-500 shrink-0 cursor-pointer group relative shadow-md bg-slate-900">
                    <img :src="currentDeliveryProofPhoto" alt="Proof of delivery photo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors flex items-center justify-center text-white text-xs font-black">
                        🔍 Tap
                    </div>
                </div>
                <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1.5">
                    <p class="font-bold text-slate-900 dark:text-white">
                        🛵 Delivered by Rider: <span class="text-orange-500" x-text="currentRiderName || 'Assigned Rider'"></span>
                    </p>
                    <p class="text-emerald-700 dark:text-emerald-400 font-semibold leading-relaxed">
                        ✓ အစားအသောက်များ သင့်လိပ်စာသို့ စနစ်တကျ အရောက်ပို့ဆောင်ပြီးစီးကြောင်း ဓာတ်ပုံဖြင့် အတည်ပြုပြီးပါပြီ။
                    </p>
                </div>
            </div>
        </div>

        <!-- Header Status Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 mb-8 transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full">Order #{{ $order->order_number }}</span>
                        <span class="text-xs text-slate-400 font-medium">{{ $order->created_at->format('M d, Y • h:i A') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-2">Order Details</h1>
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
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">📍 Delivery Info</p>
                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $order->delivery_township ?? 'Yangon' }}</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">{{ $order->delivery_address }}</p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-2">📞 {{ $order->delivery_phone }}</p>
                </div>

                <!-- Payment Method Info -->
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">💳 Payment Method</p>
                    <p class="font-black text-slate-900 dark:text-white uppercase text-base mb-1">
                        @if($order->payment_method === 'cod') 💵 Cash on Delivery
                        @elseif($order->payment_method === 'kbzpay') 📱 KBZPay
                        @elseif($order->payment_method === 'wavepay') 🌊 WavePay
                        @else {{ $order->payment_method }} @endif
                    </p>
                    @if($order->payment_screenshot)
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs text-slate-500 font-medium">Screenshot:</span>
                            <button @click="imgTitle = 'Payment Screenshot'; imgModal = true; imgSrc = '{{ asset($order->payment_screenshot) }}'"
                                    class="text-xs font-bold text-orange-500 underline hover:text-orange-600 cursor-pointer">
                                View 🔍
                            </button>
                        </div>
                    @else
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pay on delivery (COD)</p>
                    @endif
                </div>

                <!-- Assigned Rider / Notes -->
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">🛵 Delivery Rider</p>
                    <div x-show="currentRiderName">
                        <p class="font-bold text-slate-900 dark:text-white" x-text="currentRiderName"></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="currentRiderPhone ? '📞 ' + currentRiderPhone : ''"></p>
                    </div>
                    <div x-show="!currentRiderName">
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">Waiting for pickup...</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-400">
                        <span class="font-bold text-slate-500">Notes: </span>
                        <span x-text="currentNotes && currentNotes.trim() !== '' ? currentNotes : 'None'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 mb-8 transition-colors">
            <h2 class="text-lg font-black text-slate-900 dark:text-white mb-6">Ordered Items</h2>

            <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 mb-6">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3 text-center">Quantity</th>
                            <th class="px-4 py-3 text-right">Unit Price</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-800 dark:text-slate-200 font-medium">
                        @foreach ($order->orderItems as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden shrink-0">
                                            <img src="{{ $item->menuItem?->image_url ?? asset('images/hero_food.png') }}"
                                                 alt="{{ $item->menuItem?->name }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ $item->menuItem?->name ?? 'Menu Item' }}</h3>
                                            <p class="text-xs text-slate-400 font-medium">{{ $item->menuItem?->category?->name ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold font-mono text-slate-700 dark:text-slate-300">
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">{{ $item->quantity }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-600 dark:text-slate-400">
                                    {{ number_format($item->unit_price) }} MMK
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-slate-900 dark:text-white">
                                    {{ number_format($item->subtotal) }} MMK
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cost Summary Footer -->
            @php
                $itemsSubtotal = $order->orderItems->sum('subtotal');
                if ($itemsSubtotal == 0) {
                    $itemsSubtotal = $order->total_amount - $order->delivery_fee - ($order->tax_amount ?? 0);
                }
                $displayTax = $order->tax_amount > 0 ? $order->tax_amount : round($itemsSubtotal * 0.05);
            @endphp
            <div class="border-t border-slate-100 dark:border-slate-800 pt-6 mt-6 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>{{ __('Subtotal') }}</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($itemsSubtotal) }} MMK</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <span>{{ __('Tax (5%)') }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold uppercase">{{ __('Tax') }}</span>
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">+{{ number_format($displayTax) }} MMK</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>{{ __('Delivery Fee') }}</span>
                    <span class="font-bold text-slate-900 dark:text-white">+{{ number_format($order->delivery_fee) }} MMK</span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between items-center">
                    <span class="font-black text-slate-900 dark:text-white text-base">{{ __('Total Amount') }}</span>
                    <span class="font-black text-orange-500 text-xl">{{ number_format($order->total_amount) }} MMK</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="w-full sm:w-auto px-8 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-black text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all text-center">
                ← Back to Menu
            </a>
            <a href="{{ route('customer.orders.index') }}" class="w-full sm:w-auto px-8 py-3.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-sm rounded-2xl transition-all text-center">
                📦 View My Orders
            </a>
        </div>

    </main>

    <!-- Screenshot & Proof Modal -->
    <div x-show="imgModal" x-transition class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 max-w-lg w-full relative shadow-2xl space-y-3" @click.outside="imgModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <p class="font-bold text-slate-900 dark:text-white text-sm" x-text="imgTitle"></p>
                <button @click="imgModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center cursor-pointer">✕</button>
            </div>
            <img :src="imgSrc" :alt="imgTitle" class="w-full h-auto rounded-2xl border border-slate-100 dark:border-slate-800 max-h-[70vh] object-contain mx-auto">
        </div>
    </div>

</body>
</html>
