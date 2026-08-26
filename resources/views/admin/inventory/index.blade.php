<x-admin-layout 
    active="inventory" 
    title="{{ __('Inventory') }} & {{ __('Instant Inventory & Menu Switch') }} - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Inventory & Instant Menu Switch') }}"
    subheading="{{ __('Instant 1-click availability toggles, stock quantity controls, and menu inventory hub') }}">

    <x-slot:badge>
        <span class="bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>{{ __('Live Synced') }}</span>
        </span>
    </x-slot:badge>

    <div x-data="{ 
        activeStockTab: '{{ $stockStatus ?? 'all' }}',
        selectedCategory: '{{ $categoryId ?? 'all' }}',
        searchQuery: '{{ addslashes($search ?? '') }}',
        viewMode: 'grid', // 'grid' or 'table'
        
        // Stock Adjust Modal state
        adjustModalOpen: false,
        adjustItemId: null,
        adjustItemName: '',
        adjustItemCategory: '',
        adjustCurrentStock: 50,
        adjustIsAvailable: true,
        adjustActionUrl: '',

        // Bulk Restock Modal state
        bulkModalOpen: false,
        bulkTarget: 'low_stock',
        bulkAmount: 50,

        openAdjustModal(item) {
            this.adjustItemId = item.id;
            this.adjustItemName = item.name;
            this.adjustItemCategory = item.category ? item.category.name : 'Uncategorized';
            this.adjustCurrentStock = item.stock;
            this.adjustIsAvailable = item.is_available ? true : false;
            this.adjustActionUrl = '/admin/inventory/' + item.id + '/update-stock';
            this.adjustModalOpen = true;
        },

        async toggleItemStock(itemId, currentStatus, event) {
            const button = event.currentTarget;
            button.disabled = true;
            button.classList.add('opacity-60');

            try {
                const response = await fetch('/admin/inventory/' + itemId + '/toggle-stock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                        background: '#ffffff',
                        color: '#0f172a',
                        customClass: { popup: 'border border-emerald-200 rounded-2xl shadow-xl' }
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
            } catch (err) {
                // If fetch fails, fallback to submitting the form normally
                event.target.closest('form')?.submit();
            } finally {
                button.disabled = false;
                button.classList.remove('opacity-60');
            }
        }
    }" class="space-y-6">



        <!-- KPI METRIC CARDS (4 INVENTORY HEALTH STATS) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Stat 1: Total Catalog Dishes -->
            <a href="{{ route('admin.inventory.index', ['stock_status' => 'all']) }}" 
               class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs block">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('All Dishes') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-base border border-orange-100 dark:border-orange-900">
                        🍽️
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">
                    {{ $totalItemsCount }} <span class="text-xs text-slate-400 font-normal">{{ __('Dishes') }}</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center gap-1">
                    <span>{{ __('Total registered menu items') }}</span>
                </div>
            </a>

            <!-- Stat 2: In-Stock & Available -->
            <a href="{{ route('admin.inventory.index', ['stock_status' => 'available']) }}" 
               class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-md transition-all shadow-xs block">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Available') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-base border border-emerald-100 dark:border-emerald-900">
                        ✅
                    </div>
                </div>
                <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                    {{ $inStockCount }} <span class="text-xs text-slate-400 font-normal">{{ __('Active') }}</span>
                </div>
                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>{{ __('Ready for customer orders') }}</span>
                </div>
            </a>

            <!-- Stat 3: Out of Stock (Alert) -->
            <a href="{{ route('admin.inventory.index', ['stock_status' => 'out_of_stock']) }}" 
               class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-red-300 dark:hover:border-red-700 hover:shadow-md transition-all shadow-xs block">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Out of Stock') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center font-bold text-base border border-red-100 dark:border-red-900">
                        🚫
                    </div>
                </div>
                <div class="text-3xl font-black text-red-600 dark:text-red-400 mt-2">
                    {{ $outOfStockCount }} <span class="text-xs text-slate-400 font-normal">{{ __('Disabled') }}</span>
                </div>
                <div class="text-xs text-red-600 dark:text-red-400 font-medium mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                    <span>{{ __('Hidden from ordering') }}</span>
                </div>
            </a>

            <!-- Stat 4: Low Stock Alert -->
            <a href="{{ route('admin.inventory.index', ['stock_status' => 'low_stock']) }}" 
               class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-md transition-all shadow-xs block">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Low Stock') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-base border border-amber-100 dark:border-amber-900">
                        ⚠️
                    </div>
                </div>
                <div class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    {{ $lowStockCount }} <span class="text-xs text-slate-400 font-normal">{{ __('Alert (≤10)') }}</span>
                </div>
                <div class="text-xs text-amber-600 dark:text-amber-400 font-medium mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                    <span>{{ __('Needs kitchen prep / restocking') }}</span>
                </div>
            </a>

        </div>

        <!-- INSTANT INVENTORY & MENU SWITCH MAIN CARD -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
            
            <!-- Section Header with Controls -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-sm">
                            ⚡
                        </div>
                        <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ __('Instant Inventory & Menu Switch') }}</h2>
                        <span class="px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-[10px] font-bold rounded-full border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                            <span>{{ __('1-Click Toggle') }}</span>
                        </span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1">{{ __('Instantly switch dish availability on/off or adjust stock levels in real time to prevent order conflicts') }}</p>
                </div>

                <!-- View Toggle Mode & Bulk Restock -->
                <div class="flex flex-wrap items-center gap-3">
                    <button @click="bulkModalOpen = true" 
                            type="button"
                            class="px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-orange-500/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>{{ __('Quick Restock') }}</span>
                    </button>

                    <div class="bg-slate-100 dark:bg-slate-800 p-1 rounded-xl flex items-center gap-1 border border-slate-200 dark:border-slate-700">
                        <button @click="viewMode = 'grid'" 
                                type="button"
                                :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-700 text-orange-600 dark:text-orange-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                                class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            <span>{{ __('Quick Switch Grid') }}</span>
                        </button>
                        <button @click="viewMode = 'table'" 
                                type="button"
                                :class="viewMode === 'table' ? 'bg-white dark:bg-slate-700 text-orange-600 dark:text-orange-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                                class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            <span>{{ __('Detailed Stock Table') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Controls Bar -->
            <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 bg-slate-50/80 dark:bg-slate-800/60 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-700 flex-wrap">
                
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[170px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search"
                           value="{{ $search ?? '' }}" 
                           placeholder="{{ __('Search dishes by name...') }}"
                           class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                    @if(!empty($search))
                        <a href="{{ route('admin.inventory.index', request()->except('search')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</a>
                    @endif
                </div>

                <!-- Shop Selector -->
                <div class="w-full md:w-36 shrink-0">
                    <select name="shop_id" onchange="this.form.submit()"
                            class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="all">Shop: All</option>
                        @foreach($shops as $s)
                            <option value="{{ $s->id }}" {{ ($shopId ?? '') == $s->id ? 'selected' : '' }}>🏪 {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Selector -->
                <div class="w-full md:w-36 shrink-0">
                    <select name="category_id" onchange="this.form.submit()"
                            class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="">🍽️ {{ __('All Categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($categoryId ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Status Selector -->
                <div class="w-full md:w-32 shrink-0">
                    <select name="stock_status" onchange="this.form.submit()"
                            class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="all" {{ ($stockStatus ?? '') === 'all' ? 'selected' : '' }}>Status: All</option>
                        <option value="available" {{ ($stockStatus ?? '') === 'available' ? 'selected' : '' }}>✅ Available</option>
                        <option value="low_stock" {{ ($stockStatus ?? '') === 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock</option>
                        <option value="out_of_stock" {{ ($stockStatus ?? '') === 'out_of_stock' ? 'selected' : '' }}>🚫 Out of Stock</option>
                    </select>
                </div>

                <!-- Sort By Selector -->
                <div class="w-full md:w-36 shrink-0">
                    <select name="sort_by" onchange="this.form.submit()"
                            class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="stock_asc" {{ ($sortBy ?? '') === 'stock_asc' ? 'selected' : '' }}>Stock: Low to High</option>
                        <option value="stock_desc" {{ ($sortBy ?? '') === 'stock_desc' ? 'selected' : '' }}>Stock: High to Low</option>
                        <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="price_asc" {{ ($sortBy ?? '') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ ($sortBy ?? '') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Newest First</option>
                    </select>
                </div>

                @if(!empty($search) || ($categoryId) || ($shopId && $shopId !== 'all') || ($stockStatus && $stockStatus !== 'all') || ($sortBy && $sortBy !== 'stock_asc'))
                    <a href="{{ route('admin.inventory.index') }}" class="px-2.5 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>

            <!-- 1. GRID VIEW: INSTANT 1-CLICK STOCK SWITCH CARDS -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($menuItems as $item)
                    @php
                        $isAvailable = $item->is_available && $item->stock > 0;
                        $isLowStock = $item->stock > 0 && $item->stock <= 10;
                        $isOutOfStock = !$item->is_available || $item->stock <= 0;
                    @endphp
                    <div x-data="{ 
                             itemName: '{{ addslashes(strtolower($item->name)) }}',
                             itemCatId: '{{ $item->category_id }}',
                             isAvail: {{ $item->is_available ? 'true' : 'false' }},
                             stockNum: {{ (int)$item->stock }}
                         }"
                         x-show="
                             (searchQuery === '' || itemName.includes(searchQuery.toLowerCase())) &&
                             (selectedCategory === 'all' || itemCatId === selectedCategory) &&
                             (activeStockTab === 'all' || 
                              (activeStockTab === 'available' && isAvail && stockNum > 0) || 
                              (activeStockTab === 'low_stock' && stockNum > 0 && stockNum <= 10) || 
                              (activeStockTab === 'out_of_stock' && (!isAvail || stockNum <= 0)))
                         "
                         class="p-4 bg-slate-50/90 dark:bg-slate-800/80 hover:bg-white dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 hover:border-orange-300 dark:hover:border-orange-500/50 hover:shadow-md transition-all flex flex-col justify-between gap-3 group relative">
                        
                        <!-- Top Row: Image & Info -->
                        <div class="flex items-start gap-3">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 shrink-0 relative group-hover:scale-105 transition-transform">
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                @if($isOutOfStock)
                                    <div class="absolute inset-0 bg-red-900/60 backdrop-blur-[1px] flex items-center justify-center text-[9px] font-black text-white uppercase text-center leading-tight">
                                        {{ __('Sold Out') }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $item->category ? 'bg-orange-50 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 border border-orange-100 dark:border-orange-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300' }}">
                                        {{ $item->category->name ?? 'Uncategorized' }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-xs mt-1 truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                                <div class="text-xs text-orange-600 dark:text-orange-400 font-mono font-bold mt-0.5">{{ number_format($item->price) }} MMK</div>
                            </div>
                        </div>

                        <!-- Middle: Stock Level Info & Progress Bar -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-700">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ __('Stock Level') }}:</span>
                                <span class="font-mono font-bold {{ $isOutOfStock ? 'text-red-600 dark:text-red-400' : ($isLowStock ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400') }}">
                                    @if($isOutOfStock)
                                        🚫 0 ({{ __('Out of Stock') }})
                                    @elseif($isLowStock)
                                        ⚠️ {{ $item->stock }} {{ __('Left (Low)') }}
                                    @else
                                        ✅ {{ $item->stock }} {{ __('Units') }}
                                    @endif
                                </span>
                            </div>

                            <!-- Progress Meter -->
                            @php
                                $stockPercent = min(100, max(0, ($item->stock / 50) * 100));
                                $barColor = $isOutOfStock ? 'bg-red-500' : ($isLowStock ? 'bg-amber-500' : 'bg-emerald-500');
                            @endphp
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                <div class="{{ $barColor }} h-full transition-all duration-300" style="width: {{ $stockPercent }}%"></div>
                            </div>
                        </div>

                        <!-- Bottom: Action Controls -->
                        <div class="pt-2 flex items-center justify-between gap-2">
                            <!-- Instant Toggle Form -->
                            <form action="{{ route('admin.inventory.toggle-stock', $item) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="button" 
                                        @click="toggleItemStock({{ $item->id }}, {{ $item->is_available ? 'true' : 'false' }}, $event)"
                                        class="w-full py-2 px-3 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer {{ $item->is_available && $item->stock > 0 ? 'bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-950/50 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }}">
                                    <span>{{ $item->is_available && $item->stock > 0 ? '🟢 ' . __('Active / ON') : '🔴 ' . __('Disabled / OFF') }}</span>
                                </button>
                            </form>

                            <!-- Adjust Stock Modal Trigger Button -->
                            <button @click="openAdjustModal({{ json_encode($item) }})" 
                                    type="button"
                                    title="{{ __('Adjust Stock Quantity') }}"
                                    class="p-2 bg-slate-100 dark:bg-slate-700 hover:bg-orange-100 dark:hover:bg-orange-950/50 hover:text-orange-700 dark:hover:text-orange-400 text-slate-700 dark:text-slate-200 rounded-xl transition-all border border-slate-200 dark:border-slate-600 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                            </button>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400 dark:text-slate-500">
                        <div class="text-3xl mb-2">🍽️</div>
                        <div class="font-bold text-slate-700 dark:text-slate-300">{{ __('No menu items found in catalog') }}</div>
                    </div>
                @endforelse
            </div>

            <!-- 2. DETAILED TABLE VIEW -->
            <div x-show="viewMode === 'table'" class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3.5 w-16">{{ __('Dish') }}</th>
                            <th class="px-4 py-3.5">{{ __('Name & Category') }}</th>
                            <th class="px-4 py-3.5">{{ __('Shop') }}</th>
                            <th class="px-4 py-3.5">{{ __('Unit Price') }}</th>
                            <th class="px-4 py-3.5">{{ __('Stock Count') }}</th>
                            <th class="px-4 py-3.5">{{ __('1-Click Availability') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Stock Controls') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @foreach($menuItems as $item)
                            @php
                                $isAvailable = $item->is_available && $item->stock > 0;
                                $isLowStock = $item->stock > 0 && $item->stock <= 10;
                                $isOutOfStock = !$item->is_available || $item->stock <= 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <!-- Image -->
                                <td class="px-4 py-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 shrink-0">
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                    </div>
                                </td>

                                <!-- Name & Category -->
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $item->name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $item->category->name ?? 'Uncategorized' }}</div>
                                </td>

                                <!-- Shop -->
                                <td class="px-4 py-3">
                                    @if($item->shop)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-300 border border-orange-200 dark:border-orange-800">
                                            🏪 {{ $item->shop->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>

                                <!-- Price -->
                                <td class="px-4 py-3 font-mono font-bold text-orange-600 dark:text-orange-400">
                                    {{ number_format($item->price) }} MMK
                                </td>

                                <!-- Stock Count -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-black text-sm {{ $isOutOfStock ? 'text-red-600 dark:text-red-400' : ($isLowStock ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400') }}">
                                            {{ $item->stock }}
                                        </span>
                                        @if($isOutOfStock)
                                            <span class="px-2 py-0.5 bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-full text-[10px] font-bold">{{ __('Out of Stock') }}</span>
                                        @elseif($isLowStock)
                                            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-full text-[10px] font-bold">{{ __('Low Stock') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-full text-[10px] font-bold">{{ __('In Stock') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Availability Toggle -->
                                <td class="px-4 py-3">
                                    <button type="button" 
                                            @click="toggleItemStock({{ $item->id }}, {{ $item->is_available ? 'true' : 'false' }}, $event)"
                                            class="py-1 px-3 rounded-lg font-bold text-[11px] transition-all flex items-center gap-1.5 cursor-pointer {{ $item->is_available && $item->stock > 0 ? 'bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-950/50 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }}">
                                        <span>{{ $item->is_available && $item->stock > 0 ? '🟢 ' . __('Available (ON)') : '🔴 ' . __('Disabled (OFF)') }}</span>
                                    </button>
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3 text-right">
                                    <button @click="openAdjustModal({{ json_encode($item) }})" 
                                            type="button"
                                            class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-orange-950/50 hover:text-orange-700 dark:hover:text-orange-400 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 cursor-pointer inline-flex items-center gap-1">
                                        <span>✏️</span>
                                        <span>{{ __('Adjust Stock') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination Links -->
            @if(method_exists($menuItems, 'hasPages') && $menuItems->hasPages())
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $menuItems->links() }}
                </div>
            @endif

        </div>

        <!-- ================= ADJUST STOCK MODAL ================= -->
        <div x-show="adjustModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="adjustModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                     @click="adjustModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="adjustModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 dark:border-slate-800">
                    
                    <form :action="adjustActionUrl" method="POST" class="p-6 space-y-5">
                        @csrf
                        
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-lg">
                                    📦
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white" x-text="adjustItemName"></h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="adjustItemCategory"></p>
                                </div>
                            </div>
                            <button type="button" @click="adjustModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Stock Counter Input -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                {{ __('Stock Quantity (Available Portions)') }}
                            </label>
                            <div class="flex items-center gap-3">
                                <button type="button" 
                                        @click="adjustCurrentStock = Math.max(0, Number(adjustCurrentStock) - 5)"
                                        class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-base flex items-center justify-center transition-colors cursor-pointer shrink-0">
                                    -5
                                </button>
                                <button type="button" 
                                        @click="adjustCurrentStock = Math.max(0, Number(adjustCurrentStock) - 1)"
                                        class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-base flex items-center justify-center transition-colors cursor-pointer shrink-0">
                                    -1
                                </button>
                                <input type="number" 
                                       name="stock" 
                                       x-model="adjustCurrentStock"
                                       min="0" 
                                       max="5000"
                                       required
                                       class="flex-1 text-center py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-base font-mono font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                <button type="button" 
                                        @click="adjustCurrentStock = Number(adjustCurrentStock) + 1"
                                        class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-base flex items-center justify-center transition-colors cursor-pointer shrink-0">
                                    +1
                                </button>
                                <button type="button" 
                                        @click="adjustCurrentStock = Number(adjustCurrentStock) + 5"
                                        class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-base flex items-center justify-center transition-colors cursor-pointer shrink-0">
                                    +5
                                </button>
                            </div>

                            <!-- Quick Preset Pill Buttons -->
                            <div class="grid grid-cols-4 gap-2 mt-2.5">
                                <button type="button" @click="adjustCurrentStock = 0" class="py-1.5 px-2 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                    {{ __('Set 0 (Empty)') }}
                                </button>
                                <button type="button" @click="adjustCurrentStock = Number(adjustCurrentStock) + 10" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-orange-100 dark:hover:bg-orange-950/50 hover:text-orange-700 dark:hover:text-orange-400 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                    +10
                                </button>
                                <button type="button" @click="adjustCurrentStock = Number(adjustCurrentStock) + 25" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-orange-100 dark:hover:bg-orange-950/50 hover:text-orange-700 dark:hover:text-orange-400 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                    +25
                                </button>
                                <button type="button" @click="adjustCurrentStock = 50" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-orange-100 dark:hover:bg-orange-950/50 hover:text-orange-700 dark:hover:text-orange-400 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                    {{ __('Set 50') }}
                                </button>
                            </div>
                        </div>

                        <!-- Availability Toggle inside Modal -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ __('Mark as Available for Order') }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Allow customers to order this dish on the storefront') }}</div>
                            </div>
                            <input type="checkbox" 
                                   name="is_available" 
                                   value="1" 
                                   x-model="adjustIsAvailable"
                                   class="w-5 h-5 text-orange-600 rounded border-slate-300 dark:border-slate-600 focus:ring-orange-500 cursor-pointer">
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl cursor-pointer">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 active:scale-95 rounded-xl shadow-lg shadow-orange-500/20 transition-all cursor-pointer">
                                {{ __('Save Changes') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- ================= BULK RESTOCK MODAL ================= -->
        <div x-show="bulkModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="bulkModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                     @click="bulkModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="bulkModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 dark:border-slate-800">
                    
                    <form action="{{ route('admin.inventory.bulk-restock') }}" method="POST" class="p-6 space-y-5">
                        @csrf
                        
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
                                    🚀
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('Quick Restock') }}</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Batch restock menu items and restore availability') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="bulkModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Target Selection -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                {{ __('Select Dishes to Restock') }}
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 hover:bg-orange-50/50 dark:hover:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer">
                                    <input type="radio" name="target" value="low_stock" x-model="bulkTarget" class="text-orange-600 focus:ring-orange-500">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ __('Low Stock Dishes (≤ 10 units)') }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Currently') }} {{ $lowStockCount }} {{ __('items need replenishing') }}</div>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 hover:bg-orange-50/50 dark:hover:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer">
                                    <input type="radio" name="target" value="out_of_stock" x-model="bulkTarget" class="text-orange-600 focus:ring-orange-500">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ __('Out of Stock / Disabled Dishes') }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Currently') }} {{ $outOfStockCount }} {{ __('items disabled') }}</div>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 hover:bg-orange-50/50 dark:hover:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer">
                                    <input type="radio" name="target" value="all" x-model="bulkTarget" class="text-orange-600 focus:ring-orange-500">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ __('All Dishes (Full Restock)') }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Set every dish in catalog to new stock level') }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- New Stock Quantity -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                {{ __('New Stock Level Per Dish') }}
                            </label>
                            <input type="number" 
                                   name="amount" 
                                   x-model="bulkAmount"
                                   min="1" 
                                   max="1000"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm font-mono font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="bulkModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl cursor-pointer">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 rounded-xl shadow-lg shadow-orange-500/20 transition-all cursor-pointer">
                                {{ __('Execute Restock') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>

</x-admin-layout>
