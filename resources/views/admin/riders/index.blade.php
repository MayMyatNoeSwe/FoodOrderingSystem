<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rider Management - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ createModalOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= DESKTOP SIDEBAR NAVIGATION ================= -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 p-6 hidden md:flex flex-col justify-between shrink-0">
            <div class="space-y-8">
                <!-- Brand Logo -->
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
                <nav class="space-y-1.5 text-sm font-semibold">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                        <span>📊</span> <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                        <span>📁</span> <span>Categories</span>
                    </a>
                    <a href="{{ route('admin.menuItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                        <span>🍔</span> <span>Menu Items</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                        <span>📦</span> <span>Orders</span>
                    </a>
                    <a href="{{ route('admin.riders.index') }}" class="flex items-center gap-3 px-4 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">
                        <span>🛵</span> <span>Riders</span>
                        <span class="ms-auto bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $riders->total() }}</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                        <span>👤</span> <span>Users</span>
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
            </div>
        </aside>

        <!-- ================= MAIN CONTENT AREA ================= -->
        <main class="flex-1 flex flex-col min-w-0">

            <!-- Top Header -->
            <header class="bg-slate-900/60 backdrop-blur-md border-b border-slate-800 sticky top-0 z-30 px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2.5">
                        <span>🛵</span> Rider Management System
                    </h1>
                    <p class="text-slate-400 text-xs mt-0.5 font-medium">Manage delivery personnel, track active orders, and register new riders</p>
                </div>

                <button @click="createModalOpen = true" class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/20 transition-all flex items-center gap-2 cursor-pointer">
                    <span>+</span>
                    <span>Create New Rider</span>
                </button>
            </header>

            <div class="p-6 sm:p-8 space-y-8 flex-1">

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-bold rounded-2xl flex items-center gap-3">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Registered Riders</p>
                        <p class="text-3xl font-black text-white mt-2">{{ $riders->total() }}</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Active Deliveries Now</p>
                        <p class="text-3xl font-black text-purple-400 mt-2">{{ $riders->sum('active_deliveries_count') }}</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Completed Deliveries</p>
                        <p class="text-3xl font-black text-emerald-400 mt-2">{{ $riders->sum('completed_deliveries_count') }}</p>
                    </div>
                </div>

                <!-- Riders Table -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-black text-white">Rider Accounts</h2>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3.5">Rider Name / Phone</th>
                                    <th class="px-4 py-3.5">Email</th>
                                    <th class="px-4 py-3.5">City / Zone</th>
                                    <th class="px-4 py-3.5 text-center">Active Deliveries</th>
                                    <th class="px-4 py-3.5 text-center">Completed Deliveries</th>
                                    <th class="px-4 py-3.5 text-right">Joined Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                @forelse($riders as $rider)
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-2xl bg-orange-500/20 border border-orange-500/30 text-orange-400 flex items-center justify-center text-lg font-black shrink-0">
                                                    🛵
                                                </div>
                                                <div>
                                                    <div class="font-bold text-white text-sm">{{ $rider->name }}</div>
                                                    <div class="text-[11px] text-slate-400">📞 {{ $rider->phone_number ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 font-mono text-slate-400">{{ $rider->email }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded-lg text-[11px] font-bold">
                                                📍 {{ $rider->city ?? 'Yangon' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @if($rider->active_deliveries_count > 0)
                                                <span class="px-3 py-1 bg-purple-500/20 border border-purple-500/30 text-purple-400 font-black rounded-full text-xs animate-pulse">
                                                    🛵 {{ $rider->active_deliveries_count }} Active
                                                </span>
                                            @else
                                                <span class="text-slate-500 text-xs font-semibold">0 (Available)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-black rounded-full text-xs">
                                                ✅ {{ $rider->completed_deliveries_count }} Done
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right text-slate-400">
                                            {{ $rider->created_at ? $rider->created_at->format('M d, Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center text-slate-500 font-medium">
                                            No riders registered yet. Click "Create New Rider" to add one!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4">
                        {{ $riders->links() }}
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ================= CREATE NEW RIDER MODAL ================= -->
    <div x-show="createModalOpen" 
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.outside="createModalOpen = false" 
             class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-orange-500/20 text-orange-400 flex items-center justify-center text-xl">🛵</div>
                    <h3 class="text-lg font-black text-white">Create New Rider Account</h3>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-white p-1">✕</button>
            </div>

            <form method="POST" action="{{ route('admin.riders.store') }}" class="space-y-4 text-xs">
                @csrf
                
                <div>
                    <label class="block font-bold text-slate-400 mb-1">Rider Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Mg Mg Rider" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-400 mb-1">Email Address (Login ID)</label>
                    <input type="email" name="email" required placeholder="rider@foodorder.com" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-400 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" required placeholder="09xxxxxxxxx" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-400 mb-1">City / Zone</label>
                    <input type="text" name="city" value="Yangon" placeholder="Yangon" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-400 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-orange-500 focus:outline-none">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/20">Create Rider</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
