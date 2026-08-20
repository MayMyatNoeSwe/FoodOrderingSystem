<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Customer Management - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmToggleBan(form, customerName, isCurrentlyBanned) {
            const actionText = isCurrentlyBanned ? 'Unban & Activate' : 'Ban & Suspend';
            const actionIcon = isCurrentlyBanned ? 'question' : 'warning';
            const confirmBtnColor = isCurrentlyBanned ? '#10b981' : '#ef4444';
            const infoText = isCurrentlyBanned 
                ? 'This customer\'s account will be restored to active status and they will be allowed to log in and order food.'
                : 'This customer will be restricted from logging in and placing any new orders.';

            Swal.fire({
                title: actionText + ' \'' + customerName + '\'?',
                html: `<div class="text-sm text-slate-600 space-y-2">
                        <p>Are you sure you want to <strong>${actionText}</strong> account for <span class="text-orange-600 font-bold">'${customerName}'</span>?</p>
                        <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200">${infoText}</p>
                       </div>`,
                icon: actionIcon,
                showCancelButton: true,
                confirmButtonColor: confirmBtnColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, ' + actionText,
                cancelButtonText: 'Cancel',
                background: '#ffffff',
                color: '#0f172a',
                customClass: {
                    popup: 'border border-slate-200 rounded-3xl shadow-2xl',
                    title: 'text-slate-900 font-bold text-lg',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg cursor-pointer',
                    cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs cursor-pointer'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ mobileMenuOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="customers" />

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
                            <span>👥</span>
                            <span>Customer Management System</span>
                            <span class="hidden sm:inline-flex bg-orange-50 text-orange-600 border border-orange-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                Account Status & Bans
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">View customer directory, monitor ordering activity, and manage account ban/active status</p>
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

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold rounded-2xl flex items-center gap-3 shadow-sm">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm font-bold rounded-2xl flex items-center gap-3 shadow-sm">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-semibold space-y-1 shadow-sm">
                        <div class="font-bold mb-1">Please fix the following validation errors:</div>
                        @foreach($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Overview Stat Metric Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Metric 1: Total Registered Customers -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Customers</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-base border border-orange-100">
                                👥
                            </div>
                        </div>
                        <div class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalCustomers) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                            <span>Registered database accounts</span>
                        </div>
                    </div>

                    <!-- Metric 2: Active Customers -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active Customers</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-100">
                                🟢
                            </div>
                        </div>
                        <div class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($activeCustomers) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Can log in & place orders</div>
                    </div>

                    <!-- Metric 3: Banned Customers -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Banned Accounts</span>
                            <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-base border border-red-100">
                                🚫
                            </div>
                        </div>
                        <div class="text-3xl font-black text-red-600 mt-2">{{ number_format($bannedCustomers) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Restricted / Suspended</div>
                    </div>

                    <!-- Metric 4: Total Customer Orders -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Customer Orders</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base border border-blue-100">
                                📦
                            </div>
                        </div>
                        <div class="text-3xl font-black text-blue-600 mt-2">{{ number_format($totalCustomerOrders) }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Total lifetime volume</div>
                    </div>

                </div>

                <!-- Customers Directory Header & Controls -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6">
                    
                    <!-- Search & Filter Toolbar -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Customer Directory</h3>
                            <p class="text-slate-500 text-xs mt-0.5">Manage customer account access and ban/unban permissions</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <!-- Search & Filter Form -->
                            <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                
                                <div class="relative min-w-[240px]">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ $search }}" 
                                           placeholder="Search name, email, phone..." 
                                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-400">
                                    
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>

                                    @if($search)
                                        <a href="{{ route('admin.customers.index', ['status' => $status]) }}" title="Clear Search" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 p-0.5 text-xs font-bold rounded-full">✕</a>
                                    @endif
                                </div>

                                <!-- Status Filter Dropdown -->
                                <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer font-medium">
                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>🟢 Active Only</option>
                                    <option value="banned" {{ $status === 'banned' ? 'selected' : '' }}>🔴 Banned Only</option>
                                </select>

                                @if($search || $status !== 'all')
                                    <a href="{{ route('admin.customers.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 flex items-center justify-center gap-1">
                                        <span>✕</span>
                                        <span>Reset</span>
                                    </a>
                                @endif
                            </form>

                        </div>
                    </div>

                    <!-- Customers Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5">Customer Profile</th>
                                    <th class="px-4 py-3.5">Contact Details</th>
                                    <th class="px-4 py-3.5">City / Location</th>
                                    <th class="px-4 py-3.5 text-center">Account Status</th>
                                    <th class="px-4 py-3.5 text-center">Orders Placed</th>
                                    <th class="px-4 py-3.5 text-right">Total Spent</th>
                                    <th class="px-4 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                @forelse($customers as $customer)
                                    @php
                                        $initial = strtoupper(substr($customer->name, 0, 1));
                                        $isBanned = $customer->isBanned();
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition-colors {{ $isBanned ? 'bg-red-50/30' : '' }}">
                                        
                                        <!-- Customer Profile -->
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full {{ $isBanned ? 'bg-gradient-to-tr from-rose-500 to-red-600 text-white' : 'bg-gradient-to-tr from-orange-400 to-amber-500 text-white' }} flex items-center justify-center font-black text-sm shadow-sm shrink-0">
                                                    {{ $initial }}
                                                </div>
                                                <div>
                                                    <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                                        <span>{{ $customer->name }}</span>
                                                        @if($isBanned)
                                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 border border-red-200 text-[10px] font-bold rounded-full">Banned</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 font-mono">ID: #{{ $customer->id }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Contact Details -->
                                        <td class="px-4 py-4 space-y-1">
                                            <div class="font-mono text-slate-700 font-semibold flex items-center gap-1.5">
                                                <span>✉️</span>
                                                <a href="mailto:{{ $customer->email }}" class="hover:text-orange-600 transition-colors">{{ $customer->email }}</a>
                                            </div>
                                            @if($customer->phone_number)
                                                <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1.5">
                                                    <span>📞</span>
                                                    <a href="tel:{{ $customer->phone_number }}" class="hover:text-orange-600 transition-colors">{{ $customer->phone_number }}</a>
                                                </div>
                                            @else
                                                <div class="text-[10px] text-slate-400 italic">No phone registered</div>
                                            @endif
                                        </td>

                                        <!-- City / Address -->
                                        <td class="px-4 py-4">
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-bold rounded-md">
                                                <span>📍</span>
                                                <span>{{ $customer->city ?? 'Yangon' }}</span>
                                            </div>
                                            @if($customer->detail_address)
                                                <div class="text-[11px] text-slate-500 truncate max-w-xs mt-1" title="{{ $customer->detail_address }}">
                                                    {{ $customer->detail_address }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Account Status Column -->
                                        <td class="px-4 py-4 text-center">
                                            @if($isBanned)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 border border-red-200 text-red-700 font-extrabold rounded-full text-[11px]">
                                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                                    <span>Banned / Suspended</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold rounded-full text-[11px]">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                                    <span>Active</span>
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Orders Placed -->
                                        <td class="px-4 py-4 text-center">
                                            @if($customer->orders_count > 0)
                                                <a href="{{ route('admin.orders.index', ['search' => $customer->name]) }}" 
                                                   title="View orders for this customer"
                                                   class="px-2.5 py-1 bg-orange-50 hover:bg-orange-100 border border-orange-200 text-orange-700 rounded-lg text-[11px] font-bold inline-flex items-center gap-1 transition-all">
                                                    <span>🛒</span>
                                                    <span>{{ $customer->orders_count }} Orders</span>
                                                </a>
                                            @else
                                                <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 text-slate-400 text-[11px] font-semibold rounded-lg">
                                                    0 Orders
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Total Spent -->
                                        <td class="px-4 py-4 text-right">
                                            <div class="font-extrabold text-slate-900 font-mono text-xs">
                                                {{ number_format($customer->total_spent ?? 0) }} Ks
                                            </div>
                                            <div class="text-[10px] text-slate-400">Lifetime spend</div>
                                        </td>

                                        <!-- Actions: Ban / Unban & View Orders -->
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- View Orders Action -->
                                                <a href="{{ route('admin.orders.index', ['search' => $customer->name]) }}" 
                                                   title="View Customer Orders"
                                                   class="p-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-600 rounded-lg border border-slate-200 transition-all text-xs flex items-center gap-1 font-bold">
                                                    <span>🛍️</span>
                                                    <span class="hidden sm:inline">Orders</span>
                                                </a>

                                                <!-- Ban / Unban Form Action -->
                                                <form method="POST" action="{{ route('admin.customers.toggle-status', $customer) }}" onsubmit="return confirmToggleBan(this, '{{ addslashes($customer->name) }}', {{ $isBanned ? 'true' : 'false' }});">
                                                    @csrf
                                                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                                    
                                                    @if($isBanned)
                                                        <button type="submit" 
                                                                title="Unban this customer"
                                                                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 active:bg-emerald-200 text-emerald-700 border border-emerald-300 rounded-lg transition-all text-[11px] font-extrabold flex items-center gap-1 cursor-pointer shadow-sm">
                                                            <span>✅</span>
                                                            <span>Unban</span>
                                                        </button>
                                                    @else
                                                        <button type="submit" 
                                                                title="Ban this customer from ordering"
                                                                class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 text-rose-700 border border-rose-300 rounded-lg transition-all text-[11px] font-extrabold flex items-center gap-1 cursor-pointer shadow-sm">
                                                            <span>🚫</span>
                                                            <span>Ban</span>
                                                        </button>
                                                    @endif
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                            <div class="max-w-xs mx-auto space-y-3">
                                                <div class="text-3xl">👥</div>
                                                <div class="font-bold text-slate-800 text-sm">No Customers Found</div>
                                                <p class="text-xs text-slate-500">
                                                    @if($search || $status !== 'all')
                                                        No customer accounts matching current search/status criteria.
                                                    @else
                                                        No customer accounts registered yet.
                                                    @endif
                                                </p>
                                                @if($search || $status !== 'all')
                                                    <a href="{{ route('admin.customers.index') }}" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 hover:bg-slate-200">Clear Filter</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Custom Pagination Footer -->
                    @if($customers->hasPages())
                        <div class="pt-2 border-t border-slate-100">
                            {{ $customers->links() }}
                        </div>
                    @endif

                </div>

            </main>
        </div>

    </div>

</body>
</html>
