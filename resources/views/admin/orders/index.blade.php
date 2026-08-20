<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Real-Time Orders Dispatch - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteOrder(form, orderNumber) {
            Swal.fire({
                title: 'Delete Order #' + orderNumber + '?',
                html: `Are you sure you want to permanently delete order <strong class="text-orange-500">#${orderNumber}</strong>?<br><span class="text-xs text-slate-500 mt-1 block">This operation cannot be undone.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete Order',
                cancelButtonText: 'Cancel',
                background: '#ffffff',
                color: '#0f172a',
                customClass: {
                    popup: 'border border-slate-200 rounded-3xl shadow-2xl',
                    title: 'text-slate-900 font-bold text-lg',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-red-500/20 cursor-pointer',
                    cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs cursor-pointer'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }

        window.adminOrderPoller = function() {
            return {
                mobileMenuOpen: false,
                detailsModalOpen: false,
                rejectModalOpen: false,
                proofModalOpen: false,
                proofModalSrc: '',
                proofModalTitle: '',
                activeOrder: null,
                activeRejectOrder: null,
                activeRejectReason: 'Kitchen Busy',
                audioEnabled: true,
                statusStateMap: {},
                now: Date.now(),

                getRemainingSeconds: function(isoDate) {
                    if (!isoDate) return 0;
                    var t = new Date(isoDate).getTime();
                    var elapsed = Math.floor((this.now - t) / 1000);
                    return Math.max(0, 30 - elapsed);
                },

                init: function() {
                    var self = this;
                    setInterval(function() {
                        self.now = Date.now();
                    }, 1000);

                    setInterval(function() {
                        fetch('{{ route('admin.orders.json_list') }}')
                            .then(function(res) { return res.json(); })
                            .then(function(data) {
                                if (data && data.orders && data.orders.length > 0) {
                                    var hasChange = false;
                                    data.orders.forEach(function(o) {
                                        var k = 'ord_' + o.id;
                                        var v = o.status + '_' + (o.rider_id || 0) + '_' + o.payment_status;
                                        if (self.statusStateMap[k] && self.statusStateMap[k] !== v) {
                                            hasChange = true;
                                        }
                                        self.statusStateMap[k] = v;
                                    });
                                    if (hasChange && !self.detailsModalOpen && !self.rejectModalOpen && !self.proofModalOpen) {
                                        window.location.reload();
                                    }
                                }
                            })
                            .catch(function() {});
                    }, 3000);
                },

                playNotificationSound: function() {
                    if (!this.audioEnabled) return;
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15);
                        gain.gain.setValueAtTime(0.3, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.5);
                    } catch(e) { console.log('Audio error:', e); }
                },

                openDetailsModal: function(order) {
                    this.activeOrder = order;
                    this.detailsModalOpen = true;
                },

                openProofPhoto: function(src, title) {
                    this.proofModalSrc = src;
                    this.proofModalTitle = title || 'Delivery Proof Photo';
                    this.proofModalOpen = true;
                },

                openRejectModal: function(orderId, orderNum) {
                    this.activeRejectOrder = { id: orderId, number: orderNum };
                    this.rejectModalOpen = true;
                }
            };
        };
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="adminOrderPoller()">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="orders" />

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Topbar Header -->
            <header class="bg-white/90 backdrop-blur-md sticky top-0 z-30 border-b border-slate-200/80 px-6 py-4 flex items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger Toggle -->
                    <button @click="mobileMenuOpen = true" class="md:hidden p-2 text-slate-500 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                            <span>Real-Time Order Dispatch & Operations Hub</span>
                            <span class="bg-orange-50 text-orange-600 border border-orange-200 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                <span>Live Dispatch Queue</span>
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">Monitor incoming customer orders with sound alert notifications and 1-click accept/reject actions</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notification Alarm Sound Toggle Button -->
                    <button @click="audioEnabled = !audioEnabled; if(audioEnabled) playNotificationSound();" 
                            :class="audioEnabled ? 'bg-orange-50 text-orange-600 border-orange-200 hover:bg-orange-100' : 'bg-slate-100 text-slate-500 border-slate-200'"
                            class="px-3.5 py-2 text-xs font-bold rounded-xl border transition-all flex items-center gap-2 cursor-pointer shadow-sm">
                        <span x-text="audioEnabled ? '🔔 Sound Alarm ON' : '🔕 Sound Muted'"></span>
                    </button>

                    <a href="{{ route('home') }}" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-all flex items-center gap-2">
                        <span>Storefront</span>
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-6 overflow-y-auto">

                <!-- Success Alert Toast -->
                @if(session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: @json(session('success')),
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                                background: '#ffffff',
                                color: '#0f172a',
                                customClass: {
                                    popup: 'border border-emerald-200 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                <!-- Stat Metric Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Metric 1: Total Orders -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-base border border-orange-100">
                                📦
                            </div>
                        </div>
                        <div class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalOrdersCount) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
                            <span>All-time customer transactions</span>
                        </div>
                    </div>

                    <!-- Metric 2: Active & Pending Orders -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active In-Progress</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base border border-amber-100">
                                ⏳
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-600 mt-2">{{ number_format($activeCount) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Pending, Preparing & Delivery</div>
                    </div>

                    <!-- Metric 3: Completed Orders -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Completed Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-100">
                                ✅
                            </div>
                        </div>
                        <div class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($completedCount) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Delivered & fulfilled</div>
                    </div>

                    <!-- Metric 4: Total Sales Revenue -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Sales Revenue</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base border border-blue-100">
                                💰
                            </div>
                        </div>
                        <div class="text-2xl font-black text-slate-900 mt-2 truncate">{{ number_format($totalRevenue) }} <span class="text-xs text-orange-600 font-bold">MMK</span></div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Revenue generated</div>
                    </div>

                </div>

                <!-- Orders Management Table Container -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6">
                    
                    <!-- Search & Filter Controls -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Real-Time Dispatch Queue</h3>
                            <p class="text-slate-500 text-xs mt-0.5">One-click Accept, Reject with reasons, or update order dispatch status</p>
                        </div>

                        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            
                            <!-- Search Field -->
                            <div class="relative min-w-[220px]">
                                <input type="text" 
                                       name="search" 
                                       value="{{ $search }}" 
                                       placeholder="Search order #, customer, phone..." 
                                       class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 pl-9 focus:ring-0 transition-all placeholder-slate-400">
                                
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <!-- Status Filter Dropdown -->
                            <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="preparing" {{ $status === 'preparing' ? 'selected' : '' }}>👨‍🍳 Preparing</option>
                                <option value="delivering" {{ $status === 'delivering' ? 'selected' : '' }}>🛵 Delivering</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            </select>

                            <!-- Payment Method Filter -->
                            <select name="payment_method" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                <option value="">All Payment Methods</option>
                                <option value="cod" {{ $paymentMethod === 'cod' ? 'selected' : '' }}>💵 Cash on Delivery</option>
                                <option value="kbzpay" {{ $paymentMethod === 'kbzpay' ? 'selected' : '' }}>📱 KBZPay</option>
                                <option value="wavepay" {{ $paymentMethod === 'wavepay' ? 'selected' : '' }}>🌊 WavePay</option>
                            </select>

                            @if($search || $status || $paymentMethod)
                                <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 flex items-center justify-center gap-1">
                                    <span>✕</span>
                                    <span>Reset</span>
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Orders Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5">Order # / Date</th>
                                    <th class="px-4 py-3.5">Customer Info</th>
                                    <th class="px-4 py-3.5">Items Ordered</th>
                                    <th class="px-4 py-3.5">Total & Payment</th>
                                    <th class="px-4 py-3.5">Order Status</th>
                                    <th class="px-4 py-3.5 text-center">Quick Action (1-Click)</th>
                                    <th class="px-4 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                @forelse($orders as $order)
                                    @php
                                        // Status badge colors
                                        $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                        $statusLabel = ucfirst($order->status);
                                        $statusIcon = '📦';

                                        if ($order->status === 'pending') {
                                            $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                            $statusIcon = '⏳';
                                        } elseif ($order->status === 'preparing') {
                                            $statusClass = 'bg-blue-50 text-blue-700 border-blue-200';
                                            $statusIcon = '👨‍🍳';
                                        } elseif ($order->status === 'delivering') {
                                            $statusClass = 'bg-purple-50 text-purple-700 border-purple-200';
                                            $statusIcon = '🛵';
                                        } elseif ($order->status === 'completed') {
                                            $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                            $statusIcon = '✅';
                                        } elseif ($order->status === 'cancelled') {
                                            $statusClass = 'bg-red-50 text-red-700 border-red-200';
                                            $statusIcon = '❌';
                                        }

                                        // Payment Method formatting
                                        $pmLabel = strtoupper($order->payment_method);
                                        $pmIcon = '💳';
                                        if ($order->payment_method === 'cod') { $pmIcon = '💵'; $pmLabel = 'Cash on Delivery'; }
                                        elseif ($order->payment_method === 'kbzpay') { $pmIcon = '📱'; $pmLabel = 'KBZPay'; }
                                        elseif ($order->payment_method === 'wavepay') { $pmIcon = '🌊'; $pmLabel = 'WavePay'; }

                                        $isNewPending = ($order->status === 'pending');
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition-colors {{ $isNewPending ? 'bg-amber-50/50' : '' }}">
                                        
                                        <!-- Order # & Date -->
                                        <td class="px-4 py-4">
                                            <div class="font-mono text-sm font-black text-orange-600 flex items-center gap-2">
                                                <span>#{{ $order->order_number }}</span>
                                                @if($isNewPending)
                                                    <span class="px-1.5 py-0.5 bg-amber-500 text-white font-black text-[9px] uppercase rounded shadow-sm">NEW</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-500 mt-1">
                                                {{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : 'N/A' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                                {{ $order->created_at ? $order->created_at->diffForHumans() : '' }}
                                            </div>
                                        </td>

                                        <!-- Customer Info -->
                                        <td class="px-4 py-4">
                                            <div class="font-bold text-slate-900 text-sm">
                                                {{ $order->user ? $order->user->name : 'Guest Customer' }}
                                            </div>
                                            <div class="text-[11px] text-slate-600 flex items-center gap-1 mt-0.5">
                                                <span>📞</span>
                                                <span>{{ $order->delivery_phone }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 truncate max-w-[200px] mt-0.5" title="{{ $order->delivery_address }}">
                                                📍 {{ $order->delivery_address }}
                                            </div>
                                        </td>

                                        <!-- Items Ordered -->
                                        <td class="px-4 py-4">
                                            <div class="space-y-1.5 min-w-[220px]">
                                                <div class="flex items-center justify-between">
                                                    <span class="px-2 py-0.5 bg-slate-100 rounded border border-slate-200 text-[10px] font-bold text-slate-700 inline-block">
                                                        {{ $order->orderItems->sum('quantity') }} items
                                                    </span>
                                                    <a href="{{ route('admin.orderItems.index', ['search' => $order->order_number]) }}" class="text-[10px] font-bold text-orange-600 hover:underline">
                                                        Table View &rarr;
                                                    </a>
                                                </div>
                                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/80 divide-y divide-slate-200">
                                                    @foreach($order->orderItems->take(2) as $item)
                                                        <div class="p-1.5 flex items-center justify-between text-[11px]">
                                                            <span class="font-semibold text-slate-800 truncate max-w-[130px]">{{ $item->menuItem->name ?? 'Dish' }}</span>
                                                            <span class="text-slate-500 font-mono">x{{ $item->quantity }}</span>
                                                        </div>
                                                    @endforeach
                                                    @if($order->orderItems->count() > 2)
                                                        <div class="p-1 text-center text-[10px] text-slate-500 font-medium bg-slate-100">
                                                            +{{ $order->orderItems->count() - 2 }} more dishes
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Total & Payment -->
                                        <td class="px-4 py-4">
                                            <div class="text-sm font-black text-slate-900">
                                                {{ number_format($order->total_amount) }} <span class="text-[10px] text-orange-600 font-bold">MMK</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="text-xs">{{ $pmIcon }}</span>
                                                <span class="text-[11px] text-slate-600 font-semibold">{{ $pmLabel }}</span>
                                            </div>
                                            <div class="mt-1">
                                                @if($order->payment_status === 'paid')
                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold rounded-full">
                                                        ✓ Paid
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold rounded-full">
                                                        ⏳ Unpaid
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Order Status & Rider Dispatch Column -->
                                        <td class="px-4 py-4 space-y-2.5 min-w-[220px]">
                                            <!-- Status Selector Form -->
                                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="block">
                                                @csrf
                                                @method('PUT')
                                                
                                                <div class="relative">
                                                    <select name="status" 
                                                            onchange="this.form.submit()" 
                                                            class="w-full text-xs font-bold px-3 py-1.5 rounded-xl border {{ $statusClass }} focus:ring-0 cursor-pointer appearance-none pr-7">
                                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                                        <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>👨‍🍳 Preparing</option>
                                                        <option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>🛵 Delivering</option>
                                                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                                                    </select>
                                                    <div class="pointer-events-none absolute right-2 top-2.5 text-slate-500 text-[10px]">
                                                        ▼
                                                    </div>
                                                </div>
                                            </form>

                                            <!-- Rider Assignment & 30-Second Countdown Pool State -->
                                            @if(in_array($order->status, ['confirmed', 'preparing']))
                                                @if(!$order->rider_id)
                                                    @php
                                                        $orderApprovedTimestamp = $order->updated_at ? $order->updated_at->toISOString() : $order->created_at->toISOString();
                                                    @endphp
                                                    <div class="space-y-1.5 p-2.5 bg-amber-50 border border-amber-200 rounded-xl">
                                                        <!-- Live 30s Countdown Condition -->
                                                        <div x-show="getRemainingSeconds('{{ $orderApprovedTimestamp }}') > 0" class="flex items-center gap-1.5 text-[10px] font-bold text-amber-700">
                                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                            <span>Waiting Rider (<span class="font-mono font-black" x-text="getRemainingSeconds('{{ $orderApprovedTimestamp }}')"></span>s)</span>
                                                        </div>

                                                        <div x-show="getRemainingSeconds('{{ $orderApprovedTimestamp }}') === 0" class="space-y-1">
                                                            <div class="flex items-center gap-1.5 text-[10px] font-black text-red-600 animate-pulse">
                                                                <span>⚠️</span>
                                                                <span>30s Elapsed! No Rider Yet</span>
                                                            </div>
                                                            <p class="text-[9px] text-slate-500 font-semibold">Assign rider manually:</p>
                                                        </div>

                                                        <form method="POST" action="{{ route('admin.orders.assignRider', $order) }}" class="block">
                                                            @csrf
                                                            <div class="relative">
                                                                <select name="rider_id" onchange="this.form.submit()" 
                                                                        class="w-full text-[11px] font-bold px-2 py-1 rounded-lg bg-white border border-amber-300 text-slate-800 focus:outline-none focus:border-orange-500 cursor-pointer appearance-none pr-5">
                                                                    <option value="">🛵 Select Rider...</option>
                                                                    @foreach($riders as $riderItem)
                                                                        <option value="{{ $riderItem->id }}">
                                                                            🛵 {{ $riderItem->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="pointer-events-none absolute right-2 top-1.5 text-slate-500 text-[9px]">
                                                                    ▼
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @else
                                                    <div class="p-2 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between gap-1">
                                                        <div class="text-[11px] font-bold text-emerald-800 flex items-center gap-1">
                                                            <span>🛵</span>
                                                            <span>{{ $order->rider->name }}</span>
                                                        </div>
                                                        <!-- Re-assign / change rider form -->
                                                        <form method="POST" action="{{ route('admin.orders.assignRider', $order) }}">
                                                            @csrf
                                                            <select name="rider_id" onchange="this.form.submit()" class="text-[10px] bg-white border border-slate-200 rounded px-1 py-0.5 font-semibold text-slate-600 cursor-pointer">
                                                                <option value="{{ $order->rider_id }}">Assigned</option>
                                                                <option value="">✕ Unassign</option>
                                                                @foreach($riders as $riderItem)
                                                                    @if($riderItem->id != $order->rider_id)
                                                                        <option value="{{ $riderItem->id }}">🛵 {{ $riderItem->name }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                    </div>
                                                @endif
                                            @elseif($order->status === 'delivering' && $order->rider)
                                                <div class="text-[11px] font-bold text-purple-700 flex items-center gap-1.5 px-2.5 py-1.5 bg-purple-50 rounded-xl border border-purple-200">
                                                    <span>🛵</span> <span>{{ $order->rider->name }} (Delivering)</span>
                                                </div>
                                            @elseif($order->rider)
                                                <div class="text-[11px] font-bold text-slate-600 flex items-center gap-1 px-2 py-1">
                                                    <span>🛵</span> <span>{{ $order->rider->name }}</span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- 1-Click Accept / Reject Action Column -->
                                        <td class="px-4 py-4 text-center">
                                            <div class="flex flex-col items-center justify-center gap-1.5">
                                                @if($order->status === 'pending')
                                                    <!-- Accept Form -->
                                                    <form method="POST" action="{{ route('admin.orders.accept', $order) }}" class="w-full max-w-[90px]">
                                                        @csrf
                                                        <button type="submit" class="w-full px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-bold text-[11px] rounded-lg shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                            <span>✓</span>
                                                            <span>Accept</span>
                                                        </button>
                                                    </form>

                                                    <!-- Reject Button -->
                                                    <button @click="openRejectModal({{ $order->id }}, '{{ $order->order_number }}')" 
                                                            class="w-full max-w-[90px] px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold text-[11px] rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                        <span>✕</span>
                                                        <span>Reject</span>
                                                    </button>
                                                @elseif($order->status === 'preparing')
                                                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="w-full max-w-[90px]">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="delivering">
                                                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                                        <button type="submit" class="w-full px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white font-bold text-[11px] rounded-lg shadow-lg shadow-purple-500/20 transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                            <span>🛵 Dispatch</span>
                                                        </button>
                                                    </form>
                                                @elseif($order->status === 'delivering')
                                                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="w-full max-w-[90px]">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="completed">
                                                        <input type="hidden" name="payment_status" value="paid">
                                                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                                        <button type="submit" class="w-full px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[11px] rounded-lg shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                            <span>✅ Complete</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-slate-400 text-[11px] font-medium">-</span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Direct Proof Photo Button (If available) -->
                                                @if($order->delivery_proof_photo)
                                                    <button type="button"
                                                            @click="openProofPhoto('{{ asset($order->delivery_proof_photo) }}', 'Order #{{ $order->order_number }} - Delivery Proof Photo (သက်သေဓာတ်ပုံ)')"
                                                            title="View Delivery Proof Photo (သုံးစွဲသူထံ ရောက်ရှိမှု ဓာတ်ပုံ ကြည့်ရန်)"
                                                            class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200 transition-all flex items-center gap-1 cursor-pointer shadow-sm">
                                                        <span>📸</span>
                                                        <span class="hidden sm:inline text-[11px]">Proof</span>
                                                    </button>
                                                @endif

                                                <!-- View Details Button -->
                                                @php
                                                    $subtotalVal = $order->orderItems->sum('subtotal');
                                                    $taxVal = $order->tax_amount > 0 ? $order->tax_amount : round($subtotalVal * 0.05);
                                                @endphp
                                                <button @click="openDetailsModal({{ json_encode([
                                                            'id' => $order->id,
                                                            'order_number' => $order->order_number,
                                                            'customer_name' => $order->user ? $order->user->name : 'Guest',
                                                            'customer_email' => $order->user ? $order->user->email : 'N/A',
                                                            'delivery_phone' => $order->delivery_phone,
                                                            'delivery_address' => $order->delivery_address,
                                                            'subtotal' => number_format($subtotalVal),
                                                            'delivery_fee' => number_format($order->delivery_fee),
                                                            'tax_amount' => number_format($taxVal),
                                                            'total_amount' => number_format($order->total_amount),
                                                            'payment_method' => $order->payment_method,
                                                            'payment_status' => $order->payment_status,
                                                            'delivery_proof_photo' => $order->delivery_proof_photo ? asset($order->delivery_proof_photo) : null,
                                                            'status' => $order->status,
                                                            'notes' => $order->notes ?? 'No notes provided',
                                                            'created_at' => $order->created_at ? $order->created_at->format('M d, Y • h:i A') : 'N/A',
                                                            'items' => $order->orderItems->map(function($i) {
                                                                $unitPrice = $i->unit_price ?? ($i->menuItem ? $i->menuItem->price : 0);
                                                                $itemSubtotal = $i->subtotal ?? ($unitPrice * $i->quantity);
                                                                return [
                                                                    'name' => $i->menuItem ? $i->menuItem->name : 'Dish Item',
                                                                    'quantity' => $i->quantity,
                                                                    'price' => number_format($unitPrice),
                                                                    'subtotal' => number_format($itemSubtotal),
                                                                    'image' => $i->menuItem ? $i->menuItem->image_url : null
                                                                ];
                                                            })
                                                        ]) }})"
                                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-all flex items-center gap-1 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <span>Details</span>
                                                </button>

                                                <!-- Delete Order Form -->
                                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirmDeleteOrder(this, '{{ $order->order_number }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                                    <button type="submit" title="Delete Record" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors cursor-pointer border border-transparent hover:border-red-100">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                                📦
                                            </div>
                                            <div class="font-bold text-slate-800">No orders found</div>
                                            <div class="text-xs text-slate-500 mt-1">Try resetting search keywords or status filters</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="pt-4 border-t border-slate-100">
                        {{ $orders->links() }}
                    </div>

                </div>

            </main>
        </div>

    </div>

    <!-- ================= RECEIPT & ORDER DETAILS MODAL ================= -->
    <div x-show="detailsModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="detailsModalOpen = false" 
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-lg border border-orange-100">
                        🧾
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Order Receipt Details</h3>
                        <p class="text-slate-500 text-xs" x-text="activeOrder ? 'Order #' + activeOrder.order_number + ' • ' + activeOrder.created_at : ''"></p>
                    </div>
                </div>
                <button @click="detailsModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Modal Content -->
            <template x-if="activeOrder">
                <div class="space-y-6 text-xs">
                    
                    <!-- Customer & Delivery Info Box -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div>
                            <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Customer Info</span>
                            <div class="font-bold text-slate-900 text-sm" x-text="activeOrder.customer_name"></div>
                            <div class="text-slate-600 mt-0.5" x-text="'✉️ ' + activeOrder.customer_email"></div>
                            <div class="text-slate-600 mt-0.5 font-mono" x-text="'📞 ' + activeOrder.delivery_phone"></div>
                        </div>
                        <div>
                            <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Delivery Address</span>
                            <div class="text-slate-700 font-medium leading-relaxed" x-text="'📍 ' + activeOrder.delivery_address"></div>
                            <div class="text-amber-700 font-medium mt-2" x-text="'📝 Notes: ' + activeOrder.notes"></div>
                        </div>
                    </div>

                    <!-- Ordered Items Table UI -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-slate-700 font-bold uppercase tracking-wider">Order Items Table</span>
                            <a href="{{ route('admin.orderItems.index') }}" class="text-orange-600 text-xs font-bold hover:underline">View All Order Items Table &rarr;</a>
                        </div>
                        <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-3.5 py-2.5">Item</th>
                                        <th class="px-3.5 py-2.5 text-center">Qty</th>
                                        <th class="px-3.5 py-2.5 text-right">Price</th>
                                        <th class="px-3.5 py-2.5 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                    <template x-for="item in activeOrder.items" :key="item.name">
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-3.5 py-2.5">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400 font-bold text-xs">
                                                        <template x-if="item.image">
                                                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                                        </template>
                                                        <template x-if="!item.image">
                                                            <span>🍕</span>
                                                        </template>
                                                    </div>
                                                    <span class="font-bold text-slate-900 text-xs" x-text="item.name"></span>
                                                </div>
                                            </td>
                                            <td class="px-3.5 py-2.5 text-center font-mono font-bold text-slate-800" x-text="'x' + item.quantity"></td>
                                            <td class="px-3.5 py-2.5 text-right font-mono text-slate-600" x-text="item.price + ' MMK'"></td>
                                            <td class="px-3.5 py-2.5 text-right font-bold text-orange-600" x-text="item.subtotal + ' MMK'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total Amount, Tax & Payment Summary -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-bold uppercase tracking-wider text-[11px]">Payment Channel</span>
                            <span class="font-bold text-slate-900 uppercase" x-text="activeOrder.payment_method + ' (' + activeOrder.payment_status + ')'"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-bold text-slate-900" x-text="activeOrder.subtotal + ' MMK'"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1">
                                <span>Tax (5%)</span>
                                <span class="text-[9px] px-1 py-0.2 rounded bg-slate-200 text-slate-700 font-bold uppercase">Tax</span>
                            </span>
                            <span class="font-bold text-slate-900" x-text="'+' + activeOrder.tax_amount + ' MMK'"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Delivery Fee</span>
                            <span class="font-bold text-slate-900" x-text="'+' + activeOrder.delivery_fee + ' MMK'"></span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex items-center justify-between">
                            <span class="font-black text-slate-900 uppercase tracking-wider text-xs">Total Amount</span>
                            <div class="text-lg font-black text-orange-600" x-text="activeOrder.total_amount + ' MMK'"></div>
                        </div>
                    </div>

                    <!-- Proof of Delivery Photo Card with Full-Screen Preview -->
                    <template x-if="activeOrder.delivery_proof_photo">
                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-300 rounded-2xl p-4 sm:p-5 space-y-3 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5 text-emerald-900 font-black text-sm">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-base shadow-sm">
                                        📸
                                    </div>
                                    <div>
                                        <span>Delivery Proof Photo (သုံးစွဲသူထံ ရောက်ရှိမှု အတည်ပြု ဓာတ်ပုံ)</span>
                                        <p class="text-[11px] text-emerald-700 font-medium">Captured &amp; submitted by rider upon delivery</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-emerald-600 text-white text-[10px] font-black rounded-full uppercase shadow-sm">
                                    ✓ Photo Verified
                                </span>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-4 pt-1">
                                <div @click="openProofPhoto(activeOrder.delivery_proof_photo, 'Order #' + activeOrder.order_number + ' - Delivery Proof Photo (သက်သေဓာတ်ပုံ)')"
                                     class="w-full sm:w-24 h-24 rounded-xl overflow-hidden border-2 border-emerald-400 shrink-0 bg-slate-900 group relative cursor-pointer shadow-md">
                                    <img :src="activeOrder.delivery_proof_photo" alt="Delivery Proof" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <span>🔍</span>
                                        <span>Zoom</span>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-700 space-y-2 flex-1 w-full">
                                    <p class="text-emerald-950 font-bold leading-relaxed">
                                        ✓ သုံးစွဲသူထံ အစားအသောက် အရောက်ပို့ဆောင်ပြီးစီးကြောင်း ရိုက်ကူးအတည်ပြုထားသော ဓာတ်ပုံဖြစ်ပါသည်။
                                    </p>
                                    <button type="button" 
                                            @click="openProofPhoto(activeOrder.delivery_proof_photo, 'Order #' + activeOrder.order_number + ' - Delivery Proof Photo (သက်သေဓာတ်ပုံ)')"
                                            class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>🔍</span>
                                        <span>Full-Screen ဖြင့် ကြည့်ရန် (View Full-Screen)</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </template>
        </div>
    </div>

    <!-- ================= REJECT ORDER MODAL ================= -->
    <div x-show="rejectModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="rejectModalOpen = false" 
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-lg font-bold border border-red-100">
                        ❌
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Reject Order</h3>
                        <p class="text-slate-500 text-xs" x-text="activeRejectOrder ? 'Order #' + activeRejectOrder.number : ''"></p>
                    </div>
                </div>
                <button @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Modal Form -->
            <template x-if="activeRejectOrder">
                <form method="POST" :action="'/admin/orders/' + activeRejectOrder.id + '/reject'" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Select Rejection Reason <span class="text-orange-500">*</span>
                        </label>

                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-300">
                                <input type="radio" name="reason" value="Out of Stock" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-slate-800">🚫 Out of Stock (Dishes unavailable)</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-300">
                                <input type="radio" name="reason" value="Kitchen Busy" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-slate-800">👨‍🍳 Kitchen Extremely Busy</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-300">
                                <input type="radio" name="reason" value="Delivery Area Unavailable" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-slate-800">🛵 Delivery Area Unavailable</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-300">
                                <input type="radio" name="reason" value="Store Closing Soon" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-slate-800">🕒 Store Closing Soon</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 active:bg-red-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-red-500/25 transition-all cursor-pointer">
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ================= FULL-SCREEN DELIVERY PROOF MODAL ================= -->
    <div x-show="proofModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6"
         style="display: none;">
        
        <div @click.outside="proofModalOpen = false" 
             class="bg-slate-900 border border-slate-700 rounded-3xl p-5 sm:p-6 max-w-3xl w-full shadow-2xl space-y-4 max-h-[95vh] flex flex-col">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-500/30">
                        📸
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white" x-text="proofModalTitle"></h3>
                        <p class="text-xs text-slate-400">သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိမှု အတည်ပြု သက်သေဓာတ်ပုံ</p>
                    </div>
                </div>
                <button @click="proofModalOpen = false" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white font-bold flex items-center justify-center transition-colors cursor-pointer">✕</button>
            </div>

            <div class="flex-1 overflow-hidden flex items-center justify-center bg-slate-950/80 rounded-2xl border border-slate-800 p-2">
                <img :src="proofModalSrc" :alt="proofModalTitle" class="max-h-[72vh] w-auto max-w-full object-contain rounded-xl shadow-2xl">
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-800 shrink-0 text-xs">
                <span class="text-emerald-400 font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Photo Verification Confirmed
                </span>
                <a :href="proofModalSrc" target="_blank" class="text-orange-400 hover:text-orange-300 font-bold flex items-center gap-1 hover:underline">
                    <span>Open Original Tab</span> ↗
                </a>
            </div>
        </div>
    </div>

</body>
</html>
