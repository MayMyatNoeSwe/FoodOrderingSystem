<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Your Cart — {{ config('app.name', 'FoodOrder') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-orange-500 selection:text-white">

<div x-data="cartApp()" x-init="init()" class="min-h-screen">

    <!-- ===== NAVBAR ===== -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 shadow-sm">
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
                <div class="flex items-center gap-3">
                    <a href="/" class="text-sm font-semibold text-slate-600 hover:text-orange-500 transition-colors">&larr; Back to Menu</a>
                    @auth
                        <span class="text-xs text-slate-400">|</span>
                        <span class="text-sm font-semibold text-slate-700">{{ Auth::user()->name }}</span>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900">Your Cart &#128;&#115;</h1>
            <p class="text-slate-500 text-sm mt-1">Review your items and place your order</p>
        </div>

        <!-- ===== EMPTY CART STATE ===== -->
        <div x-show="items.length === 0" x-transition class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center text-5xl mb-6 shadow-inner">
                &#128;&#115;
            </div>
            <h2 class="text-2xl font-black text-slate-900 mb-2">Your cart is empty</h2>
            <p class="text-slate-500 mb-6 max-w-sm">Looks like you haven't added anything yet. Browse our menu and find something delicious!</p>
            <a href="/" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">
                Browse Menu
            </a>
        </div>

        <!-- ===== CART WITH ITEMS ===== -->
        <div x-show="items.length > 0" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- LEFT: Cart Items -->
            <div class="lg:col-span-2 space-y-4">

                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-widest">
                        <span x-text="items.length"></span> Item(s)
                    </span>
                    <button @click="clearCart()" class="text-xs font-semibold text-red-400 hover:text-red-600 transition-colors cursor-pointer">
                        Clear All
                    </button>
                </div>

                <template x-for="(item, index) in items" :key="item.id">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-4 group hover:shadow-md transition-all">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 bg-slate-100">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-900 text-sm truncate" x-text="item.name"></h3>
                            <p class="text-orange-500 font-black text-sm mt-0.5">
                                <span x-text="formatPrice(item.price)"></span> MMK
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="item.category ?? ''"></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="decreaseQty(index)"
                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-600 text-slate-700 font-black text-lg flex items-center justify-center transition-all cursor-pointer">
                                &minus;
                            </button>
                            <span class="w-8 text-center font-bold text-slate-900 text-sm" x-text="item.qty"></span>
                            <button @click="increaseQty(index)"
                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-600 text-slate-700 font-black text-lg flex items-center justify-center transition-all cursor-pointer">
                                +
                            </button>
                        </div>
                        <div class="text-right shrink-0 min-w-[90px]">
                            <p class="text-xs text-slate-400 mb-0.5">Subtotal</p>
                            <p class="font-black text-slate-900 text-sm">
                                <span x-text="formatPrice(item.price * item.qty)"></span> MMK
                            </p>
                        </div>
                        <button @click="removeItem(index)"
                            class="w-8 h-8 rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-all cursor-pointer ml-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Promo Code -->
                <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-4 flex items-center gap-3">
                    <div class="w-9 h-9 bg-orange-50 rounded-xl flex items-center justify-center text-orange-500 shrink-0 text-lg">
                        &#127;&#183;
                    </div>
                    <input type="text" placeholder="Promo code (e.g. FIRST20)" class="flex-1 text-sm bg-transparent border-none outline-none placeholder-slate-400 text-slate-800">
                    <button class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-lg transition-all cursor-pointer">Apply</button>
                </div>
            </div>

            <!-- RIGHT: Summary + Checkout -->
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
                            <span class="font-semibold text-slate-900">2,000 MMK</span>
                        </div>
                        <div class="border-t border-slate-100 pt-3 mt-3 flex justify-between">
                            <span class="font-black text-slate-900">Total</span>
                            <span class="font-black text-orange-500 text-lg"><span x-text="formatPrice(subtotal() + 2000)"></span> MMK</span>
                        </div>
                    </div>
                </div>

                <!-- Checkout Form (Logged In) -->
                @auth
                <form id="checkout-form" method="POST" action="{{ route('user.orders.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="cart_items" id="cart_items_input">
                    <input type="hidden" name="total_amount" id="total_amount_input">
                    <input type="hidden" name="delivery_fee" value="2000">

                    <h2 class="text-base font-black text-slate-900">Delivery Details</h2>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Delivery Address <span class="text-red-400">*</span></label>
                        <textarea name="delivery_address" rows="2" required
                            placeholder="No. 123, Street Name, Township, City..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400"></textarea>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Phone Number <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="tel" name="delivery_phone" required
                                placeholder="+95 9 123 456 789"
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all placeholder-slate-400">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">Payment Method <span class="text-red-400">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="cod" class="peer sr-only" checked>
                                <div class="peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-500 border border-slate-200 rounded-xl p-2.5 text-center text-xs font-bold text-slate-600 hover:border-orange-300 transition-all select-none">
                                    💵<br>COD
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="kbzpay" class="peer sr-only">
                                <div class="peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-500 border border-slate-200 rounded-xl p-2.5 text-center text-xs font-bold text-slate-600 hover:border-orange-300 transition-all select-none">
                                    📱<br>KBZPay
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="wavepay" class="peer sr-only">
                                <div class="peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-500 border border-slate-200 rounded-xl p-2.5 text-center text-xs font-bold text-slate-600 hover:border-orange-300 transition-all select-none">
                                    🌊<br>WavePay
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Special Notes <span class="text-slate-300">(optional)</span></label>
                        <textarea name="notes" rows="2"
                            placeholder="No spicy, extra sauce, ring the bell..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none transition-all resize-none placeholder-slate-400"></textarea>
                    </div>

                    <button type="submit" @click="submitOrder($event)"
                        class="w-full py-3.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-black text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Place Order &mdash; <span x-text="formatPrice(subtotal() + 2000)"></span> MMK
                    </button>

                    <p class="text-xs text-center text-slate-400">By placing an order you agree to our delivery policy.</p>
                </form>

                @else
                <!-- Guest -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center">
                    <div class="text-4xl mb-3">🔐</div>
                    <h3 class="font-bold text-slate-900 mb-1">Login to Checkout</h3>
                    <p class="text-sm text-slate-500 mb-4">You need to be logged in to place an order.</p>
                    <a href="{{ route('login') }}" class="block w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all text-center">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="block mt-2 text-xs text-slate-500 hover:text-orange-500 transition-colors">
                        Don't have an account? Register
                    </a>
                </div>
                @endauth

            </div>
        </div>
    </main>
</div>

<script>
function cartApp() {
    return {
        items: [],

        init() {
            const stored = localStorage.getItem('foodorder_cart');
            this.items = stored ? JSON.parse(stored) : [];
        },

        save() {
            localStorage.setItem('foodorder_cart', JSON.stringify(this.items));
        },

        increaseQty(index) {
            this.items[index].qty++;
            this.save();
        },

        decreaseQty(index) {
            if (this.items[index].qty > 1) {
                this.items[index].qty--;
                this.save();
            } else {
                this.removeItem(index);
            }
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.save();
        },

        clearCart() {
            if (confirm('Clear all items from cart?')) {
                this.items = [];
                this.save();
            }
        },

        subtotal() {
            return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        totalQty() {
            return this.items.reduce((sum, item) => sum + item.qty, 0);
        },

        formatPrice(num) {
            return Number(num).toLocaleString();
        },

        submitOrder(event) {
            if (this.items.length === 0) {
                event.preventDefault();
                alert('Your cart is empty!');
                return;
            }
            document.getElementById('cart_items_input').value = JSON.stringify(this.items);
            document.getElementById('total_amount_input').value = this.subtotal() + 2000;
        }
    };
}

// Global helper so welcome page buttons can call window.addToCart(item)
window.addToCart = function(item) {
    const cart = JSON.parse(localStorage.getItem('foodorder_cart') || '[]');
    const existing = cart.find(i => i.id === item.id);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ ...item, qty: 1 });
    }
    localStorage.setItem('foodorder_cart', JSON.stringify(cart));
};
</script>

</body>
</html>
