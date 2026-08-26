<x-admin-layout 
    active="categories" 
    title="Food Categories - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Food Categories Catalog') }}"
    subheading="{{ __('Organize menu catalog categories and item classifications') }}">

    <x-slot:head>
        <script>
            function confirmDelete(form, itemName, type = 'category') {
                Swal.fire({
                    title: 'Delete ' + (type === 'category' ? 'Category' : 'Food Item') + '?',
                    html: `Are you sure you want to delete category <strong class="text-orange-500">'${itemName}'</strong>?<br><span class="text-xs text-slate-500 mt-1 block">This will remove it from the catalog.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Delete Category',
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
            {{ $categories->total() }} {{ __('Categories') }}
        </span>
    </x-slot:badge>

    <div x-data="{ 
        createModalOpen: {{ (isset($errors) && $errors->any()) && !old('_method') ? 'true' : 'false' }}, 
        editModalOpen: {{ (isset($errors) && $errors->any()) && old('_method') === 'PUT' ? 'true' : 'false' }}, 
        editCategoryId: {{ old('edit_category_id') ? old('edit_category_id') : 'null' }}, 
        createCategoryName: '{{ old('name') && !old('_method') ? addslashes(old('name')) : '' }}',
        editCategoryName: '{{ old('name') && old('_method') === 'PUT' ? addslashes(old('name')) : '' }}', 
        editCategoryUrl: '{{ old('edit_category_url', '') }}',
        
        slugify(text) {
            return text.toString().toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-');
        },
        openEditModal(id, name, url) {
            this.editCategoryId = id;
            this.editCategoryName = name;
            this.editCategoryUrl = url;
            this.editModalOpen = true;
        }
    }" class="space-y-6">



        <!-- Validation Errors Banner -->
        @if(isset($errors) && $errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-2xl text-red-700 dark:text-red-300 text-xs font-semibold space-y-1.5 shadow-xs">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-200 font-bold mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ __('Please fix the following validation errors:') }}</span>
                </div>
                @foreach($errors->all() as $error)
                    <p class="pl-6">• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Overview Stat Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            
            <!-- Metric Card 1: Total Categories -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total Categories') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-base border border-orange-100 dark:border-orange-900">
                        📂
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $categories->total() }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>{{ __('Active food groupings') }}</span>
                </div>
            </div>

            <!-- Metric Card 2: Total Menu Items Categorized -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Linked Food Items') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-base border border-amber-100 dark:border-amber-900">
                        🍕
                    </div>
                </div>
                <div class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    {{ $categories->sum('menu_items_count') }} {{ __('Items') }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Mapped to categories') }}</div>
            </div>

            <!-- Metric Card 3: Filter / Active State Summary -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Search Filter Status') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-base border border-blue-100 dark:border-blue-900">
                        🔍
                    </div>
                </div>
                <div class="text-base font-bold text-slate-900 dark:text-white mt-2 truncate">
                    @if($search)
                        {{ __('Filtered:') }} "<span class="text-orange-600 dark:text-orange-400">{{ $search }}</span>"
                    @else
                        {{ __('Showing All Categories') }}
                    @endif
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center justify-between">
                    <span>{{ __('Page') }} {{ $categories->currentPage() }} {{ __('of') }} {{ max(1, $categories->lastPage()) }}</span>
                    @if($search)
                        <a href="{{ route('admin.categories.index') }}" class="text-orange-600 dark:text-orange-400 hover:underline text-[11px] font-bold">{{ __('Clear Filter') }}</a>
                    @endif
                </div>
            </div>

        </div>

        <!-- Categories Management Header & Controls -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
            
            <!-- Search & Action Toolbar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ __('Category List') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ __('Manage category titles, slugs, and menu assignments') }}</p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-wrap">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5 flex-wrap">
                        <div class="relative min-w-[200px]">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="{{ __('Search category...') }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3.5 py-2 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-400">
                            
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            @if($search)
                                <a href="{{ route('admin.categories.index', request()->except('search')) }}" 
                                   title="{{ __('Clear Search') }}" 
                                   class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-700 dark:hover:text-white text-xs font-bold">
                                    ✕
                                </a>
                            @endif
                        </div>

                        {{-- Shop Filter --}}
                        <select name="shop_id" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-0 transition-all cursor-pointer">
                            <option value="all">Shop: All Shops</option>
                            @foreach($shops as $s)
                                <option value="{{ $s->id }}" {{ ($shopId ?? '') == $s->id ? 'selected' : '' }}>🏪 {{ $s->name }}</option>
                            @endforeach
                        </select>

                        {{-- Sort By --}}
                        <select name="sort_by" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-0 transition-all cursor-pointer">
                            <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                            <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                            <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="items_count_desc" {{ ($sortBy ?? '') === 'items_count_desc' ? 'selected' : '' }}>Most Items</option>
                        </select>

                        @if(!empty($search) || (!empty($shopId) && $shopId !== 'all') || (!empty($sortBy) && $sortBy !== 'latest'))
                            <a href="{{ route('admin.categories.index') }}" 
                               class="px-2.5 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all whitespace-nowrap">
                                Reset
                            </a>
                        @endif
                    </form>

                    <!-- Add Category Trigger Button -->
                    <button @click="createModalOpen = true" 
                            type="button"
                            class="px-3.5 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-md shadow-orange-500/25 transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>{{ __('Add Category') }}</span>
                    </button>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5 w-16">ID</th>
                            <th class="px-4 py-3.5">{{ __('Category Name') }}</th>
                            <th class="px-4 py-3.5">{{ __('Shop') }}</th>
                            <th class="px-4 py-3.5">{{ __('URL Slug') }}</th>
                            <th class="px-4 py-3.5">{{ __('Items Count') }}</th>
                            <th class="px-4 py-3.5">{{ __('Created Date') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($categories as $category)
                            @php
                                $nameLower = strtolower($category->name);
                                $icon = '🍽️';
                                if (str_contains($nameLower, 'pizza')) { $icon = '🍕'; }
                                elseif (str_contains($nameLower, 'burger') || str_contains($nameLower, 'sandwich')) { $icon = '🍔'; }
                                elseif (str_contains($nameLower, 'noodle') || str_contains($nameLower, 'pasta') || str_contains($nameLower, 'ramen')) { $icon = '🍜'; }
                                elseif (str_contains($nameLower, 'beverage') || str_contains($nameLower, 'drink') || str_contains($nameLower, 'coffee') || str_contains($nameLower, 'juice')) { $icon = '🍹'; }
                                elseif (str_contains($nameLower, 'dessert') || str_contains($nameLower, 'cake') || str_contains($nameLower, 'ice cream')) { $icon = '🍰'; }
                                elseif (str_contains($nameLower, 'rice') || str_contains($nameLower, 'asian') || str_contains($nameLower, 'bento')) { $icon = '🍱'; }
                                elseif (str_contains($nameLower, 'chicken') || str_contains($nameLower, 'bbq') || str_contains($nameLower, 'meat')) { $icon = '🍗'; }
                                elseif (str_contains($nameLower, 'salad') || str_contains($nameLower, 'veggie')) { $icon = '🥗'; }
                                elseif (str_contains($nameLower, 'seafood') || str_contains($nameLower, 'fish')) { $icon = '🦐'; }
                            @endphp

                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <!-- ID -->
                                <td class="px-4 py-4 font-mono text-slate-400">
                                    #{{ $category->id }}
                                </td>

                                <!-- Name -->
                                <td class="px-4 py-4 font-bold text-slate-900 dark:text-white">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-950/50 border border-orange-100 dark:border-orange-900 text-orange-600 dark:text-orange-400 flex items-center justify-center text-sm shadow-xs shrink-0">
                                            {{ $icon }}
                                        </div>
                                        <span class="text-sm font-extrabold">{{ $category->name }}</span>
                                    </div>
                                </td>

                                <!-- Shop -->
                                <td class="px-4 py-4">
                                    @if($category->shop)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-300 border border-orange-200 dark:border-orange-800">
                                            🏪 {{ $category->shop->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>

                                <!-- Slug -->
                                <td class="px-4 py-4 font-mono text-[11px]">
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold inline-block">
                                        {{ $category->slug }}
                                    </span>
                                </td>

                                <!-- Items Count -->
                                <td class="px-4 py-4">
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 rounded-full border border-amber-200 dark:border-amber-800 text-[11px] font-bold inline-flex items-center gap-1.5">
                                        <span>{{ $category->menu_items_count }}</span>
                                        <span class="text-amber-600/80 dark:text-amber-400/80 font-normal">{{ __('Food Items') }}</span>
                                    </span>
                                </td>

                                <!-- Created Date -->
                                <td class="px-4 py-4 text-slate-500 dark:text-slate-400 text-[11px]">
                                    {{ $category->created_at ? $category->created_at->format('M d, Y') : 'N/A' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Trigger -->
                                        <button @click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ route('admin.categories.update', $category) }}')" 
                                                class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-700 transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                            <span>✏️</span>
                                            <span>{{ __('Edit') }}</span>
                                        </button>

                                        <!-- Delete Form -->
                                        <form method="POST" 
                                              action="{{ route('admin.categories.destroy', $category) }}" 
                                              onsubmit="return confirmDelete(this, '{{ addslashes($category->name) }}', 'category');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                            <button type="submit" class="px-3 py-1.5 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-lg transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                <span>🗑️</span>
                                                <span>{{ __('Delete') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-3">
                                        <div class="text-3xl">🍽️</div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ __('No Food Categories Found') }}</div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            @if($search)
                                                {{ __('No category matching') }} "<span class="text-orange-600 dark:text-orange-400">{{ $search }}</span>". {{ __('Try clearing your search keyword.') }}
                                            @else
                                                {{ __('Get started by creating your first food category for your restaurant menu.') }}
                                            @endif
                                        </p>
                                        @if($search)
                                            <a href="{{ route('admin.categories.index') }}" class="inline-block px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700">{{ __('Clear Search') }}</a>
                                        @else
                                            <button @click="createModalOpen = true" class="inline-block px-4 py-2 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/20">{{ __('Add First Category') }}</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination Footer -->
            @if($categories->hasPages())
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    {{ $categories->links() }}
                </div>
            @endif

        </div>

        <!-- ================= CREATE CATEGORY MODAL ================= -->
        <template x-teleport="body">
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
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 max-w-md w-full shadow-2xl space-y-4">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg font-bold border border-orange-100 dark:border-orange-900">
                                ➕
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('Create New Category') }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">{{ __('Add a new food category to organize menu items') }}</p>
                            </div>
                        </div>
                        <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 text-base font-bold cursor-pointer">✕</button>
                    </div>

                    <!-- Modal Form -->
                    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4 text-xs">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">

                        <div>
                            <label for="create_category_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">
                                {{ __('Category Name') }} <span class="text-orange-500">*</span>
                            </label>
                            <input type="text" 
                                   id="create_category_name" 
                                   name="name" 
                                   x-model="createCategoryName" 
                                   required 
                                   autofocus
                                   placeholder="e.g. Italian Pasta, Tacos, Refreshing Drinks" 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all placeholder-slate-400">
                        </div>

                        <!-- Slug Preview Indicator -->
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-0.5">
                            <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">{{ __('Auto-Generated URL Slug') }}</span>
                            <div class="font-mono text-xs text-orange-600 dark:text-orange-400 font-bold truncate">
                                <span x-text="createCategoryName ? slugify(createCategoryName) : 'category-slug-preview'"></span>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="createModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-5 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                                {{ __('Create Category') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ================= EDIT CATEGORY MODAL ================= -->
        <template x-teleport="body">
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
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 max-w-md w-full shadow-2xl space-y-4">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg font-bold border border-orange-100 dark:border-orange-900">
                                ✏️
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('Edit Category') }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">{{ __('Update existing food category details') }}</p>
                            </div>
                        </div>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 text-base font-bold cursor-pointer">✕</button>
                    </div>

                    <!-- Modal Form -->
                    <form method="POST" :action="editCategoryUrl" class="space-y-4 text-xs">
                        @csrf
                        @method('PUT')

                        <!-- Hidden inputs to retain state on validation failure -->
                        <input type="hidden" name="edit_category_id" :value="editCategoryId">
                        <input type="hidden" name="edit_category_url" :value="editCategoryUrl">
                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">

                        <div>
                            <label for="edit_category_name_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">
                                {{ __('Category Name') }} <span class="text-orange-500">*</span>
                            </label>
                            <input type="text" 
                                   id="edit_category_name_input" 
                                   name="name" 
                                   x-model="editCategoryName" 
                                   required 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all">
                        </div>

                        <!-- Slug Preview Indicator -->
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-0.5">
                            <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">{{ __('Updated URL Slug') }}</span>
                            <div class="font-mono text-xs text-orange-600 dark:text-orange-400 font-bold truncate">
                                <span x-text="editCategoryName ? slugify(editCategoryName) : 'category-slug-preview'"></span>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-5 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                                {{ __('Update Category') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>

</x-admin-layout>
