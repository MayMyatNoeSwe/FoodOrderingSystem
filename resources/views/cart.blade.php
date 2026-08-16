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

        const liveItemStocks = {!! json_encode(\App\Models\MenuItem::pluck('stock', 'id')->all()) !!};

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

                getItemStock(item) {
                    if (liveItemStocks && liveItemStocks[item.id] !== undefined && liveItemStocks[item.id] !== null) {
                        return Number(liveItemStocks[item.id]);
                    }
                    if (item.stock !== undefined && item.stock !== null) {
                        return Number(item.stock);
                    }
                    return 999;
                },

                isMaxStock(item) {
                    const maxStock = this.getItemStock(item);
                    return item.qty >= maxStock;
                },

                init() {
                    try {
                        const stored = localStorage.getItem('foodorder_cart');
                        if (stored && stored !== 'undefined') {
                            const parsed = JSON.parse(stored);
                            this.items = Array.isArray(parsed) ? parsed.map(item => {
                                const maxStock = this.getItemStock(item);
                                item.stock = maxStock;
                                if (item.qty > maxStock) {
                                    item.qty = Math.max(1, maxStock);
                                }
                                return item;
                            }) : [];
                            this.save();
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

                increaseQty(index) {
                    const item = this.items[index];
                    const maxStock = this.getItemStock(item);
                    if (item.qty < maxStock) {
                        item.qty++;
                        this.save();
                    } else {
                        alert('Cannot add more. Available stock limit for "' + item.name + '" is ' + maxStock + '.');
                    }
                },

                decreaseQty(index) {
                    if (this.items[index].qty > 1) { this.items[index].qty--; this.save(); }
                    else { this.removeItem(index); }
                },

                removeItem(index) { this.items.splice(index, 1); this.save(); },

                clearCart() {
                    if (confirm('Are you sure you want to clear all items from your cart?')) { this.items = []; this.save(); }
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
                    if (fee <= 2000) return 'Zone 1 — Downtown';
                    if (fee <= 3000) return 'Zone 2 — Inner City';
                    if (fee <= 5000) return 'Zone 3 — Outer City';
                    return 'Zone 4 — Suburbs';
                },

                canSubmit() {
                    if (this.items.length === 0) return false;
                    if (!this.selectedTownship) return false;
                    for (let i of this.items) {
                        const maxStock = (i.stock !== undefined && i.stock !== null) ? Number(i.stock) : null;
                        if (maxStock !== null && i.qty > maxStock) return false;
                    }
                    return true;
                },

                submitOrder(event) {
                    event.preventDefault();
                    if (this.isSubmitting) return;

                    for (let i of this.items) {
                        const maxStock = (i.stock !== undefined && i.stock !== null) ? Number(i.stock) : null;
                        if (maxStock !== null && i.qty > maxStock) {
                            alert('Quantity for "' + i.name + '" exceeds available stock (' + maxStock + '). Please adjust your quantity.');
                            return;
                        }
                    }
                    if (!this.canSubmit()) {
                        if (!this.selectedTownship) {
                            alert('Please select a township first!');
                        }
                        return;
                    }

                    // Populate hidden fields
                    document.getElementById('cart_items_input').value        = JSON.stringify(this.items);
                    document.getElementById('total_amount_input').value      = this.total();
                    document.getElementById('delivery_fee_input').value      = this.deliveryFee;
                    document.getElementById('region_type_input').value       = 'Yangon';
                    document.getElementById('delivery_township_input').value = `Yangon — ${this.selectedTownship}`;

                    this.isSubmitting = true;

                    // Submit after Alpine finishes updating DOM (avoids disabled-button cancelling native submit)
                    this.$nextTick(() => {
                        document.getElementById('checkout-form').submit();
                    });
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

        @if(session('error'))
            <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 text-sm font-semibold rounded-2xl px-5 py-4 shadow-sm">
                <span class="text-xl">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900">🛒 Your Cart</h1>
            <p class="text-slate-500 text-sm mt-1">Select your township and payment method to place your order</p>
        </div>

        <!-- ===== EMPTY STATE ===== -->
        <div x-show="items.length === 0" x-transition class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center text-5xl mb-6 shadow-inner">🛒</div>
            <h2 class="text-2xl font-black text-slate-900 mb-2">Your Cart is Empty</h2>
            <p class="text-slate-500 mb-6 max-w-sm">Browse our menu and add your favorite delicious items!</p>
            <a href="/" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">Browse Menu</a>
        </div>

        <!-- ===== MAIN CART GRID ===== -->
        <div x-show="items.length > 0" x-transition class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            <!-- ============ LEFT COL: Items + Location ============ -->
            <div class="xl:col-span-2 space-y-5">

                <!-- Cart Items Header -->
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-widest">
                        <span x-text="items.length"></span> Item(s)
                    </span>
                    <button @click="clearCart()" class="text-xs font-semibold text-red-400 hover:text-red-600 transition-colors cursor-pointer">🗑 Clear Cart</button>
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
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <p class="text-xs text-slate-400" x-text="item.category ?? ''"></p>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                      :class="isMaxStock(item) ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'">
                                    Stock: <span x-text="getItemStock(item)"></span>
                                    <span x-show="isMaxStock(item)">(Max Reached)</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="decreaseQty(index)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-600 font-black text-lg flex items-center justify-center transition-all cursor-pointer">&minus;</button>
                            <span class="w-8 text-center font-bold text-slate-900 text-sm" x-text="item.qty"></span>
                            <button @click="increaseQty(index)"
                                    :disabled="isMaxStock(item)"
                                    :class="isMaxStock(item) ? 'opacity-30 cursor-not-allowed pointer-events-none bg-slate-200 text-slate-400' : 'hover:bg-orange-100 hover:text-orange-600 cursor-pointer bg-slate-100 text-slate-900'"
                                    class="w-8 h-8 rounded-lg font-black text-lg flex items-center justify-center transition-all">+</button>
                        </div>
                        <div class="text-right shrink-0 min-w-[80px]">
                            <p class="text-xs text-slate-400 mb-0.5">Subtotal</p>
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
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">📍 Select Delivery Township</h3>
                        <span class="text-xs font-bold px-3 py-1 bg-green-100 text-green-700 rounded-full">Yangon Region Only</span>
                    </div>

                    <!-- Fresh Food Notice -->
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
                        <span class="text-xl shrink-0">🥗</span>
                        <p class="text-xs text-emerald-800 font-semibold leading-relaxed">
                            For maximum freshness, we deliver <strong class="font-black text-emerald-950">exclusively to Yangon Region townships</strong>.
                        </p>
                    </div>

                    <!-- Yangon Township Select -->
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">Yangon Township <span class="text-red-400">*</span></label>
                        <select x-model="selectedTownship" @change="onTownshipChange()"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all bg-white font-medium">
                            <option value="">-- Select Township --</option>
                            <optgroup label="── Zone 1 ── 2,000 MMK (Downtown)">
                                <option value="Kyauktada">Kyauktada</option>
                                <option value="Pabedan">Pabedan</option>
                                <option value="Lanmadaw">Lanmadaw</option>
                                <option value="Latha">Latha</option>
                                <option value="Botahtaung">Botahtaung</option>
                                <option value="Pazundaung">Pazundaung</option>
                                <option value="Mingalar Taung Nyunt">Mingalar Taung Nyunt</option>
                                <option value="Ahlone">Ahlone</option>
                            </optgroup>
                            <optgroup label="── Zone 2 ── 3,000 MMK (Inner City)">
                                <option value="Kamaryut">Kamaryut</option>
                                <option value="Bahan">Bahan</option>
                                <option value="Tamwe">Tamwe</option>
                                <option value="Dagon">Dagon</option>
                                <option value="Yankin">Yankin</option>
                                <option value="Sanchaung">Sanchaung</option>
                                <option value="Hlaing">Hlaing</option>
                                <option value="Mayangone">Mayangone</option>
                                <option value="Insein">Insein</option>
                                <option value="Thaketa">Thaketa</option>
                                <option value="Thingangyun">Thingangyun</option>
                            </optgroup>
                            <optgroup label="── Zone 3 ── 5,000 MMK (Outer City)">
                                <option value="Shwepyithar">Shwepyithar</option>
                                <option value="Hlaingtharyar">Hlaingtharyar</option>
                                <option value="North Okkalapa">North Okkalapa</option>
                                <option value="South Okkalapa">South Okkalapa</option>
                                <option value="East Dagon">East Dagon</option>
                                <option value="North Dagon">North Dagon</option>
                                <option value="South Dagon">South Dagon</option>
                                <option value="Dagon Seikkan">Dagon Seikkan</option>
                            </optgroup>
                            <optgroup label="── Zone 4 ── 7,000 MMK (Suburbs)">
                                <option value="Dala">Dala</option>
                                <option value="Twante">Twante</option>
                                <option value="Cocogyun">Cocogyun — 10,000 MMK</option>
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
                            <span>Subtotal (<span x-text="totalQty()"></span> items)</span>
                            <span class="font-semibold text-slate-900"><span x-text="formatPrice(subtotal())"></span> MMK</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Delivery Fee</span>
                            <span class="font-semibold" :class="deliveryFee > 0 ? 'text-slate-900' : 'text-slate-400'">
                                <span x-show="deliveryFee > 0" x-text="formatPrice(deliveryFee) + ' MMK'"></span>
                                <span x-show="deliveryFee === 0" class="text-xs">Select township</span>
                            </span>
                        </div>
                        <div class="border-t border-slate-100 pt-3 mt-2 flex justify-between">
                            <span class="font-black text-slate-900">Total Amount</span>
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

                    <h2 class="text-base font-black text-slate-900">Delivery Information</h2>

                    {{-- Full Address --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            Detailed Delivery Address <span class="text-red-400">*</span>
                        </label>
                        <textarea name="delivery_address" rows="2" required
                            placeholder="Building, street, ward/township details..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400">{{ old('delivery_address', Auth::check() ? (Auth::user()->detail_address ?? '') : '') }}</textarea>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                            Phone Number <span class="text-red-400">*</span>
                        </label>
                        <input type="tel" name="delivery_phone" required
                            value="{{ old('delivery_phone', Auth::check() ? (Auth::user()->phone_number ?? '') : '') }}"
                            placeholder="+95 9 ..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all placeholder-slate-400">
                    </div>

                    {{-- Payment Method (COD ONLY) --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">
                            Payment Method <span class="text-red-400">*</span>
                        </label>
                        <input type="hidden" name="payment_method" value="cod">

                        <div class="bg-green-50 border-2 border-green-500 rounded-xl p-4 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                                💵
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 text-sm">Cash on Delivery (COD)</h4>
                                <p class="text-xs text-green-700 font-semibold mt-0.5">Pay in cash when your order is delivered — No advance payment required</p>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Notes <span class="text-slate-300">(optional)</span></label>
                        <textarea name="notes" rows="2"
                            placeholder="Special instructions, extra sauce..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400"></textarea>
                    </div>

                    {{-- Submit Button --}}
                    @auth
                        <button type="button"
                            @click="submitOrder($event)"
                            :disabled="!canSubmit() || isSubmitting"
                            class="w-full py-3.5 text-white font-black text-sm rounded-xl shadow-lg transition-all flex items-center justify-center gap-2"
                            :class="(canSubmit() && !isSubmitting)
                                ? 'bg-orange-500 hover:bg-orange-600 active:bg-orange-700 shadow-orange-500/25 cursor-pointer'
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
                            <span x-text="isSubmitting ? 'Placing Order...' : 'Place Order (COD)'"></span>
                            <span x-show="!isSubmitting">&mdash; <span x-text="formatPrice(total())"></span> MMK</span>
                        </button>
                    @else
                        <button type="button" @click="window.location.href='{{ route('login') }}'"
                            class="w-full py-3.5 text-white font-black text-sm rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer bg-orange-500 hover:bg-orange-600 active:bg-orange-700 shadow-orange-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Place Order
                        </button>
                    @endauth

                    <p class="text-xs text-center text-slate-400 leading-relaxed">
                        <span x-show="paymentMethod === 'cod'">Pay cash upon delivery</span>
                    </p>
                </form>

            </div>
        </div>
    </main>
</div>

</body>
</html>
