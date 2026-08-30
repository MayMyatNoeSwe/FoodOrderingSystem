@extends('layouts.shop_owner')

@section('heading', '🍽️ ' . $shop->name . ' — Menu Items')

@section('content')
<div x-data="{
    columnDropdownOpen: false,
    cols: {
        item: true,
        price: true,
        stock: true,
        status: true,
        actions: true
    },
    init() {
        const savedCols = localStorage.getItem('shop_menu_items_cols');
        if (savedCols) {
            try { this.cols = Object.assign(this.cols, JSON.parse(savedCols)); } catch (e) {}
        }
    },
    toggleCol(colName) {
        this.cols[colName] = !this.cols[colName];
        localStorage.setItem('shop_menu_items_cols', JSON.stringify(this.cols));
    },
    setAllCols(val) {
        for (let k in this.cols) { this.cols[k] = val; }
        localStorage.setItem('shop_menu_items_cols', JSON.stringify(this.cols));
    },
    resetCols() {
        this.cols = { item: true, price: true, stock: true, status: true, actions: true };
        localStorage.removeItem('shop_menu_items_cols');
    },
    getActiveColCount() { return Object.values(this.cols).filter(Boolean).length; },
    getTotalColCount() { return Object.keys(this.cols).length; }
}" class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-black text-slate-900 dark:text-white">🍽️ {{ __('Menu Items Catalog') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $menuItems->total() }} {{ __('registered menu item(s)') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Column Visibility Filter Dropdown -->
            <div class="relative" @click.outside="columnDropdownOpen = false">
                <button type="button" @click="columnDropdownOpen = !columnDropdownOpen"
                        class="px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                    <span>{{ __('Columns Filter') }}</span>
                    <span class="px-1.5 py-0.5 rounded-full bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 font-mono text-[10px] font-black border border-orange-200 dark:border-orange-800" x-text="getActiveColCount() + '/' + getTotalColCount()"></span>
                </button>

                <div x-show="columnDropdownOpen" x-cloak
                     x-transition
                     class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl p-3 z-40 space-y-2">
                    
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                        <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                            <span>👁️</span><span>{{ __('Visible Columns') }}</span>
                        </span>
                        <div class="flex items-center gap-1.5 text-[10px] font-bold">
                            <button type="button" @click="setAllCols(true)" class="text-orange-600 hover:underline cursor-pointer">{{ __('All') }}</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="resetCols()" class="text-slate-500 hover:underline cursor-pointer">{{ __('Reset') }}</button>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                            <input type="checkbox" :checked="cols.item" @change="toggleCol('item')" class="rounded border-slate-300 text-orange-600 focus:ring-0">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">🖼️ {{ __('Item') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                            <input type="checkbox" :checked="cols.price" @change="toggleCol('price')" class="rounded border-slate-300 text-orange-600 focus:ring-0">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">💰 {{ __('Price') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                            <input type="checkbox" :checked="cols.stock" @change="toggleCol('stock')" class="rounded border-slate-300 text-orange-600 focus:ring-0">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">📦 {{ __('Stock') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                            <input type="checkbox" :checked="cols.status" @change="toggleCol('status')" class="rounded border-slate-300 text-orange-600 focus:ring-0">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">🟢 {{ __('Status') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                            <input type="checkbox" :checked="cols.actions" @change="toggleCol('actions')" class="rounded border-slate-300 text-orange-600 focus:ring-0">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">🛠️ {{ __('Actions') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <button onclick="document.getElementById('createItemModal').classList.remove('hidden'); document.getElementById('createItemModal').classList.add('flex');"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add Item') }}
            </button>
        </div>
    </div>

    <!-- Search & Filter Controls Toolbar -->
    <form method="GET" action="{{ route('shop_owner.menu-items.index') }}" class="flex flex-wrap items-center gap-3 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm w-full">
        
        <!-- Search Box -->
        <div class="relative flex-1 min-w-[240px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" 
                   name="search"
                   value="{{ $search ?? '' }}" 
                   placeholder="{{ __('Search dish by name...') }}"
                   class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
            @if(!empty($search))
                <a href="{{ route('shop_owner.menu-items.index', request()->except('search')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</a>
            @endif
        </div>

        <!-- Category Selector -->
        <div class="w-full sm:w-auto shrink-0">
            <select name="category_id" onchange="this.form.submit()"
                    class="w-full sm:w-44 py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                <option value="">{{ __('Category: All') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($categoryId ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Stock Status Selector -->
        <div class="w-full sm:w-auto shrink-0">
            <select name="stock_status" onchange="this.form.submit()"
                    class="w-full sm:w-40 py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                <option value="all" {{ ($stockStatus ?? '') === 'all' ? 'selected' : '' }}>Stock: All</option>
                <option value="available" {{ ($stockStatus ?? '') === 'available' ? 'selected' : '' }}>✅ In Stock</option>
                <option value="low_stock" {{ ($stockStatus ?? '') === 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock</option>
                <option value="out_of_stock" {{ ($stockStatus ?? '') === 'out_of_stock' ? 'selected' : '' }}>🚫 Out of Stock</option>
            </select>
        </div>

        <!-- Status Selector -->
        <div class="w-full sm:w-auto shrink-0">
            <select name="status" onchange="this.form.submit()"
                    class="w-full sm:w-40 py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                <option value="">Status: All</option>
                <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>🟢 Available</option>
                <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>🔴 Unavailable</option>
            </select>
        </div>

        <!-- Sort By Selector -->
        <div class="w-full sm:w-auto shrink-0 flex gap-2 items-center">
            <select name="sort_by" onchange="this.form.submit()"
                    class="w-full sm:w-44 py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                <option value="price_asc" {{ ($sortBy ?? '') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_desc" {{ ($sortBy ?? '') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="stock_desc" {{ ($sortBy ?? '') === 'stock_desc' ? 'selected' : '' }}>Stock: High to Low</option>
                <option value="stock_asc" {{ ($sortBy ?? '') === 'stock_asc' ? 'selected' : '' }}>Stock: Low to High</option>
            </select>

            @if(!empty($search) || ($categoryId) || ($stockStatus && $stockStatus !== 'all') || ($status !== null && $status !== '') || ($sortBy && $sortBy !== 'latest'))
                <a href="{{ route('shop_owner.menu-items.index') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all whitespace-nowrap">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($menuItems->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center shadow-sm">
            <div class="text-6xl mb-4">🍽️</div>
            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">No menu items found</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Try adjusting your search or filters.</p>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead class="bg-slate-50/50 dark:bg-slate-800/50 text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th x-show="cols.item" class="px-6 py-4 text-left">Item</th>
                            <th x-show="cols.price" class="px-6 py-4 text-left">Price</th>
                            <th x-show="cols.stock" class="px-6 py-4 text-left">Stock</th>
                            <th x-show="cols.status" class="px-6 py-4 text-left">Status</th>
                            <th x-show="cols.actions" class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($menuItems as $item)
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-200">
                            <td x-show="cols.item" class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 shadow-sm border border-slate-200 dark:border-slate-700 group-hover:shadow-md transition-all">
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    </div>
                                    <div class="min-w-0 flex flex-col justify-center">
                                        <div class="font-bold text-slate-900 dark:text-white truncate text-base group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">{{ $item->name }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            @if($item->category)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                    {{ $item->category->name }}
                                                </span>
                                            @endif
                                            @if($item->description)
                                                <span class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[150px]">{{ $item->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td x-show="cols.price" class="px-6 py-4">
                                <div class="font-black text-slate-900 dark:text-white text-base">{{ number_format($item->price) }} <span class="text-xs font-semibold text-slate-400">MMK</span></div>
                            </td>
                            <td x-show="cols.stock" class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 w-16 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $item->isLowStock() ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ min(100, max(5, ($item->stock / max($item->min_stock_level ?: 1, 100)) * 100)) }}%"></div>
                                    </div>
                                    <span class="font-bold text-sm {{ $item->isLowStock() ? 'text-amber-600 dark:text-amber-500' : 'text-slate-700 dark:text-slate-300' }}">
                                        {{ $item->stock }}
                                    </span>
                                </div>
                                @if($item->isLowStock())
                                    <div class="text-[10px] font-bold text-amber-600 dark:text-amber-500 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Low Stock
                                    </div>
                                @endif
                            </td>
                            <td x-show="cols.status" class="px-6 py-4">
                                @if($item->is_available)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Available
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Unavailable
                                    </span>
                                @endif
                            </td>
                            <td x-show="cols.actions" class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button onclick="openEditItem({{ $item->id }}, {{ json_encode($item->only(['name','description','price','stock','min_stock_level','is_available','category_id'])) }})"
                                            class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-all dark:hover:bg-orange-500/10 cursor-pointer" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <form method="POST" action="{{ route('shop_owner.menu-items.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all dark:hover:bg-red-500/10 cursor-pointer" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                                <div class="flex items-center justify-end group-hover:hidden text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($menuItems->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $menuItems->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ===== CREATE ITEM MODAL ===== --}}
    <div id="createItemModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-black text-slate-900 dark:text-white">🍽️ Add Menu Item</h2>
                <button onclick="document.getElementById('createItemModal').classList.add('hidden'); document.getElementById('createItemModal').classList.remove('flex');" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">✕</button>
            </div>
            <form method="POST" action="{{ route('shop_owner.menu-items.store') }}" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                @include('shop_owner.menu_items._form', ['item' => null])
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-sm transition-all cursor-pointer">Add Item</button>
                    <button type="button" onclick="document.getElementById('createItemModal').classList.add('hidden'); document.getElementById('createItemModal').classList.remove('flex');" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== EDIT ITEM MODAL ===== --}}
    <div id="editItemModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-black text-slate-900 dark:text-white">✏️ Edit Menu Item</h2>
                <button onclick="document.getElementById('editItemModal').classList.add('hidden'); document.getElementById('editItemModal').classList.remove('flex');" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">✕</button>
            </div>
            <form id="editItemForm" method="POST" action="" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf @method('PUT')
                @include('shop_owner.menu_items._form', ['item' => 'edit'])
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-sm transition-all cursor-pointer">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editItemModal').classList.add('hidden'); document.getElementById('editItemModal').classList.remove('flex');" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditItem(itemId, itemData) {
        const form = document.getElementById('editItemForm');
        form.action = `/shop-owner/menu-items/${itemId}`;

        ['name','description','price','stock','min_stock_level'].forEach(f => {
            const el = form.querySelector(`[name="${f}"]`);
            if (el) el.value = itemData[f] ?? '';
        });
        const catEl = form.querySelector('[name="category_id"]');
        if (catEl) catEl.value = itemData.category_id ?? '';
        const availEl = form.querySelector('[name="is_available"]');
        if (availEl) availEl.checked = itemData.is_available;

        const modal = document.getElementById('editItemModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    </script>
</div>
@endsection
