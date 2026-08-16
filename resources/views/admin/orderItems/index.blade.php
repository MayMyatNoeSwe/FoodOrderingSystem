<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Order Items Table — {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteOrderItem(form, itemName, orderNumber) {
            Swal.fire({
                title: 'Remove Item?',
                html: `Are you sure you want to remove <strong class="text-orange-400">${itemName}</strong> from Order <strong class="text-white">#${orderNumber}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: 'Yes, Delete Item',
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
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ mobileMenuOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="orderItems" />

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Topbar Header -->
            <header class="bg-slate-900/90 backdrop-blur-md sticky top-0 z-30 border-b border-slate-800 px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button @click="mobileMenuOpen = true" class="md:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-xl font-black text-white tracking-tight flex items-center gap-2.5">
                            <span>Order Items Management & Sales Breakdown</span>
                            <span class="bg-orange-500/20 text-orange-400 border border-orange-500/30 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                                <span>Itemized Master Table</span>
                            </span>
                        </h1>
                        <p class="text-xs text-slate-400 hidden sm:block">Master view of individual ordered food items across all customer transactions</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-all flex items-center gap-2">
                        <span>📦 Orders Queue</span>
                    </a>
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-6 overflow-y-auto">

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
                                customClass: { popup: 'border border-emerald-500/30 rounded-2xl shadow-xl' }
                            });
                        });
                    </script>
                @endif

                <!-- Stat Metric Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Stat 1: Total Items Sold -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Items Sold</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center font-bold text-base">
                                🍽️
                            </div>
                        </div>
                        <div class="text-3xl font-black text-white mt-2">{{ number_format($totalQuantitySold) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Dishes delivered across orders</div>
                    </div>

                    <!-- Stat 2: Total Items Value -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Gross Items Revenue</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-base">
                                💰
                            </div>
                        </div>
                        <div class="text-2xl font-black text-white mt-2 truncate">{{ number_format($totalItemsRevenue) }} <span class="text-xs text-orange-400 font-bold">MMK</span></div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Accumulated food item subtotals</div>
                    </div>

                    <!-- Stat 3: Top Selling Dish -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Top Selling Dish</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-base">
                                ⭐
                            </div>
                        </div>
                        <div class="text-lg font-black text-amber-400 mt-2 truncate" title="{{ $topItemName }}">{{ $topItemName }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Most frequently ordered food</div>
                    </div>

                    <!-- Stat 4: Unique Order Items Count -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Item Records</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-base">
                                📑
                            </div>
                        </div>
                        <div class="text-3xl font-black text-blue-400 mt-2">{{ number_format($orderItems->total()) }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Individual itemized entries</div>
                    </div>

                </div>

                <!-- Order Items Master Table Container -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-6">
                    
                    <!-- Search & Filter Controls -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-white tracking-tight">Order Items Master Table</h3>
                            <p class="text-slate-400 text-xs mt-0.5">Filter by dish name, category, or customer order number</p>
                        </div>

                        <form method="GET" action="{{ route('admin.orderItems.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            
                            <!-- Search Field -->
                            <div class="relative min-w-[220px]">
                                <input type="text" 
                                       name="search" 
                                       value="{{ $search }}" 
                                       placeholder="Search item, order #, customer..." 
                                       class="w-full bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 pl-9 focus:ring-0 transition-all placeholder-slate-500">
                                
                                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <!-- Category Filter Dropdown -->
                            <select name="category_id" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>

                            <!-- Status Filter Dropdown -->
                            <select name="status" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer">
                                <option value="">All Order Statuses</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="preparing" {{ $status === 'preparing' ? 'selected' : '' }}>👨‍🍳 Preparing</option>
                                <option value="delivering" {{ $status === 'delivering' ? 'selected' : '' }}>🛵 Delivering</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            </select>

                            @if($search || $categoryId || $status)
                                <a href="{{ route('admin.orderItems.index') }}" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 flex items-center justify-center gap-1">
                                    <span>✕</span>
                                    <span>Reset</span>
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Master Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3.5">Ordered Dish Item</th>
                                    <th class="px-4 py-3.5">Category</th>
                                    <th class="px-4 py-3.5">Order # / Date</th>
                                    <th class="px-4 py-3.5">Customer Info</th>
                                    <th class="px-4 py-3.5 text-center">Qty × Price</th>
                                    <th class="px-4 py-3.5 text-right">Subtotal</th>
                                    <th class="px-4 py-3.5">Order Status</th>
                                    <th class="px-4 py-3.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                @forelse($orderItems as $item)
                                    @php
                                        $dishName = $item->menuItem ? $item->menuItem->name : 'Dish (Removed)';
                                        $dishImage = $item->menuItem ? $item->menuItem->image_url : null;
                                        $categoryName = $item->menuItem && $item->menuItem->category ? $item->menuItem->category->name : 'General';
                                        $unitPrice = $item->unit_price ?? ($item->menuItem ? $item->menuItem->price : 0);
                                        $itemSubtotal = $item->subtotal ?? ($unitPrice * $item->quantity);
                                        $order = $item->order;
                                        $orderNum = $order ? $order->order_number : 'N/A';
                                        $orderStatus = $order ? $order->status : 'pending';
                                        
                                        $statusClass = match($orderStatus) {
                                            'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                            'preparing' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                            'delivering' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                            'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                            'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/30',
                                            default => 'bg-slate-800 text-slate-400 border-slate-700',
                                        };
                                    @endphp

                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        
                                        <!-- Dish Item -->
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 overflow-hidden shrink-0 flex items-center justify-center text-slate-500 font-bold text-xs">
                                                    @if($dishImage)
                                                        <img src="{{ $dishImage }}" alt="{{ $dishName }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span>🍽️</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-bold text-white text-xs">{{ $dishName }}</div>
                                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ number_format($unitPrice) }} MMK</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 bg-slate-950 border border-slate-800 text-amber-400 text-[10px] font-bold rounded-lg inline-block">
                                                {{ $categoryName }}
                                            </span>
                                        </td>

                                        <!-- Order # / Date -->
                                        <td class="px-4 py-4">
                                            @if($order)
                                                <a href="{{ route('admin.orders.index', ['search' => $orderNum]) }}" class="font-mono text-xs font-black text-orange-400 hover:underline">
                                                    #{{ $orderNum }}
                                                </a>
                                                <div class="text-[11px] text-slate-400 mt-0.5">
                                                    {{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : 'N/A' }}
                                                </div>
                                            @else
                                                <span class="text-slate-500">Deleted Order</span>
                                            @endif
                                        </td>

                                        <!-- Customer Info -->
                                        <td class="px-4 py-4">
                                            <div class="font-bold text-white text-xs">
                                                {{ $order && $order->user ? $order->user->name : ($order ? 'Guest' : 'N/A') }}
                                            </div>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $order ? $order->delivery_phone : '' }}
                                            </div>
                                        </td>

                                        <!-- Qty x Price -->
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-slate-200 font-bold text-xs">
                                                {{ $item->quantity }} × {{ number_format($unitPrice) }}
                                            </span>
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="px-4 py-4 text-right">
                                            <div class="font-black text-white text-xs">
                                                {{ number_format($itemSubtotal) }} <span class="text-[10px] text-orange-400 font-bold">MMK</span>
                                            </div>
                                        </td>

                                        <!-- Order Status -->
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $statusClass }}">
                                                {{ ucfirst($orderStatus) }}
                                            </span>
                                        </td>

                                        <!-- Action -->
                                        <td class="px-4 py-4 text-right">
                                            <form method="POST" action="{{ route('admin.orderItems.destroy', $item) }}" onsubmit="return confirmDeleteOrderItem(this, '{{ addslashes($dishName) }}', '{{ $orderNum }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Item Record" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-colors cursor-pointer border border-transparent hover:border-red-500/20">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                            <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                                🍽️
                                            </div>
                                            <div class="font-bold text-slate-300">No order items found</div>
                                            <div class="text-xs text-slate-500 mt-1">Try clearing search filters or checking customer orders</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="pt-4 border-t border-slate-800">
                        {{ $orderItems->links() }}
                    </div>

                </div>

            </main>
        </div>

    </div>

</body>
</html>
