@extends('layouts.shop_owner')

@section('heading', '📂 ' . $shop->name . ' — Categories')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-black text-slate-900 dark:text-white">📂 {{ __('Shop Categories') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $categories->total() }} {{ __('registered categor' . ($categories->total() === 1 ? 'y' : 'ies')) }}</p>
        </div>
        <button onclick="document.getElementById('createCategoryModal').classList.remove('hidden'); document.getElementById('createCategoryModal').classList.add('flex');"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('Add Category') }}
        </button>
    </div>

    <!-- Search & Sort Controls Toolbar -->
    <form method="GET" action="{{ route('shop_owner.categories.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 bg-white dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex-wrap">
        
        <!-- Search Box -->
        <div class="relative flex-1 min-w-[180px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" 
                   name="search"
                   value="{{ $search ?? '' }}" 
                   placeholder="{{ __('Search category name...') }}"
                   class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
            @if(!empty($search))
                <a href="{{ route('shop_owner.categories.index', request()->except('search')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</a>
            @endif
        </div>

        <!-- Sort By Selector -->
        <div class="w-full sm:w-48 shrink-0">
            <select name="sort_by" onchange="this.form.submit()"
                    class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                <option value="items_count_desc" {{ ($sortBy ?? '') === 'items_count_desc' ? 'selected' : '' }}>Items Count (High to Low)</option>
                <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Newest First</option>
                <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
            </select>
        </div>

        @if(!empty($search) || ($sortBy && $sortBy !== 'name_asc'))
            <a href="{{ route('shop_owner.categories.index') }}" class="px-2.5 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all whitespace-nowrap">
                Reset
            </a>
        @endif
    </form>

    @if($categories->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center shadow-sm">
            <div class="text-6xl mb-4">📂</div>
            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">No categories found</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Try adjusting your search query.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $category)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 flex items-center justify-between gap-3 group hover:shadow-md hover:border-orange-300 dark:hover:border-orange-800 transition-all">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-950/40 flex items-center justify-center text-xl shrink-0">📂</div>
                    <div class="min-w-0">
                        <div class="font-bold text-slate-900 dark:text-white truncate">{{ $category->name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $category->menu_items_count }} item{{ $category->menu_items_count !== 1 ? 's' : '' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button onclick="openEditCategory({{ $category->id }}, '{{ addslashes($category->name) }}')"
                            class="p-1.5 text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-950/30 rounded-lg transition-colors cursor-pointer text-sm" title="Edit">✏️</button>
                    <form method="POST" action="{{ route('shop_owner.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors cursor-pointer text-sm" title="Delete">🗑️</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-2xl">
                {{ $categories->links() }}
            </div>
        @endif
    @endif

    {{-- Create Modal --}}
    <div id="createCategoryModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-200 dark:border-slate-800">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-black text-slate-900 dark:text-white">📂 Add Category</h2>
                <button onclick="document.getElementById('createCategoryModal').classList.add('hidden'); document.getElementById('createCategoryModal').classList.remove('flex');" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">✕</button>
            </div>
            <form method="POST" action="{{ route('shop_owner.categories.store') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Burgers, Salads, Drinks..."
                           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-sm transition-all cursor-pointer">Create</button>
                    <button type="button" onclick="document.getElementById('createCategoryModal').classList.add('hidden'); document.getElementById('createCategoryModal').classList.remove('flex');" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editCategoryModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-200 dark:border-slate-800">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-black text-slate-900 dark:text-white">✏️ Edit Category</h2>
                <button onclick="document.getElementById('editCategoryModal').classList.add('hidden'); document.getElementById('editCategoryModal').classList.remove('flex');" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">✕</button>
            </div>
            <form id="editCategoryForm" method="POST" action="" class="p-5 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editCategoryName" name="name" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500/30 focus:border-orange-400 outline-none transition-all">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-sm transition-all cursor-pointer">Save</button>
                    <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden'); document.getElementById('editCategoryModal').classList.remove('flex');" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditCategory(id, name) {
        document.getElementById('editCategoryForm').action = `/shop-owner/categories/${id}`;
        document.getElementById('editCategoryName').value = name;
        const modal = document.getElementById('editCategoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    </script>
</div>
@endsection
