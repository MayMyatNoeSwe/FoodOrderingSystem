@props(['active' => 'dashboard'])

@php
    $navItems = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'badge' => null,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>',
        ],
        [
            'key' => 'categories',
            'label' => 'Categories',
            'route' => 'admin.categories.index',
            'badge' => $navCategoryCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>',
        ],
        [
            'key' => 'menuItems',
            'label' => 'Items',
            'route' => 'admin.menuItems.index',
            'badge' => $navMenuItemCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>',
        ],
        [
            'key' => 'inventory',
            'label' => 'Inventory',
            'route' => 'admin.inventory.index',
            'badge' => $navInventoryCount ?? ($navMenuItemCount ?? 0),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
        ],
        [
            'key' => 'orders',
            'label' => 'Orders',
            'route' => 'admin.orders.index',
            'badge' => $navOrderCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>',
        ],
        [
            'key' => 'orderItems',
            'label' => 'Order Items',
            'route' => 'admin.orderItems.index',
            'badge' => $navOrderItemCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>',
        ],
        [
            'key' => 'riders',
            'label' => 'Riders',
            'route' => 'admin.riders.index',
            'badge' => $navRiderCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
        ],
        [
            'key' => 'customers',
            'label' => 'Customers',
            'route' => 'admin.customers.index',
            'badge' => $navCustomerCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
        ],
        [
            'key' => 'complaints',
            'label' => 'Complaints & Help',
            'route' => 'admin.complaints.index',
            'badge' => $navPendingComplaintCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
        ],
    ];
@endphp

