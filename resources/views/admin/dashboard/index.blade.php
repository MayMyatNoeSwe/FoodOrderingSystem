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
                <a href="{{ route('home') }}" class="flex items-center gap-3">
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

                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Categories</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $totalCategoriesCount }}</span>
                    </a>

                    <a href="{{ route('admin.menuItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Menu Items</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $totalFoodItems }}</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                        <span class="ms-auto bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $activeOrdersCount }}</span>
                    </a>
                </nav>
            </div>

            <!-- Admin Profile Quick Footer -->
            <div class="border-t border-slate-800 pt-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="text-xs">
                        <div class="font-bold text-white">{{ Auth::user()->name }}</div>
                        <div class="text-amber-400 font-medium">System Admin</div>
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
                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Live Database System</span>
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
                    
                    <!-- Stat 1: Total Sales Revenue -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Sales Revenue</div>
                        <div class="text-3xl font-black text-white mt-2">{{ number_format($totalSalesRevenue) }} MMK</div>
                        <div class="text-xs text-emerald-400 font-semibold mt-2 flex items-center gap-1">
                            <span>↑ 18.5%</span> <span class="text-slate-500">vs last month</span>
                        </div>
                    </div>

                    <!-- Stat 2: Active Orders -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Active Orders</div>
                        <div class="text-3xl font-black text-orange-400 mt-2">{{ $activeOrdersCount }} Orders</div>
                        <div class="text-xs text-orange-400/80 font-semibold mt-2">{{ $pendingPreparationCount }} Pending Preparation</div>
                    </div>

                    <!-- Stat 3: Total Food Items -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Food Items</div>
                        <div class="text-3xl font-black text-white mt-2">{{ $totalFoodItems }} Items</div>
                        <div class="text-xs text-slate-500 font-semibold mt-2">Across {{ $totalCategoriesCount }} Categories</div>
                    </div>

                    <!-- Stat 4: Registered Customers -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                        <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Registered Customers</div>
                        <div class="text-3xl font-black text-white mt-2">{{ $registeredCustomersCount }}</div>
                        <div class="text-xs text-emerald-400 font-semibold mt-2">Active Accounts</div>
                    </div>

                </div>

                <!-- Recent Orders Table -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">Recent Customer Orders</h3>
                            <p class="text-slate-400 text-xs mt-0.5">Real-time incoming orders from MySQL database</p>
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 bg-orange-500/10 border border-orange-500/30 text-orange-400 hover:bg-orange-500/20 text-xs font-semibold rounded-xl transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Refresh Orders</span>
                        </a>
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
                                    <th class="px-4 py-3.5">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                @forelse($recentOrders as $order)
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-4 font-bold text-white">#{{ $order->order_number }}</td>
                                        <td class="px-4 py-4">
                                            <span class="font-bold text-slate-200">{{ $order->user ? $order->user->name : 'Guest User' }}</span>
                                            <span class="block text-[11px] text-slate-400">{{ $order->delivery_phone }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if($order->payment_method === 'kbzpay')
                                                <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded border border-blue-500/30 uppercase font-bold text-[10px]">KBZPay</span>
                                            @elseif($order->payment_method === 'wavepay')
                                                <span class="px-2 py-0.5 bg-purple-500/20 text-purple-400 rounded border border-purple-500/30 uppercase font-bold text-[10px]">WavePay</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 rounded border border-emerald-500/30 uppercase font-bold text-[10px]">COD</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 font-bold text-white">{{ number_format($order->total_amount) }} MMK</td>
                                        <td class="px-4 py-4">
                                            @if($order->status === 'completed')
                                                <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 rounded-full font-bold border border-emerald-500/30 capitalize">Completed</span>
                                            @elseif($order->status === 'preparing')
                                                <span class="px-2.5 py-1 bg-amber-500/20 text-amber-400 rounded-full font-bold border border-amber-500/30 capitalize">Preparing</span>
                                            @elseif($order->status === 'delivering')
                                                <span class="px-2.5 py-1 bg-blue-500/20 text-blue-400 rounded-full font-bold border border-blue-500/30 capitalize">Out for Delivery</span>
                                            @elseif($order->status === 'cancelled')
                                                <span class="px-2.5 py-1 bg-red-500/20 text-red-400 rounded-full font-bold border border-red-500/30 capitalize">Cancelled</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-slate-700 text-slate-300 rounded-full font-bold capitalize">{{ $order->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-slate-400 text-[11px]">
                                            {{ $order->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">No orders found in database.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>

    </div>

</body>
</html>
