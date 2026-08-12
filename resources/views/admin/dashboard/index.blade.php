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
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">
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

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navOrderCount }}</span>
                    </a>

                    <a href="{{ route('admin.orderItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span>Order Items</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navOrderItemCount ?? 0 }}</span>
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
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navOrderCount }}</span>
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