<!-- ================= DESKTOP SIDEBAR ================= -->
<aside :class="sidebarFolded ? 'w-20 p-3' : 'w-68 xl:w-72 p-5'"
       class="bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 hidden md:flex flex-col justify-between shrink-0 sticky top-0 h-screen shadow-xs sidebar-transition z-20 select-none">
    
    <div class="space-y-5">
        <!-- Admin Brand & Fold Button -->
        <div class="flex items-center justify-between gap-2 px-1 pt-1">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group overflow-hidden min-w-0" :class="sidebarFolded ? 'justify-center w-full' : ''">
                <div class="w-10 h-10 rounded-xl bg-[#D70F64] flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-[#D70F64]/30 group-hover:scale-105 transition-transform duration-200 ease-out shrink-0">
                    🐼
                </div>
                <div x-show="!sidebarFolded" x-cloak class="truncate transition-opacity duration-200">
                    <span class="text-xl font-black text-[#D70F64] tracking-tight lowercase">food<span class="text-slate-900 dark:text-white">panda</span></span>
                    <span class="block text-[10px] text-pink-600 font-bold uppercase tracking-widest truncate">{{ __('Admin Portal') }}</span>
                </div>
            </a>

            <!-- Sidebar Fold/Unfold Toggle Button in Header (visible when expanded) -->
            <button x-show="!sidebarFolded"
                    @click="toggleSidebar()"
                    type="button"
                    title="{{ __('Fold sidebar') }}"
                    class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors duration-150 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1 text-xs sm:text-[13px]">
            @foreach($navItems as $item)
                @php $isActive = ($active === $item['key']); @endphp
                <a href="{{ route($item['route']) }}"
                   title="{{ __($item['label']) }}"
                   :class="sidebarFolded ? 'justify-center px-0 py-2.5' : 'gap-3 px-3.5 py-2.5'"
                   class="relative flex items-center rounded-xl transition-colors duration-150 {{ $isActive ? 'bg-orange-500 text-white font-bold shadow-md shadow-orange-500/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-medium' }}">
                    
                    <!-- Icon -->
                    <div class="shrink-0">{!! $item['icon'] !!}</div>

                    <!-- Label (Expanded Mode) -->
                    <span x-show="!sidebarFolded" x-cloak class="flex-1 truncate whitespace-nowrap leading-relaxed font-semibold">
                        {{ __($item['label']) }}
                    </span>

                    <!-- Badge (Expanded Mode) -->
                    @if($item['badge'] !== null)
                        <span x-show="!sidebarFolded" x-cloak class="shrink-0 ms-auto {{ $isActive ? 'bg-white/25 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-[11px] font-bold px-2 py-0.5 rounded-full">
                            {{ $item['badge'] }}
                        </span>
                    @endif

                    <!-- Badge Indicator Dot (Folded Mini Mode) -->
                    @if($item['badge'] !== null && $item['badge'] > 0)
                        <span x-show="sidebarFolded" x-cloak class="absolute top-1.5 right-2 w-2 h-2 rounded-full {{ $isActive ? 'bg-white' : 'bg-orange-500' }}"></span>
                    @endif

                    <!-- Floating Tooltip (Folded Mini Mode on Hover) -->
                    <div x-show="sidebarFolded" x-cloak
                         class="absolute left-full ml-3 px-3 py-1.5 bg-slate-900 dark:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xl whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-150 z-50 flex items-center gap-2 border border-slate-700">
                        <span>{{ __($item['label']) }}</span>
                        @if($item['badge'] !== null)
                            <span class="bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </nav>
    </div>

    <!-- Bottom Controls & Profile Quick Footer -->
    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
        
        <!-- Bottom Sidebar Toggle Button (expand/collapse button) -->
        <button @click="toggleSidebar()"
                type="button"
                :title="sidebarFolded ? '{{ __('Expand Sidebar') }}' : '{{ __('Collapse Sidebar') }}'"
                :class="sidebarFolded ? 'justify-center w-full px-0 py-2' : 'justify-start w-full px-3 py-2'"
                class="flex items-center gap-3 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors duration-150 cursor-pointer">
            <svg class="w-5 h-5 transition-transform duration-200" :class="sidebarFolded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            <span x-show="!sidebarFolded" x-cloak class="truncate">{{ __('Collapse Sidebar') }}</span>
        </button>

        <!-- Admin Profile -->
        <div class="flex items-center justify-between" :class="sidebarFolded ? 'flex-col gap-2' : ''">
            <div class="flex items-center gap-3 overflow-hidden" :class="sidebarFolded ? 'justify-center w-full' : ''" :title="'{{ Auth::user()->name ?? 'Admin' }}'">
                <div class="w-9 h-9 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400 font-black text-sm shrink-0">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div x-show="!sidebarFolded" x-cloak class="text-xs truncate">
                    <div class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ Auth::user()->name ?? 'Admin' }}</div>
                    <div class="text-amber-600 dark:text-amber-400 font-semibold">{{ __('Admin Portal') }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                @csrf
                <button type="submit" title="{{ __('Log Out') }}" class="p-2 text-slate-400 hover:text-red-500 transition-colors duration-150 cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- ================= MOBILE DRAWER NAVIGATION ================= -->
<div x-show="mobileMenuOpen"
     x-cloak
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden"
     @click="mobileMenuOpen = false"></div>

<aside x-show="mobileMenuOpen"
       x-cloak
       class="fixed inset-y-0 left-0 w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 p-5 flex flex-col justify-between z-50 md:hidden shadow-2xl">

    <div class="space-y-6">
        <!-- Header & Close -->
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#D70F64] flex items-center justify-center text-white text-xl font-black shadow-lg shrink-0">
                    🐼
                </div>
                <div class="truncate">
                    <span class="text-base font-black text-[#D70F64] lowercase">food<span class="text-slate-900 dark:text-white">panda</span></span>
                    <span class="block text-[9px] text-pink-600 font-bold uppercase tracking-widest truncate">{{ __('Admin Portal') }}</span>
                </div>
            </a>
            <button @click="mobileMenuOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 cursor-pointer transition-colors duration-150">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1 text-xs sm:text-[13px]">
            @foreach($navItems as $item)
                @php $isActive = ($active === $item['key']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors duration-150 {{ $isActive ? 'bg-orange-500 text-white font-bold shadow-md shadow-orange-500/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-medium' }}">
                    <div class="shrink-0">{!! $item['icon'] !!}</div>
                    <span class="flex-1 truncate whitespace-nowrap leading-relaxed font-semibold">{{ __($item['label']) }}</span>
                    @if($item['badge'] !== null)
                        <span class="shrink-0 ms-auto {{ $isActive ? 'bg-white/25 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-[11px] font-bold px-2 py-0.5 rounded-full">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400 font-black text-sm">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="text-xs">
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div class="text-amber-600 dark:text-amber-400 font-semibold">{{ __('Admin Portal') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
            @csrf
            <button type="submit" title="{{ __('Log Out') }}" class="p-2 text-slate-400 hover:text-red-500 transition-colors duration-150 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </button>
        </form>
    </div>
</aside>
