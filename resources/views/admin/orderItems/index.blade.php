<x-admin-layout 
    active="orderItems" 
    title="Order Items Table — {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Order Items Table') }}"
    subheading="{{ __('Master view of individual ordered food items across all customer transactions') }}">

    <x-slot:head>
        <script>
            function confirmDeleteOrderItem(form, itemName, orderNumber) {
                Swal.fire({
                    title: 'Remove Item?',
                    html: `Are you sure you want to remove <strong class="text-orange-500">${itemName}</strong> from Order <strong class="text-slate-900">#${orderNumber}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Delete Item',
                    cancelButtonText: 'Cancel',
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'border border-slate-200 rounded-3xl shadow-2xl',
                        title: 'text-slate-900 font-bold text-lg',
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
    </x-slot:head>

    <x-slot:badge>
        <span class="bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
            {{ $orderItems->total() }} {{ __('Items') }}
        </span>
    </x-slot:badge>

    <div class="space-y-6">

        <!-- Stat Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Stat 1: Total Items Sold -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total Items Sold') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-base border border-orange-100 dark:border-orange-900">
                        🍽️
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ number_format($totalQuantitySold ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Dishes delivered across orders') }}</div>
            </div>

            <!-- Stat 2: Total Items Revenue -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Itemized Gross Sales') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-base border border-emerald-100 dark:border-emerald-900">
                        💰
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 truncate">{{ number_format($totalItemsRevenue ?? 0) }} <span class="text-xs text-orange-600 dark:text-orange-400 font-bold">MMK</span></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Gross ordered dish value') }}</div>
            </div>

            <!-- Stat 3: Unique Dishes Ordered -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Active Menu Diversity') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-base border border-blue-100 dark:border-blue-900">
                        📋
                    </div>
                </div>
                <div class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ number_format($uniqueMenuItemsCount ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Different menu items ordered') }}</div>
            </div>

            <!-- Stat 4: Average Dish Price -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Average Dish Price') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-base border border-purple-100 dark:border-purple-900">
                        📊
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 truncate">{{ number_format($avgItemPrice ?? 0) }} <span class="text-xs text-purple-600 dark:text-purple-400 font-bold">MMK</span></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Avg order item price') }}</div>
            </div>

        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
            
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Filter Ordered Items') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Filter by specific order number, search dishes, or filter by category') }}</p>
                </div>

                <form method="GET" action="{{ route('admin.orderItems.index') }}" class="flex flex-wrap items-center gap-3">
                    
                    <!-- Search Input -->
                    <div class="relative min-w-[200px] flex-1 sm:flex-initial">
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="{{ __('Search dish or order #...') }}" 
                               class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3.5 py-2 pl-9 focus:ring-0 transition-all placeholder-slate-400">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Category Filter -->
                    <select name="category_id" 
                            onchange="this.form.submit()" 
                            class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-0 focus:border-orange-500 cursor-pointer">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Order ID Filter if specified -->
                    @if($orderId)
                        <input type="hidden" name="order_id" value="{{ $orderId }}">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 dark:bg-orange-950/50 border border-orange-200 dark:border-orange-800 rounded-xl text-xs font-bold text-orange-700 dark:text-orange-400">
                            <span>{{ __('Order:') }} #{{ $orderId }}</span>
                            <a href="{{ route('admin.orderItems.index', array_filter(['search' => $search, 'category_id' => $categoryId])) }}" class="text-orange-500 hover:text-orange-700 font-black">✕</a>
                        </div>
                    @endif

                    <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-md shadow-orange-500/20 transition-all cursor-pointer">
                        {{ __('Filter') }}
                    </button>

                    @if($search || $categoryId || $orderId)
                        <a href="{{ route('admin.orderItems.index') }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                            {{ __('Reset') }}
                        </a>
                    @endif

                </form>
            </div>

        </div>

        <!-- Master Table Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
            
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5 w-14">ID</th>
                            <th class="px-4 py-3.5">{{ __('Order Number') }}</th>
                            <th class="px-4 py-3.5">{{ __('Dish Item') }}</th>
                            <th class="px-4 py-3.5">{{ __('Category') }}</th>
                            <th class="px-4 py-3.5">{{ __('Qty × Unit Price') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Subtotal') }}</th>
                            <th class="px-4 py-3.5">{{ __('Status') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($orderItems as $item)
                            @php
                                $orderNum = $item->order ? $item->order->order_number : ('#' . $item->order_id);
                                $dishName = $item->menuItem ? $item->menuItem->name : 'Deleted Dish Item';
                                $categoryName = ($item->menuItem && $item->menuItem->category) ? $item->menuItem->category->name : 'Unassigned';
                                $unitPrice = $item->unit_price ?? ($item->menuItem->price ?? 0);
                                $itemSubtotal = $item->subtotal ?? ($unitPrice * $item->quantity);
                                $orderStatus = $item->order ? $item->order->status : 'unknown';

                                $statusClass = match($orderStatus) {
                                    'pending' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                    'confirmed', 'preparing' => 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                    'delivering' => 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                    'completed' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                    'cancelled' => 'bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800',
                                    default => 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'
                                };
                            @endphp

                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <!-- ID -->
                                <td class="px-4 py-4 font-mono text-slate-400">
                                    #{{ $item->id }}
                                </td>

                                <!-- Order Number -->
                                <td class="px-4 py-4">
                                    @if($item->order)
                                        <a href="{{ route('admin.orders.index', ['search' => $item->order->order_number]) }}" class="font-mono font-bold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 hover:underline">
                                            {{ $orderNum }}
                                        </a>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $item->order->created_at ? $item->order->created_at->format('M d, H:i') : '' }}
                                        </div>
                                    @else
                                        <span class="font-mono text-slate-400">#{{ $item->order_id }}</span>
                                    @endif
                                </td>

                                <!-- Dish Item -->
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $dishName }}</div>
                                    @if($item->menuItem)
                                        <div class="text-[10px] text-slate-400">Item ID #{{ $item->menu_item_id }}</div>
                                    @endif
                                </td>

                                <!-- Category -->
                                <td class="px-4 py-4">
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-md border border-slate-200 dark:border-slate-700 text-[10px] font-bold">
                                        {{ $categoryName }}
                                    </span>
                                </td>

                                <!-- Quantity x Unit Price -->
                                <td class="px-4 py-4 font-mono">
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 font-bold text-xs">
                                        {{ $item->quantity }} × {{ number_format($unitPrice) }}
                                    </span>
                                </td>

                                <!-- Subtotal -->
                                <td class="px-4 py-4 text-right">
                                    <div class="font-black text-slate-900 dark:text-white text-xs">
                                        {{ number_format($itemSubtotal) }} <span class="text-[10px] text-orange-600 dark:text-orange-400 font-bold">MMK</span>
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
                                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                        <button type="submit" title="{{ __('Delete Item Record') }}" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-xl transition-colors cursor-pointer border border-transparent hover:border-red-100 dark:hover:border-red-900">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                        🍽️
                                    </div>
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ __('No order items found') }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Try clearing search filters or checking customer orders') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if($orderItems->hasPages())
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $orderItems->links() }}
                </div>
            @endif

        </div>

    </div>

</x-admin-layout>
