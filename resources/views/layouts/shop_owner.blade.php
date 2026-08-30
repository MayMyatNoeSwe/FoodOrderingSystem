<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shop Owner Portal — {{ config('app.name') }}</title>

    <script>
        if (localStorage.getItem('foodorder_theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|dm-sans:400,500,600,700,800&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen">

<div class="flex min-h-screen" x-data="{
    darkMode: localStorage.getItem('foodorder_theme') === 'dark',
    toggleTheme() {
        this.darkMode = !this.darkMode;
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('foodorder_theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('foodorder_theme', 'light');
        }
    }
}">

    {{-- Sidebar --}}
    <aside class="w-60 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0 sticky top-0 h-screen shadow-sm hidden md:flex">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-amber-400 flex items-center justify-center text-white text-lg font-black shadow">
                    🏪
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-black text-slate-900 dark:text-white truncate">{{ Auth::user()->ownedShop?->name ?? 'My Shop' }}</div>
                    <div class="text-[10px] text-orange-500 font-semibold">Shop Owner</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-1">
            @php
                $currentRoute = request()->routeIs('shop_owner.dashboard') ? 'dashboard'
                    : (request()->routeIs('shop_owner.menu-items.*') ? 'menu-items' : '');
            @endphp

            @foreach([
                ['key'=>'dashboard',   'label'=>'Dashboard',   'route'=>'shop_owner.dashboard',          'icon'=>'📊'],
                ['key'=>'menu-items',  'label'=>'Menu Items',  'route'=>'shop_owner.menu-items.index',   'icon'=>'🍽️'],
            ] as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150
                          {{ $currentRoute === $item['key']
                              ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30'
                              : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="text-base">{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                🌐 <span>View Storefront</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition-colors text-left cursor-pointer">
                    🚪 <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Topbar --}}
        <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-6 py-3.5 flex items-center justify-between sticky top-0 z-20 shadow-sm">
            <h1 class="text-lg font-black text-slate-900 dark:text-white">
                @yield('heading', 'Shop Owner Portal')
            </h1>
            <div class="flex items-center gap-3">
                <button @click="toggleTheme()"
                        class="w-9 h-9 flex items-center justify-center bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 text-sm transition-colors cursor-pointer">
                    <span x-show="!darkMode">🌙</span>
                    <span x-show="darkMode" style="display:none">☀️</span>
                </button>
                <div class="text-xs text-right hidden sm:block">
                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</div>
                    <div class="text-orange-500 font-semibold">Shop Owner</div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 space-y-6">
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3500, timerProgressBar: true });
                    });
                </script>
            @endif
            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: @json(session('error')), showConfirmButton: false, timer: 4500, timerProgressBar: true });
                    });
                </script>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
