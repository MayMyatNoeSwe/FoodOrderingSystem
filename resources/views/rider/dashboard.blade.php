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
    <script>
        window.riderPortalApp = function(initialTab) {
            return {
                activeTab: initialTab || 'available',
                openOrdersCount: {{ $availableOrders->count() }},
                proofModalOpen: false,
                proofModalSrc: '',
                init: function() {
                    // Auto-poll for new unassigned orders every 4 seconds
                    var self = this;
                    setInterval(function() {
                        fetch('{{ route('admin.orders.json_list') }}')
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data && data.orders) {
                                    // Check if there are confirmed orders without rider
                                    var currentUnassigned = data.orders.filter(function(o) {
                                        return (o.status === 'confirmed' || o.status === 'preparing') && !o.rider_id;
                                    }).length;
                                    if (currentUnassigned !== self.openOrdersCount) {
                                        window.location.reload();
                                    }
                                }
                            })
                            .catch(function() {});
                    }, 4000);
                }
            };
        };
    </script>
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 selection:bg-orange-500 selection:text-white min-h-screen pb-16"
      x-data="riderPortalApp('{{ $availableOrders->count() > 0 ? 'available' : ($activeDeliveries->count() > 0 ? 'active' : 'available') }}')">

    <!-- Top Header -->
    <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800 px-4 sm:px-6 py-4 shadow-xl">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-orange-500/30">
                    🛵
                </div>
                <div>
                    <h1 class="text-lg font-black text-white leading-tight">Rider Delivery Portal</h1>
                    <p class="text-xs text-orange-400 font-bold">Welcome, {{ $rider->name }} 👋</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-language-switcher variant="compact" />
                <a href="{{ route('home') }}" class="text-xs font-bold text-slate-400 hover:text-white transition-colors">
                    {{ __('View Storefront') }}
                </a>
                <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-red-400 font-bold text-xs rounded-xl border border-slate-700 transition-all cursor-pointer">
                        {{ __('Log Out') }}
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

        @if(session('error'))
            <div class="p-4 bg-red-500/10 border border-red-500/30 text-red-400 text-xs sm:text-sm font-bold rounded-2xl flex items-center gap-3 shadow-lg">
                <span class="text-lg">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4 text-center">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-lg relative overflow-hidden">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ready for Pick Up</span>
                <span class="text-2xl sm:text-3xl font-black text-amber-400 mt-1 block">{{ $availableOrders->count() }}</span>
                @if($availableOrders->count() > 0)
                    <span class="absolute top-2 right-2 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                @endif
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-lg">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">My Active</span>
                <span class="text-2xl sm:text-3xl font-black text-purple-400 mt-1 block">{{ $activeDeliveries->count() }}</span>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-lg">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Delivered Today</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 mt-1 block">{{ $stats['completed_today'] }}</span>
            </div>
        </div>

        <!-- Navigation Tabs (3 Tabs: Open Orders Pool, My Active, Completed) -->
        <div class="flex items-center gap-2 p-1.5 bg-slate-900 border border-slate-800 rounded-2xl">
            <button @click="activeTab = 'available'"
                    :class="activeTab === 'available' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-400 hover:text-white font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer relative">
                <span>📢 Open Pickups</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $availableOrders->count() > 0 ? 'bg-amber-900 text-white font-black animate-pulse' : 'bg-slate-950/40 text-slate-400' }}">
                    {{ $availableOrders->count() }}
                </span>
            </button>

            <button @click="activeTab = 'active'"
                    :class="activeTab === 'active' ? 'bg-orange-500 text-white font-black shadow-md' : 'text-slate-400 hover:text-white font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span>🛵 My Deliveries</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-950/40">{{ $activeDeliveries->count() }}</span>
            </button>

            <button @click="activeTab = 'completed'"
                    :class="activeTab === 'completed' ? 'bg-orange-500 text-white font-black shadow-md' : 'text-slate-400 hover:text-white font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span>✅ History</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-950/40">{{ $completedDeliveries->count() }}</span>
            </button>
        </div>

        <!-- 1. OPEN PICKUPS TAB (ORDERS WAITING FOR RIDER) -->
        <div x-show="activeTab === 'available'" class="space-y-4">
            @if($availableOrders->count() > 0)
                <div class="p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-2xl flex items-center justify-between text-xs text-amber-300">
                    <span class="flex items-center gap-2 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                        <span>{{ $availableOrders->count() }} new order(s) approved by kitchen & ready for rider pickup!</span>
                    </span>
                    <span class="text-[11px] opacity-80">First Come First Serve</span>
                </div>
            @endif

            @forelse($availableOrders as $order)
                <div class="bg-slate-900 border-2 border-amber-500/40 hover:border-amber-500 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 transition-all">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-amber-500/20 border border-amber-500/40 text-amber-400 font-mono font-black text-xs rounded-full">
                                #{{ $order->order_number }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-medium">
                                Approved: {{ $order->updated_at ? $order->updated_at->diffForHumans() : 'Just now' }}
                            </span>
                        </div>

                        <span class="px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-black text-[11px] uppercase tracking-wider rounded-xl animate-pulse flex items-center gap-1.5">
                            <span>🍳 Ready for Pickup</span>
                        </span>
                    </div>

                    <!-- Delivery & Customer Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800 space-y-1.5">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">📍 Delivery Destination</p>
                            <p class="font-bold text-orange-400 text-sm">{{ $order->delivery_township ?? 'Yangon' }}</p>
                            <p class="text-slate-300 leading-relaxed">{{ $order->delivery_address }}</p>
                        </div>

                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800 space-y-1.5">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">👤 Customer & Payment</p>
                            <p class="font-bold text-white">{{ $order->user->name ?? 'Customer' }}</p>
                            <div class="flex items-center justify-between pt-1">
                                <span class="text-slate-400 uppercase font-semibold">
                                    @if($order->payment_method === 'cod') 💵 Cash on Delivery
                                    @elseif($order->payment_method === 'kbzpay') 📱 KBZPay
                                    @elseif($order->payment_method === 'wavepay') 🌊 WavePay
                                    @else {{ $order->payment_method }} @endif
                                </span>
                                <span class="font-black text-emerald-400 font-mono text-sm">
                                    {{ number_format($order->total_amount) }} MMK
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Ordered Items Summary -->
                    <div class="bg-slate-950/60 p-3 rounded-2xl border border-slate-800/60 text-xs text-slate-300 flex items-center justify-between">
                        <span>🍽️ <strong>{{ $order->orderItems->sum('quantity') }} items:</strong> {{ $order->orderItems->map(fn($i) => $i->menuItem->name ?? 'Dish')->take(3)->implode(', ') }}</span>
                        <span class="text-orange-400 font-bold">+{{ number_format($order->delivery_fee) }} MMK Fee</span>
                    </div>

                    <!-- Claim / Pickup CTA Button -->
                    <form method="POST" action="{{ route('rider.orders.pickup', $order) }}">
                        @csrf
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 active:scale-95 text-slate-950 font-black text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span class="text-base">🛵</span>
                            <span>Accept & Pick Up This Order</span>
                        </button>
                    </form>

                </div>
            @empty
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center text-slate-500 space-y-3">
                    <div class="text-4xl">🛵</div>
                    <p class="font-bold text-slate-300 text-base">No Open Orders Waiting for Pickup</p>
                    <p class="text-xs text-slate-500">When customers order and admin approves, available orders will appear here in real-time!</p>
                </div>
            @endforelse
        </div>

        <!-- 2. MY ACTIVE DELIVERIES TAB -->
        <div x-show="activeTab === 'active'" class="space-y-4" style="display: none;">
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
                                <span>👨‍🍳 Kitchen Preparing</span>
                            </span>
                        @else
                            <span class="px-3 py-1 bg-blue-500/20 border border-blue-500/30 text-blue-400 font-black text-[11px] uppercase tracking-wider rounded-xl">
                                <span>✓ Picked Up</span>
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
                            <span class="text-slate-400 font-medium">Cash to Collect: </span>
                            <span class="text-sm font-black text-emerald-400 font-mono">
                                {{ number_format($order->total_amount) }} MMK
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

                    <!-- Action Buttons & Delivery Proof Section -->
                    <div class="pt-2">
                        @if($order->status !== 'delivering')
                            <form method="POST" action="{{ route('rider.orders.start', $order) }}">
                                @csrf
                                <button type="submit" class="w-full py-3.5 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold text-xs rounded-2xl shadow-lg shadow-purple-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <span>🛵 Start Delivery Route</span>
                                </button>
                            </form>
                        @else
                            <!-- PROOF OF DELIVERY FORM WITH PHOTO -->
                            <form method="POST" action="{{ route('rider.orders.complete', $order) }}" enctype="multipart/form-data" 
                                  x-data="{ photoPreview: null, isUploading: false }" 
                                  @submit="isUploading = true"
                                  class="space-y-3 p-4 bg-slate-950 rounded-2xl border border-purple-500/40 shadow-inner">
                                @csrf
                                
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold text-slate-200 flex items-center gap-1.5">
                                            <span>📸</span>
                                            <span>သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိကြောင်း ဓာတ်ပုံ (Photo Proof)</span>
                                        </label>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 font-bold">Photo Verification</span>
                                    </div>

                                    <!-- Camera & File capture box -->
                                    <div class="relative border-2 border-dashed border-slate-700 hover:border-purple-400 rounded-2xl p-4 text-center transition-all bg-slate-900/60 group cursor-pointer">
                                        <input type="file" name="delivery_proof_photo" accept="image/*" capture="environment" required
                                               @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }"
                                               class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                                        
                                        <template x-if="!photoPreview">
                                            <div class="py-2 space-y-1.5 pointer-events-none">
                                                <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center mx-auto text-xl group-hover:scale-110 transition-transform">
                                                    📷
                                                </div>
                                                <p class="text-xs font-bold text-white">Tap to Take or Upload Delivery Photo</p>
                                                <p class="text-[10px] text-slate-400">သုံးစွဲသူထံ အစားအသောက် ပေးအပ်သည့် ဓာတ်ပုံ ရိုက်ကူးပါ</p>
                                            </div>
                                        </template>

                                        <template x-if="photoPreview">
                                            <div class="relative inline-block">
                                                <img :src="photoPreview" alt="Delivery Proof" class="max-h-44 mx-auto rounded-xl border border-purple-500 object-cover shadow-xl">
                                                <span class="absolute bottom-2 right-2 px-2.5 py-1 bg-emerald-600 text-white font-black text-[10px] rounded-lg shadow-lg flex items-center gap-1">
                                                    ✓ Photo Ready
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <button type="submit" 
                                        :disabled="isUploading"
                                        class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 active:scale-98 text-white font-black text-xs rounded-xl shadow-lg shadow-emerald-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <template x-if="isUploading">
                                        <span>⏳ Uploading & Confirming...</span>
                                    </template>
                                    <template x-if="!isUploading">
                                        <span>✅ သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိပြီး အတည်ပြုမည်</span>
                                    </template>
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            @empty
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center text-slate-500 space-y-3">
                    <div class="text-4xl">🛵</div>
                    <p class="font-bold text-slate-300 text-base">No Active Deliveries Right Now!</p>
                    <p class="text-xs text-slate-500">Pick up orders from the <strong>Open Pickups</strong> tab to start delivering.</p>
                </div>
            @endforelse
        </div>

        <!-- 3. COMPLETED HISTORY TAB -->
        <div x-show="activeTab === 'completed'" class="space-y-4" style="display: none;">
            @forelse($completedDeliveries as $order)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-emerald-400">#{{ $order->order_number }}</span>
                            <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-400 font-bold rounded-full text-[10px]">✅ Delivered</span>
                        </div>
                        <p class="font-bold text-white mt-1">{{ $order->user->name ?? 'Customer' }} • {{ $order->delivery_township }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $order->updated_at ? $order->updated_at->format('M d, Y • h:i A') : '' }}</p>
                        
                        @if($order->delivery_proof_photo)
                            <div class="mt-2 flex items-center gap-2">
                                <button type="button" @click="proofModalSrc = '{{ asset($order->delivery_proof_photo) }}'; proofModalOpen = true;"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-500/20 hover:bg-purple-500/30 border border-purple-500/40 text-purple-300 text-[11px] font-bold rounded-xl transition-colors cursor-pointer">
                                    <span>📸 View Photo Proof (သက်သေဓာတ်ပုံ)</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="text-right sm:self-center">
                        <p class="font-black text-emerald-400 font-mono text-sm">{{ number_format($order->total_amount) }} MMK</p>
                        <p class="text-[10px] text-slate-500 font-medium">Earned Fee: {{ number_format($order->delivery_fee) }} MMK</p>
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

    <!-- Photo Proof Viewer Modal -->
    <div x-show="proofModalOpen" x-transition class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 max-w-lg w-full relative shadow-2xl space-y-3" @click.outside="proofModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📸</span>
                    <h3 class="font-black text-white text-sm">Delivery Proof Photo (သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိမှု ဓာတ်ပုံ)</h3>
                </div>
                <button @click="proofModalOpen = false" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white font-bold flex items-center justify-center cursor-pointer">✕</button>
            </div>
            <img :src="proofModalSrc" alt="Delivery Proof" class="w-full h-auto rounded-2xl border border-slate-800 max-h-[70vh] object-contain mx-auto">
        </div>
    </div>

</body>
</html>
