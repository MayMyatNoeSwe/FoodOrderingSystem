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
    <x-storefront-navbar />

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                <span class="text-3xl">📦</span>
                {{ __('My Orders') }}
                <span class="text-base font-bold text-slate-400 ml-1">({{ $orders->count() }})</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5">{{ __('Track and manage all your past and active food orders') }}</p>
        </div>

        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-sm font-semibold rounded-2xl px-5 py-4 shadow-sm">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($orders->isEmpty())
            <!-- Empty State -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-16 text-center shadow-sm">
                <div class="w-24 h-24 bg-orange-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-5xl mb-6 mx-auto shadow-inner">📦</div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">{{ __('No orders placed yet') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-8 max-w-sm mx-auto">{{ __("Looks like you haven't added any dishes yet.") }}</p>
                <a href="/" class="px-8 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all inline-block">
                    {{ __('Explore Menu') }}
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
                                <a href="{{ route('customer.orders.show', $order) }}"
                                   class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-sm shadow-orange-500/20 transition-all whitespace-nowrap">
                                    Details &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="px-5 py-4">
                            <div class="flex flex-col lg:flex-row gap-5">

                                <!-- Left: Items Ordered -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Items Ordered</p>
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
                                            <p class="text-xs text-slate-400 italic">No item details available</p>
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
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Delivery Info</p>
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
                                    @php
                                        $subtotalCalc = $order->orderItems->sum('subtotal');
                                        if ($subtotalCalc == 0) {
                                            $subtotalCalc = $order->total_amount - ($order->delivery_fee ?? 0) - ($order->tax_amount ?? 0);
                                        }
                                        $taxCalc = $order->tax_amount > 0 ? $order->tax_amount : round($subtotalCalc * 0.05);
                                    @endphp
                                    <div class="bg-slate-50 rounded-xl p-3 space-y-1.5">
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <span>{{ __('Subtotal') }}</span>
                                            <span>{{ number_format($subtotalCalc) }} MMK</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <span>{{ __('Tax (5%)') }}</span>
                                            <span>+{{ number_format($taxCalc) }} MMK</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <span>{{ __('Delivery Fee') }}</span>
                                            <span>+{{ number_format($order->delivery_fee ?? 0) }} MMK</span>
                                        </div>
                                        <div class="border-t border-slate-200 pt-1.5 flex justify-between">
                                            <span class="text-xs font-black text-slate-900">{{ __('Total Amount') }}</span>
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
                                Track Order &rarr;
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </main>

</body>
</html>
