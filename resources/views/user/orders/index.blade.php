<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Orders — {{ config('app.name', 'FoodOrder') }}</title>
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
</head>
<body x-data="{
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
    cartCount: (function() {
        try {
            const stored = localStorage.getItem('foodorder_cart');
            const items = stored ? JSON.parse(stored) : [];
            return Array.isArray(items) ? items.reduce((s,i) => s + (i.qty||0), 0) : 0;
        } catch(e) { return 0; }
    })()
}" class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 selection:bg-orange-500 selection:text-white min-h-screen">

    <!-- ===== NAVBAR ===== -->
    <header class="sticky top-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Brand Logo -->
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Food<span class="text-orange-500">Order</span></span>
                </a>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold">
                    <a href="/" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors">Home</a>
                    <a href="/#categories" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors">Categories</a>
                    <a href="/#menu" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors">Popular Menu</a>
                    <a href="/#features" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors">Why Us</a>
                </nav>

                <!-- Header Actions -->
                <div class="flex items-center space-x-3">
                    <!-- Theme Toggle Button -->
                    <button @click="toggleTheme()"
                            title="Toggle Theme"
                            class="p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all duration-200 cursor-pointer inline-flex items-center justify-center border border-slate-200/60 dark:border-slate-700/60">
                        <span x-show="!darkMode" class="text-base">🌙</span>
                        <span x-show="darkMode" class="text-base" style="display:none;">☀️</span>
                    </button>

                    <!-- Shopping Cart Button -->
                    <a href="{{ route('cart') }}" class="relative p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all duration-200 cursor-pointer inline-flex items-center justify-center border border-slate-200/60 dark:border-slate-700/60">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span
                            x-show="cartCount > 0"
                            x-text="cartCount"
                            class="absolute -top-1.5 -right-1.5 bg-orange-500 text-white text-xs font-black min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center shadow-md">
                        </span>
                    </a>

                    @if (Route::has('login'))
                        @auth
                            @if (Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-amber-500/25 transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <span>Admin Portal</span>
                                </a>
                            @endif

                            <!-- Logged-In User Profile Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" @click.outside="open = false" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 text-slate-800 font-bold text-xs sm:text-sm rounded-xl border border-slate-200 flex items-center gap-2 transition-all cursor-pointer">
                                    <div class="w-7 h-7 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-black shadow-sm">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Dropdown Menu Box -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                                    <div class="px-4 py-2 border-b border-slate-100">
                                        <p class="text-[11px] text-slate-400">Signed in as</p>
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                    
                                    <a href="{{ route('user.orders.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                        📦 My Orders (Order များ)
                                    </a>

                                    <a href="/#menu" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                        🍕 Explore Menu
                                    </a>

                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                        ⚙️ Profile Settings
                                    </a>

                                    <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')" class="border-t border-slate-100 mt-1 pt-1">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors cursor-pointer flex items-center justify-between">
                                            <span>Log Out</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-orange-500/25 transition-all">
                                Log in
                            </a>
                        @endauth
                    @endif

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
