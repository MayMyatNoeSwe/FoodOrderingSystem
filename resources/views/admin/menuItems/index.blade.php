<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Items - {{ config('app.name', 'Food Ordering System') }}</title>

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
          editItemMinStock: {{ old('min_stock_level') && old('_method') === 'PUT' ? old('min_stock_level') : '10' }},
          editItemDescription: '{{ old('description') && old('_method') === 'PUT' ? addslashes(old('description')) : '' }}',
          editItemImage: '{{ old('image') && old('_method') === 'PUT' ? addslashes(old('image')) : '' }}',
          editItemIsAvailable: {{ old('is_available') && old('_method') === 'PUT' ? 'true' : 'false' }},
          editItemUrl: '{{ old('edit_item_url', '') }}',

          createImageInput: '',
          createImagePreviews: [],
          
          editExistingImages: [],
          editNewImagePreviews: [],

          draggedIdx: null,

          handleCreateFiles(e) {
              const files = Array.from(e.target.files || []);
              if (files.length > 0) {
                  this.createImagePreviews = files.map(file => URL.createObjectURL(file));
              } else {
                  this.createImagePreviews = [];
              }
          },

          handleEditFiles(e) {
              const files = Array.from(e.target.files || []);
              if (files.length > 0) {
                  const newUrls = files.map(file => URL.createObjectURL(file));
                  this.editNewImagePreviews = [...this.editNewImagePreviews, ...newUrls];
              } else {
                  this.editNewImagePreviews = [];
              }
          },

          removeExistingImage(index) {
              this.editExistingImages.splice(index, 1);
          },

          moveExistingImage(fromIdx, toIdx) {
              if (toIdx < 0 || toIdx >= this.editExistingImages.length) return;
              const item = this.editExistingImages.splice(fromIdx, 1)[0];
              this.editExistingImages.splice(toIdx, 0, item);
          },

          setAsCoverExistingImage(idx) {
              if (idx <= 0 || idx >= this.editExistingImages.length) return;
              const item = this.editExistingImages.splice(idx, 1)[0];
              this.editExistingImages.unshift(item);
          },

          removeCreatePreview(index) {
              this.createImagePreviews.splice(index, 1);
          },

          moveCreateImage(fromIdx, toIdx) {
              if (toIdx < 0 || toIdx >= this.createImagePreviews.length) return;
              const item = this.createImagePreviews.splice(fromIdx, 1)[0];
              this.createImagePreviews.splice(toIdx, 0, item);
          },

          setAsCoverCreateImage(idx) {
              if (idx <= 0 || idx >= this.createImagePreviews.length) return;
              const item = this.createImagePreviews.splice(idx, 1)[0];
              this.createImagePreviews.unshift(item);
          },

          removeEditNewPreview(index) {
              this.editNewImagePreviews.splice(index, 1);
          },

          handleDrop(targetIdx, type = 'edit') {
              if (this.draggedIdx === null || this.draggedIdx === targetIdx) return;
              if (type === 'edit') {
                  this.moveExistingImage(this.draggedIdx, targetIdx);
              } else {
                  this.moveCreateImage(this.draggedIdx, targetIdx);
              }
              this.draggedIdx = null;
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
              this.editItemMinStock = (item.min_stock_level !== undefined && item.min_stock_level !== null) ? item.min_stock_level : 10;
              this.editItemDescription = item.description || '';
              this.editItemImage = item.image || '';
              
              if (item.images && Array.isArray(item.images) && item.images.length > 0) {
                  this.editExistingImages = [...item.images];
              } else if (item.image) {
                  this.editExistingImages = [item.image];
              } else {
                  this.editExistingImages = [];
              }

              this.editNewImagePreviews = [];
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
                            <span>Items Catalog</span>
                            <span class="hidden sm:inline-flex bg-orange-50 text-orange-600 border border-orange-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                {{ $menuItems->total() }} Dishes & Drinks
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">Manage items, multiple photos, min stock thresholds, and prices</p>
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
                    
                    <!-- Metric Card 1: Total Items -->
                    <a href="{{ route('admin.menuItems.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-5 relative overflow-hidden group hover:border-orange-300 hover:shadow-md transition-all shadow-sm block">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Items</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-base border border-orange-100">
                                🍕
                            </div>
                        </div>
                        <div class="text-3xl font-black text-slate-900 mt-2">{{ $totalItemsCount ?? $menuItems->total() }}</div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Total dishes in catalog</div>
                    </a>

                    <!-- Metric Card 2: In Stock -->
                    <a href="{{ route('admin.menuItems.index', array_filter(['category_id' => $categoryId, 'stock_status' => 'in_stock', 'search' => $search])) }}" class="bg-white border {{ ($stockStatus ?? '') === 'in_stock' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200/80' }} rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-300 hover:shadow-md transition-all shadow-sm block">
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
                    </a>

                    <!-- Metric Card 3: Low Stock Alert -->
                    <a href="{{ route('admin.menuItems.index', array_filter(['category_id' => $categoryId, 'stock_status' => 'low_stock', 'search' => $search])) }}" class="bg-white border {{ ($stockStatus ?? '') === 'low_stock' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200/80' }} rounded-2xl p-5 relative overflow-hidden group hover:border-amber-300 hover:shadow-md transition-all shadow-sm block">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Low Stock (≤ min)</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base border border-amber-100">
                                ⚠️
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-600 mt-2">
                            {{ $lowStockCount ?? 0 }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-2">Needs restocking soon</div>
                    </a>

                    <!-- Metric Card 4: Out of Stock -->
                    <a href="{{ route('admin.menuItems.index', array_filter(['category_id' => $categoryId, 'stock_status' => 'out_of_stock', 'search' => $search])) }}" class="bg-white border {{ ($stockStatus ?? '') === 'out_of_stock' ? 'border-red-500 ring-2 ring-red-500/20' : 'border-slate-200/80' }} rounded-2xl p-5 relative overflow-hidden group hover:border-red-300 hover:shadow-md transition-all shadow-sm block">
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
                    </a>

                </div>

                <!-- Items Management Section -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6">
                    
                    <!-- Search & Action Toolbar -->
                    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Food &amp; Drink Offerings</h3>
                            <p class="text-slate-500 text-xs mt-0.5">Manage details, multi-photos, prices, and min stock levels</p>
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

                                <!-- Text Search Input -->
                                <div class="relative w-full sm:w-56">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ $search }}" 
                                           placeholder="Search item name..." 
                                           class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-800 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-400">
                                    
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>

                                    @if($search || $categoryId || $stockStatus)
                                        <a href="{{ route('admin.menuItems.index') }}" 
                                           title="Clear Filters" 
                                           class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 p-0.5 text-xs font-bold rounded-full">
                                            ✕
                                        </a>
                                    @endif
                                </div>
                            </form>

                            <!-- Add Item Trigger Button -->
                            <button @click="createModalOpen = true" 
                                    class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Add Item</span>
                            </button>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5 w-16">Item</th>
                                    <th class="px-4 py-3.5">Name &amp; Description</th>
                                    <th class="px-4 py-3.5">Category</th>
                                    <th class="px-4 py-3.5">Price</th>
                                    <th class="px-4 py-3.5">Min Stock Alert</th>
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
                                        
                                        $imagesCount = is_array($item->images) ? count($item->images) : ($item->image ? 1 : 0);
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <!-- Image Thumbnail with Multi-Photo Badge -->
                                        <td class="px-4 py-4">
                                            <div class="relative w-12 h-12 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0">
                                                @if($item->image || !empty($item->images))
                                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full bg-orange-50 text-orange-600 flex items-center justify-center text-xl">
                                                        {{ $fallbackIcon }}
                                                    </div>
                                                @endif

                                                @if($imagesCount > 1)
                                                    <span class="absolute bottom-0 right-0 bg-slate-900/80 text-white font-bold text-[8px] px-1 py-0.2 rounded-tl-md">
                                                        +{{ $imagesCount }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Name & Description -->
                                        <td class="px-4 py-4">
                                            <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                                <span>{{ $item->name }}</span>
                                                @if($imagesCount > 1)
                                                    <span class="text-[10px] text-purple-600 bg-purple-50 border border-purple-200 px-1.5 py-0.2 rounded-full font-bold">
                                                        📸 {{ $imagesCount }} photos
                                                    </span>
                                                @endif
                                            </div>
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

                                        <!-- Min Stock Level -->
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg font-mono font-bold text-[11px] inline-flex items-center gap-1">
                                                <span>⚠️ Min:</span>
                                                <span>{{ $item->min_stock_level ?? 10 }} units</span>
                                            </span>
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
                                                    <span>Unavailable</span>
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
                                                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
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
                                                <div class="font-bold text-slate-800 text-sm">No Items Found</div>
                                                <p class="text-xs text-slate-500">
                                                    @if($search || $categoryId || $stockStatus)
                                                        No dish matching your current filter. Try clearing filters.
                                                    @else
                                                        Start adding delicious dishes and beverages to your food ordering catalog.
                                                    @endif
                                                </p>
                                                @if($search || $categoryId || $stockStatus)
                                                    <a href="{{ route('admin.menuItems.index') }}" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 hover:bg-slate-200">Clear Filters</a>
                                                @else
                                                    <button @click="createModalOpen = true" class="inline-block px-4 py-2 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/20">Add First Item</button>
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

    <!-- ================= CREATE ITEM MODAL ================= -->
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
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg font-bold border border-orange-100">
                        ➕
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Add New Item</h3>
                        <p class="text-slate-500 text-xs">Create a new offering with multi-photos and stock thresholds</p>
                    </div>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('admin.menuItems.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">

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

                <!-- Category, Price & Min Stock Level -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Category <span class="text-orange-500">*</span>
                        </label>
                        <select name="category_id" 
                                required 
                                class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-3 py-3 focus:ring-0 transition-all">
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
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-3 py-3 focus:ring-0 transition-all placeholder-slate-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Min Stock Alert
                        </label>
                        <input type="number" 
                               name="min_stock_level" 
                               min="0" 
                               value="10" 
                               placeholder="10" 
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-3 py-3 focus:ring-0 transition-all placeholder-slate-400"
                               title="Threshold when low-stock warning triggers in Inventory">
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

                <!-- Multiple Photos Upload & Live Gallery Preview -->
                <div class="space-y-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <span>📸</span>
                            <span>Upload Multiple Photos (ဓာတ်ပုံများ)</span>
                        </label>
                        <span class="text-[10px] text-purple-700 bg-purple-100 font-bold px-2 py-0.5 rounded-full"
                              x-text="(createImagePreviews.length > 0 ? createImagePreviews.length + ' Photos Selected' : (createImageInput ? '1 Photo via URL' : 'Multiple Allowed'))">
                        </span>
                    </div>

                    <!-- Multiple Files Picker Box -->
                    <div class="relative border-2 border-dashed border-slate-300 hover:border-orange-500 rounded-2xl p-4 text-center transition-all bg-white cursor-pointer group">
                        <input type="file" 
                               name="image_files[]" 
                               multiple 
                               accept="image/*" 
                               @change="handleCreateFiles($event)" 
                               class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <div class="py-2 space-y-1 pointer-events-none">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mx-auto text-xl group-hover:scale-110 transition-transform">
                                🖼️
                            </div>
                            <p class="text-xs font-bold text-slate-800">Click to Select Multiple Photos</p>
                            <p class="text-[10px] text-slate-500">Select one or multiple dish images (JPG, PNG, WEBP)</p>
                        </div>
                    </div>

                    <!-- Live Photo Gallery Preview Grid with Reordering -->
                    <template x-if="createImagePreviews.length > 0">
                        <div class="space-y-2 pt-2 border-t border-slate-200/80">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-bold text-slate-700">Selected Photos (Drag or use ◀ ▶ arrows to rearrange):</span>
                                <span class="text-[10px] text-amber-600 font-bold">First = Main Cover</span>
                            </div>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                                <template x-for="(src, idx) in createImagePreviews" :key="'create-' + idx">
                                    <div class="relative group rounded-2xl overflow-hidden border-2 transition-all bg-white aspect-square shadow-sm cursor-grab active:cursor-grabbing"
                                         :class="idx === 0 ? 'border-amber-400 ring-2 ring-amber-400/20' : 'border-slate-200 hover:border-orange-400'"
                                         draggable="true"
                                         @dragstart="draggedIdx = idx"
                                         @dragover.prevent=""
                                         @drop.prevent="handleDrop(idx, 'create')">
                                        
                                        <img :src="src" class="w-full h-full object-cover select-none pointer-events-none">
                                        
                                        <!-- Top Badges / Set Cover Action -->
                                        <template x-if="idx === 0">
                                            <span class="absolute top-1.5 left-1.5 bg-amber-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-lg shadow-sm flex items-center gap-0.5 z-10 pointer-events-none">
                                                ⭐ Cover
                                            </span>
                                        </template>
                                        <template x-if="idx > 0">
                                            <button type="button" 
                                                    @click.stop="setAsCoverCreateImage(idx)" 
                                                    title="Set as Cover Photo" 
                                                    class="absolute top-1.5 left-1.5 bg-black/75 hover:bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity z-10 shadow-sm cursor-pointer">
                                                ⭐ Set Cover
                                            </button>
                                        </template>

                                        <!-- Remove Button -->
                                        <button type="button" 
                                                @click.stop="removeCreatePreview(idx)" 
                                                title="Remove Photo"
                                                class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs font-black flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10 cursor-pointer shadow-md">
                                            ✕
                                        </button>

                                        <!-- Bottom Reorder Navigation Bar -->
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-1.5 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                            <button type="button" 
                                                    @click.stop="moveCreateImage(idx, idx - 1)" 
                                                    :disabled="idx === 0"
                                                    :class="idx === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/30 cursor-pointer'"
                                                    class="w-5 h-5 rounded-md bg-black/40 text-white text-[10px] font-bold flex items-center justify-center transition-colors"
                                                    title="Move Left (Earlier)">
                                                ◀
                                            </button>
                                            
                                            <span class="text-[9px] text-white/90 font-mono font-bold" x-text="'#' + (idx + 1)"></span>

                                            <button type="button" 
                                                    @click.stop="moveCreateImage(idx, idx + 1)" 
                                                    :disabled="idx === createImagePreviews.length - 1"
                                                    :class="idx === createImagePreviews.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/30 cursor-pointer'"
                                                    class="w-5 h-5 rounded-md bg-black/40 text-white text-[10px] font-bold flex items-center justify-center transition-colors"
                                                    title="Move Right (Later)">
                                                ▶
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Or Image URL (Single or Comma-separated) -->
                    <div class="pt-2 border-t border-slate-200/80 space-y-1">
                        <span class="text-[10px] text-slate-500 font-semibold block">Or paste image URL(s):</span>
                        <input type="text" 
                               name="image" 
                               x-model="createImageInput" 
                               placeholder="https://... or /images/dish.png (comma separated for multiple)" 
                               class="w-full bg-white border border-slate-200 focus:border-orange-500 text-slate-900 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all placeholder-slate-400">
                    </div>
                </div>

                <!-- Availability Toggle -->
                <div class="pt-1 flex items-center gap-3">
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

    <!-- ================= EDIT ITEM MODAL ================= -->
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
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg font-bold border border-orange-100">
                        ✏️
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Edit Item</h3>
                        <p class="text-slate-500 text-xs">Update dish details, multiple photos, min stock level, and price</p>
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
                <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">

                <!-- Hidden inputs to persist existing images array -->
                <template x-for="(img, idx) in editExistingImages" :key="'ex-' + idx">
                    <input type="hidden" name="existing_images[]" :value="img">
                </template>

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

                <!-- Category, Price & Min Stock Level -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Category <span class="text-orange-500">*</span>
                        </label>
                        <select name="category_id" 
                                x-model="editItemCategoryId" 
                                required 
                                class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-3 py-3 focus:ring-0 transition-all">
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
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-3 py-3 focus:ring-0 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Min Stock Alert
                        </label>
                        <input type="number" 
                               name="min_stock_level" 
                               min="0" 
                               x-model="editItemMinStock" 
                               placeholder="10" 
                               class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white text-slate-900 text-sm rounded-xl px-3 py-3 focus:ring-0 transition-all"
                               title="Threshold when low-stock warning triggers in Inventory">
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

                <!-- Multiple Photos Upload & Live Gallery Management -->
                <div class="space-y-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <span>📸</span>
                            <span>Item Photos Gallery (ဓာတ်ပုံများ)</span>
                        </label>
                        <span class="text-[10px] text-purple-700 bg-purple-100 font-bold px-2 py-0.5 rounded-full"
                              x-text="(editExistingImages.length + editNewImagePreviews.length) + ' Photos'">
                        </span>
                    </div>

                    <!-- Existing Saved Photos Grid with Drag & Reorder Controls -->
                    <template x-if="editExistingImages.length > 0">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">
                                    Current Saved Photos <span class="text-slate-400 font-normal normal-case">(Drag or use ◀ ▶ arrows to rearrange)</span>
                                </span>
                                <span class="text-[10px] text-amber-600 font-bold">First = Main Cover</span>
                            </div>

                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                                <template x-for="(img, idx) in editExistingImages" :key="'saved-' + idx">
                                    <div class="relative group rounded-2xl overflow-hidden border-2 transition-all bg-white aspect-square shadow-sm cursor-grab active:cursor-grabbing"
                                         :class="idx === 0 ? 'border-amber-400 ring-2 ring-amber-400/20' : 'border-slate-200 hover:border-orange-400'"
                                         draggable="true"
                                         @dragstart="draggedIdx = idx"
                                         @dragover.prevent=""
                                         @drop.prevent="handleDrop(idx, 'edit')">
                                        
                                        <img :src="resolveImageSrc(img)" class="w-full h-full object-cover select-none pointer-events-none">
                                        
                                        <!-- Top Cover Badge / Action -->
                                        <template x-if="idx === 0">
                                            <span class="absolute top-1.5 left-1.5 bg-amber-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-lg shadow-sm flex items-center gap-0.5 z-10 pointer-events-none">
                                                ⭐ Cover
                                            </span>
                                        </template>
                                        <template x-if="idx > 0">
                                            <button type="button" 
                                                    @click.stop="setAsCoverExistingImage(idx)" 
                                                    title="Set as Cover Photo" 
                                                    class="absolute top-1.5 left-1.5 bg-black/75 hover:bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity z-10 shadow-sm cursor-pointer">
                                                ⭐ Set Cover
                                            </button>
                                        </template>

                                        <!-- Remove Photo Button -->
                                        <button type="button" 
                                                @click.stop="removeExistingImage(idx)" 
                                                title="Remove photo" 
                                                class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs font-black flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10 cursor-pointer shadow-md">
                                            ✕
                                        </button>

                                        <!-- Bottom Reorder Navigation Bar -->
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-1.5 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                            <button type="button" 
                                                    @click.stop="moveExistingImage(idx, idx - 1)" 
                                                    :disabled="idx === 0"
                                                    :class="idx === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/30 cursor-pointer'"
                                                    class="w-5 h-5 rounded-md bg-black/40 text-white text-[10px] font-bold flex items-center justify-center transition-colors"
                                                    title="Move Left (Earlier)">
                                                ◀
                                            </button>
                                            
                                            <span class="text-[9px] text-white/90 font-mono font-bold" x-text="'#' + (idx + 1)"></span>

                                            <button type="button" 
                                                    @click.stop="moveExistingImage(idx, idx + 1)" 
                                                    :disabled="idx === editExistingImages.length - 1"
                                                    :class="idx === editExistingImages.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/30 cursor-pointer'"
                                                    class="w-5 h-5 rounded-md bg-black/40 text-white text-[10px] font-bold flex items-center justify-center transition-colors"
                                                    title="Move Right (Later)">
                                                ▶
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Newly Selected Files Preview -->
                    <template x-if="editNewImagePreviews.length > 0">
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/80">
                            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">+ New Photos to Upload:</span>
                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="(src, idx) in editNewImagePreviews" :key="'new-' + idx">
                                    <div class="relative group rounded-xl overflow-hidden border border-emerald-300 bg-white aspect-square shadow-sm">
                                        <img :src="src" class="w-full h-full object-cover">
                                        <button type="button" @click="removeEditNewPreview(idx)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Add More / Replace Files Button -->
                    <div class="relative border-2 border-dashed border-slate-300 hover:border-orange-500 rounded-2xl p-3 text-center transition-all bg-white cursor-pointer group">
                        <input type="file" 
                               name="image_files[]" 
                               multiple 
                               accept="image/*" 
                               @change="handleEditFiles($event)" 
                               class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <div class="py-1 space-y-0.5 pointer-events-none">
                            <p class="text-xs font-bold text-slate-800">📷 Add More Photos</p>
                            <p class="text-[10px] text-slate-500">Hold Ctrl/Shift to pick multiple new images</p>
                        </div>
                    </div>

                    <!-- Image URL input -->
                    <div class="pt-1 space-y-1">
                        <span class="text-[10px] text-slate-500 font-semibold block">Or add via Image URL(s):</span>
                        <input type="text" 
                               name="image" 
                               x-model="editItemImage" 
                               placeholder="https://... (comma separated)" 
                               class="w-full bg-white border border-slate-200 focus:border-orange-500 text-slate-900 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all placeholder-slate-400">
                    </div>
                </div>

                <!-- Availability Toggle -->
                <div class="pt-1 flex items-center gap-3">
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
