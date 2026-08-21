<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Inventory') }} & {{ __('Instant Inventory & Menu Switch') }} - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ 
          mobileMenuOpen: false,
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
      }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="inventory" />

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
                            <span>📦</span>
                            <span>{{ __('Inventory') }} & {{ __('Instant Inventory & Menu Switch') }}</span>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ __('Live Synced') }}</span>
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">Instant 1-click availability toggles, stock quantity controls, and menu inventory hub</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Bulk Restock Button -->
                    <button @click="bulkModalOpen = true" 
                            class="px-3.5 py-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-orange-500/20 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="hidden sm:inline">{{ __('Quick Restock') }}</span>
                    </button>

                    <x-language-switcher variant="compact" />

                    <!-- Storefront Link -->
                    <a href="{{ route('home') }}" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-all flex items-center gap-2">
                        <span>{{ __('View Storefront') }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-6 overflow-y-auto">

                <!-- Flash Success Notification Toast -->
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
                                background: '#ffffff',
                                color: '#0f172a',
                                customClass: { popup: 'border border-emerald-200 rounded-2xl shadow-xl' }
                            });
                        });
                    </script>
                @endif

                <!-- KPI METRIC CARDS (4 INVENTORY HEALTH STATS) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Stat 1: Total Catalog Dishes -->
                    <a href="{{ route('admin.inventory.index', ['stock_status' => 'all']) }}" 
                       class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm block">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">{{ __('All Dishes') }}</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-base border border-orange-100">
                                🍽️
                            </div>
                        </div>
                        <div class="text-3xl font-black text-slate-900 mt-2">
                            {{ $totalItemsCount }} <span class="text-xs text-slate-400 font-normal">Dishes</span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-2 flex items-center gap-1">
                            <span>Total registered menu items</span>
                        </div>
                    </a>

                    <!-- Stat 2: In-Stock & Available -->
                    <a href="{{ route('admin.inventory.index', ['stock_status' => 'available']) }}" 
                       class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-300 hover:shadow-md transition-all shadow-sm block">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">{{ __('Available') }}</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-100">
                                ✅
                            </div>
                        </div>
                        <div class="text-3xl font-black text-emerald-600 mt-2">
                            {{ $inStockCount }} <span class="text-xs text-slate-400 font-normal">Active</span>
                        </div>
                        <div class="text-xs text-emerald-600 font-medium mt-2 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                            <span>Ready for customer orders</span>
                        </div>
                    </a>

                    <!-- Stat 3: Out of Stock (Alert) -->
                    <a href="{{ route('admin.inventory.index', ['stock_status' => 'out_of_stock']) }}" 
                       class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-red-300 hover:shadow-md transition-all shadow-sm block">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">{{ __('Out of Stock') }}</span>
                            <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-base border border-red-100">
                                🚫
                            </div>
                        </div>
                        <div class="text-3xl font-black text-red-600 mt-2">
                            {{ $outOfStockCount }} <span class="text-xs text-slate-400 font-normal">Disabled</span>
                        </div>
                        <div class="text-xs text-red-600 font-medium mt-2 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                            <span>Hidden from ordering</span>
                        </div>
                    </a>

                    <!-- Stat 4: Low Stock Alert -->
                    <a href="{{ route('admin.inventory.index', ['stock_status' => 'low_stock']) }}" 
                       class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-300 hover:shadow-md transition-all shadow-sm block">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">{{ __('Low Stock') }}</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base border border-amber-100">
                                ⚠️
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-600 mt-2">
                            {{ $lowStockCount }} <span class="text-xs text-slate-400 font-normal">Alert (≤10)</span>
                        </div>
                        <div class="text-xs text-amber-600 font-medium mt-2 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                            <span>Needs kitchen prep / restocking</span>
                        </div>
                    </a>

                </div>

                <!-- INSTANT INVENTORY & MENU SWITCH MAIN CARD -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6">
                    
                    <!-- Section Header with Controls -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-orange-500/10 text-orange-600 flex items-center justify-center font-bold text-sm">
                                    ⚡
                                </div>
                                <h2 class="text-lg font-black text-slate-900 tracking-tight">{{ __('Instant Inventory & Menu Switch') }}</h2>
                                <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-full border border-amber-200 flex items-center gap-1">
                                    <span>1-Click Toggle</span>
                                </span>
                            </div>
                            <p class="text-slate-500 text-xs mt-1">Instantly switch dish availability on/off or adjust stock levels in real time to prevent order conflicts</p>
                        </div>

                        <!-- View Toggle Mode (Grid vs Detailed Table) -->
                        <div class="flex items-center gap-2">
                            <div class="bg-slate-100 p-1 rounded-xl flex items-center gap-1 border border-slate-200">
                                <button @click="viewMode = 'grid'" 
                                        :class="viewMode === 'grid' ? 'bg-white text-orange-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800'"
                                        class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                    <span>Quick Switch Grid</span>
                                </button>
                                <button @click="viewMode = 'table'" 
                                        :class="viewMode === 'table' ? 'bg-white text-orange-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800'"
                                        class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                    </svg>
                                    <span>Detailed Stock Table</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Search & Filter Controls Bar -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-slate-50/80 p-3 rounded-2xl border border-slate-200/80">
                        
                        <!-- Search Box -->
                        <div class="relative flex-1 min-w-[200px]">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   placeholder="Filter dishes by name or keyword..."
                                   class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white">
                            <button x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Category Selector -->
                        <div class="w-full md:w-48 shrink-0">
                            <select x-model="selectedCategory" 
                                    class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white font-medium text-slate-700">
                                <option value="all">🍽️ All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Status Tabs -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                            <button @click="activeStockTab = 'all'" 
                                    :class="activeStockTab === 'all' ? 'bg-orange-500 text-white font-bold shadow shadow-orange-500/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                    class="px-3 py-2 text-xs rounded-xl transition-all cursor-pointer shrink-0">
                                All ({{ count($menuItems) }})
                            </button>
                            <button @click="activeStockTab = 'available'" 
                                    :class="activeStockTab === 'available' ? 'bg-emerald-600 text-white font-bold shadow shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                    class="px-3 py-2 text-xs rounded-xl transition-all cursor-pointer shrink-0">
                                Available ({{ $menuItems->where('is_available', true)->where('stock', '>', 0)->count() }})
                            </button>
                            <button @click="activeStockTab = 'low_stock'" 
                                    :class="activeStockTab === 'low_stock' ? 'bg-amber-600 text-white font-bold shadow shadow-amber-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                    class="px-3 py-2 text-xs rounded-xl transition-all cursor-pointer shrink-0">
                                Low Stock ({{ $menuItems->where('stock', '>', 0)->where('stock', '<=', 10)->count() }})
                            </button>
                            <button @click="activeStockTab = 'out_of_stock'" 
                                    :class="activeStockTab === 'out_of_stock' ? 'bg-red-600 text-white font-bold shadow shadow-red-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                    class="px-3 py-2 text-xs rounded-xl transition-all cursor-pointer shrink-0">
                                Out of Stock ({{ $menuItems->filter(fn($i) => !$i->is_available || $i->stock <= 0)->count() }})
                            </button>
                        </div>

                    </div>

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
                                 class="p-4 bg-slate-50/90 hover:bg-white rounded-2xl border border-slate-200/80 hover:border-orange-300 hover:shadow-md transition-all flex flex-col justify-between gap-3 group relative">
                                
                                <!-- Top Row: Image & Info -->
                                <div class="flex items-start gap-3">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0 relative group-hover:scale-105 transition-transform">
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                        @if($isOutOfStock)
                                            <div class="absolute inset-0 bg-red-900/60 backdrop-blur-[1px] flex items-center justify-center text-[9px] font-black text-white uppercase text-center leading-tight">
                                                Sold Out
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $item->category ? 'bg-orange-50 text-orange-700 border border-orange-100' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $item->category->name ?? 'Uncategorized' }}
                                            </span>
                                        </div>
                                        <h3 class="font-bold text-slate-900 text-xs mt-1 truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                                        <div class="text-xs text-orange-600 font-mono font-bold mt-0.5">{{ number_format($item->price) }} MMK</div>
                                    </div>
                                </div>

                                <!-- Middle: Stock Level Info & Progress Bar -->
                                <div class="space-y-1.5 pt-2 border-t border-slate-100">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-slate-500 font-medium">{{ __('Stock Level') }}:</span>
                                        <span class="font-mono font-bold {{ $isOutOfStock ? 'text-red-600' : ($isLowStock ? 'text-amber-600' : 'text-emerald-700') }}">
                                            @if($isOutOfStock)
                                                🚫 0 (Out of Stock)
                                            @elseif($isLowStock)
                                                ⚠️ {{ $item->stock }} Left (Low)
                                            @else
                                                ✅ {{ $item->stock }} Units
                                            @endif
                                        </span>
                                    </div>

                                    <!-- Visual Stock Health Bar -->
                                    <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                                        @php
                                            $percent = min(100, max(0, ($item->stock / 50) * 100));
                                            $barColor = $isOutOfStock ? 'bg-red-500' : ($isLowStock ? 'bg-amber-500' : 'bg-emerald-500');
                                        @endphp
                                        <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-300" style="width: {{ $item->is_available ? $percent : 0 }}%"></div>
                                    </div>
                                </div>

                                <!-- Bottom Row: 1-Click Toggle Switch & Quick Adjust Button -->
                                <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                                    
                                    <!-- Instant 1-Click Toggle Form with AJAX / Standard fallback -->
                                    <form method="POST" action="{{ route('admin.inventory.toggle-stock', $item) }}" class="flex items-center gap-2">
                                        @csrf
                                        <button type="button" 
                                                @click="toggleItemStock({{ $item->id }}, {{ $item->is_available ? 'true' : 'false' }}, $event)"
                                                title="Click to toggle availability" 
                                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $item->is_available ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                            <span class="sr-only">Toggle Dish Stock</span>
                                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $item->is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                        <span class="text-[11px] font-bold {{ $item->is_available ? 'text-emerald-700' : 'text-slate-400' }}">
                                            {{ $item->is_available ? 'Available' : 'Disabled' }}
                                        </span>
                                    </form>

                                    <!-- Quick Adjust Modal Opener -->
                                    <button type="button" 
                                            @click="openAdjustModal({{ json_encode($item) }})"
                                            class="p-1.5 text-slate-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors cursor-pointer"
                                            title="{{ __('Adjust Stock') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                        </svg>
                                    </button>

                                </div>

                            </div>
                        @empty
                            <div class="col-span-full py-12 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <div class="text-4xl mb-3">🍽️</div>
                                <h3 class="text-sm font-bold text-slate-800">No dishes found</h3>
                                <p class="text-xs text-slate-500 mt-1">Try adjusting your filters or search terms.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- 2. TABLE VIEW: DETAILED INVENTORY & STOCK MANAGEMENT -->
                    <div x-show="viewMode === 'table'" class="overflow-x-auto rounded-2xl border border-slate-200/80">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5">Dish Details</th>
                                    <th class="p-3.5">Category</th>
                                    <th class="p-3.5">Price</th>
                                    <th class="p-3.5">Stock Level</th>
                                    <th class="p-3.5">Status</th>
                                    <th class="p-3.5">Instant Switch</th>
                                    <th class="p-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($menuItems as $item)
                                    @php
                                        $isAvailable = $item->is_available && $item->stock > 0;
                                        $isLowStock = $item->stock > 0 && $item->stock <= 10;
                                        $isOutOfStock = !$item->is_available || $item->stock <= 0;
                                    @endphp
                                    <tr x-data="{ 
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
                                        class="hover:bg-slate-50/80 transition-colors">
                                        
                                        <!-- Dish Info -->
                                        <td class="p-3.5 flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0">
                                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-900 truncate max-w-[180px]" title="{{ $item->name }}">{{ $item->name }}</div>
                                                <div class="text-[10px] text-slate-400">ID #{{ $item->id }}</div>
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td class="p-3.5">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $item->category ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $item->category->name ?? 'Uncategorized' }}
                                            </span>
                                        </td>

                                        <!-- Price -->
                                        <td class="p-3.5 font-mono font-bold text-slate-900">
                                            {{ number_format($item->price) }} MMK
                                        </td>

                                        <!-- Stock Level with Inline Quick Steppers -->
                                        <td class="p-3.5">
                                            <div class="flex items-center gap-2">
                                                <form method="POST" action="{{ route('admin.inventory.update-stock', $item) }}" class="flex items-center gap-1.5">
                                                    @csrf
                                                    <input type="number" 
                                                           name="stock" 
                                                           value="{{ $item->stock }}" 
                                                           min="0" 
                                                           max="99999"
                                                           class="w-16 px-2 py-1 text-xs text-center font-mono font-bold rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500 {{ $isOutOfStock ? 'bg-red-50 text-red-700 border-red-200' : ($isLowStock ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-white text-slate-800') }}">
                                                    <button type="submit" title="Save Stock" class="p-1 text-slate-400 hover:text-emerald-600 rounded cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                                
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isOutOfStock ? 'bg-red-50 text-red-600' : ($isLowStock ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                                                    {{ $isOutOfStock ? 'Empty' : ($isLowStock ? 'Low' : 'Good') }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="p-3.5">
                                            @if($item->is_available && $item->stock > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Available
                                                </span>
                                            @elseif(!$item->is_available)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    Disabled
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    Out of Stock
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Instant Toggle Switch -->
                                        <td class="p-3.5">
                                            <form method="POST" action="{{ route('admin.inventory.toggle-stock', $item) }}">
                                                @csrf
                                                <button type="button" 
                                                        @click="toggleItemStock({{ $item->id }}, {{ $item->is_available ? 'true' : 'false' }}, $event)"
                                                        title="Click to toggle availability" 
                                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $item->is_available ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                                    <span class="sr-only">Toggle Dish Stock</span>
                                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $item->is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Actions -->
                                        <td class="p-3.5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" 
                                                        @click="openAdjustModal({{ json_encode($item) }})"
                                                        class="px-2.5 py-1 bg-slate-100 hover:bg-orange-500 hover:text-white text-slate-700 font-bold text-[10px] rounded-lg transition-all cursor-pointer">
                                                    {{ __('Adjust Stock') }}
                                                </button>
                                                <a href="{{ route('admin.menuItems.index', ['search' => $item->name]) }}" 
                                                   title="Edit Item" 
                                                   class="p-1 text-slate-400 hover:text-slate-700 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-400">
                                            No items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

            </main>
        </div>

    </div>

    <!-- ================= SINGLE DISH ADJUST STOCK MODAL ================= -->
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
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                
                <form :action="adjustActionUrl" method="POST" class="p-6 space-y-5">
                    @csrf
                    
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-orange-500/10 text-orange-600 flex items-center justify-center font-bold text-lg">
                                📦
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900" x-text="'Adjust Stock: ' + adjustItemName"></h3>
                                <p class="text-xs text-slate-500" x-text="adjustItemCategory"></p>
                            </div>
                        </div>
                        <button type="button" @click="adjustModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Numeric Stock Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            {{ __('Stock Level') }} (Quantity in Kitchen)
                        </label>
                        <input type="number" 
                               name="stock" 
                               x-model="adjustCurrentStock"
                               min="0" 
                               max="99999"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm font-mono font-bold">
                    </div>

                    <!-- Quick Increment Buttons -->
                    <div>
                        <span class="block text-[11px] font-bold text-slate-500 mb-1.5">Quick Add Buttons:</span>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" @click="adjustCurrentStock = Number(adjustCurrentStock) + 5" class="py-1.5 px-2 bg-slate-100 hover:bg-orange-100 hover:text-orange-700 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                +5
                            </button>
                            <button type="button" @click="adjustCurrentStock = Number(adjustCurrentStock) + 10" class="py-1.5 px-2 bg-slate-100 hover:bg-orange-100 hover:text-orange-700 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                +10
                            </button>
                            <button type="button" @click="adjustCurrentStock = Number(adjustCurrentStock) + 25" class="py-1.5 px-2 bg-slate-100 hover:bg-orange-100 hover:text-orange-700 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                +25
                            </button>
                            <button type="button" @click="adjustCurrentStock = 50" class="py-1.5 px-2 bg-slate-100 hover:bg-orange-100 hover:text-orange-700 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                Set 50
                            </button>
                        </div>
                    </div>

                    <!-- Availability Toggle inside Modal -->
                    <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div>
                            <div class="text-xs font-bold text-slate-900">Mark as Available for Order</div>
                            <div class="text-[11px] text-slate-500">Allow customers to order this dish on the storefront</div>
                        </div>
                        <input type="checkbox" 
                               name="is_available" 
                               value="1" 
                               x-model="adjustIsAvailable"
                               class="w-5 h-5 text-orange-600 rounded border-slate-300 focus:ring-orange-500 cursor-pointer">
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 active:scale-95 rounded-xl shadow-lg shadow-orange-500/20 transition-all cursor-pointer">
                            Save Changes
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
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                
                <form action="{{ route('admin.inventory.bulk-restock') }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-lg">
                                🚀
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900">{{ __('Quick Restock') }}</h3>
                                <p class="text-xs text-slate-500">Batch restock menu items and restore availability</p>
                            </div>
                        </div>
                        <button type="button" @click="bulkModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Target Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Select Dishes to Restock
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-orange-50/50 rounded-xl border border-slate-200 cursor-pointer">
                                <input type="radio" name="target" value="low_stock" x-model="bulkTarget" class="text-orange-600 focus:ring-orange-500">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Low Stock Dishes (≤ 10 units)</div>
                                    <div class="text-[11px] text-slate-500">Currently {{ $lowStockCount }} items need replenishing</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-orange-50/50 rounded-xl border border-slate-200 cursor-pointer">
                                <input type="radio" name="target" value="out_of_stock" x-model="bulkTarget" class="text-orange-600 focus:ring-orange-500">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Out of Stock / Disabled Dishes</div>
                                    <div class="text-[11px] text-slate-500">Currently {{ $outOfStockCount }} items disabled</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-orange-50/50 rounded-xl border border-slate-200 cursor-pointer">
                                <input type="radio" name="target" value="all" x-model="bulkTarget" class="text-orange-600 focus:ring-orange-500">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">All Dishes (Full Restock)</div>
                                    <div class="text-[11px] text-slate-500">Set every dish in catalog to new stock level</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- New Stock Quantity -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            New Stock Level Per Dish
                        </label>
                        <input type="number" 
                               name="amount" 
                               x-model="bulkAmount"
                               min="1" 
                               max="1000"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm font-mono font-bold">
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="bulkModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 rounded-xl shadow-lg shadow-orange-500/20 transition-all cursor-pointer">
                            Execute Restock
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</body>
</html>
