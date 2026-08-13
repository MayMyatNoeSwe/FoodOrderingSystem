<nav x-data="{
    open: false,
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
    },
    getCartCount() {
        try {
            const stored = localStorage.getItem('foodorder_cart');
            const items = stored ? JSON.parse(stored) : [];
            return Array.isArray(items) ? items.reduce(function(s, i) { return s + (i.qty || 0); }, 0) : 0;
        } catch(e) { return 0; }
    },
    cartCount: 0,
    init() {
        this.cartCount = this.getCartCount();
    }
}" 
@cart-updated.window="cartCount = getCartCount()"
class="sticky top-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <span class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Food<span class="text-orange-500">Order</span></span>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold">
                <a href="{{ route('home') }}" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors {{ request()->routeIs('home') ? 'text-orange-500 font-bold' : '' }}">Home</a>
                <a href="{{ route('home') }}#categories" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors">Categories</a>
                <a href="{{ route('home') }}#menu" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors">Popular Menu</a>
                <a href="{{ route('home') }}#features" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors">Why Us</a>
            </nav>

            <!-- Header Actions -->
            <div class="flex items-center space-x-3">
                <!-- Theme Toggle Button -->
                <button @click="toggleTheme()"
                        title="Toggle Theme"
                        class="p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all duration-200 cursor-pointer inline-flex items-center justify-center border border-slate-200/60 dark:border-slate-700/60">
                    <span x-show="!darkMode" class="text-base">🌙</span>
                    <span x-show="darkMode" class="text-base" style="display:none;">☀️</span>
                </button>

                <!-- Shopping Cart Button -->
                <a href="{{ route('cart') }}" class="relative p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all duration-200 cursor-pointer inline-flex items-center justify-center border border-slate-200/60 dark:border-slate-700/60">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <!-- Cart Item Count Badge -->
                    <span
                        x-show="cartCount > 0"
                        x-text="cartCount"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-50"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="absolute -top-1.5 -right-1.5 bg-orange-500 text-white text-xs font-black min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center shadow-md">
                    </span>
                </a>

                @if (Route::has('login'))
                    @auth
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:flex px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-amber-500/25 transition-all items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span>Admin Portal</span>
                            </a>
                        @endif

                        <!-- Logged-In User Profile Dropdown -->
                        <div x-data="{ userMenuOpen: false }" class="relative">
                            <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs sm:text-sm rounded-xl border border-slate-200/60 dark:border-slate-700/60 flex items-center gap-2 transition-all cursor-pointer">
                                <div class="w-7 h-7 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-black shadow-sm">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="max-w-[120px] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu Box -->
                            <div x-show="userMenuOpen" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50"
                                 style="display: none;">
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-[11px] text-slate-400">Signed in as</p>
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->email }}</p>
                                </div>
                                
                                <a href="{{ route('user.orders.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors">
                                    📦 My Orders (Order များ)
                                </a>

                                <a href="{{ route('home') }}#menu" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors">
                                    🍕 Explore Menu
                                </a>

                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors">
                                    ⚙️ Profile Settings
                                </a>

                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-800 transition-colors sm:hidden">
                                        🛡️ Admin Portal
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')" class="border-t border-slate-100 dark:border-slate-800 mt-1 pt-1">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-slate-800 transition-colors cursor-pointer flex items-center justify-between">
                                        <span>Log Out</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest View: Single Clean Log in Button -->
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-orange-500/25 transition-all">
                            Log in
                        </a>
                    @endauth
                @endif

                <!-- Mobile Hamburger Button -->
                <button @click="open = !open" class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div x-show="open" class="md:hidden bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-4 pt-2 pb-4 space-y-2" style="display: none;">
        <a href="{{ route('home') }}" class="block px-3 py-2 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-500">Home</a>
        <a href="{{ route('home') }}#categories" class="block px-3 py-2 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-500">Categories</a>
        <a href="{{ route('home') }}#menu" class="block px-3 py-2 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-500">Popular Menu</a>
        <a href="{{ route('home') }}#features" class="block px-3 py-2 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-500">Why Us</a>
    </div>
</nav>
