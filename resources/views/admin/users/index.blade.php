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
<body class="font-sans antialiased text-slate-800 bg-slate-950 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ mobileMenuOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="users" />

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
                            <span>User Accounts</span>
                            <span class="hidden sm:inline-flex bg-orange-500/20 text-orange-400 border border-orange-500/30 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                Access Control
                            </span>
                        </h1>
                        <p class="text-xs text-slate-400 hidden sm:block">Manage registered customer profiles, administrative roles, and system credentials</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
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

                <!-- Error Alert Toast -->
                @if(session('error'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: @json(session('error')),
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                                background: '#0f172a',
                                color: '#f8fafc',
                                customClass: {
                                    popup: 'border border-red-500/30 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                <!-- Validation Errors Banner -->
                @if($errors->any())
                    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-2xl text-red-400 text-xs font-semibold space-y-1.5 shadow-lg shadow-red-500/5">
                        <div class="flex items-center gap-2 text-red-300 font-bold mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Please fix the following validation errors:</span>
                        </div>
                        @foreach($errors->all() as $error)
                            <p class="pl-6">• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Overview Stat Metric Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Metric 1: Total Users -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total User Accounts</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center font-bold text-base">
                                👥
                            </div>
                        </div>
                        <div class="text-3xl font-black text-white mt-2">{{ number_format($totalUsersCount) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                            <span>Registered database users</span>
                        </div>
                    </div>

                    <!-- Metric 2: System Administrators -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">System Admins</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-base">
                                👑
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-400 mt-2">{{ number_format($adminCount) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Administrative privileges</div>
                    </div>

                    <!-- Metric 3: Customer Accounts -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Customer Accounts</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-base">
                                🛒
                            </div>
                        </div>
                        <div class="text-3xl font-black text-blue-400 mt-2">{{ number_format($customerCount) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Food ordering buyers</div>
                    </div>

                    <!-- Metric 4: New Accounts This Month -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">New This Month</span>
                            <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold text-base">
                                ✨
                            </div>
                        </div>
                        <div class="text-3xl font-black text-purple-400 mt-2">{{ number_format($newThisMonthCount) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Recent registrations</div>
                    </div>

                </div>

                <!-- Users Directory Header & Controls -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-6">
                    
                    <!-- Search & Action Toolbar -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-white tracking-tight">User Account Directory</h3>
                            <p class="text-slate-400 text-xs mt-0.5">Filter by role, search user name or email address</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <!-- Search & Filter Form -->
                            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                
                                <div class="relative min-w-[220px]">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ $search }}" 
                                           placeholder="Search name or email..." 
                                           class="w-full bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-500">
                                    
                                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>

                                    @if($search)
                                        <a href="{{ route('admin.users.index') }}" title="Clear Search" class="absolute right-2.5 top-2.5 text-slate-500 hover:text-white p-0.5 text-xs font-bold rounded-full">✕</a>
                                    @endif
                                </div>

                                <!-- Role Filter Dropdown -->
                                <select name="role" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                    <option value="">All Roles</option>
                                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>👑 Admin</option>
                                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>👤 Customer</option>
                                </select>

                                @if($search || $role)
                                    <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 flex items-center justify-center gap-1">
                                        <span>✕</span>
                                        <span>Reset</span>
                                    </a>
                                @endif
                            </form>

                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3.5">User Profile</th>
                                    <th class="px-4 py-3.5">Email Address</th>
                                    <th class="px-4 py-3.5">Role</th>
                                    <th class="px-4 py-3.5">Orders Placed</th>
                                    <th class="px-4 py-3.5 text-right">Registered Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                @forelse($users as $user)
                                    @php
                                        $initial = strtoupper(substr($user->name, 0, 1));
                                        $isAdmin = $user->isAdmin();
                                        $isSelf = (Auth::id() === $user->id);
                                    @endphp

                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        
                                        <!-- User Profile -->
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full {{ $isAdmin ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40' : 'bg-orange-500/20 text-orange-400 border border-orange-500/40' }} flex items-center justify-center font-black text-sm shrink-0">
                                                    {{ $initial }}
                                                </div>
                                                <div>
                                                    <div class="font-extrabold text-white text-sm flex items-center gap-2">
                                                        <span>{{ $user->name }}</span>
                                                        @if($isSelf)
                                                            <span class="px-2 py-0.5 bg-orange-500/20 text-orange-400 border border-orange-500/30 text-[10px] font-bold rounded-full">You</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 font-mono">ID: #{{ $user->id }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Email Address -->
                                        <td class="px-4 py-4 font-mono text-slate-300">
                                            {{ $user->email }}
                                        </td>

                                        <!-- Role Badge -->
                                        <td class="px-4 py-4">
                                            @if($isAdmin)
                                                <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/30 text-[11px] font-bold rounded-full inline-flex items-center gap-1">
                                                    <span>👑</span>
                                                    <span>System Admin</span>
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/30 text-[11px] font-bold rounded-full inline-flex items-center gap-1">
                                                    <span>👤</span>
                                                    <span>Customer</span>
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Orders Placed -->
                                        <td class="px-4 py-4">
                                            @if($isAdmin)
                                                <span class="px-2.5 py-1 bg-slate-950 border border-slate-800 text-slate-500 text-[11px] font-semibold">
                                                    — N/A (Admin)
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-slate-300 text-[11px] font-bold">
                                                    {{ $user->orders_count }} Orders
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Registered Date -->
                                        <td class="px-4 py-4 text-right text-slate-400 text-[11px]">
                                            <div>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</div>
                                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $user->created_at ? $user->created_at->diffForHumans() : '' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                                            <div class="max-w-xs mx-auto space-y-3">
                                                <div class="text-3xl">👥</div>
                                                <div class="font-bold text-slate-300 text-sm">No Users Found</div>
                                                <p class="text-xs text-slate-500">
                                                    @if($search || $role)
                                                        No user accounts matching current filter criteria. Try clearing search keyword.
                                                    @else
                                                        No user accounts registered yet.
                                                    @endif
                                                </p>
                                                @if($search || $role)
                                                    <a href="{{ route('admin.users.index') }}" class="inline-block px-4 py-2 bg-slate-800 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 hover:text-white">Clear Search Filter</a>
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
                        <div class="pt-2 border-t border-slate-800">
                            {{ $users->links() }}
                        </div>
                    @endif

                </div>

            </main>
        </div>

    </div>

</body>
</html>
