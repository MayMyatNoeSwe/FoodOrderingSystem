<x-admin-layout
    title="Shops Management"
    active="shops"
    heading="🏪 Shops"
    subheading="Manage all vendor shops on the platform"
>
    <x-slot:actions>
        <button onclick="document.getElementById('createShopModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Shop
        </button>
    </x-slot:actions>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $activeCount   = $shops->where('status', 'active')->count();
            $inactiveCount = $shops->where('status', 'inactive')->count();
            $pendingCount  = $shops->where('status', 'pending')->count();
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $shops->count() }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Total Shops</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-2xl font-black text-emerald-600">{{ $activeCount }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Active</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-2xl font-black text-slate-400">{{ $inactiveCount }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Inactive</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-2xl font-black text-amber-500">{{ $pendingCount }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Pending</div>
        </div>
    </div>

    {{-- Search, Filter, Sort Toolbar --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.shops.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search shops by name, address, phone, email..."
                       class="w-full pl-9 pr-8 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(!empty($search))
                    <a href="{{ route('admin.shops.index', request()->except('search')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold">✕</a>
                @endif
            </div>

            {{-- Status Filter --}}
            <div class="w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-orange-500 outline-none transition-all cursor-pointer">
                    <option value="all">Status: All</option>
                    <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>● Active</option>
                    <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>● Inactive</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>● Pending</option>
                </select>
            </div>

            {{-- Sort By --}}
            <div class="w-full sm:w-auto">
                <select name="sort_by" onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-orange-500 outline-none transition-all cursor-pointer">
                    <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                    <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                    <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="items_count" {{ ($sortBy ?? '') === 'items_count' ? 'selected' : '' }}>Most Items</option>
                    <option value="orders_count" {{ ($sortBy ?? '') === 'orders_count' ? 'selected' : '' }}>Most Orders</option>
                </select>
            </div>

            @if(!empty($search) || (!empty($status) && $status !== 'all') || (!empty($sortBy) && $sortBy !== 'latest'))
                <a href="{{ route('admin.shops.index') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Shops Grid --}}
    @if($shops->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center shadow-sm">
            <div class="text-6xl mb-4">🏪</div>
            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">No shops yet</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Create the first shop to get started.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($shops as $shop)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden group hover:shadow-md transition-all duration-200">
                {{-- Cover --}}
                <div class="h-28 bg-gradient-to-br from-orange-400 to-amber-500 relative overflow-hidden">
                    @if($shop->cover_image)
                        <img src="{{ asset($shop->cover_image) }}" alt="Cover" class="w-full h-full object-cover opacity-80">
                    @endif
                    {{-- Status Badge --}}
                    <div class="absolute top-3 right-3">
                        @if($shop->status === 'active')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow">● Active</span>
                        @elseif($shop->status === 'inactive')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-500 text-white shadow">● Inactive</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow">● Pending</span>
                        @endif
                    </div>
                    {{-- Logo --}}
                    <div class="absolute -bottom-5 left-4 w-12 h-12 rounded-xl border-2 border-white dark:border-slate-900 bg-white dark:bg-slate-800 shadow overflow-hidden flex items-center justify-center">
                        @if($shop->logo)
                            <img src="{{ asset($shop->logo) }}" alt="{{ $shop->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-xl">🏪</span>
                        @endif
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-4 pt-8">
                    <h3 class="font-black text-slate-900 dark:text-white text-base leading-tight">{{ $shop->name }}</h3>
                    @if($shop->description)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ $shop->description }}</p>
                    @endif
                    <div class="flex items-center gap-1 mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate">{{ $shop->address }}</span>
                    </div>

                    {{-- Owner --}}
                    <div class="mt-2 flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-gradient-to-tr from-orange-400 to-amber-400 flex items-center justify-center text-white text-[9px] font-black">
                            {{ strtoupper(substr($shop->owner?->name ?? '?', 0, 1)) }}
                        </div>
                        <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">{{ $shop->owner?->name ?? 'No owner assigned' }}</span>
                    </div>

                    {{-- Counts --}}
                    <div class="mt-3 flex gap-3 text-xs text-slate-500 dark:text-slate-400">
                        <span>🍽️ {{ $shop->menu_items_count ?? 0 }} items</span>
                        <span>📦 {{ $shop->orders_count ?? 0 }} orders</span>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex items-center gap-2 flex-wrap">
                        {{-- Toggle Status --}}
                        <form method="POST" action="{{ route('admin.shops.toggle-status', $shop) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs font-bold rounded-lg border transition-colors cursor-pointer
                                    {{ $shop->status === 'active'
                                        ? 'border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                                        : 'border-emerald-300 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' }}">
                                {{ $shop->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        {{-- Edit --}}
                        <button onclick="openEditShopModal({{ $shop->id }}, {{ json_encode($shop->only(['name','description','address','phone','email','status','owner_id'])) }})"
                                class="px-3 py-1.5 text-xs font-bold text-orange-600 border border-orange-300 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-950/30 transition-colors cursor-pointer">
                            Edit
                        </button>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.shops.destroy', $shop) }}" onsubmit="return confirm('Delete shop \'{{ addslashes($shop->name) }}\' and all its data?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 text-xs font-bold text-red-600 border border-red-300 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    @if($shops->hasPages())
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                Showing {{ $shops->firstItem() ?? 0 }} to {{ $shops->lastItem() ?? 0 }} of {{ $shops->total() }} shops
            </span>
            <div>
                {{ $shops->links() }}
            </div>
        </div>
    @endif


    {{-- ===== CREATE SHOP MODAL ===== --}}
    <div id="createShopModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">🏪 Add New Shop</h2>
                <button onclick="document.getElementById('createShopModal').classList.add('hidden'); document.getElementById('createShopModal').classList.remove('flex');" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.shops.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                @include('admin.shops._form', ['shop' => null, 'availableOwners' => $availableOwners])
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-sm transition-all cursor-pointer">Create Shop</button>
                    <button type="button" onclick="document.getElementById('createShopModal').classList.add('hidden'); document.getElementById('createShopModal').classList.remove('flex');" class="px-6 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== EDIT SHOP MODAL ===== --}}
    <div id="editShopModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">✏️ Edit Shop</h2>
                <button onclick="document.getElementById('editShopModal').classList.add('hidden'); document.getElementById('editShopModal').classList.remove('flex');" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">✕</button>
            </div>
            <form id="editShopForm" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf @method('PUT')
                @include('admin.shops._form', ['shop' => 'edit', 'availableOwners' => $availableOwners])
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-sm transition-all cursor-pointer">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editShopModal').classList.add('hidden'); document.getElementById('editShopModal').classList.remove('flex');" class="px-6 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditShopModal(shopId, shopData) {
        const form = document.getElementById('editShopForm');
        form.action = `/admin/shops/${shopId}`;

        // Populate fields
        ['name','description','address','phone','email'].forEach(field => {
            const el = form.querySelector(`[name="${field}"]`);
            if (el) el.value = shopData[field] ?? '';
        });
        const statusEl = form.querySelector('[name="status"]');
        if (statusEl) statusEl.value = shopData.status ?? 'active';
        const ownerEl = form.querySelector('[name="owner_id"]');
        if (ownerEl) ownerEl.value = shopData.owner_id ?? '';

        const modal = document.getElementById('editShopModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    </script>
</x-admin-layout>
