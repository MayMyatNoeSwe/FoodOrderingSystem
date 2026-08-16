<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cart — {{ config('app.name', 'FoodOrder') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>

    <!-- Theme Initialization (Prevents FOUC) -->
    <script>
        if (localStorage.getItem('foodorder_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{--
        IMPORTANT: window.cartApp must be defined as a plain global function in a regular
        <script> block (NOT type="module") placed BEFORE the Vite module tag.
        Regular scripts are synchronous and complete before any deferred/module scripts run,
        so window.cartApp will exist on window by the time Alpine (loaded via Vite module) calls
        Alpine.start() and processes x-data="cartApp()".
    --}}
    <script>
        const yangonFees = {
            'Kyauktada': 2000, 'Pabedan': 2000, 'Lanmadaw': 2000, 'Latha': 2000,
            'Botahtaung': 2000, 'Pazundaung': 2000, 'Mingalar Taung Nyunt': 2000, 'Ahlone': 2000,
            'Kamaryut': 3000, 'Bahan': 3000, 'Tamwe': 3000, 'Dagon': 3000,
            'Yankin': 3000, 'Sanchaung': 3000, 'Hlaing': 3000, 'Mayangone': 3000,
            'Insein': 3000, 'Thaketa': 3000, 'Thingangyun': 3000,
            'Shwepyithar': 5000, 'Hlaingtharyar': 5000, 'North Okkalapa': 5000,
            'South Okkalapa': 5000, 'East Dagon': 5000, 'North Dagon': 5000,
            'South Dagon': 5000, 'Dagon Seikkan': 5000,
            'Dala': 7000, 'Twante': 7000, 'Cocogyun': 10000
        };

        window.cartApp = function() {
            return {
                items: [],
                selectedTownship: @json(Auth::check() ? (string)(Auth::user()->city ?? '') : ''),
                deliveryFee: 0,
                paymentMethod: 'cod',
                isSubmitting: false,
                darkMode: localStorage.getItem('foodorder_theme') === 'dark',

                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('foodorder_theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('foodorder_theme', 'light');
                    }
                },

                init() {
                    try {
                        const stored = localStorage.getItem('foodorder_cart');
                        if (stored && stored !== 'undefined') {
                            const parsed = JSON.parse(stored);
                            this.items = Array.isArray(parsed) ? parsed : [];
                        } else {
                            this.items = [];
                        }
                    } catch (e) {
                        console.error('Cart parse error:', e);
                        this.items = [];
                    }
                    if (this.selectedTownship) {
                        this.onTownshipChange();
                    }
                },

                save() {
                    localStorage.setItem('foodorder_cart', JSON.stringify(this.items));
                    window.dispatchEvent(new CustomEvent('cart-updated'));
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

                onTownshipChange() {
                    this.deliveryFee = yangonFees[this.selectedTownship] || 0;
                },

                getZoneLabel() {
                    const fee = this.deliveryFee;
                    if (fee <= 2000) return 'Zone 1 — မြို့ပြလယ်';
                    if (fee <= 3000) return 'Zone 2 — မြို့အလယ်';
                    if (fee <= 5000) return 'Zone 3 — မြို့ပြင်';
                    return 'Zone 4 — ဝေးသောမြို့နယ်';
                },

                canSubmit() {
                    if (this.items.length === 0) return false;
                    if (!this.selectedTownship) return false;
                    return true;
                },

                submitOrder(event) {
                    if (this.isSubmitting) {
                        event.preventDefault();
                        return;
                    }
                    if (!this.canSubmit()) {
                        event.preventDefault();
                        if (!this.selectedTownship) {
                            alert('မြို့နယ် ရွေးချယ်ပါ!');
                        }
                        return;
                    }
                    this.isSubmitting = true;
                    document.getElementById('cart_items_input').value        = JSON.stringify(this.items);
                    document.getElementById('total_amount_input').value      = this.total();
                    document.getElementById('delivery_fee_input').value      = this.deliveryFee;
                    document.getElementById('region_type_input').value       = 'Yangon';
                    document.getElementById('delivery_township_input').value = `Yangon — ${this.selectedTownship}`;
                }
            };
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 selection:bg-orange-500 selection:text-white min-h-screen">

<div x-data="cartApp()" x-init="init()" class="min-h-screen">

    <!-- ===== NAVBAR ===== -->
    <x-storefront-navbar />

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

                <!-- ===== LOCATION SELECTION (YANGON ONLY) ===== -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">📍 Delivery မြို့နယ် ရွေးချယ်ပါ</h3>
                        <span class="text-xs font-bold px-3 py-1 bg-green-100 text-green-700 rounded-full">Yangon Region Only</span>
                    </div>

                    <!-- Fresh Food Notice -->
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
                        <span class="text-xl shrink-0">🥗</span>
                        <p class="text-xs text-emerald-800 font-semibold leading-relaxed">
                            မလတ်ဆတ် အမြန်ပုတ်သိုးလွယ်သော လတ်ဆတ်ဆတ် အစားအစာများ ဖြစ်ပါသောကြောင့် <strong class="font-black text-emerald-950">ရန်ကုန်တိုင်းဒေသကြီး မြို့နယ်များသို့သာ</strong> ပို့ဆောင်ပေးပါသည်။
                        </p>
                    </div>

                    <!-- Yangon Township Select -->
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">ရန်ကုန်မြို့နယ် <span class="text-red-400">*</span></label>
                        <select x-model="selectedTownship" @change="onTownshipChange()"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all bg-white font-medium">
                            <option value="">-- မြို့နယ် ရွေးချယ်ပါ --</option>
                            <optgroup label="── Zone 1 ── 2,000 MMK (မြို့ပြလယ်)">
                                <option value="Kyauktada">ကျောက်တံတား (Kyauktada)</option>
                                <option value="Pabedan">ပန်းဘဲတန်း (Pabedan)</option>
                                <option value="Lanmadaw">လမ်းမတော် (Lanmadaw)</option>
                                <option value="Latha">လသာ (Latha)</option>
                                <option value="Botahtaung">ဗိုလ်တထောင် (Botahtaung)</option>
                                <option value="Pazundaung">ပုဇွန်တောင် (Pazundaung)</option>
                                <option value="Mingalar Taung Nyunt">မင်္ဂလာတောင်ညွှန့် (Mingalar Taung Nyunt)</option>
                                <option value="Ahlone">အလုံ (Ahlone)</option>
                            </optgroup>
                            <optgroup label="── Zone 2 ── 3,000 MMK (မြို့အလယ်)">
                                <option value="Kamaryut">ကမာရွတ် (Kamaryut)</option>
                                <option value="Bahan">ဗဟန်း (Bahan)</option>
                                <option value="Tamwe">တာမွေ (Tamwe)</option>
                                <option value="Dagon">ဒဂုံ (Dagon)</option>
                                <option value="Yankin">ရန်ကင်း (Yankin)</option>
                                <option value="Sanchaung">စမ်းချောင်း (Sanchaung)</option>
                                <option value="Hlaing">လှိုင် (Hlaing)</option>
                                <option value="Mayangone">မရမ်းကုန်း (Mayangone)</option>
                                <option value="Insein">အင်းစိန် (Insein)</option>
                                <option value="Thaketa">သာကေတ (Thaketa)</option>
                                <option value="Thingangyun">သင်္ဃန်းကျွန်း (Thingangyun)</option>
                            </optgroup>
                            <optgroup label="── Zone 3 ── 5,000 MMK (မြို့ပြင်)">
                                <option value="Shwepyithar">ရွှေပြည်သာ (Shwepyithar)</option>
                                <option value="Hlaingtharyar">လှိုင်သာယာ (Hlaingtharyar)</option>
                                <option value="North Okkalapa">မြောက်ဥက္ကလာပ (North Okkalapa)</option>
                                <option value="South Okkalapa">တောင်ဥက္ကလာပ (South Okkalapa)</option>
                                <option value="East Dagon">အရှေ့ဒဂုံ (East Dagon)</option>
                                <option value="North Dagon">မြောက်ဒဂုံ (North Dagon)</option>
                                <option value="South Dagon">တောင်ဒဂုံ (South Dagon)</option>
                                <option value="Dagon Seikkan">ဒဂုံဆိပ်ကမ်း (Dagon Seikkan)</option>
                            </optgroup>
                            <optgroup label="── Zone 4 ── 7,000 MMK (ဝေးသောမြို့နယ်)">
                                <option value="Dala">ဒလ (Dala)</option>
                                <option value="Twante">တွံတေး (Twante)</option>
                                <option value="Cocogyun">ကိုကိုးကျွန်း (Cocogyun) — 10,000 MMK</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Delivery Fee Badge -->
                    <div x-show="selectedTownship && deliveryFee > 0" x-transition class="mt-3 flex items-center gap-4 p-4 bg-orange-50 rounded-xl border border-orange-100">
                        <span class="text-3xl">🛵</span>
                        <div>
                            <p class="text-xs text-orange-700 font-bold uppercase tracking-wide">Delivery Fee</p>
                            <p class="text-2xl font-black text-orange-500"><span x-text="formatPrice(deliveryFee)"></span> <span class="text-base">MMK</span></p>
                        </div>
                        <span class="ml-auto text-xs font-black px-3 py-1.5 rounded-full bg-orange-100 text-orange-700" x-text="getZoneLabel()"></span>
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
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400">{{ old('delivery_address', Auth::check() ? (Auth::user()->detail_address ?? '') : '') }}</textarea>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            ဖုန်းနံပါတ် <span class="text-red-400">*</span>
                        </label>
                        <input type="tel" name="delivery_phone" required
                            value="{{ old('delivery_phone', Auth::check() ? (Auth::user()->phone_number ?? '') : '') }}"
                            placeholder="+95 9 ..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all placeholder-slate-400">
                    </div>

                    {{-- Payment Method (COD ONLY) --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            ငွေပေးချေနည်း <span class="text-red-400">*</span>
                        </label>
                        <input type="hidden" name="payment_method" value="cod">

                        <div class="bg-green-50 border-2 border-green-500 rounded-xl p-4 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                                💵
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 text-sm">Cash on Delivery (COD)</h4>
                                <p class="text-xs text-green-700 font-semibold mt-0.5">ပစ္စည်းရောက်မှ ရွှေငွေ/လက်ငင်း ပေးချေပါ — QR/ကြိုတင်ငွေပေးရန် မလိုပါ</p>
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
                    @auth
                        <button type="submit" @click="submitOrder($event)"
                            :disabled="isSubmitting || !canSubmit()"
                            class="w-full py-3.5 text-white font-black text-sm rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                            :class="(canSubmit() && !isSubmitting)
                                ? 'bg-orange-500 hover:bg-orange-600 active:bg-orange-700 shadow-orange-500/25'
                                : 'bg-slate-300 opacity-70 cursor-not-allowed'">
                            <template x-if="isSubmitting">
                                <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSubmitting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <span x-text="isSubmitting ? 'Order တင်နေပါသည်...' : (paymentMethod === 'cod' ? 'Order တင်မည်' : 'Order တင်မည် (ငွေချေပြီး)')"></span>
                            <span x-show="!isSubmitting">&mdash; <span x-text="formatPrice(total())"></span> MMK</span>
                        </button>
                    @else
                        <button type="button" @click="window.location.href='{{ route('login') }}'"
                            class="w-full py-3.5 text-white font-black text-sm rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer bg-orange-500 hover:bg-orange-600 active:bg-orange-700 shadow-orange-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Order တင်မည်
                        </button>
                    @endauth

                    <p class="text-xs text-center text-slate-400 leading-relaxed">
                        <span x-show="paymentMethod === 'cod'">ပစ္စည်းရောက်မှ ငွေချေရမည်</span>
                    </p>
                </form>

            </div>
        </div>
    </main>
</div>

</body>
</html>
