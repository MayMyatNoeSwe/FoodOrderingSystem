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
                html: `Are you sure you want to permanently delete order <strong class="text-orange-400">#${orderNumber}</strong>?<br><span class="text-xs text-slate-400 mt-1 block">This operation cannot be undone.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: 'Yes, Delete Order',
                cancelButtonText: 'Cancel',
                background: '#0f172a',
                color: '#f8fafc',
                customClass: {
                    popup: 'border border-slate-800 rounded-3xl shadow-2xl',
                    title: 'text-white font-bold text-lg',
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
                activeOrder: null,
                activeRejectOrder: null,
                activeRejectReason: 'Kitchen Busy',
                audioEnabled: true,
                statusStateMap: {},

                init: function() {
                    var self = this;
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
                                    if (hasChange && !self.detailsModalOpen && !self.rejectModalOpen) {
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

                openRejectModal: function(orderId, orderNum) {
                    this.activeRejectOrder = { id: orderId, number: orderNum };
                    this.rejectModalOpen = true;
                }
            };
        };
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="adminOrderPoller()">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= DESKTOP SIDEBAR ================= -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 hidden md:flex flex-col justify-between p-6 shrink-0 sticky top-0 h-screen">
            <div class="space-y-8">
                <!-- Admin Brand -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                        🍕
                    </div>
                    <div>
                        <span class="text-lg font-black text-white tracking-tight">Food<span class="text-orange-500">Order</span></span>
                        <span class="block text-[10px] text-amber-400 font-bold uppercase tracking-widest">Admin Portal</span>
                    </div>
                </a>

                <!-- Navigation Links -->
                <nav class="space-y-1.5 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Categories</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navCategoryCount }}</span>
                    </a>

                    <a href="{{ route('admin.menuItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Menu Items</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navMenuItemCount }}</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                        <span class="ms-auto bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $orders->total() }}</span>
                    </a>

                    <a href="{{ route('admin.orderItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span>Order Items</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navOrderItemCount ?? 0 }}</span>
                    </a>

                    <a href="{{ route('admin.riders.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Riders</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $riders->count() }}</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Users</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navUserCount }}</span>
                    </a>
                </nav>
            </div>

            <!-- Admin Profile Quick Footer -->
            <div class="border-t border-slate-800 pt-4 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 font-bold text-sm shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="text-xs truncate">
                        <div class="font-bold text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="text-amber-400 font-medium">System Admin</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-red-400 transition-colors cursor-pointer rounded-lg hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ================= MOBILE DRAWER NAVIGATION ================= -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 md:hidden"
             @click="mobileMenuOpen = false"></div>

        <aside x-show="mobileMenuOpen"
               x-transition:enter="transition transform ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition transform ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 w-72 bg-slate-900 border-r border-slate-800 p-6 flex flex-col justify-between z-50 md:hidden">
            
            <div class="space-y-8">
                <!-- Header & Close -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black shadow-lg">
                            🍕
                        </div>
                        <div>
                            <span class="text-base font-black text-white">Food<span class="text-orange-500">Order</span></span>
                            <span class="block text-[9px] text-amber-400 font-bold uppercase tracking-widest">Admin Portal</span>
                        </div>
                    </a>
                    <button @click="mobileMenuOpen = false" class="text-slate-400 hover:text-white p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-2 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Categories</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navCategoryCount }}</span>
                    </a>

                    <a href="{{ route('admin.menuItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Menu Items</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navMenuItemCount }}</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                        <span class="ms-auto bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $orders->total() }}</span>
                    </a>

                    <a href="{{ route('admin.orderItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span>Order Items</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navOrderItemCount ?? 0 }}</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Users</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navUserCount }}</span>
                    </a>
                </nav>
            </div>

            <div class="border-t border-slate-800 pt-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="text-xs">
                        <div class="font-bold text-white">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="text-amber-400 font-medium">System Admin</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Topbar Header -->
            <header class="bg-slate-900/90 backdrop-blur-md sticky top-0 z-30 border-b border-slate-800 px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger Toggle -->
                    <button @click="mobileMenuOpen = true" class="md:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-xl font-black text-white tracking-tight flex items-center gap-2.5">
                            <span>Real-Time Order Dispatch & Operations Hub</span>
                            <span class="bg-orange-500/20 text-orange-400 border border-orange-500/30 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                                <span>Live Dispatch Queue</span>
                            </span>
                        </h1>
                        <p class="text-xs text-slate-400 hidden sm:block">Monitor incoming customer orders with sound alert notifications and 1-click accept/reject actions</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notification Alarm Sound Toggle Button -->
                    <button @click="audioEnabled = !audioEnabled; if(audioEnabled) playNotificationSound();" 
                            :class="audioEnabled ? 'bg-orange-500/10 text-orange-400 border-orange-500/30' : 'bg-slate-800 text-slate-500 border-slate-700'"
                            class="px-3.5 py-2 text-xs font-bold rounded-xl border transition-all flex items-center gap-2 cursor-pointer">
                        <span x-text="audioEnabled ? '🔔 Sound Alarm ON' : '🔕 Sound Muted'"></span>
                    </button>

                    <a href="{{ route('home') }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-all flex items-center gap-2">
                        <span>Storefront</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                background: '#0f172a',
                                color: '#f8fafc',
                                customClass: {
                                    popup: 'border border-emerald-500/30 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                <!-- Stat Metric Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Metric 1: Total Orders -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center font-bold text-base">
                                📦
                            </div>
                        </div>
                        <div class="text-3xl font-black text-white mt-2">{{ number_format($totalOrdersCount) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>
                            <span>All-time customer transactions</span>
                        </div>
                    </div>

                    <!-- Metric 2: Active & Pending Orders -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Active In-Progress</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-base">
                                ⏳
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-400 mt-2">{{ number_format($activeCount) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Pending, Preparing & Delivery</div>
                    </div>

                    <!-- Metric 3: Completed Orders -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Completed Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-base">
                                ✅
                            </div>
                        </div>
                        <div class="text-3xl font-black text-emerald-400 mt-2">{{ number_format($completedCount) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Delivered & fulfilled</div>
                    </div>

                    <!-- Metric 4: Total Sales Revenue -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Sales Revenue</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-base">
                                💰
                            </div>
                        </div>
                        <div class="text-2xl font-black text-white mt-2 truncate">{{ number_format($totalRevenue) }} <span class="text-xs text-orange-400 font-bold">MMK</span></div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Revenue generated</div>
                    </div>

                </div>

                <!-- Orders Management Table Container -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-6">
                    
                    <!-- Search & Filter Controls -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-white tracking-tight">Real-Time Dispatch Queue</h3>
                            <p class="text-slate-400 text-xs mt-0.5">One-click Accept, Reject with reasons, or update order dispatch status</p>
                        </div>

                        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            
                            <!-- Search Field -->
                            <div class="relative min-w-[220px]">
                                <input type="text" 
                                       name="search" 
                                       value="{{ $search }}" 
                                       placeholder="Search order #, customer, phone..." 
                                       class="w-full bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 pl-9 focus:ring-0 transition-all placeholder-slate-500">
                                
                                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <!-- Status Filter Dropdown -->
                            <select name="status" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="preparing" {{ $status === 'preparing' ? 'selected' : '' }}>👨‍🍳 Preparing</option>
                                <option value="delivering" {{ $status === 'delivering' ? 'selected' : '' }}>🛵 Delivering</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            </select>

                            <!-- Payment Method Filter -->
                            <select name="payment_method" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                <option value="">All Payment Methods</option>
                                <option value="cod" {{ $paymentMethod === 'cod' ? 'selected' : '' }}>💵 Cash on Delivery</option>
                                <option value="kbzpay" {{ $paymentMethod === 'kbzpay' ? 'selected' : '' }}>📱 KBZPay</option>
                                <option value="wavepay" {{ $paymentMethod === 'wavepay' ? 'selected' : '' }}>🌊 WavePay</option>
                            </select>

                            @if($search || $status || $paymentMethod)
                                <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 flex items-center justify-center gap-1">
                                    <span>✕</span>
                                    <span>Reset</span>
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Orders Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800">
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
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                @forelse($orders as $order)
                                    @php
                                        // Status badge colors
                                        $statusClass = 'bg-slate-800 text-slate-300 border-slate-700';
                                        $statusLabel = ucfirst($order->status);
                                        $statusIcon = '📦';

                                        if ($order->status === 'pending') {
                                            $statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                                            $statusIcon = '⏳';
                                        } elseif ($order->status === 'preparing') {
                                            $statusClass = 'bg-blue-500/10 text-blue-400 border-blue-500/30';
                                            $statusIcon = '👨‍🍳';
                                        } elseif ($order->status === 'delivering') {
                                            $statusClass = 'bg-purple-500/10 text-purple-400 border-purple-500/30';
                                            $statusIcon = '🛵';
                                        } elseif ($order->status === 'completed') {
                                            $statusClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
                                            $statusIcon = '✅';
                                        } elseif ($order->status === 'cancelled') {
                                            $statusClass = 'bg-red-500/10 text-red-400 border-red-500/30';
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

                                    <tr class="hover:bg-slate-800/40 transition-colors {{ $isNewPending ? 'bg-amber-500/5' : '' }}">
                                        
                                        <!-- Order # & Date -->
                                        <td class="px-4 py-4">
                                            <div class="font-mono text-sm font-black text-orange-400 flex items-center gap-2">
                                                <span>#{{ $order->order_number }}</span>
                                                @if($isNewPending)
                                                    <span class="px-1.5 py-0.5 bg-amber-500 text-slate-950 font-black text-[9px] uppercase rounded animate-bounce">NEW</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 mt-1">
                                                {{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : 'N/A' }}
                                            </div>
                                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                                {{ $order->created_at ? $order->created_at->diffForHumans() : '' }}
                                            </div>
                                        </td>

                                        <!-- Customer Info -->
                                        <td class="px-4 py-4">
                                            <div class="font-bold text-white text-sm">
                                                {{ $order->user ? $order->user->name : 'Guest Customer' }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
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
                                                    <span class="px-2 py-0.5 bg-slate-950 rounded border border-slate-800 text-[10px] font-bold text-slate-400 inline-block">
                                                        {{ $order->orderItems->sum('quantity') }} items
                                                    </span>
                                                    <a href="{{ route('admin.orderItems.index', ['search' => $order->order_number]) }}" class="text-[10px] font-bold text-orange-400 hover:underline">
                                                        Table View &rarr;
                                                    </a>
                                                </div>
                                                <div class="border border-slate-800 rounded-xl overflow-hidden bg-slate-950/60 divide-y divide-slate-800/60">
                                                    @foreach($order->orderItems->take(2) as $item)
                                                        <div class="p-1.5 flex items-center justify-between text-[11px]">
                                                            <span class="font-semibold text-slate-200 truncate max-w-[130px]">{{ $item->menuItem->name ?? 'Dish' }}</span>
                                                            <span class="text-slate-400 font-mono">x{{ $item->quantity }}</span>
                                                        </div>
                                                    @endforeach
                                                    @if($order->orderItems->count() > 2)
                                                        <div class="p-1 text-center text-[10px] text-slate-500 font-medium bg-slate-900/40">
                                                            +{{ $order->orderItems->count() - 2 }} more dishes
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Total & Payment -->
                                        <td class="px-4 py-4">
                                            <div class="text-sm font-black text-white">
                                                {{ number_format($order->total_amount) }} <span class="text-[10px] text-orange-400 font-bold">MMK</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="text-xs">{{ $pmIcon }}</span>
                                                <span class="text-[11px] text-slate-400 font-semibold">{{ $pmLabel }}</span>
                                            </div>
                                            <div class="mt-1">
                                                @if($order->payment_status === 'paid')
                                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold rounded-full">
                                                        ✓ Paid
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold rounded-full">
                                                        ⏳ Unpaid
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Order Status & Rider Dropdown Form -->
                                        <td class="px-4 py-4 space-y-2">
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
                                                    <div class="pointer-events-none absolute right-2 top-2.5 text-slate-400 text-[10px]">
                                                        ▼
                                                    </div>
                                                </div>
                                            </form>

                                            <!-- Rider Assignment -->
                                            @if(!in_array($order->status, ['cancelled', 'completed']))
                                                <form method="POST" action="{{ route('admin.orders.assignRider', $order) }}" class="block">
                                                    @csrf
                                                    <div class="relative">
                                                        <select name="rider_id" onchange="this.form.submit()" 
                                                                class="w-full text-[11px] font-bold px-2.5 py-1 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 focus:outline-none focus:border-orange-500 cursor-pointer appearance-none pr-6">
                                                            <option value="">🛵 Select Rider...</option>
                                                            @foreach($riders as $riderItem)
                                                                <option value="{{ $riderItem->id }}" {{ $order->rider_id == $riderItem->id ? 'selected' : '' }}>
                                                                    🛵 {{ $riderItem->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="pointer-events-none absolute right-2 top-1.5 text-slate-400 text-[9px]">
                                                            ▼
                                                        </div>
                                                    </div>
                                                </form>
                                            @elseif($order->rider)
                                                <div class="text-[11px] font-bold text-orange-400 flex items-center gap-1">
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
                                                            class="w-full max-w-[90px] px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 font-bold text-[11px] rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                        <span>✕</span>
                                                        <span>Reject</span>
                                                    </button>
                                                @elseif($order->status === 'preparing')
                                                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="w-full max-w-[90px]">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="delivering">
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
                                                        <button type="submit" class="w-full px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[11px] rounded-lg shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                            <span>✅ Complete</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-slate-500 text-[11px] font-medium">-</span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- View Details Button -->
                                                <button @click="openDetailsModal({{ json_encode([
                                                            'id' => $order->id,
                                                            'order_number' => $order->order_number,
                                                            'customer_name' => $order->user ? $order->user->name : 'Guest',
                                                            'customer_email' => $order->user ? $order->user->email : 'N/A',
                                                            'delivery_phone' => $order->delivery_phone,
                                                            'delivery_address' => $order->delivery_address,
                                                            'total_amount' => number_format($order->total_amount),
                                                            'payment_method' => $order->payment_method,
                                                            'payment_status' => $order->payment_status,
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
                                                        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-all flex items-center gap-1 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <span>Details</span>
                                                </button>

                                                <!-- Delete Order Form -->
                                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirmDeleteOrder(this, '{{ $order->order_number }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Delete Record" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-colors cursor-pointer border border-transparent hover:border-red-500/20">
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
                                            <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                                📦
                                            </div>
                                            <div class="font-bold text-slate-300">No orders found</div>
                                            <div class="text-xs text-slate-500 mt-1">Try resetting search keywords or status filters</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="pt-4 border-t border-slate-800">
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
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="detailsModalOpen = false" 
             class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center font-bold text-lg">
                        🧾
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Order Receipt Details</h3>
                        <p class="text-slate-400 text-xs" x-text="activeOrder ? 'Order #' + activeOrder.order_number + ' • ' + activeOrder.created_at : ''"></p>
                    </div>
                </div>
                <button @click="detailsModalOpen = false" class="text-slate-500 hover:text-white p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Modal Content -->
            <template x-if="activeOrder">
                <div class="space-y-6 text-xs">
                    
                    <!-- Customer & Delivery Info Box -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-950 p-4 rounded-2xl border border-slate-800">
                        <div>
                            <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Customer Info</span>
                            <div class="font-bold text-white text-sm" x-text="activeOrder.customer_name"></div>
                            <div class="text-slate-400 mt-0.5" x-text="'✉️ ' + activeOrder.customer_email"></div>
                            <div class="text-slate-400 mt-0.5 font-mono" x-text="'📞 ' + activeOrder.delivery_phone"></div>
                        </div>
                        <div>
                            <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Delivery Address</span>
                            <div class="text-slate-300 font-medium leading-relaxed" x-text="'📍 ' + activeOrder.delivery_address"></div>
                            <div class="text-amber-400/80 font-medium mt-2" x-text="'📝 Notes: ' + activeOrder.notes"></div>
                        </div>
                    </div>

                    <!-- Ordered Items Table UI -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-slate-400 font-bold uppercase tracking-wider">Order Items Table</span>
                            <a href="{{ route('admin.orderItems.index') }}" class="text-orange-400 text-xs font-bold hover:underline">View All Order Items Table &rarr;</a>
                        </div>
                        <div class="border border-slate-800 rounded-2xl overflow-hidden bg-slate-950">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-900 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800">
                                    <tr>
                                        <th class="px-3.5 py-2.5">Item</th>
                                        <th class="px-3.5 py-2.5 text-center">Qty</th>
                                        <th class="px-3.5 py-2.5 text-right">Price</th>
                                        <th class="px-3.5 py-2.5 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                    <template x-for="item in activeOrder.items" :key="item.name">
                                        <tr class="hover:bg-slate-900/50 transition-colors">
                                            <td class="px-3.5 py-2.5">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 overflow-hidden shrink-0 flex items-center justify-center text-slate-500 font-bold text-xs">
                                                        <template x-if="item.image">
                                                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                                        </template>
                                                        <template x-if="!item.image">
                                                            <span>🍕</span>
                                                        </template>
                                                    </div>
                                                    <span class="font-bold text-white text-xs" x-text="item.name"></span>
                                                </div>
                                            </td>
                                            <td class="px-3.5 py-2.5 text-center font-mono font-bold text-slate-300" x-text="'x' + item.quantity"></td>
                                            <td class="px-3.5 py-2.5 text-right font-mono text-slate-400" x-text="item.price + ' MMK'"></td>
                                            <td class="px-3.5 py-2.5 text-right font-bold text-orange-400" x-text="item.subtotal + ' MMK'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total Amount & Payment Summary -->
                    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-slate-500 font-bold uppercase tracking-wider block">Payment Channel</span>
                            <div class="font-bold text-white text-xs mt-1 uppercase" x-text="activeOrder.payment_method + ' (' + activeOrder.payment_status + ')'"></div>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-500 font-bold uppercase tracking-wider block">Total Amount</span>
                            <div class="text-xl font-black text-orange-400 mt-0.5" x-text="activeOrder.total_amount + ' MMK'"></div>
                        </div>
                    </div>

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
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="rejectModalOpen = false" 
             class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center text-lg font-bold">
                        ❌
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Reject Order</h3>
                        <p class="text-slate-400 text-xs" x-text="activeRejectOrder ? 'Order #' + activeRejectOrder.number : ''"></p>
                    </div>
                </div>
                <button @click="rejectModalOpen = false" class="text-slate-500 hover:text-white p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Modal Form -->
            <template x-if="activeRejectOrder">
                <form method="POST" :action="'/admin/orders/' + activeRejectOrder.id + '/reject'" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wider">
                            Select Rejection Reason <span class="text-orange-500">*</span>
                        </label>

                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-slate-950 rounded-xl border border-slate-800 cursor-pointer hover:border-slate-700">
                                <input type="radio" name="reason" value="Out of Stock" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-white">🚫 Out of Stock (Dishes unavailable)</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 bg-slate-950 rounded-xl border border-slate-800 cursor-pointer hover:border-slate-700">
                                <input type="radio" name="reason" value="Kitchen Busy" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-white">👨‍🍳 Kitchen Extremely Busy</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 bg-slate-950 rounded-xl border border-slate-800 cursor-pointer hover:border-slate-700">
                                <input type="radio" name="reason" value="Delivery Area Unavailable" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-white">🛵 Delivery Area Unavailable</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 bg-slate-950 rounded-xl border border-slate-800 cursor-pointer hover:border-slate-700">
                                <input type="radio" name="reason" value="Store Closing Soon" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                <span class="text-xs font-bold text-white">🕒 Store Closing Soon</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition-all cursor-pointer">
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

</body>
</html>
