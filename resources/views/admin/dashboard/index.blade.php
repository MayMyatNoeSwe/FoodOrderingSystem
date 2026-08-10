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
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 selection:bg-orange-500 selection:text-white">

    <div class="min-h-screen flex">

        <!-- ================= SIDEBAR ================= -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 hidden md:flex flex-col justify-between p-6">
            <div class="space-y-8">
                <!-- Admin Brand -->
                <a href="/" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black shadow-lg shadow-orange-500/30">
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

                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Categories</span>
                    </a>

                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Menu Items</span>
                    </a>

                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                        <span class="ms-auto bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">5</span>
                    </a>
                </nav>
            </div>

            <!-- Admin Profile Quick Footer -->
            <div class="border-t border-slate-800 pt-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 font-bold text-sm">
                        A
                    </div>
                    <div class="text-xs">
                        <div class="font-bold text-white">{{ Auth::user()->name }}</div>
                        <div class="text-slate-500">Administrator</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-red-400 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Topbar -->
            <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-white">Admin Dashboard</h1>
                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-2.5 py-0.5 rounded-full">
                        Live System
                    </span>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-all flex items-center gap-2">
                        <span>View Storefront</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Dashboard Content Grid -->
            <main class="flex-1 p-6 space-y-8 overflow-y-auto">
                
                <!-- Stat Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Stat 1 -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Sales Revenue</div>
                        <div class="text-3xl font-black text-white mt-2">$2,450.00</div>
                        <div class="text-xs text-emerald-400 font-semibold mt-2 flex items-center gap-1">
                            <span>↑ 18.5%</span> <span class="text-slate-500">vs last week</span>
                        </div>
                    </div>

                    <!-- Stat 2 -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Active Orders</div>
                        <div class="text-3xl font-black text-orange-400 mt-2">18 Orders</div>
                        <div class="text-xs text-orange-400/80 font-semibold mt-2">5 Pending Preparation</div>
                    </div>

                    <!-- Stat 3 -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Food Items</div>
                        <div class="text-3xl font-black text-white mt-2">45 Items</div>
                        <div class="text-xs text-slate-500 font-semibold mt-2">Across 5 Categories</div>
                    </div>

                    <!-- Stat 4 -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Registered Customers</div>
                        <div class="text-3xl font-black text-white mt-2">1,240</div>
                        <div class="text-xs text-emerald-400 font-semibold mt-2">↑ 12 New today</div>
                    </div>

                </div>

                <!-- Recent Orders Table -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">Recent Customer Orders</h3>
                            <p class="text-slate-400 text-xs mt-0.5">Real-time incoming orders and delivery statuses</p>
                        </div>
                        <button class="px-3.5 py-2 bg-orange-500/10 border border-orange-500/30 text-orange-400 hover:bg-orange-500/20 text-xs font-semibold rounded-xl transition-all">
                            Refresh Orders
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950/60 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3.5">Order ID</th>
                                    <th class="px-4 py-3.5">Customer</th>
                                    <th class="px-4 py-3.5">Payment</th>
                                    <th class="px-4 py-3.5">Amount</th>
                                    <th class="px-4 py-3.5">Status</th>
                                    <th class="px-4 py-3.5">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                <tr>
                                    <td class="px-4 py-4 font-bold text-white">#ORD-9021</td>
                                    <td class="px-4 py-4">John Doe <span class="block text-[11px] text-slate-500">+95 9 1234 5678</span></td>
                                    <td class="px-4 py-4"><span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded border border-blue-500/30">KBZPay</span></td>
                                    <td class="px-4 py-4 font-bold text-white">$21.49</td>
                                    <td class="px-4 py-4">
                                        <span class="px-2.5 py-1 bg-amber-500/20 text-amber-400 rounded-full font-bold border border-amber-500/30">
                                            Preparing
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <button class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-all text-[11px] font-semibold">
                                            Manage Status
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4 py-4 font-bold text-white">#ORD-9020</td>
                                    <td class="px-4 py-4">Sarah Connor <span class="block text-[11px] text-slate-500">+95 9 9876 5432</span></td>
                                    <td class="px-4 py-4"><span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 rounded border border-emerald-500/30">Cash on Delivery</span></td>
                                    <td class="px-4 py-4 font-bold text-white">$15.99</td>
                                    <td class="px-4 py-4">
                                        <span class="px-2.5 py-1 bg-blue-500/20 text-blue-400 rounded-full font-bold border border-blue-500/30">
                                            Out for Delivery
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <button class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-all text-[11px] font-semibold">
                                            Manage Status
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4 py-4 font-bold text-white">#ORD-9019</td>
                                    <td class="px-4 py-4">Michael Scott <span class="block text-[11px] text-slate-500">+95 9 4455 6677</span></td>
                                    <td class="px-4 py-4"><span class="px-2 py-0.5 bg-purple-500/20 text-purple-400 rounded border border-purple-500/30">WavePay</span></td>
                                    <td class="px-4 py-4 font-bold text-white">$34.50</td>
                                    <td class="px-4 py-4">
                                        <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 rounded-full font-bold border border-emerald-500/30">
                                            Completed
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <button class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-all text-[11px] font-semibold">
                                            View Order
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>

    </div>

</body>
</html>
