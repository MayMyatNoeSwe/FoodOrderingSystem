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
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ 
          mobileMenuOpen: false
      }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="dashboard" />

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
                            <span>Admin Kitchen Operations & Control</span>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Live Synced</span>
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">Kitchen order operations & key performance revenue summary</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-language-switcher variant="compact" />
                    <!-- Storefront Link -->
                    <a href="{{ route('home') }}" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-all flex items-center gap-2">
                        <span>{{ __('View Storefront') }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                background: '#ffffff',
                                color: '#0f172a',
                                customClass: {
                                    popup: 'border border-emerald-200 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                <!-- QUICK BUSINESS OVERVIEW (4 KEY ESSENTIAL STATS) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Stat 1: Today's Revenue -->
                    <div data-reveal="fade-up" data-reveal-delay="0" class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Today's Revenue</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-100">
                                💰
                            </div>
                        </div>
                        <div class="text-3xl font-black text-slate-900 mt-2 truncate">
                            {{ number_format($todaysRevenue) }} <span class="text-xs text-orange-600 font-bold">MMK</span>
                        </div>
                        <div class="text-xs text-emerald-600 font-semibold mt-2 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                            <span>Completed sales today</span>
                        </div>
                    </div>

                    <!-- Stat 2: Today's Orders -->
                    <div data-reveal="fade-up" data-reveal-delay="80" class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Today's Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base border border-blue-100">
                                📦
                            </div>
                        </div>
                        <div class="text-3xl font-black text-blue-600 mt-2">
                            {{ number_format($todaysOrdersCount) }} <span class="text-xs text-slate-500 font-normal">Orders</span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Incoming customer orders</div>
                    </div>

                    <!-- Stat 3: Pending Orders -->
                    <div data-reveal="fade-up" data-reveal-delay="160" class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Pending Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base border border-amber-100">
                                👨‍🍳
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-600 mt-2">
                            {{ number_format($pendingOrdersCount) }} <span class="text-xs text-slate-500 font-normal">Active</span>
                        </div>
                        <div class="text-xs text-amber-700 font-medium mt-2">Waiting for kitchen / delivery dispatch</div>
                    </div>

                    <!-- Stat 4: Cancellation Rate -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Cancellation Rate</span>
                            <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-base border border-red-100">
                                ⚠️
                            </div>
                        </div>
                        <div class="text-3xl font-black text-red-600 mt-2">
                            {{ $cancellationRate }}%
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Percentage of rejected/cancelled orders</div>
                    </div>

                </div>

                <!-- OPERATIONS & QUICK NAVIGATION HUBS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- CALL-TO-ACTION CARD FOR ORDERS DISPATCH PAGE -->
                    <div class="bg-gradient-to-br from-orange-50 via-amber-50 to-white border border-orange-200 rounded-3xl p-6 flex flex-col justify-between gap-6 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-orange-500/30 shrink-0">
                                ⚡
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900">Orders Dispatch & Operations Hub</h2>
                                <p class="text-slate-600 text-xs mt-1 leading-relaxed">Accept, reject with reasons, and manage kitchen order dispatching with real-time sound alarms on the Orders page.</p>
                            </div>
                        </div>

                        <a href="{{ route('admin.orders.index') }}" class="px-5 py-3 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-between gap-2 shrink-0 cursor-pointer">
                            <span>Open Orders Dispatch Page</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>

                    <!-- CALL-TO-ACTION CARD FOR INVENTORY & MENU SWITCH PAGE -->
                    <div class="bg-gradient-to-br from-amber-50 via-orange-50/40 to-white border border-amber-200 rounded-3xl p-6 flex flex-col justify-between gap-6 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-amber-500/30 shrink-0">
                                📦
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-black text-slate-900">Instant Inventory & Menu Switch</h2>
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold rounded-full">
                                        1-Click Control
                                    </span>
                                </div>
                                <p class="text-slate-600 text-xs mt-1 leading-relaxed">Manage stock quantities, replenish low-stock dishes, and instantly toggle dish availability on the new dedicated Inventory page.</p>
                            </div>
                        </div>

                        <a href="{{ route('admin.inventory.index') }}" class="px-5 py-3 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold text-xs rounded-xl shadow-lg shadow-slate-900/20 transition-all flex items-center justify-between gap-2 shrink-0 cursor-pointer">
                            <span>Open Inventory & Menu Switch</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>

                </div>

            </main>
        </div>

    </div>

    <x-scroll-to-top />

</body>
</html>
