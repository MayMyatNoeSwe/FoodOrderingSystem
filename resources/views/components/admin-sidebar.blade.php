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
            'label' => 'Menu Items',
            'route' => 'admin.menuItems.index',
            'badge' => $navMenuItemCount ?? 0,
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>',
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
    ];
@endphp

<!-- ================= DESKTOP SIDEBAR ================= -->
<aside class="w-64 bg-white border-r border-slate-200/80 hidden md:flex flex-col justify-between p-6 shrink-0 sticky top-0 h-screen shadow-sm">
    <div class="space-y-8">
        <!-- Admin Brand -->
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                🍕
            </div>
            <div>
                <span class="text-lg font-black text-slate-900 tracking-tight">Food<span class="text-orange-500">Order</span></span>
                <span class="block text-[10px] text-amber-600 font-bold uppercase tracking-widest">{{ __('Admin Portal') }}</span>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="space-y-1.5 text-sm">
            @foreach($navItems as $item)
                @php $isActive = ($active === $item['key']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium {{ $isActive ? 'bg-orange-500 text-white font-bold shadow-lg shadow-orange-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                    {!! $item['icon'] !!}
                    <span>{{ __($item['label']) }}</span>
                    @if($item['badge'] !== null)
                        <span class="ms-auto {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }} text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>


    </div>

    <!-- Admin Profile Quick Footer -->
    <div class="border-t border-slate-100 pt-4 flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-600 font-black text-sm shrink-0">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="text-xs truncate">
                <div class="font-bold text-slate-800 truncate">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div class="text-amber-600 font-semibold">{{ __('Admin Portal') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
            @csrf
            <button type="submit" title="{{ __('Log Out') }}" class="p-2 text-slate-400 hover:text-red-500 transition-colors cursor-pointer rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </button>
        </form>
    </div>
</aside>

<!-- ================= MOBILE DRAWER NAVIGATION ================= -->
<div x-show="mobileMenuOpen"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden"
     @click="mobileMenuOpen = false"></div>

<aside x-show="mobileMenuOpen"
       x-transition:enter="transition transform ease-out duration-200"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition transform ease-in duration-150"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       class="fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-200 p-6 flex flex-col justify-between z-50 md:hidden shadow-2xl">

    <div class="space-y-6">
        <!-- Header & Close -->
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black shadow-lg">
                    🍕
                </div>
                <div>
                    <span class="text-base font-black text-slate-900">Food<span class="text-orange-500">Order</span></span>
                    <span class="block text-[9px] text-amber-600 font-bold uppercase tracking-widest">{{ __('Admin Portal') }}</span>
                </div>
            </a>
            <button @click="mobileMenuOpen = false" class="text-slate-400 hover:text-slate-700 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1.5 text-sm">
            @foreach($navItems as $item)
                @php $isActive = ($active === $item['key']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium {{ $isActive ? 'bg-orange-500 text-white font-bold shadow-lg shadow-orange-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    {!! $item['icon'] !!}
                    <span>{{ __($item['label']) }}</span>
                    @if($item['badge'] !== null)
                        <span class="ms-auto {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }} text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>


    </div>

    <div class="border-t border-slate-100 pt-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-600 font-black text-sm">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="text-xs">
                <div class="font-bold text-slate-800">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div class="text-amber-600 font-semibold">{{ __('Admin Portal') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
            @csrf
            <button type="submit" title="{{ __('Log Out') }}" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </button>
        </form>
    </div>
</aside>
