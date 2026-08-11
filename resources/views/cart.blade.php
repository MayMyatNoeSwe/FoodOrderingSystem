<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cart — {{ config('app.name', 'FoodOrder') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-orange-500 selection:text-white">

<div x-data="cartApp()" x-init="init()" class="min-h-screen">

    <!-- ===== NAVBAR ===== -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                    <a href="/" class="text-sm font-semibold text-slate-600 hover:text-orange-500 transition-colors">&larr; Menu</a>
                    @auth
                        <span class="hidden sm:inline text-xs text-slate-400">|</span>
                        <span class="hidden sm:inline text-sm font-bold text-slate-700">{{ Auth::user()->name }}</span>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900">🛒 Your Cart</h1>
            <p class="text-slate-500 text-sm mt-1">မြို့နယ်နှင့် ငွေပေးချေမှုနည်းလမ်း ရွေးချယ်ပြီး Order တင်ပါ</p>
        </div>

        <!-- ===== EMPTY STATE ===== -->
        <div x-show="items.length === 0" x-transition class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center text-5xl mb-6 shadow-inner">🛒</div>
            <h2 class="text-2xl font-black text-slate-900 mb-2">Cart ထဲတွင် ပစ္စည်း မရှိပါ</h2>
            <p class="text-slate-500 mb-6 max-w-sm">မနူးကို ကြည့်ပြီး သင်နှစ်သက်သော အစားအစာများ ထည့်ပါ!</p>
            <a href="/" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">Menu ကြည့်မည်</a>
        </div>

        <!-- ===== MAIN CART GRID ===== -->
        <div x-show="items.length > 0" x-transition class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            <!-- ============ LEFT COL: Items + Location ============ -->
            <div class="xl:col-span-2 space-y-5">

                <!-- Cart Items Header -->
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-widest">
                        <span x-text="items.length"></span> မျိုး
                    </span>
                    <button @click="clearCart()" class="text-xs font-semibold text-red-400 hover:text-red-600 transition-colors cursor-pointer">🗑 အားလုံးဖျက်မည်</button>
                </div>

                <!-- Cart Item Cards -->
                <template x-for="(item, index) in items" :key="item.id">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-4 group hover:shadow-md transition-all">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 bg-slate-100">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-900 text-sm truncate" x-text="item.name"></h3>
                            <p class="text-orange-500 font-black text-sm mt-0.5"><span x-text="formatPrice(item.price)"></span> MMK</p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="item.category ?? ''"></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="decreaseQty(index)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-600 font-black text-lg flex items-center justify-center transition-all cursor-pointer">&minus;</button>
                            <span class="w-8 text-center font-bold text-slate-900 text-sm" x-text="item.qty"></span>
                            <button @click="increaseQty(index)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-600 font-black text-lg flex items-center justify-center transition-all cursor-pointer">+</button>
                        </div>
                        <div class="text-right shrink-0 min-w-[80px]">
                            <p class="text-xs text-slate-400 mb-0.5">စုစုပေါင်း</p>
                            <p class="font-black text-slate-900 text-sm"><span x-text="formatPrice(item.price * item.qty)"></span> MMK</p>
                        </div>
                        <button @click="removeItem(index)" class="w-8 h-8 rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-all cursor-pointer ml-1 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                <!-- ===== LOCATION SELECTION ===== -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">📍 Delivery ဒေသ နှင့် မြို့နယ် ရွေးချယ်ပါ</h3>

                    <!-- Region Select -->
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">တိုင်းဒေသကြီး / ပြည်နယ် <span class="text-red-400">*</span></label>
                        <select x-model="selectedRegion" @change="onRegionChange()"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all bg-white font-semibold">
                            <option value="">-- တိုင်းဒေသကြီး / ပြည်နယ် ရွေးချယ်ပါ --</option>
                            <option value="Yangon">🏙️ ရန်ကုန်တိုင်းဒေသကြီး (Yangon Region)</option>
                            <option value="Sagaing">🌾 စစ်ကိုင်းတိုင်းဒေသကြီး (Sagaing Region)</option>
                            <option value="Mandalay">🏰 မန္တလေးတိုင်းဒေသကြီး (Mandalay Region)</option>
                            <option value="Naypyitaw">🏛️ နေပြည်တော် (Naypyitaw)</option>
                            <option value="Bago">🌳 ပဲခူးတိုင်းဒေသကြီး (Bago Region)</option>
                            <option value="Magway">☀️ မကွေးတိုင်းဒေသကြီး (Magway Region)</option>
                            <option value="Ayeyarwady">🌾 ဧရာဝတီတိုင်းဒေသကြီး (Ayeyarwady Region)</option>
                            <option value="Shan">⛰️ ရှမ်းပြည်နယ် (Shan State)</option>
                            <option value="Mon">🌊 မွန်ပြည်နယ် (Mon State)</option>
                            <option value="Kayin">⛰️ ကရင်ပြည်နယ် (Kayin State)</option>
                            <option value="Rakhine">🏖️ ရခိုင်ပြည်နယ် (Rakhine State)</option>
                            <option value="Kachin">🏔️ ကချင်ပြည်နယ် (Kachin State)</option>
                            <option value="Tanintharyi">🏝️ တနင်္သာရီတိုင်းဒေသကြီး (Tanintharyi Region)</option>
                            <option value="Kayah">⛰️ ကယားပြည်နယ် (Kayah State)</option>
                            <option value="Chin">🏔️ ချင်းပြည်နယ် (Chin State)</option>
                        </select>
                    </div>

                    <!-- Township Select (Populated dynamically) -->
                    <div x-show="selectedRegion" x-transition>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">မြို့ / မြို့နယ် <span class="text-red-400">*</span></label>
                        <select x-model="selectedTownship" @change="onTownshipChange()"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all bg-white font-medium">
                            <option value="">-- မြို့နယ် ရွေးချယ်ပါ --</option>
                            <template x-for="ts in availableTownships" :key="ts.name">
                                <option :value="ts.name" x-text="ts.name + ' (' + formatPrice(ts.fee) + ' MMK)'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Delivery Fee & Zone Badge -->
                    <div x-show="selectedTownship && deliveryFee > 0" x-transition class="mt-3 flex items-center gap-4 p-4 bg-orange-50 rounded-xl border border-orange-100">
                        <span class="text-3xl">🛵</span>
                        <div>
                            <p class="text-xs text-orange-700 font-bold uppercase tracking-wide">Delivery Fee</p>
                            <p class="text-2xl font-black text-orange-500"><span x-text="formatPrice(deliveryFee)"></span> <span class="text-base">MMK</span></p>
                        </div>
                        <span class="ml-auto text-xs font-black px-3 py-1.5 rounded-full bg-orange-100 text-orange-700"
                            x-text="selectedRegion === 'Yangon' ? 'Yangon Delivery' : selectedRegion + ' Region/State'"></span>
                    </div>

                    <!-- Outside Yangon Notice -->
                    <div x-show="selectedRegion && selectedRegion !== 'Yangon'" x-transition class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3">
                        <span class="text-xl shrink-0">⚠️</span>
                        <p class="text-xs font-bold text-amber-800">
                            ရန်ကုန်ပြင်ပ Order များ — KBZPay သို့မဟုတ် WavePay (Prepaid) ဖြင့်သာ လက်ခံပါမည်။
                        </p>
                    </div>
                </div>

            </div>

            <!-- ============ RIGHT COL: Summary + Checkout ============ -->
            <div class="space-y-5">

                <!-- Order Summary -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="text-base font-black text-slate-900 mb-4">Order Summary</h2>
                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>စုစုပေါင်း (<span x-text="totalQty()"></span> ခု)</span>
                            <span class="font-semibold text-slate-900"><span x-text="formatPrice(subtotal())"></span> MMK</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Delivery ဖိ</span>
                            <span class="font-semibold" :class="deliveryFee > 0 ? 'text-slate-900' : 'text-slate-400'">
                                <span x-show="deliveryFee > 0" x-text="formatPrice(deliveryFee) + ' MMK'"></span>
                                <span x-show="deliveryFee === 0" class="text-xs">မြို့နယ်ရွေးပါ</span>
                            </span>
                        </div>
                        <div class="border-t border-slate-100 pt-3 mt-2 flex justify-between">
                            <span class="font-black text-slate-900">ပေးရမည့်ငွေ</span>
                            <span class="font-black text-orange-500 text-lg"><span x-text="formatPrice(total())"></span> MMK</span>
                        </div>
                    </div>
                </div>

                <!-- ===== CHECKOUT FORM ===== -->
                @auth
                <form id="checkout-form" method="POST" action="{{ route('user.orders.store') }}"
                    enctype="multipart/form-data"
                    class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                    @csrf

                    {{-- Hidden fields injected by Alpine --}}
                    <input type="hidden" name="cart_items"        id="cart_items_input">
                    <input type="hidden" name="total_amount"      id="total_amount_input">
                    <input type="hidden" name="delivery_fee"      id="delivery_fee_input">
                    <input type="hidden" name="region_type"       id="region_type_input">
                    <input type="hidden" name="delivery_township" id="delivery_township_input">

                    <h2 class="text-base font-black text-slate-900">Delivery အချက်အလက်</h2>

                    {{-- Full Address --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            အသေးစိတ်လိပ်စာ <span class="text-red-400">*</span>
                        </label>
                        <textarea name="delivery_address" rows="2" required
                            placeholder="အမှတ်၊ လမ်း၊ ရပ်ကွက်/ကျောင်းဆောင် ..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400"></textarea>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            ဖုန်းနံပါတ် <span class="text-red-400">*</span>
                        </label>
                        <input type="tel" name="delivery_phone" required
                            placeholder="+95 9 ..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all placeholder-slate-400">
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            ငွေပေးချေနည်း <span class="text-red-400">*</span>
                        </label>

                        <div class="space-y-2">

                            {{-- COD — Yangon only --}}
                            <label class="cursor-pointer block" x-show="selectedRegion === 'Yangon'">
                                <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="peer sr-only">
                                <div class="peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600 border-2 border-slate-200 rounded-xl p-3 flex items-center gap-3 hover:border-green-300 transition-all">
                                    <span class="text-xl shrink-0">💵</span>
                                    <div>
                                        <p class="font-bold text-sm">Cash on Delivery (COD)</p>
                                        <p class="text-xs opacity-70">ပစ္စည်းရောက်မှ ငွေချေမည် — QR မလို</p>
                                    </div>
                                </div>
                            </label>

                            {{-- KBZPay --}}
                            <label class="cursor-pointer block">
                                <input type="radio" name="payment_method" value="kbzpay" x-model="paymentMethod" class="peer sr-only">
                                <div class="peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-2 border-slate-200 rounded-xl p-3 flex items-center gap-3 hover:border-blue-300 transition-all">
                                    <span class="text-xl shrink-0">🏦</span>
                                    <div>
                                        <p class="font-bold text-sm">KBZPay</p>
                                        <p class="text-xs opacity-70">ကြိုတင်ငွေချေ — QR ဖြင့် ပေးချေ + Screenshot တင်ရမည်</p>
                                    </div>
                                </div>
                            </label>

                            {{-- WavePay --}}
                            <label class="cursor-pointer block">
                                <input type="radio" name="payment_method" value="wavepay" x-model="paymentMethod" class="peer sr-only">
                                <div class="peer-checked:bg-yellow-400 peer-checked:text-slate-900 peer-checked:border-yellow-400 border-2 border-slate-200 rounded-xl p-3 flex items-center gap-3 hover:border-yellow-300 transition-all">
                                    <span class="text-xl shrink-0">🌊</span>
                                    <div>
                                        <p class="font-bold text-sm">WavePay</p>
                                        <p class="text-xs opacity-70">ကြိုတင်ငွေချေ — QR ဖြင့် ပေးချေ + Screenshot တင်ရမည်</p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- ===== KBZPay QR Panel ===== --}}
                        <div x-show="paymentMethod === 'kbzpay'" x-transition class="mt-4 rounded-2xl overflow-hidden border border-blue-200">
                            <div class="bg-blue-600 px-4 py-3 text-center">
                                <p class="text-white font-black text-sm">KBZ Pay ဖြင့် ငွေပေးချေပါ</p>
                            </div>
                            <div class="p-4 bg-blue-50 flex flex-col items-center gap-3">
                                {{-- QR Code Image --}}
                                <div class="bg-white p-3 rounded-2xl shadow-lg border-4 border-blue-200">
                                    <img src="/images/kbzpay_qr.jpg"
                                         alt="KBZPay QR Code"
                                         class="w-48 h-48 object-contain"
                                         onerror="this.parentElement.innerHTML='<div class=\'w-48 h-48 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 text-xs font-bold text-center p-4\'>KBZPay QR<br><br>public/images/<br>kbzpay_qr.jpg<br>ထည့်ပါ</div>'">
                                </div>
                                <div class="text-center">
                                    <p class="font-black text-blue-900 text-sm">DAW MAY MYAT NOE SWE</p>
                                    <p class="text-blue-700 text-xs font-semibold">09457549229</p>
                                    <div class="mt-2 bg-blue-600 text-white font-black text-base px-4 py-2 rounded-xl inline-block">
                                        <span x-text="formatPrice(total())"></span> MMK
                                    </div>
                                </div>

                                {{-- Instructions --}}
                                <div class="w-full bg-white border border-blue-100 rounded-xl p-3">
                                    <p class="text-xs font-bold text-blue-800 mb-2">📱 ငွေပေးချေနည်း</p>
                                    <ol class="text-xs text-blue-700 space-y-1 list-decimal list-inside leading-relaxed">
                                        <li>KBZPay App ဖွင့်ပါ</li>
                                        <li>QR Scanner နှိပ်ပြီး QR ကို စကင်ဖတ်ပါ</li>
                                        <li>Amount: <span class="font-black" x-text="formatPrice(total()) + ' MMK'"></span> ထည့်ပါ</li>
                                        <li>ငွေပေးချေပြီးနောက် Screenshot ရိုက်ပါ</li>
                                        <li>အောက်တွင် Screenshot တင်ပါ ✅</li>
                                    </ol>
                                </div>

                                {{-- Screenshot Upload --}}
                                <div class="w-full">
                                    <label class="text-xs font-bold text-blue-800 block mb-1.5">
                                        ငွေချေပြီးကြောင်း Screenshot တင်ပါ <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="payment_screenshot" id="kbz_screenshot" accept="image/*"
                                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:font-bold file:cursor-pointer hover:file:bg-blue-700 border-2 border-dashed border-blue-300 rounded-xl p-2 bg-white cursor-pointer">
                                </div>
                            </div>
                        </div>

                        {{-- ===== WavePay QR Panel ===== --}}
                        <div x-show="paymentMethod === 'wavepay'" x-transition class="mt-4 rounded-2xl overflow-hidden border border-yellow-300">
                            <div class="bg-yellow-400 px-4 py-3 text-center">
                                <p class="text-slate-900 font-black text-sm">WavePay ဖြင့် ငွေပေးချေပါ</p>
                            </div>
                            <div class="p-4 bg-yellow-50 flex flex-col items-center gap-3">
                                {{-- QR Code Image --}}
                                <div class="bg-yellow-300 p-3 rounded-2xl shadow-lg border-4 border-yellow-300">
                                    <img src="/images/wavepay_qr.jpg"
                                         alt="WavePay QR Code"
                                         class="w-48 h-48 object-contain bg-white rounded-xl"
                                         onerror="this.parentElement.innerHTML='<div class=\'w-48 h-48 bg-yellow-100 rounded-xl flex items-center justify-center text-yellow-700 text-xs font-bold text-center p-4\'>WavePay QR<br><br>public/images/<br>wavepay_qr.jpg<br>ထည့်ပါ</div>'">
                                </div>
                                <div class="text-center">
                                    <p class="font-black text-slate-900 text-sm">May Myat Noe Swe</p>
                                    <p class="text-yellow-700 text-xs font-semibold">09457549229</p>
                                    <div class="mt-2 bg-yellow-400 text-slate-900 font-black text-base px-4 py-2 rounded-xl inline-block">
                                        <span x-text="formatPrice(total())"></span> MMK
                                    </div>
                                </div>

                                {{-- Instructions --}}
                                <div class="w-full bg-white border border-yellow-200 rounded-xl p-3">
                                    <p class="text-xs font-bold text-yellow-800 mb-2">📱 ငွေပေးချေနည်း</p>
                                    <ol class="text-xs text-yellow-700 space-y-1 list-decimal list-inside leading-relaxed">
                                        <li>WavePay App ဖွင့်ပါ</li>
                                        <li>QR Code ကို စကင်ဖတ်ပါ</li>
                                        <li>Amount: <span class="font-black" x-text="formatPrice(total()) + ' MMK'"></span> ထည့်ပါ</li>
                                        <li>ငွေပေးချေပြီးနောက် Screenshot ရိုက်ပါ</li>
                                        <li>အောက်တွင် Screenshot တင်ပါ ✅</li>
                                    </ol>
                                </div>

                                {{-- Screenshot Upload --}}
                                <div class="w-full">
                                    <label class="text-xs font-bold text-yellow-800 block mb-1.5">
                                        ငွေချေပြီးကြောင်း Screenshot တင်ပါ <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="payment_screenshot" id="wave_screenshot" accept="image/*"
                                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-yellow-500 file:text-white file:font-bold file:cursor-pointer hover:file:bg-yellow-600 border-2 border-dashed border-yellow-300 rounded-xl p-2 bg-white cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">မှတ်ချက် <span class="text-slate-300">(optional)</span></label>
                        <textarea name="notes" rows="2"
                            placeholder="မသတ်တမ်းသပ်ပါ၊ Extra sauce ထပ်ပေး..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400"></textarea>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" @click="submitOrder($event)"
                        class="w-full py-3.5 text-white font-black text-sm rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="canSubmit()
                            ? 'bg-orange-500 hover:bg-orange-600 active:bg-orange-700 shadow-orange-500/25'
                            : 'bg-slate-300 cursor-not-allowed'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="paymentMethod === 'cod' ? 'Order တင်မည်' : 'Order တင်မည် (ငွေချေပြီး)'"></span>
                        &mdash; <span x-text="formatPrice(total())"></span> MMK
                    </button>

                    <p class="text-xs text-center text-slate-400 leading-relaxed">
                        <span x-show="paymentMethod !== 'cod'">💡 Screenshot စစ်ဆေးပြီး Admin မှ Order Confirm ပေးပါမည်</span>
                        <span x-show="paymentMethod === 'cod'">ပစ္စည်းရောက်မှ ငွေချေရမည်</span>
                    </p>
                </form>

                @else
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center">
                    <div class="text-4xl mb-3">🔐</div>
                    <h3 class="font-bold text-slate-900 mb-1">Login ဝင်ရောက်ပါ</h3>
                    <p class="text-sm text-slate-500 mb-4">Order တင်ရန် Login လုပ်ရပါမည်</p>
                    <a href="{{ route('login') }}" class="block w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all text-center">Log In</a>
                    <a href="{{ route('register') }}" class="block mt-2 text-xs text-slate-500 hover:text-orange-500 transition-colors">Account မရှိဘူးလား? Register</a>
                </div>
                @endauth

            </div>
        </div>
    </main>
</div>

<script>
function cartApp() {
    const townshipsData = {
        'Yangon': [
            { name: 'ကျောက်တံတား (Kyauktada)', fee: 2000 },
            { name: 'ပန်းဘဲတန်း (Pabedan)', fee: 2000 },
            { name: 'လမ်းမတော် (Lanmadaw)', fee: 2000 },
            { name: 'လသာ (Latha)', fee: 2000 },
            { name: 'ဗိုလ်တထောင် (Botahtaung)', fee: 2000 },
            { name: 'ပုဇွန်တောင် (Pazundaung)', fee: 2000 },
            { name: 'မင်္ဂလာတောင်ညွှန့် (Mingalar Taung Nyunt)', fee: 2000 },
            { name: 'အလုံ (Ahlone)', fee: 2000 },
            { name: 'ကမာရွတ် (Kamaryut)', fee: 3000 },
            { name: 'ဗဟန်း (Bahan)', fee: 3000 },
            { name: 'တာမွေ (Tamwe)', fee: 3000 },
            { name: 'ဒဂုံ (Dagon)', fee: 3000 },
            { name: 'ရန်ကင်း (Yankin)', fee: 3000 },
            { name: 'စမ်းချောင်း (Sanchaung)', fee: 3000 },
            { name: 'လှိုင် (Hlaing)', fee: 3000 },
            { name: 'မရမ်းကုန်း (Mayangone)', fee: 3000 },
            { name: 'အင်းစိန် (Insein)', fee: 3000 },
            { name: 'သာကေတ (Thaketa)', fee: 3000 },
            { name: 'သင်္ဃန်းကျွန်း (Thingangyun)', fee: 3000 },
            { name: 'ရွှေပြည်သာ (Shwepyithar)', fee: 5000 },
            { name: 'လှိုင်သာယာ (Hlaingtharyar)', fee: 5000 },
            { name: 'မြောက်ဥက္ကလာပ (North Okkalapa)', fee: 5000 },
            { name: 'တောင်ဥက္ကလာပ (South Okkalapa)', fee: 5000 },
            { name: 'အရှေ့ဒဂုံ (East Dagon)', fee: 5000 },
            { name: 'မြောက်ဒဂုံ (North Dagon)', fee: 5000 },
            { name: 'တောင်ဒဂုံ (South Dagon)', fee: 5000 },
            { name: 'ဒဂုံဆိပ်ကမ်း (Dagon Seikkan)', fee: 5000 },
            { name: 'ဒလ (Dala)', fee: 7000 },
            { name: 'တွံတေး (Twante)', fee: 7000 },
            { name: 'ကိုကိုးကျွန်း (Cocogyun)', fee: 10000 }
        ],
        'Sagaing': [
            { name: 'စစ်ကိုင်း (Sagaing)', fee: 5000 },
            { name: 'မုံရွာ (Monywa)', fee: 5500 },
            { name: 'ရွှေဘို (Shwebo)', fee: 6000 },
            { name: 'ကသာ (Katha)', fee: 6500 },
            { name: 'ကလေး (Kalay)', fee: 7000 },
            { name: 'ရေဦး (Ye-U)', fee: 6000 },
            { name: 'ဝန်းသို (Wuntho)', fee: 6500 },
            { name: 'တမူး (Tamu)', fee: 7500 },
            { name: 'အင်းတော် (Indaw)', fee: 6500 },
            { name: 'ယင်းမာပင် (Yinmabin)', fee: 6000 }
        ],
        'Mandalay': [
            { name: 'ချမ်းအေးသာစံ (Chanayethazan)', fee: 4000 },
            { name: 'မဟာအောင်မြေ (Maha Aungmye)', fee: 4000 },
            { name: 'ပြည်ကြီးတံခွန် (Pyigyidagun)', fee: 4500 },
            { name: 'အမရပူရ (Amarapura)', fee: 4500 },
            { name: 'ပုသိမ်ကြီး (Patheingyi)', fee: 4500 },
            { name: 'ပြင်ဦးလွင် (Pyin Oo Lwin)', fee: 5500 },
            { name: 'မိတ္ထီလာ (Meiktila)', fee: 5500 },
            { name: 'မြင်းခြံ (Myingyan)', fee: 5500 },
            { name: 'ညောင်ဦး / ပုဂံ (Nyaung-U / Bagan)', fee: 6000 },
            { name: 'ရမည်းသင်း (Yamethin)', fee: 6000 }
        ],
        'Naypyitaw': [
            { name: 'ဇမ္ဗူသီရိ (Zabuthiri)', fee: 4000 },
            { name: 'ဥတ္တရသီရိ (Ottarathiri)', fee: 4000 },
            { name: 'ပုဗ္ဗသီရိ (Pobbathiri)', fee: 4000 },
            { name: 'ဒက္ခိဏသီရိ (Dekkhinathiri)', fee: 4000 },
            { name: 'လယ်ဝေး (Lewe)', fee: 4500 },
            { name: 'ပျဉ်းမနား (Pyinmana)', fee: 4500 },
            { name: 'တပ်ကုန်း (Tatkon)', fee: 5000 }
        ],
        'Bago': [
            { name: 'ပဲခူး (Bago)', fee: 4500 },
            { name: 'တောင်ငူ (Taungoo)', fee: 5500 },
            { name: 'ပြည် (Pyay)', fee: 5500 },
            { name: 'သာယာဝတီ (Tharrawaddy)', fee: 5000 },
            { name: 'ညောင်လေးပင် (Nyaunglebin)', fee: 5000 }
        ],
        'Magway': [
            { name: 'မကွေး (Magway)', fee: 5000 },
            { name: 'ပခုက္ကူ (Pakokku)', fee: 5500 },
            { name: 'ရေနံချောင်း (Yenangyaung)', fee: 5500 },
            { name: 'မင်းဘူး (Minbu)', fee: 5500 },
            { name: 'သရက် (Thayet)', fee: 6000 }
        ],
        'Ayeyarwady': [
            { name: 'ပုသိမ် (Pathein)', fee: 5000 },
            { name: 'ဟင်းသတ္တ (Hinthada)', fee: 5000 },
            { name: 'မအူပင် (Maubin)', fee: 4500 },
            { name: 'မြောင်းမြ (Myaungmya)', fee: 5500 },
            { name: 'ဖျာပုံ (Pyapon)', fee: 5500 }
        ],
        'Shan': [
            { name: 'တောင်ကြီး (Taunggyi)', fee: 5500 },
            { name: 'လားရှိုး (Lashio)', fee: 6500 },
            { name: 'တာချီလိတ် (Tachileik)', fee: 7500 },
            { name: 'ကျိုင်းတုံ (Kengtung)', fee: 7500 },
            { name: 'မူဆယ် (Muse)', fee: 7500 },
            { name: 'ကလော (Kalaw)', fee: 5500 },
            { name: 'ညောင်ရွှေ / အင်းလေး (Nyaungshwe)', fee: 6000 }
        ],
        'Mon': [
            { name: 'မော်လမြိုင် (Mawlamyine)', fee: 5000 },
            { name: 'သထုံ (Thaton)', fee: 4500 },
            { name: 'မုဒုံ (Mudon)', fee: 5000 },
            { name: 'ရေး (Ye)', fee: 6000 }
        ],
        'Kayin': [
            { name: 'ဘားအံ (Hpa-An)', fee: 5000 },
            { name: 'မြဝတီ (Myawaddy)', fee: 7000 },
            { name: 'ကော့ကရိုက် (Kawkareik)', fee: 6500 }
        ],
        'Rakhine': [
            { name: 'စစ်တွေ (Sittwe)', fee: 6500 },
            { name: 'သံတွဲ / ငပလီ (Thandwe)', fee: 7000 },
            { name: 'ကျောက်ဖြူ (Kyaukphyu)', fee: 7000 }
        ],
        'Kachin': [
            { name: 'မြစ်ကြီးနား (Myitkyina)', fee: 6500 },
            { name: 'ဗန်းမော် (Bhamo)', fee: 7000 },
            { name: 'မိုးညှင်း (Mohnyin)', fee: 6500 }
        ],
        'Tanintharyi': [
            { name: 'ထားဝယ် (Dawei)', fee: 6500 },
            { name: 'မြိတ် (Myeik)', fee: 7000 },
            { name: 'ကော့သောင်း (Kawthaung)', fee: 8000 }
        ],
        'Kayah': [
            { name: 'လွိုင်ကော် (Loikaw)', fee: 6500 },
            { name: 'ဒီးမော့ဆို (Demoso)', fee: 7000 }
        ],
        'Chin': [
            { name: 'ဟာခါး (Hakha)', fee: 7500 },
            { name: 'ဖလန်း (Falam)', fee: 7500 },
            { name: 'မင်းတပ် (Mindat)', fee: 7500 },
            { name: 'တီးတိန် (Tedim)', fee: 7500 }
        ]
    };

    return {
        items: [],
        selectedRegion: '',
        selectedTownship: '',
        availableTownships: [],
        deliveryFee: 0,
        paymentMethod: 'cod',

        init() {
            const stored = localStorage.getItem('foodorder_cart');
            this.items = stored ? JSON.parse(stored) : [];
        },

        save() {
            localStorage.setItem('foodorder_cart', JSON.stringify(this.items));
        },

        increaseQty(index) { this.items[index].qty++; this.save(); },

        decreaseQty(index) {
            if (this.items[index].qty > 1) { this.items[index].qty--; this.save(); }
            else { this.removeItem(index); }
        },

        removeItem(index) { this.items.splice(index, 1); this.save(); },

        clearCart() {
            if (confirm('Cart ထဲမှ ပစ္စည်းအားလုံး ဖျက်မည်လား?')) { this.items = []; this.save(); }
        },

        subtotal() {
            return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        totalQty() {
            return this.items.reduce((sum, item) => sum + item.qty, 0);
        },

        total() {
            return this.subtotal() + (this.deliveryFee || 0);
        },

        formatPrice(num) {
            return Number(num).toLocaleString();
        },

        onRegionChange() {
            this.selectedTownship = '';
            this.deliveryFee = 0;
            this.availableTownships = townshipsData[this.selectedRegion] || [];
            if (this.selectedRegion && this.selectedRegion !== 'Yangon') {
                this.paymentMethod = 'kbzpay';
            } else {
                this.paymentMethod = 'cod';
            }
        },

        onTownshipChange() {
            const ts = this.availableTownships.find(t => t.name === this.selectedTownship);
            this.deliveryFee = ts ? ts.fee : 0;
        },

        canSubmit() {
            if (this.items.length === 0) return false;
            if (!this.selectedRegion) return false;
            if (!this.selectedTownship) return false;
            return true;
        },

        submitOrder(event) {
            if (!this.canSubmit()) {
                event.preventDefault();
                if (!this.selectedRegion) {
                    alert('တိုင်းဒေသကြီး / ပြည်နယ် ရွေးချယ်ပါ!');
                } else if (!this.selectedTownship) {
                    alert('မြို့နယ် ရွေးချယ်ပါ!');
                }
                return;
            }

            // Inject hidden fields before form submit
            document.getElementById('cart_items_input').value        = JSON.stringify(this.items);
            document.getElementById('total_amount_input').value      = this.total();
            document.getElementById('delivery_fee_input').value      = this.deliveryFee;
            document.getElementById('region_type_input').value       = this.selectedRegion;
            document.getElementById('delivery_township_input').value  = `${this.selectedRegion} — ${this.selectedTownship}`;
        }
    };
}
</script>

</body>
</html>
