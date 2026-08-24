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
                deliveryAddress: @json(Auth::check() ? (string)(Auth::user()->detail_address ?? '') : ''),
                deliveryPhone: @json(Auth::check() ? (string)(Auth::user()->phone_number ?? '') : ''),
                phoneError: '',
                deliveryNotes: '',
                deliveryFee: 0,
                paymentMethod: 'cod',
                slipPreview: null,
                slipFileName: '',
                copiedAccount: false,
                isSubmitting: false,
                darkMode: localStorage.getItem('foodorder_theme') === 'dark',

                isPhoneValid() {
                    if (!this.deliveryPhone) return false;
                    const digits = this.deliveryPhone.replace(/\D/g, '');
                    if (digits.startsWith('959')) {
                        const local = digits.substring(3);
                        return local.length >= 7 && local.length <= 9;
                    }
                    if (digits.startsWith('09')) {
                        const local = digits.substring(2);
                        return local.length >= 7 && local.length <= 9;
                    }
                    if (digits.startsWith('9') && digits.length >= 8) {
                        const local = digits.substring(1);
                        return local.length >= 7 && local.length <= 9;
                    }
                    return digits.length >= 7 && digits.length <= 11;
                },

                onPhoneInput(event) {
                    let val = event.target.value || '';
                    // Allow only digits, +, spaces, and dashes
                    val = val.replace(/[^\d+\s-]/g, '');
                    
                    if (val.includes('+')) {
                        val = '+' + val.replace(/\+/g, '');
                    }
                    
                    this.deliveryPhone = val;
                    this.validatePhone();
                    this.saveDeliveryInfo();
                },

                validatePhone() {
                    if (!this.deliveryPhone || !this.deliveryPhone.trim()) {
                        this.phoneError = 'ဖုန်းနံပါတ် ထည့်သွင်းပေးပါ (Phone number is required)';
                        return false;
                    }
                    const digits = this.deliveryPhone.replace(/\D/g, '');
                    if (digits.length < 7) {
                        this.phoneError = 'ဖုန်းနံပါတ် တိုလွန်းပါသည် (အနည်းဆုံး ဂဏန်း ၇ လုံး)';
                        return false;
                    }
                    if (digits.length > 12) {
                        this.phoneError = 'ဖုန်းနံပါတ် ရှည်လွန်းပါသည် (အများဆုံး ဂဏန်း ၁၂ လုံး)';
                        return false;
                    }
                    this.phoneError = '';
                    return true;
                },

                copyAccountNumber(num) {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(num);
                        this.copiedAccount = true;
                        setTimeout(() => { this.copiedAccount = false; }, 2000);
                    }
                },

                handleSlipUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB limit. Please choose a smaller image.');
                        event.target.value = '';
                        return;
                    }
                    this.slipFileName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.slipPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                removeSlip() {
                    this.slipPreview = null;
                    this.slipFileName = '';
                    const input = document.getElementById('payment_screenshot_input');
                    if (input) input.value = '';
                },

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

                    // Restore delivery information from localStorage (gives priority to guest-entered information over DB defaults)
                    try {
                        const savedDelivery = localStorage.getItem('foodorder_delivery_info');
                        if (savedDelivery) {
                            const parsedInfo = JSON.parse(savedDelivery);
                            if (parsedInfo.township && typeof parsedInfo.township === 'string' && parsedInfo.township.trim() !== '') {
                                this.selectedTownship = parsedInfo.township.trim();
                            }
                            if (parsedInfo.address && typeof parsedInfo.address === 'string' && parsedInfo.address.trim() !== '') {
                                this.deliveryAddress = parsedInfo.address.trim();
                            }
                            if (parsedInfo.phone && typeof parsedInfo.phone === 'string' && parsedInfo.phone.trim() !== '') {
                                this.deliveryPhone = parsedInfo.phone.trim();
                            }
                            if (parsedInfo.paymentMethod) {
                                this.paymentMethod = parsedInfo.paymentMethod;
                            }
                            if (parsedInfo.notes) {
                                this.deliveryNotes = parsedInfo.notes;
                            }
                        }
                    } catch (e) {
                        console.error('Delivery info parse error:', e);
                    }

                    if (this.selectedTownship) {
                        this.onTownshipChange();
                    }
                },

                save() {
                    localStorage.setItem('foodorder_cart', JSON.stringify(this.items));
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                },

                saveDeliveryInfo() {
                    try {
                        localStorage.setItem('foodorder_delivery_info', JSON.stringify({
                            township: this.selectedTownship,
                            address: this.deliveryAddress,
                            phone: this.deliveryPhone,
                            paymentMethod: this.paymentMethod,
                            notes: this.deliveryNotes
                        }));
                    } catch (_) {}
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

                taxRate: 0.05,

                subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                taxAmount() {
                    return Math.round(this.subtotal() * this.taxRate);
                },

                totalQty() {
                    return this.items.reduce((sum, item) => sum + item.qty, 0);
                },

                total() {
                    return this.subtotal() + this.taxAmount() + (this.deliveryFee || 0);
                },

                formatPrice(num) {
                    return Number(num).toLocaleString();
                },

                onTownshipChange() {
                    this.deliveryFee = yangonFees[this.selectedTownship] || 0;
                    this.saveDeliveryInfo();
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
                    if (!this.deliveryAddress || !this.deliveryAddress.trim()) return false;
                    if (!this.isPhoneValid()) return false;
                    for (let i of this.items) {
                        const maxStock = (i.stock !== undefined && i.stock !== null) ? Number(i.stock) : null;
                        if (maxStock !== null && i.qty > maxStock) return false;
                    }
                    return true;
                },

                goToLogin() {
                    this.saveDeliveryInfo();
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent("{{ route('cart') }}");
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
                        } else if (!this.deliveryAddress || !this.deliveryAddress.trim()) {
                            alert('Please enter your full delivery address!');
                        } else if (!this.isPhoneValid()) {
                            this.validatePhone();
                            alert('ကျေးဇူးပြု၍ တရားဝင် မြန်မာဖုန်းနံပါတ် (+95 9...) ကို မှန်ကန်စွာ ထည့်သွင်းပေးပါ!');
                        }
                        return;
                    }

                    // Populate hidden fields
                    document.getElementById('cart_items_input').value        = JSON.stringify(this.items);
                    document.getElementById('total_amount_input').value      = this.total();
                    document.getElementById('delivery_fee_input').value      = this.deliveryFee;
                    document.getElementById('tax_amount_input').value        = this.taxAmount();
                    document.getElementById('region_type_input').value       = 'Yangon';
                    document.getElementById('delivery_township_input').value = `Yangon — ${this.selectedTownship}`;

                    this.isSubmitting = true;
                    this.saveDeliveryInfo();

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
            <h1 class="text-3xl font-black text-slate-900 dark:text-white">🛒 {{ __('Shopping Cart') }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Delivery Information') }} &amp; {{ __('Payment Method') }}</p>
        </div>

        <!-- ===== EMPTY STATE ===== -->
        <div x-show="items.length === 0" x-transition class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-24 h-24 bg-orange-50 dark:bg-slate-900 rounded-full flex items-center justify-center text-5xl mb-6 shadow-inner">🛒</div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">{{ __('Your Cart is Empty') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-6 max-w-sm">{{ __("Looks like you haven't added any dishes yet.") }}</p>
            <a href="/" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">{{ __('Start Ordering') }}</a>
        </div>

        <!-- ===== MAIN CART GRID ===== -->
        <div x-show="items.length > 0" x-transition class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            <!-- ============ LEFT COL: Items + Location ============ -->
            <div class="xl:col-span-2 space-y-5">

                <!-- Cart Items Header -->
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                        <span x-text="items.length"></span> {{ __('Ordered Items') }}
                    </span>
                    <button @click="clearCart()" class="text-xs font-semibold text-red-400 hover:text-red-600 transition-colors cursor-pointer">🗑 {{ __('Clear') }}</button>
                </div>

                <!-- Cart Item Cards -->
                <template x-for="(item, index) in items" :key="item.id">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-3.5 sm:p-4 group hover:shadow-md transition-all">
                        
                        <!-- ================= LAPTOP / DESKTOP VIEW (sm+) ================= -->
                        <div class="hidden sm:flex sm:items-center sm:gap-4">
                            <!-- Food Image -->
                            <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 bg-slate-100 dark:bg-slate-800">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>

                            <!-- Title & Pricing Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm lg:text-base truncate" x-text="item.name"></h3>
                                <p class="text-orange-500 font-black text-sm mt-0.5"><span x-text="formatPrice(item.price)"></span> MMK</p>
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    <p class="text-xs text-slate-400" x-text="item.category ?? ''"></p>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                          :class="isMaxStock(item) ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400'">
                                        Stock: <span x-text="getItemStock(item)"></span>
                                        <span x-show="isMaxStock(item)">(Max Reached)</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Quantity Stepper Controls -->
                            <div class="flex items-center gap-2 shrink-0">
                                <button @click="decreaseQty(index)" 
                                        type="button"
                                        class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-orange-100 hover:text-orange-600 dark:hover:bg-slate-700 font-black text-lg flex items-center justify-center transition-all cursor-pointer select-none">&minus;</button>
                                <span class="w-8 text-center font-bold text-slate-900 dark:text-white text-sm" x-text="item.qty"></span>
                                <button @click="increaseQty(index)"
                                        type="button"
                                        :disabled="isMaxStock(item)"
                                        :class="isMaxStock(item) ? 'opacity-30 cursor-not-allowed pointer-events-none bg-slate-200 text-slate-400' : 'hover:bg-orange-100 hover:text-orange-600 cursor-pointer bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white dark:hover:bg-slate-700'"
                                        class="w-8 h-8 rounded-lg font-black text-lg flex items-center justify-center transition-all select-none">+</button>
                            </div>

                            <!-- Subtotal -->
                            <div class="text-right shrink-0 min-w-[90px]">
                                <p class="text-xs text-slate-400 mb-0.5">{{ __('Subtotal') }}</p>
                                <p class="font-black text-slate-900 dark:text-white text-sm lg:text-base"><span x-text="formatPrice(item.price * item.qty)"></span> MMK</p>
                            </div>

                            <!-- Remove Item Button -->
                            <button @click="removeItem(index)" 
                                    type="button"
                                    title="Remove item"
                                    class="w-8 h-8 rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/40 flex items-center justify-center transition-all cursor-pointer ml-1 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- ================= MOBILE VIEW (< sm) ================= -->
                        <div class="sm:hidden space-y-3">
                            <!-- Top Details Row -->
                            <div class="flex items-start gap-3">
                                <!-- Food Image -->
                                <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>

                                <!-- Details: Name, Unit Price, Category, Stock -->
                                <div class="flex-1 min-w-0 pr-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-bold text-slate-900 dark:text-white text-sm leading-snug line-clamp-2" x-text="item.name"></h3>
                                        
                                        <!-- Remove Button -->
                                        <button @click="removeItem(index)" 
                                                type="button"
                                                title="Remove item"
                                                class="w-7 h-7 rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/40 flex items-center justify-center transition-all cursor-pointer shrink-0 -mt-1 -mr-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                        <span class="text-orange-500 font-black text-sm"><span x-text="formatPrice(item.price)"></span> MMK</span>
                                        <span class="text-xs text-slate-400" x-show="item.category" x-text="'• ' + item.category"></span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                              :class="isMaxStock(item) ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400'">
                                            Stock: <span x-text="getItemStock(item)"></span>
                                            <span x-show="isMaxStock(item)">(Max Reached)</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Controls Row: Stepper + Subtotal -->
                            <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 pt-2.5">
                                <!-- Qty Stepper -->
                                <div class="flex items-center gap-2">
                                    <button @click="decreaseQty(index)" 
                                            type="button"
                                            class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-orange-100 hover:text-orange-600 dark:hover:bg-slate-700 font-black text-lg flex items-center justify-center transition-all cursor-pointer select-none">&minus;</button>
                                    <span class="w-7 text-center font-bold text-slate-900 dark:text-white text-sm" x-text="item.qty"></span>
                                    <button @click="increaseQty(index)"
                                            type="button"
                                            :disabled="isMaxStock(item)"
                                            :class="isMaxStock(item) ? 'opacity-30 cursor-not-allowed pointer-events-none bg-slate-200 text-slate-400' : 'hover:bg-orange-100 hover:text-orange-600 cursor-pointer bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white dark:hover:bg-slate-700'"
                                            class="w-8 h-8 rounded-lg font-black text-lg flex items-center justify-center transition-all select-none">+</button>
                                </div>

                                <!-- Subtotal -->
                                <div class="text-right">
                                    <span class="text-[11px] text-slate-400 font-semibold mr-1">{{ __('Subtotal') }}:</span>
                                    <span class="font-black text-slate-900 dark:text-white text-sm"><span x-text="formatPrice(item.price * item.qty)"></span> MMK</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>

                <!-- ===== LOCATION SELECTION (YANGON ONLY) ===== -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">📍 {{ __('Select Township') }}</h3>
                        <span class="text-xs font-bold px-3 py-1 bg-green-100 text-green-700 rounded-full">Yangon Region Only</span>
                    </div>

                    <!-- Fresh Food Notice -->
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl flex items-center gap-3">
                        <span class="text-xl shrink-0">🥗</span>
                        <p class="text-xs text-emerald-800 dark:text-emerald-300 font-semibold leading-relaxed">
                            {{ __('100% Fresh & Clean') }} — <strong class="font-black text-emerald-950 dark:text-emerald-200">Yangon Region</strong>
                        </p>
                    </div>

                    <!-- Yangon Township Select -->
                    <div>
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">{{ __('Select Township') }} <span class="text-red-400">*</span></label>
                        <select x-model="selectedTownship" @change="onTownshipChange()"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium">
                            <option value="">-- {{ __('Select Township') }} --</option>
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
                    <div x-show="selectedTownship && deliveryFee > 0" x-transition class="mt-3 flex items-center gap-4 p-4 bg-orange-50 dark:bg-orange-950/30 rounded-xl border border-orange-100 dark:border-orange-900/40">
                        <span class="text-3xl">🛵</span>
                        <div>
                            <p class="text-xs text-orange-700 dark:text-orange-400 font-bold uppercase tracking-wide">{{ __('Delivery Fee') }}</p>
                            <p class="text-2xl font-black text-orange-500"><span x-text="formatPrice(deliveryFee)"></span> <span class="text-base">MMK</span></p>
                        </div>
                        <span class="ml-auto text-xs font-black px-3 py-1.5 rounded-full bg-orange-100 dark:bg-orange-900/60 text-orange-700 dark:text-orange-300" x-text="getZoneLabel()"></span>
                    </div>
                </div>

            </div>

            <!-- ============ RIGHT COL: Summary + Checkout ============ -->
            <div class="space-y-5">

                <!-- Order Summary -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                    <h2 class="text-base font-black text-slate-900 dark:text-white mb-4">{{ __('Order Summary') }}</h2>
                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>{{ __('Subtotal') }} (<span x-text="totalQty()"></span> items)</span>
                            <span class="font-semibold text-slate-900 dark:text-white"><span x-text="formatPrice(subtotal())"></span> MMK</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span class="flex items-center gap-1.5">
                                <span>{{ __('Tax (5%)') }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold uppercase">{{ __('Tax') }}</span>
                            </span>
                            <span class="font-semibold text-slate-900 dark:text-white">+<span x-text="formatPrice(taxAmount())"></span> MMK</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>{{ __('Delivery Fee') }}</span>
                            <span class="font-semibold" :class="deliveryFee > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-400'">
                                <span x-show="deliveryFee > 0" x-text="'+' + formatPrice(deliveryFee) + ' MMK'"></span>
                                <span x-show="deliveryFee === 0" class="text-xs">{{ __('Select Township') }}</span>
                            </span>
                        </div>
                        <div class="border-t border-slate-100 dark:border-slate-800 pt-3 mt-2 flex justify-between">
                            <span class="font-black text-slate-900 dark:text-white">{{ __('Total Amount') }}</span>
                            <span class="font-black text-orange-500 text-lg"><span x-text="formatPrice(total())"></span> MMK</span>
                        </div>
                    </div>
                </div>

                <!-- ===== CHECKOUT FORM ===== -->
                <form id="checkout-form" method="POST" action="{{ route('customer.orders.store') }}"
                    enctype="multipart/form-data"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    @csrf

                    {{-- Hidden fields injected by Alpine --}}
                    <input type="hidden" name="cart_items"        id="cart_items_input">
                    <input type="hidden" name="total_amount"      id="total_amount_input">
                    <input type="hidden" name="delivery_fee"      id="delivery_fee_input">
                    <input type="hidden" name="tax_amount"        id="tax_amount_input">
                    <input type="hidden" name="region_type"       id="region_type_input">
                    <input type="hidden" name="delivery_township" id="delivery_township_input">

                    <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Delivery Information') }}</h2>

                    {{-- Full Address --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">
                            {{ __('Delivery Address') }} <span class="text-red-400">*</span>
                        </label>
                        <textarea name="delivery_address" rows="2" required
                            x-model="deliveryAddress"
                            @input="saveDeliveryInfo()"
                            placeholder="{{ __('Enter your full address (Street, Ward, City)') }}"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100"></textarea>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">
                            {{ __('Contact Phone') }} <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="tel" 
                                   name="delivery_phone" 
                                   required
                                   x-model="deliveryPhone"
                                   @input="onPhoneInput($event)"
                                   @blur="validatePhone()"
                                   placeholder="+95 9... or 09..."
                                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all placeholder-slate-400 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium"
                                   :class="phoneError ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : (isPhoneValid() ? 'border-emerald-400 dark:border-emerald-600 focus:border-emerald-500' : '')">
                            
                            <!-- Valid Indicator Badge -->
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" x-show="isPhoneValid()">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60">
                                    ✓ Valid
                                </span>
                            </div>
                        </div>

                        <!-- Validation & Help Message -->
                        <p x-show="phoneError" x-text="phoneError" class="text-xs text-red-500 mt-1.5 font-semibold"></p>
                        <p x-show="!phoneError" class="text-[11px] text-slate-400 mt-1.5">
                            {{ __('Enter phone number') }} (<span class="font-mono text-orange-500 font-bold">+95 9</span> / <span class="font-mono font-bold text-slate-600 dark:text-slate-300">09</span>)
                        </p>
                    </div>

                    {{-- Payment Method Selector --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
                            {{ __('Payment Method') }} <span class="text-red-400">*</span>
                        </label>
                        <input type="hidden" name="payment_method" :value="paymentMethod">

                        <!-- Payment Method Option Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-3">
                            <!-- COD Option -->
                            <button type="button" 
                                    @click="paymentMethod = 'cod'; saveDeliveryInfo();"
                                    class="p-3 rounded-2xl border-2 text-left transition-all flex flex-col justify-between gap-2 cursor-pointer"
                                    :class="paymentMethod === 'cod' 
                                        ? 'border-green-500 bg-green-50/70 dark:bg-green-950/40 text-green-900 dark:text-green-200 shadow-sm' 
                                        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-700 text-slate-700 dark:text-slate-300'">
                                <div class="flex items-center justify-between">
                                    <span class="text-xl">💵</span>
                                    <span x-show="paymentMethod === 'cod'" class="w-4 h-4 rounded-full bg-green-500 text-white flex items-center justify-center text-[10px] font-black">✓</span>
                                </div>
                                <div>
                                    <p class="text-xs font-black">Cash on Delivery</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Pay cash upon arrival</p>
                                </div>
                            </button>

                            <!-- KBZPay Option -->
                            <button type="button" 
                                    @click="paymentMethod = 'kbzpay'; saveDeliveryInfo();"
                                    class="p-3 rounded-2xl border-2 text-left transition-all flex flex-col justify-between gap-2 cursor-pointer"
                                    :class="paymentMethod === 'kbzpay' 
                                        ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200 shadow-sm' 
                                        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-700 text-slate-700 dark:text-slate-300'">
                                <div class="flex items-center justify-between">
                                    <span class="text-xl">📱</span>
                                    <span x-show="paymentMethod === 'kbzpay'" class="w-4 h-4 rounded-full bg-blue-500 text-white flex items-center justify-center text-[10px] font-black">✓</span>
                                </div>
                                <div>
                                    <p class="text-xs font-black">KBZPay (KPay)</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Direct Wallet / Payslip</p>
                                </div>
                            </button>

                            <!-- WavePay Option -->
                            <button type="button" 
                                    @click="paymentMethod = 'wavepay'; saveDeliveryInfo();"
                                    class="p-3 rounded-2xl border-2 text-left transition-all flex flex-col justify-between gap-2 cursor-pointer"
                                    :class="paymentMethod === 'wavepay' 
                                        ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 shadow-sm' 
                                        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-700 text-slate-700 dark:text-slate-300'">
                                <div class="flex items-center justify-between">
                                    <span class="text-xl">🌊</span>
                                    <span x-show="paymentMethod === 'wavepay'" class="w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-black">✓</span>
                                </div>
                                <div>
                                    <p class="text-xs font-black">WavePay</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Wave Money / Payslip</p>
                                </div>
                            </button>
                        </div>

                        <!-- COD Detailed Info -->
                        <div x-show="paymentMethod === 'cod'" x-transition class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800/60 rounded-2xl p-3.5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-green-500 text-white flex items-center justify-center text-xl shadow-sm shrink-0">💵</div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-xs">{{ __('Cash on Delivery') }}</h4>
                                <p class="text-[11px] text-green-700 dark:text-green-400 font-medium">{{ __('Pay in cash directly to our rider upon receiving your meal.') }}</p>
                            </div>
                        </div>

                        <!-- Digital Wallet Transfer & Payslip Upload Box -->
                        <div x-show="paymentMethod === 'kbzpay' || paymentMethod === 'wavepay'" x-transition class="space-y-3">
                            <!-- Merchant Details Card -->
                            <div class="p-4 rounded-2xl border text-xs"
                                 :class="paymentMethod === 'kbzpay' 
                                    ? 'bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/40 dark:to-indigo-950/40 border-blue-200 dark:border-blue-800/60 text-blue-950 dark:text-blue-200' 
                                    : 'bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-950/40 dark:to-yellow-950/40 border-amber-200 dark:border-amber-800/60 text-amber-950 dark:text-amber-200'">
                                <div class="flex items-center justify-between pb-2 border-b"
                                     :class="paymentMethod === 'kbzpay' ? 'border-blue-200/60 dark:border-blue-800/40' : 'border-amber-200/60 dark:border-amber-800/40'">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base" x-text="paymentMethod === 'kbzpay' ? '📱' : '🌊'"></span>
                                        <span class="font-black" x-text="paymentMethod === 'kbzpay' ? 'KBZPay Transfer Details' : 'WavePay Transfer Details'"></span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                          :class="paymentMethod === 'kbzpay' ? 'bg-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-amber-200 text-amber-800 dark:bg-amber-900 dark:text-amber-300'">
                                        Merchant Pay
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2.5">
                                    <div>
                                        <p class="text-[10px] opacity-70 font-semibold uppercase">Account Name</p>
                                        <p class="font-black text-slate-900 dark:text-white">Food Express MM</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] opacity-70 font-semibold uppercase">Account Phone</p>
                                        <div class="flex items-center gap-1.5 font-black text-slate-900 dark:text-white font-mono">
                                            <span>09-987654321</span>
                                            <button type="button" @click="copyAccountNumber('09987654321')" class="text-[10px] px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded-md border shadow-xs hover:scale-105 transition-transform cursor-pointer">
                                                <span x-text="copiedAccount ? 'Copied! ✓' : 'Copy 📋'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2.5 pt-2 border-t flex items-center justify-between text-[11px] font-bold"
                                     :class="paymentMethod === 'kbzpay' ? 'border-blue-200/60 dark:border-blue-800/40' : 'border-amber-200/60 dark:border-amber-800/40'">
                                    <span>Exact Amount to Transfer:</span>
                                    <span class="text-sm font-black text-orange-600 dark:text-orange-400"><span x-text="formatPrice(total())"></span> MMK</span>
                                </div>
                            </div>

                            <!-- Payslip Upload Field -->
                            <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                        <span>🧾</span>
                                        <span>Upload Payslip / Transfer Screenshot (ငွေလွှဲပြေစာ)</span>
                                    </label>
                                    <span class="text-[10px] text-slate-400">Max 5MB (JPG/PNG)</span>
                                </div>

                                <!-- Custom File Input / Drop Box -->
                                <div class="relative">
                                    <input type="file" 
                                           id="payment_screenshot_input" 
                                           name="payment_screenshot" 
                                           accept="image/*"
                                           @change="handleSlipUpload($event)"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                                    <!-- Upload Placeholder when no image is selected -->
                                    <div x-show="!slipPreview" 
                                         class="border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-orange-400 dark:hover:border-orange-400 rounded-xl p-4 text-center transition-colors bg-white dark:bg-slate-800/50">
                                        <div class="text-2xl mb-1">📸</div>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Click or Drag &amp; Drop Payslip Screenshot</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Attach the successful transaction receipt from your wallet</p>
                                    </div>
                                </div>

                                <!-- Payslip Preview Container -->
                                <template x-if="slipPreview">
                                    <div class="flex items-center justify-between gap-3 p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200 shrink-0 bg-slate-100">
                                                <img :src="slipPreview" alt="Payslip Preview" class="w-full h-full object-cover">
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="slipFileName || 'Payment Screenshot'"></p>
                                                <p class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                                    <span>✓ Ready to attach</span>
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeSlip()" class="px-2.5 py-1 text-[11px] font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition-colors cursor-pointer shrink-0">
                                            Remove ✕
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">{{ __('Special Notes / Instructions') }} <span class="text-slate-300">(optional)</span></label>
                        <textarea name="notes" rows="2"
                            x-model="deliveryNotes"
                            @input="saveDeliveryInfo()"
                            placeholder="{{ __('Any allergy or delivery note (optional)') }}"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100"></textarea>
                    </div>

                    {{-- Submit Button --}}
                    @auth
                        <button type="button"
                            @click="submitOrder($event)"
                            :disabled="!canSubmit() || isSubmitting"
                            class="w-full relative group py-3.5 px-6 text-white font-black text-sm rounded-2xl transition-all duration-200 flex items-center justify-center gap-2.5 shadow-lg select-none"
                            :class="(canSubmit() && !isSubmitting)
                                ? 'bg-gradient-to-r from-orange-500 via-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-orange-500/25 hover:shadow-orange-500/35 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer'
                                : 'bg-slate-300 dark:bg-slate-800 text-slate-400 dark:text-slate-500 shadow-none cursor-not-allowed'">
                            
                            <template x-if="isSubmitting">
                                <svg class="w-5 h-5 animate-spin shrink-0 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSubmitting">
                                <span class="text-base leading-none" x-text="paymentMethod === 'cod' ? '🛵' : '🧾'"></span>
                            </template>
                            <span x-text="isSubmitting ? '{{ __('Placing Order...') }}' : (paymentMethod === 'cod' ? '{{ __('Place Order') }}' : '{{ __('Upload Payslip & Place Order') }}')"></span>
                        </button>
                    @else
                        <button type="button" 
                            @click="goToLogin()"
                            class="w-full relative group py-3.5 px-6 text-white font-black text-sm rounded-2xl transition-all duration-200 flex items-center justify-center gap-2.5 shadow-lg shadow-orange-500/25 hover:shadow-orange-500/35 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer bg-gradient-to-r from-orange-500 via-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 select-none">
                            <span class="text-base leading-none">🔑</span>
                            <span>{{ __('Log in to Place Order') }}</span>
                        </button>
                    @endauth

                    <p class="text-xs text-center text-slate-400 leading-relaxed">
                        <span x-show="paymentMethod === 'cod'">{{ __('Pay in cash when your food arrives') }}</span>
                        <span x-show="paymentMethod === 'kbzpay' || paymentMethod === 'wavepay'">{{ __('Verification takes 1-3 minutes after placing order') }}</span>
                    </p>
                </form>

            </div>
        </div>
    </main>
</div>

<x-scroll-to-top />

</body>
</html>
