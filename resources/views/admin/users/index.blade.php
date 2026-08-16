<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>User Accounts - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ mobileMenuOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="users" />

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
                            <span>User Accounts</span>
                            <span class="hidden sm:inline-flex bg-orange-50 text-orange-600 border border-orange-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                Access Directory
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">View registered customer profiles, riders, and administrative team accounts</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
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

                <!-- Overview Stat Metric Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Metric 1: Total Users -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total User Accounts</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-base border border-orange-100">
                                👥
                            </div>
                        </div>
                        <div class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalUsersCount) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                            <span>Registered database users</span>
                        </div>
                    </div>

                    <!-- Metric 2: System Administrators -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">System Admins</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base border border-amber-100">
                                👑
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-600 mt-2">{{ number_format($adminCount) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Administrative privileges</div>
                    </div>

                    <!-- Metric 3: Riders -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Delivery Riders</span>
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-base border border-purple-100">
                                🛵
                            </div>
                        </div>
                        <div class="text-3xl font-black text-purple-600 mt-2">{{ number_format($riderCount ?? 0) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Fleet delivery staff</div>
                    </div>

                    <!-- Metric 4: Customer Accounts -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Customer Accounts</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base border border-blue-100">
                                🛒
                            </div>
                        </div>
                        <div class="text-3xl font-black text-blue-600 mt-2">{{ number_format($customerCount) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Food ordering buyers</div>
                    </div>

                </div>

                <!-- Users Directory Header & Controls -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6">
                    
                    <!-- Search & Action Toolbar -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">User Account Directory</h3>
                            <p class="text-slate-500 text-xs mt-0.5">View-only directory of all user accounts</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <!-- Search & Filter Form -->
                            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                
                                <div class="relative min-w-[220px]">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ $search }}" 
                                           placeholder="Search name or email..." 
                                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-400">
                                    
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>

                                    @if($search)
                                        <a href="{{ route('admin.users.index') }}" title="Clear Search" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 p-0.5 text-xs font-bold rounded-full">✕</a>
                                    @endif
                                </div>

                                <!-- Role Filter Dropdown -->
                                <select name="role" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                    <option value="">All Roles</option>
                                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>👑 Admin</option>
                                    <option value="rider" {{ $role === 'rider' ? 'selected' : '' }}>🛵 Rider</option>
                                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>👤 Customer</option>
                                </select>

                                @if($search || $role)
                                    <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 flex items-center justify-center gap-1">
                                        <span>✕</span>
                                        <span>Reset</span>
                                    </a>
                                @endif
                            </form>

                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5">User Profile</th>
                                    <th class="px-4 py-3.5">Email Address</th>
                                    <th class="px-4 py-3.5">Role</th>
                                    <th class="px-4 py-3.5">Orders Placed</th>
                                    <th class="px-4 py-3.5 text-right">Registered Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                @forelse($users as $user)
                                    @php
                                        $initial = strtoupper(substr($user->name, 0, 1));
                                        $isAdmin = $user->isAdmin();
                                        $isRider = ($user->role === 'rider');
                                        $isSelf = (Auth::id() === $user->id);
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition-colors">
                                        
                                        <!-- User Profile -->
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full {{ $isAdmin ? 'bg-amber-50 text-amber-600 border border-amber-200' : ($isRider ? 'bg-purple-50 text-purple-600 border border-purple-200' : 'bg-orange-50 text-orange-600 border border-orange-200') }} flex items-center justify-center font-black text-sm shrink-0">
                                                    {{ $initial }}
                                                </div>
                                                <div>
                                                    <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                                        <span>{{ $user->name }}</span>
                                                        @if($isSelf)
                                                            <span class="px-2 py-0.5 bg-orange-50 text-orange-600 border border-orange-200 text-[10px] font-bold rounded-full">You</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 font-mono">ID: #{{ $user->id }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Email Address -->
                                        <td class="px-4 py-4 font-mono text-slate-600">
                                            {{ $user->email }}
                                        </td>

                                        <!-- Role Badge -->
                                        <td class="px-4 py-4">
                                            @if($isAdmin)
                                                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold rounded-full inline-flex items-center gap-1">
                                                    <span>👑</span>
                                                    <span>System Admin</span>
                                                </span>
                                            @elseif($isRider)
                                                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 text-[11px] font-bold rounded-full inline-flex items-center gap-1">
                                                    <span>🛵</span>
                                                    <span>Delivery Rider</span>
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-bold rounded-full inline-flex items-center gap-1">
                                                    <span>👤</span>
                                                    <span>Customer</span>
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Orders Placed -->
                                        <td class="px-4 py-4">
                                            @if($isAdmin)
                                                <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 text-slate-500 text-[11px] font-semibold rounded-lg">
                                                    — N/A (Admin)
                                                </span>
                                            @elseif($isRider)
                                                <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 text-slate-500 text-[11px] font-semibold rounded-lg">
                                                    — N/A (Rider)
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 text-[11px] font-bold">
                                                    {{ $user->orders_count }} Orders
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Registered Date -->
                                        <td class="px-4 py-4 text-right text-slate-500 text-[11px]">
                                            <div>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $user->created_at ? $user->created_at->diffForHumans() : '' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                                            <div class="max-w-xs mx-auto space-y-3">
                                                <div class="text-3xl">👥</div>
                                                <div class="font-bold text-slate-800 text-sm">No Users Found</div>
                                                <p class="text-xs text-slate-500">
                                                    @if($search || $role)
                                                        No user accounts matching current filter criteria. Try clearing search keyword.
                                                    @else
                                                        No user accounts registered yet.
                                                    @endif
                                                </p>
                                                @if($search || $role)
                                                    <a href="{{ route('admin.users.index') }}" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 hover:bg-slate-200">Clear Search Filter</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Custom Pagination Footer -->
                    @if($users->hasPages())
                        <div class="pt-2 border-t border-slate-100">
                            {{ $users->links() }}
                        </div>
                    @endif

                </div>

            </main>
        </div>

    </div>

</body>
</html>
