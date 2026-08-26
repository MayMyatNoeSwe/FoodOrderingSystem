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
                        $paymentConfig = match(true) {
                            $order->payment_status === 'paid' && $order->payment_method === 'cod' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => '✓ Paid (Cash)'],
                            $order->payment_status === 'paid' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => '✓ Paid (Online)'],
                            $order->payment_status === 'pending_verification' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => '🔍 Verifying Slip'],
                            $order->payment_method === 'cod' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => '💵 COD (Unpaid)'],
                            default => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => '⏳ Unpaid'],
                        };
                        $isActive = in_array($order->status, ['pending', 'confirmed', 'preparing', 'delivering']);
                    @endphp

                    <div class="card-lift bg-white dark:bg-slate-900 rounded-2xl border {{ $isActive ? 'border-orange-200 dark:border-orange-900/60 shadow-orange-500/10' : 'border-slate-100 dark:border-slate-800' }} shadow-sm overflow-hidden">

                        <!-- Card Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 sm:px-6 py-4 {{ $isActive ? 'bg-orange-50/60 dark:bg-orange-950/20' : 'bg-slate-50/60 dark:bg-slate-800/40' }} border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3 flex-wrap">
                                <!-- Order Number -->
                                <span class="font-mono font-black text-slate-900 dark:text-white text-base tracking-tight">#{{ $order->order_number }}</span>

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

                        <!-- Card Body (Equal 2 Columns) -->
                        <div class="px-5 sm:px-6 py-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 items-start">

                                <!-- Left Column (50%): Items Ordered -->
                                <div class="space-y-3 min-w-0">
                                    <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-slate-800">
                                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Items Ordered') }}</p>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">{{ $order->orderItems->sum('quantity') }} {{ __('items') }}</span>
                                    </div>
                                    <div class="space-y-2.5">
                                        @forelse($order->orderItems as $item)
                                            <div class="p-2.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    @if($item->menuItem && $item->menuItem->image)
                                                        <img src="{{ asset($item->menuItem->image) }}"
                                                             alt="{{ $item->menuItem->name ?? 'Item' }}"
                                                             class="w-10 h-10 rounded-lg object-cover shrink-0 bg-slate-100 dark:bg-slate-800">
                                                    @else
                                                        <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-slate-800 flex items-center justify-center text-xl shrink-0">🍽️</div>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                                            {{ $item->menuItem->name ?? 'Item (Removed)' }}
                                                        </p>
                                                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                                                            {{ number_format($item->unit_price) }} MMK × {{ $item->quantity }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-black text-slate-900 dark:text-slate-100 font-mono shrink-0">
                                                    {{ number_format($item->subtotal) }} <span class="text-xs text-orange-500 font-bold">MMK</span>
                                                </span>
                                            </div>
                                        @empty
                                            <p class="text-xs text-slate-400 italic">No item details available</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Right Column (50%): Delivery Info + Financial Summary -->
                                <div class="space-y-4 min-w-0">
                                    <!-- Delivery Info -->
                                    <div class="space-y-1.5">
                                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-1 border-b border-slate-100 dark:border-slate-800">{{ __('Delivery Info') }}</p>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-start gap-1.5 pt-1">
                                            <span>📍</span>
                                            <span class="leading-relaxed font-bold text-slate-900 dark:text-white">{{ $order->delivery_township ?? 'Yangon' }}@if($order->delivery_address) — <span class="font-normal text-slate-600 dark:text-slate-400">{{ $order->delivery_address }}</span>@endif</span>
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                            <span>📞</span>
                                            <span class="font-mono">{{ $order->delivery_phone }}</span>
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
                                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3.5 space-y-2 border border-slate-100 dark:border-slate-800">
                                        <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ __('Subtotal') }}</span>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ number_format($subtotalCalc) }} MMK</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ __('Tax (5%)') }}</span>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">+{{ number_format($taxCalc) }} MMK</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ __('Delivery Fee') }}</span>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">+{{ number_format($order->delivery_fee ?? 0) }} MMK</span>
                                        </div>
                                        <div class="border-t border-slate-200 dark:border-slate-700 pt-2 flex justify-between items-center">
                                            <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Total Amount') }}</span>
                                            <span class="text-base font-black text-orange-500 font-mono">{{ number_format($order->total_amount) }} MMK</span>
                                        </div>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="flex items-center justify-between text-xs p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                        <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[11px]">{{ __('Payment Method') }}</span>
                                        <span class="font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase">
                                            @if($order->payment_method === 'cod')
                                                <span>💵</span> <span>COD (Cash)</span>
                                            @elseif($order->payment_method === 'kbzpay')
                                                <span>📱</span> <span class="text-blue-600 dark:text-blue-400">KBZPay</span>
                                            @elseif($order->payment_method === 'wavepay')
                                                <span>🌊</span> <span class="text-amber-600 dark:text-amber-400">WavePay</span>
                                            @else
                                                <span>💳</span> <span>{{ strtoupper($order->payment_method) }}</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Card Footer: Time ago & Actions -->
                        <div class="px-5 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/40 dark:bg-slate-800/20">
                            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ $order->created_at->diffForHumans() }}</span>
                            
                            <div class="flex items-center gap-3">
                                @if($order->status !== 'pending')
                                    <a href="{{ route('orders.payslip', $order) }}" target="_blank"
                                       class="text-xs font-bold text-[#D70F64] hover:underline flex items-center gap-1">
                                        <span>🧾</span>
                                        <span>{{ __('Digital Slip') }}</span>
                                    </a>
                                @endif
                                <a href="{{ route('customer.orders.show', $order) }}"
                                   class="text-xs font-bold text-orange-500 hover:text-orange-600 dark:text-orange-400 transition-colors flex items-center gap-1">
                                    <span>Track Order &rarr;</span>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </main>

</body>
</html>
