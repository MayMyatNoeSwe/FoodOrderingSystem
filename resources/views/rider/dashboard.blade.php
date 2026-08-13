<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rider Delivery Portal - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 selection:bg-orange-500 selection:text-white min-h-screen pb-16"
      x-data="{ activeTab: 'active' }">

    <!-- Top Header -->
    <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800 px-4 sm:px-6 py-4 shadow-xl">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-orange-500 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-orange-500/30">
                    🛵
                </div>
                <div>
                    <h1 class="text-lg font-black text-white leading-tight">Rider Delivery Portal</h1>
                    <p class="text-xs text-orange-400 font-bold">Welcome, {{ Auth::user()->name }} 👋</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-xs font-bold text-slate-400 hover:text-white transition-colors">
                    Storefront
                </a>
                <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-red-400 font-bold text-xs rounded-xl border border-slate-700 transition-all cursor-pointer">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 space-y-6">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs sm:text-sm font-bold rounded-2xl flex items-center gap-3 shadow-lg">
                <span class="text-lg">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4 text-center">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-lg">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active</span>
                <span class="text-2xl sm:text-3xl font-black text-purple-400 mt-1 block">{{ $stats['active_count'] }}</span>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-lg">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Delivered Today</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 mt-1 block">{{ $stats['completed_today'] }}</span>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-lg">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Delivery Fees</span>
                <span class="text-lg sm:text-xl font-black text-orange-400 mt-1.5 block font-mono">{{ number_format($stats['total_earnings_today']) }} MMK</span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 p-1.5 bg-slate-900 border border-slate-800 rounded-2xl">
            <button @click="activeTab = 'active'"
                    :class="activeTab === 'active' ? 'bg-orange-500 text-white font-black shadow-md' : 'text-slate-400 hover:text-white font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span>🛵 Active Deliveries</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-950/40">{{ $activeDeliveries->count() }}</span>
            </button>
            <button @click="activeTab = 'completed'"
                    :class="activeTab === 'completed' ? 'bg-orange-500 text-white font-black shadow-md' : 'text-slate-400 hover:text-white font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span>✅ Completed History</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-950/40">{{ $completedDeliveries->count() }}</span>
            </button>
        </div>

        <!-- ACTIVE DELIVERIES TAB -->
        <div x-show="activeTab === 'active'" class="space-y-4">
            @forelse($activeDeliveries as $order)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 transition-all hover:border-slate-700">
                    
                    <!-- Card Header -->
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-orange-500/20 border border-orange-500/30 text-orange-400 font-mono font-black text-xs rounded-full">
                                #{{ $order->order_number }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-medium">
                                {{ $order->created_at ? $order->created_at->diffForHumans() : '' }}
                            </span>
                        </div>

                        <!-- Status Badge -->
                        @if($order->status === 'delivering')
                            <span class="px-3 py-1 bg-purple-500/20 border border-purple-500/30 text-purple-400 font-black text-[11px] uppercase tracking-wider rounded-xl animate-pulse flex items-center gap-1.5">
                                <span>🛵 Out for Delivery</span>
                            </span>
                        @elseif($order->status === 'preparing')
                            <span class="px-3 py-1 bg-indigo-500/20 border border-indigo-500/30 text-indigo-400 font-black text-[11px] uppercase tracking-wider rounded-xl flex items-center gap-1.5">
                                <span>👨‍🍳 Preparing</span>
                            </span>
                        @else
                            <span class="px-3 py-1 bg-blue-500/20 border border-blue-500/30 text-blue-400 font-black text-[11px] uppercase tracking-wider rounded-xl">
                                <span>✓ Confirmed</span>
                            </span>
                        @endif
                    </div>

                    <!-- Customer & Delivery Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        
                        <!-- Customer Contact -->
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800/80 space-y-2">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">👤 Customer Details</p>
                            <p class="font-bold text-white text-sm">{{ $order->user->name ?? 'Guest Customer' }}</p>
                            
                            @if($order->delivery_phone)
                                <a href="tel:{{ $order->delivery_phone }}" 
                                   class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-400 font-bold rounded-xl text-xs transition-colors cursor-pointer">
                                    <span>📞 Call: {{ $order->delivery_phone }}</span>
                                </a>
                            @endif
                        </div>

                        <!-- Delivery Address -->
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800/80 space-y-1.5">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">📍 Delivery Address</p>
                            <p class="font-bold text-orange-400">{{ $order->delivery_township ?? 'Yangon' }}</p>
                            <p class="text-slate-300 leading-relaxed">{{ $order->delivery_address }}</p>
                        </div>
                    </div>

                    <!-- Payment & Amount Info -->
                    <div class="flex items-center justify-between bg-slate-950 p-3.5 rounded-2xl border border-slate-800/80 text-xs">
                        <div>
                            <span class="text-slate-400 font-medium">Payment Mode: </span>
                            <span class="font-bold text-white uppercase">
                                @if($order->payment_method === 'cod') 💵 Cash on Delivery
                                @elseif($order->payment_method === 'kbzpay') 📱 KBZPay
                                @elseif($order->payment_method === 'wavepay') 🌊 WavePay
                                @else {{ $order->payment_method }} @endif
                            </span>
                            <span class="ms-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-orange-500/20 text-orange-400' }}">
                                {{ $order->payment_status }}
                            </span>
                        </div>

                        <div class="text-right">
                            <span class="text-slate-400 font-medium">Total Cash to Collect: </span>
                            <span class="text-sm font-black text-emerald-400 font-mono">
                                {{ number_format($order->total_amount + $order->delivery_fee) }} MMK
                            </span>
                        </div>
                    </div>

                    <!-- Ordered Items List -->
                    <div class="bg-slate-950/60 p-3 rounded-2xl border border-slate-800/60 divide-y divide-slate-800/60 text-xs">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">🍽️ Ordered Items ({{ $order->orderItems->sum('quantity') }})</p>
                        @foreach($order->orderItems as $item)
                            <div class="py-1.5 flex items-center justify-between text-slate-300">
                                <span class="font-medium">• {{ $item->menuItem->name ?? 'Dish' }}</span>
                                <span class="font-mono text-orange-400 font-bold">x{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex items-center gap-3">
                        @if($order->status !== 'delivering')
                            <form method="POST" action="{{ route('rider.orders.start', $order) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold text-xs rounded-2xl shadow-lg shadow-purple-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <span>🛵 Start Delivery (Pick Up)</span>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('rider.orders.complete', $order) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-2xl shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>✅ Mark Delivered & Paid</span>
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center text-slate-500 space-y-3">
                    <div class="text-4xl">🛵</div>
                    <p class="font-bold text-slate-300 text-base">No Active Deliveries Right Now!</p>
                    <p class="text-xs text-slate-500">When orders are confirmed by admin, they will appear here for pickup and delivery.</p>
                </div>
            @endforelse
        </div>

        <!-- COMPLETED HISTORY TAB -->
        <div x-show="activeTab === 'completed'" class="space-y-4" style="display: none;">
            @forelse($completedDeliveries as $order)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg flex items-center justify-between gap-4 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-emerald-400">#{{ $order->order_number }}</span>
                            <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-400 font-bold rounded-full text-[10px]">✅ Delivered</span>
                        </div>
                        <p class="font-bold text-white mt-1">{{ $order->user->name ?? 'Customer' }} • {{ $order->delivery_township }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $order->updated_at ? $order->updated_at->format('M d, Y • h:i A') : '' }}</p>
                    </div>

                    <div class="text-right">
                        <p class="font-black text-emerald-400 font-mono text-sm">{{ number_format($order->total_amount + $order->delivery_fee) }} MMK</p>
                        <p class="text-[10px] text-slate-500 font-medium">Fee: {{ number_format($order->delivery_fee) }} MMK</p>
                    </div>
                </div>
            @empty
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center text-slate-500 space-y-2">
                    <div class="text-3xl">🎉</div>
                    <p class="font-bold text-slate-300">No Completed Deliveries Yet</p>
                </div>
            @endforelse
        </div>

    </main>

</body>
</html>
