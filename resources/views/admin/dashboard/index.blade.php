<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ 
          mobileMenuOpen: false,
          activeStockTab: 'all'
      }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="dashboard" />

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
                            <span>Admin Kitchen Operations & Control</span>
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Live Synced</span>
                            </span>
                        </h1>
                        <p class="text-xs text-slate-400 hidden sm:block">Instant inventory availability toggles & key performance revenue summary</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Storefront Link -->
                    <a href="{{ route('home') }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-all flex items-center gap-2">
                        <span>View Storefront</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Main Scrollable Dashboard Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-8 overflow-y-auto">

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

                <!-- PILLAR 3: QUICK BUSINESS OVERVIEW (4 KEY ESSENTIAL STATS) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Stat 1: Today's Revenue -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Today's Revenue</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-base">
                                💰
                            </div>
                        </div>
                        <div class="text-3xl font-black text-white mt-2 truncate">
                            {{ number_format($todaysRevenue) }} <span class="text-xs text-orange-400 font-bold">MMK</span>
                        </div>
                        <div class="text-xs text-emerald-400 font-semibold mt-2 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                            <span>Completed sales today</span>
                        </div>
                    </div>

                    <!-- Stat 2: Today's Orders -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Today's Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-base">
                                📦
                            </div>
                        </div>
                        <div class="text-3xl font-black text-blue-400 mt-2">
                            {{ number_format($todaysOrdersCount) }} <span class="text-xs text-slate-400 font-normal">Orders</span>
                        </div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Incoming customer orders</div>
                    </div>

                    <!-- Stat 3: Pending Orders -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Pending Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-base">
                                👨‍🍳
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-400 mt-2">
                            {{ number_format($pendingOrdersCount) }} <span class="text-xs text-slate-400 font-normal">Active</span>
                        </div>
                        <div class="text-xs text-amber-400/80 font-medium mt-2">Waiting for kitchen / delivery dispatch</div>
                    </div>

                    <!-- Stat 4: Cancellation Rate -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Cancellation Rate</span>
                            <div class="w-9 h-9 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center font-bold text-base">
                                ⚠️
                            </div>
                        </div>
                        <div class="text-3xl font-black text-red-400 mt-2">
                            {{ $cancellationRate }}%
                        </div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Percentage of rejected/cancelled orders</div>
                    </div>

                </div>

                <!-- CALL-TO-ACTION CARD FOR ORDERS DISPATCH PAGE -->
                <div class="bg-gradient-to-r from-orange-500/10 via-amber-500/10 to-slate-900 border border-orange-500/30 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-orange-500/30 shrink-0">
                            ⚡
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-white">Real-Time Order Dispatch & Operations Hub</h2>
                            <p class="text-slate-400 text-xs mt-1">Accept, reject with reasons, and manage kitchen order dispatching with real-time sound alarms on the Orders page.</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.orders.index') }}" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/30 transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                        <span>Open Orders Dispatch Page</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>

                <!-- PILLAR 2: INSTANT INVENTORY / MENU CONTROL (INSTANT 1-CLICK STOCK SWITCH) -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-6">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-white tracking-tight">Instant Inventory & Menu Switch</h3>
                                <span class="px-2 py-0.5 bg-amber-500/20 text-amber-400 text-[10px] font-bold rounded-full border border-amber-500/30">
                                    1-Sec Stock Toggle
                                </span>
                            </div>
                            <p class="text-slate-400 text-xs mt-0.5">Instantly mark dishes as Available or Out of Stock to prevent customer order conflicts</p>
                        </div>

                        <!-- Stock Filter Pills -->
                        <div class="flex items-center gap-2">
                            <button @click="activeStockTab = 'all'" :class="activeStockTab === 'all' ? 'bg-orange-500 text-white font-bold' : 'bg-slate-950 text-slate-400'" class="px-3 py-1.5 text-xs rounded-xl border border-slate-800 cursor-pointer">
                                All Dishes ({{ count($menuItemsQuickControl) }})
                            </button>
                            <button @click="activeStockTab = 'available'" :class="activeStockTab === 'available' ? 'bg-emerald-500 text-white font-bold' : 'bg-slate-950 text-slate-400'" class="px-3 py-1.5 text-xs rounded-xl border border-slate-800 cursor-pointer">
                                Available ({{ $menuItemsQuickControl->where('is_available', true)->count() }})
                            </button>
                            <button @click="activeStockTab = 'unavailable'" :class="activeStockTab === 'unavailable' ? 'bg-red-500 text-white font-bold' : 'bg-slate-950 text-slate-400'" class="px-3 py-1.5 text-xs rounded-xl border border-slate-800 cursor-pointer">
                                Out of Stock ({{ $menuItemsQuickControl->where('is_available', false)->count() }})
                            </button>
                        </div>
                    </div>

                    <!-- Quick Switch Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($menuItemsQuickControl as $item)
                            <div x-show="activeStockTab === 'all' || (activeStockTab === 'available' && {{ $item->is_available ? 'true' : 'false' }}) || (activeStockTab === 'unavailable' && !{{ $item->is_available ? 'true' : 'false' }})" 
                                 class="p-3.5 bg-slate-950 rounded-xl border border-slate-800/80 flex items-center justify-between gap-3 hover:border-slate-700 transition-all">
                                
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-900 border border-slate-800 shrink-0">
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="truncate">
                                        <div class="font-bold text-white text-xs truncate" title="{{ $item->name }}">{{ $item->name }}</div>
                                        <div class="text-[10px] text-orange-400 font-mono font-bold">{{ number_format($item->price) }} MMK</div>
                                    </div>
                                </div>

                                <!-- Instant 1-Click Toggle Form -->
                                <form method="POST" action="{{ route('admin.menuItems.toggle-stock', $item) }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" 
                                            title="Click to toggle availability" 
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $item->is_available ? 'bg-emerald-500' : 'bg-slate-700' }}">
                                        <span class="sr-only">Toggle Dish Stock</span>
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $item->is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                </div>

            </main>
        </div>

    </div>

</body>
</html>
