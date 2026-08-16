<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Menu Items - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(form, itemName, type = 'item') {
            Swal.fire({
                title: 'Delete ' + (type === 'category' ? 'Category' : 'Food Item') + '?',
                html: `Are you sure you want to delete <strong class="text-orange-500">'${itemName}'</strong>?<br><span class="text-xs text-slate-500 mt-1 block">This action cannot be undone.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete',
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
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ 
          mobileMenuOpen: false,
          createModalOpen: {{ $errors->any() && !old('_method') ? 'true' : 'false' }}, 
          editModalOpen: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }}, 
          
          editItemId: {{ old('edit_item_id') ? old('edit_item_id') : 'null' }}, 
          editItemName: '{{ old('name') && old('_method') === 'PUT' ? addslashes(old('name')) : '' }}',
          editItemCategoryId: '{{ old('category_id') && old('_method') === 'PUT' ? old('category_id') : '' }}',
          editItemPrice: '{{ old('price') && old('_method') === 'PUT' ? old('price') : '' }}',
          editItemStock: '{{ old('stock') && old('_method') === 'PUT' ? old('stock') : '' }}',
          editItemDescription: '{{ old('description') && old('_method') === 'PUT' ? addslashes(old('description')) : '' }}',
          editItemImage: '{{ old('image') && old('_method') === 'PUT' ? addslashes(old('image')) : '' }}',
          editItemIsAvailable: {{ old('is_available') && old('_method') === 'PUT' ? 'true' : 'false' }},
          editItemUrl: '{{ old('edit_item_url', '') }}',

          createImageInput: '',
          createPreviewUrl: '',
          editPreviewUrl: '',

          handleCreateFile(e) {
              const file = e.target.files[0];
              if (file) {
                  this.createPreviewUrl = URL.createObjectURL(file);
              }
          },
          handleEditFile(e) {
              const file = e.target.files[0];
              if (file) {
                  this.editPreviewUrl = URL.createObjectURL(file);
              }
          },
          resolveImageSrc(imgPath) {
              if (!imgPath) return '';
              const img = imgPath.trim();
              if (img.startsWith('http://') || img.startsWith('https://')) return img;
              if (img.startsWith('/images/') || img.startsWith('images/')) return '/' + img.replace(/^\//, '');
              if (img.startsWith('/storage/') || img.startsWith('storage/')) return '/' + img.replace(/^\//, '');
              return '/storage/' + img;
          },

          openEditModal(item, url) {
              this.editItemId = item.id;
              this.editItemName = item.name;
              this.editItemCategoryId = item.category_id;
              this.editItemPrice = item.price;
              this.editItemStock = item.stock !== undefined ? item.stock : 50;
              this.editItemDescription = item.description || '';
              this.editItemImage = item.image || '';
              this.editPreviewUrl = item.image ? this.resolveImageSrc(item.image) : '';
              this.editItemIsAvailable = item.is_available ? true : false;
              this.editItemUrl = url;
              this.editModalOpen = true;
          }
      }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="menuItems" />

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
                            <span>Menu Items Catalog</span>
                            <span class="hidden sm:inline-flex bg-orange-50 text-orange-600 border border-orange-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                {{ $menuItems->total() }} Dishes & Drinks
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">Manage menu offerings, prices, availability, and descriptions</p>
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

            <!-- Main Scrollable Dashboard Content -->
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
                                background: '#ffffff',
                                color: '#0f172a',
                                customClass: {
                                    popup: 'border border-emerald-200 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                <!-- Validation Errors Banner -->
                @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-semibold space-y-1.5 shadow-sm">
                        <div class="flex items-center gap-2 text-red-800 font-bold mb-1">
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

                <!-- Overview Stat Metric Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- Metric Card 1: Total Menu Items -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Menu Items</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-base border border-orange-100">
                                🍕
                            </div>
                        </div>
                        <div class="text-3xl font-black text-slate-900 mt-2">{{ $totalItemsCount ?? $menuItems->total() }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Total Dishes in catalog</div>
                    </div>

                    <!-- Metric Card 2: In Stock -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">In Stock (> 10)</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-100">
                                📦
                            </div>
                        </div>
                        <div class="text-3xl font-black text-emerald-600 mt-2">
                            {{ $inStockCount ?? 0 }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Healthy inventory levels</div>
                    </div>

                    <!-- Metric Card 3: Low Stock Alert -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Low Stock (1-10)</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base border border-amber-100">
                                ⚠️
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-600 mt-2">
                            {{ $lowStockCount ?? 0 }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Needs restocking soon</div>
                    </div>

                    <!-- Metric Card 4: Out of Stock -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 hover:shadow-md transition-all shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Out of Stock (0)</span>
                            <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-base border border-red-100">
                                🚫
                            </div>
                        </div>
                        <div class="text-3xl font-black text-red-600 mt-2">
                            {{ $outOfStockCount ?? 0 }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Items depleted</div>
                    </div>

                </div>

                <!-- Menu Items Management Section -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6">
                    
                    <!-- Search & Action Toolbar -->
                    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Food & Drink Offerings</h3>
                            <p class="text-slate-500 text-xs mt-0.5">Manage details, pricing, availability toggles, and images</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            
                            <!-- Search & Filter Form -->
                            <form method="GET" action="{{ route('admin.menuItems.index') }}" class="flex flex-col sm:flex-row items-center gap-2">
                                
                                <!-- Category Select Filter -->
                                <select name="category_id" 
                                        onchange="this.form.submit()" 
                                        class="bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3 py-2.5 focus:ring-0 cursor-pointer w-full sm:w-auto">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <!-- Stock Status Select Filter -->
                                <select name="stock_status" 
                                        onchange="this.form.submit()" 
                                        class="bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3 py-2.5 focus:ring-0 cursor-pointer w-full sm:w-auto">
                                    <option value="">All Stock Levels</option>
                                    <option value="in_stock" {{ ($stockStatus ?? '') === 'in_stock' ? 'selected' : '' }}>In Stock (> 10)</option>
                                    <option value="low_stock" {{ ($stockStatus ?? '') === 'low_stock' ? 'selected' : '' }}>Low Stock (1-10)</option>
                                    <option value="out_of_stock" {{ ($stockStatus ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                                </select>

                                <!-- Text Search Input -->
                                <div class="relative w-full sm:w-56">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ $search }}" 
                                           placeholder="Search food item name..." 
                                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-400">
                                    
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>

                                    @if($search || $categoryId)
                                        <a href="{{ route('admin.menuItems.index') }}" 
                                           title="Clear Filters" 
                                           class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 p-0.5 text-xs font-bold rounded-full">
                                            ✕
                                        </a>
                                    @endif
                                </div>
                            </form>

                            <!-- Add Menu Item Trigger Button -->
                            <button @click="createModalOpen = true" 
                                    class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Add Menu Item</span>
                            </button>
                        </div>
                    </div>

                    <!-- Menu Items Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5 w-16">Item</th>
                                    <th class="px-4 py-3.5">Name & Description</th>
                                    <th class="px-4 py-3.5">Category</th>
                                    <th class="px-4 py-3.5">Price</th>
                                    <th class="px-4 py-3.5">Stock</th>
                                    <th class="px-4 py-3.5">Status</th>
                                    <th class="px-4 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                @forelse($menuItems as $item)
                                    @php
                                        $catName = strtolower($item->category ? $item->category->name : '');
                                        $fallbackIcon = '🍽️';
                                        if (str_contains($catName, 'pizza')) { $fallbackIcon = '🍕'; }
                                        elseif (str_contains($catName, 'burger')) { $fallbackIcon = '🍔'; }
                                        elseif (str_contains($catName, 'noodle') || str_contains($catName, 'pasta')) { $fallbackIcon = '🍜'; }
                                        elseif (str_contains($catName, 'drink') || str_contains($catName, 'beverage')) { $fallbackIcon = '🍹'; }
                                        elseif (str_contains($catName, 'dessert')) { $fallbackIcon = '🍰'; }
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <!-- Image Thumbnail -->
                                        <td class="px-4 py-4">
                                            @if($item->image)
                                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0">
                                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                                </div>
                                            @else
                                                <div class="w-12 h-12 rounded-xl bg-orange-50 border border-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                                                    {{ $fallbackIcon }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Name & Description -->
                                        <td class="px-4 py-4">
                                            <div class="font-extrabold text-slate-900 text-sm">{{ $item->name }}</div>
                                            <div class="text-slate-500 text-xs mt-0.5 line-clamp-1 max-w-sm">
                                                {{ $item->description ?? 'No description available.' }}
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg border border-slate-200 text-[11px] font-bold">
                                                {{ $item->category ? $item->category->name : 'Unassigned' }}
                                            </span>
                                        </td>

                                        <!-- Price -->
                                        <td class="px-4 py-4 font-black text-emerald-600 text-sm whitespace-nowrap">
                                            {{ number_format($item->price) }} MMK
                                        </td>

                                        <!-- Stock -->
                                        <td class="px-4 py-4 font-bold text-xs whitespace-nowrap">
                                            @if($item->stock > 10)
                                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg border border-slate-200 font-mono">
                                                    📦 {{ number_format($item->stock) }} left
                                                </span>
                                            @elseif($item->stock > 0)
                                                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg border border-amber-200 font-mono font-bold">
                                                    ⚠️ {{ number_format($item->stock) }} left
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg border border-red-200 font-mono font-bold">
                                                    🚫 0 left
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Availability Status -->
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($item->is_available)
                                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200 text-[11px] font-bold inline-flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    <span>Available</span>
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full border border-red-200 text-[11px] font-bold inline-flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    <span>Out of Stock</span>
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Edit Trigger Button -->
                                                <button @click="openEditModal({{ json_encode($item) }}, '{{ route('admin.menuItems.update', $item) }}')" 
                                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 rounded-lg border border-slate-200 transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                    <span>✏️</span>
                                                    <span>Edit</span>
                                                </button>

                                                <!-- Delete Form Button -->
                                                <form method="POST" 
                                                      action="{{ route('admin.menuItems.destroy', $item) }}" 
                                                      onsubmit="return confirmDelete(this, '{{ addslashes($item->name) }}', 'item');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                        <span>🗑️</span>
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                            <div class="max-w-xs mx-auto space-y-3">
                                                <div class="text-3xl">🍕</div>
                                                <div class="font-bold text-slate-800 text-sm">No Menu Items Found</div>
                                                <p class="text-xs text-slate-500">
                                                    @if($search || $categoryId)
                                                        No dish matching your current filter. Try clearing filters.
                                                    @else
                                                        Start adding delicious dishes and beverages to your food ordering catalog.
                                                    @endif
                                                </p>
                                                @if($search || $categoryId)
                                                    <a href="{{ route('admin.menuItems.index') }}" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 hover:bg-slate-200">Clear Filters</a>
                                                @else
                                                    <button @click="createModalOpen = true" class="inline-block px-4 py-2 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/20">Add First Menu Item</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    @if($menuItems->hasPages())
                        <div class="pt-2 border-t border-slate-100">
                            {{ $menuItems->links() }}
                        </div>
                    @endif

                </div>

            </main>
        </div>

    </div>

    <!-- ================= CREATE MENU ITEM MODAL ================= -->
    <div x-show="createModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="createModalOpen = false" 
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg font-bold border border-orange-100">
                        ➕
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Add New Menu Item</h3>
                        <p class="text-slate-500 text-xs">Create a new dish or drink offering</p>
                    </div>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('admin.menuItems.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Item Name <span class="text-orange-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           required 
                           placeholder="e.g. Pepperoni Feast Pizza, Iced Matcha Latte" 
                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all placeholder-slate-400">
                </div>

                <!-- Category, Price & Stock -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Category <span class="text-orange-500">*</span>
                        </label>
                        <select name="category_id" 
                                required 
                                class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Price (MMK) <span class="text-orange-500">*</span>
                        </label>
                        <input type="number" 
                               name="price" 
                               step="1" 
                               min="0" 
                               required 
                               placeholder="15000" 
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all placeholder-slate-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Stock Total <span class="text-orange-500">*</span>
                        </label>
                        <input type="number" 
                               name="stock" 
                               min="0" 
                               value="50" 
                               required 
                               placeholder="50" 
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all placeholder-slate-400">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Description</label>
                    <textarea name="description" 
                              rows="3" 
                              placeholder="Brief description of ingredients, flavor profile..." 
                              class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all placeholder-slate-400"></textarea>
                </div>

                <!-- Live Image Preview Widget -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Image Preview & Upload</label>
                    
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0 flex items-center justify-center relative shadow-sm">
                            <template x-if="createPreviewUrl || (createImageInput && resolveImageSrc(createImageInput))">
                                <img :src="createPreviewUrl || resolveImageSrc(createImageInput)" 
                                     alt="Preview" 
                                     class="w-full h-full object-cover">
                            </template>
                            <template x-if="!createPreviewUrl && (!createImageInput || !resolveImageSrc(createImageInput))">
                                <span class="text-2xl opacity-40">🖼️</span>
                            </template>
                        </div>
                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" :class="(createPreviewUrl || createImageInput) ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                <span x-text="(createPreviewUrl || createImageInput) ? 'Image Selected' : 'No Image Selected'"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 truncate" x-text="createPreviewUrl || createImageInput || 'Upload a file or enter image URL'"></p>
                        </div>
                    </div>

                    <input type="text" 
                           name="image" 
                           x-model="createImageInput" 
                           placeholder="https://images.unsplash.com/photo-..." 
                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-xs rounded-xl px-4 py-2.5 focus:ring-0 transition-all placeholder-slate-400">
                    <div class="text-[10px] text-slate-500 font-medium">Or replace with file upload:</div>
                    <input type="file" 
                           name="image_file" 
                           accept="image/*" 
                           @change="handleCreateFile($event)" 
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <!-- Availability Toggle -->
                <div class="pt-2 flex items-center gap-3">
                    <input type="checkbox" 
                           id="create_is_available" 
                           name="is_available" 
                           value="1" 
                           checked 
                           class="w-4 h-4 rounded border-slate-300 text-orange-500 focus:ring-0 cursor-pointer">
                    <label for="create_is_available" class="text-xs font-bold text-slate-700 cursor-pointer">
                        Available for ordering immediately
                    </label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                        Create Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= EDIT MENU ITEM MODAL ================= -->
    <div x-show="editModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="editModalOpen = false" 
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg font-bold border border-orange-100">
                        ✏️
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Edit Menu Item</h3>
                        <p class="text-slate-500 text-xs">Update dish details, price, or stock status</p>
                    </div>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Form -->
            <form method="POST" :action="editItemUrl" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <input type="hidden" name="edit_item_id" :value="editItemId">
                <input type="hidden" name="edit_item_url" :value="editItemUrl">

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Item Name <span class="text-orange-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           x-model="editItemName" 
                           required 
                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all">
                </div>

                <!-- Category, Price & Stock -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Category <span class="text-orange-500">*</span>
                        </label>
                        <select name="category_id" 
                                x-model="editItemCategoryId" 
                                required 
                                class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Price (MMK) <span class="text-orange-500">*</span>
                        </label>
                        <input type="number" 
                               name="price" 
                               step="1" 
                               min="0" 
                               x-model="editItemPrice" 
                               required 
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Stock Total <span class="text-orange-500">*</span>
                        </label>
                        <input type="number" 
                               name="stock" 
                               min="0" 
                               x-model="editItemStock" 
                               required 
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Description</label>
                    <textarea name="description" 
                              rows="3" 
                              x-model="editItemDescription" 
                              class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all"></textarea>
                </div>

                <!-- Live Image Preview Widget -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Image Preview & Upload</label>
                    
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0 flex items-center justify-center relative shadow-sm">
                            <template x-if="editPreviewUrl || (editItemImage && resolveImageSrc(editItemImage))">
                                <img :src="editPreviewUrl || resolveImageSrc(editItemImage)" 
                                     alt="Preview" 
                                     class="w-full h-full object-cover">
                            </template>
                            <template x-if="!editPreviewUrl && (!editItemImage || !resolveImageSrc(editItemImage))">
                                <span class="text-2xl opacity-40">🖼️</span>
                            </template>
                        </div>
                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" :class="(editPreviewUrl || editItemImage) ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                <span x-text="(editPreviewUrl || editItemImage) ? 'Current Image Preview' : 'No Image Set'"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 truncate font-mono" x-text="editPreviewUrl || editItemImage || 'Upload a file or enter image URL'"></p>
                        </div>
                    </div>

                    <input type="text" 
                           name="image" 
                           x-model="editItemImage" 
                           placeholder="https://images.unsplash.com/photo-..." 
                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-xs rounded-xl px-4 py-2.5 focus:ring-0 transition-all placeholder-slate-400">
                    <div class="text-[10px] text-slate-500 font-medium">Or replace with file upload:</div>
                    <input type="file" 
                           name="image_file" 
                           accept="image/*" 
                           @change="handleEditFile($event)" 
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <!-- Availability Toggle -->
                <div class="pt-2 flex items-center gap-3">
                    <input type="checkbox" 
                           id="edit_is_available" 
                           name="is_available" 
                           value="1" 
                           x-model="editItemIsAvailable" 
                           class="w-4 h-4 rounded border-slate-300 text-orange-500 focus:ring-0 cursor-pointer">
                    <label for="edit_is_available" class="text-xs font-bold text-slate-700 cursor-pointer">
                        Available for customer ordering
                    </label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
