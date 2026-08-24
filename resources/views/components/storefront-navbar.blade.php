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
        <div class="flex items-center justify-between h-[72px] gap-3">
            
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0">
                <div class="w-9 h-9 rounded-xl bg-[#D70F64] flex items-center justify-center text-white text-xl shadow-md shadow-pink-500/30 group-hover:scale-105 transition-transform duration-300">
                    🐼
                </div>
                <span class="text-xl font-black tracking-tight text-[#D70F64] lowercase">food<span class="text-slate-900 dark:text-white">panda</span></span>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden lg:flex items-center space-x-4 xl:space-x-6 text-sm font-semibold flex-1 justify-center">
                <a href="{{ route('home') }}" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors whitespace-nowrap {{ request()->routeIs('home') ? 'text-orange-500 font-bold' : '' }}">{{ __('Home') }}</a>
                <a href="{{ route('home') }}#categories" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors whitespace-nowrap">{{ __('Categories') }}</a>
                <a href="{{ route('home') }}#menu" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors whitespace-nowrap">{{ __('Popular Menu') }}</a>
                <a href="{{ route('home') }}#features" class="text-slate-600 dark:text-slate-300 hover:text-orange-500 transition-colors whitespace-nowrap">{{ __('Why Us') }}</a>
            </nav>

            <!-- Header Actions -->
            <div class="flex items-center space-x-1.5 sm:space-x-2">
                <!-- Language Switcher (compact pill) -->
                <x-language-switcher variant="compact" />

                <!-- Theme Toggle Button -->
                <button @click="toggleTheme()"
                        title="{{ __('Toggle Theme') }}"
                        class="p-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg transition-all duration-200 cursor-pointer inline-flex items-center justify-center border border-slate-200/60 dark:border-slate-700/60">
                    <span x-show="!darkMode" class="text-sm">🌙</span>
                    <span x-show="darkMode" class="text-sm" style="display:none;">☀️</span>
                </button>

                <!-- Shopping Cart Button -->
                <a href="{{ route('cart') }}" class="relative p-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg transition-all duration-200 cursor-pointer inline-flex items-center justify-center border border-slate-200/60 dark:border-slate-700/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <!-- Cart Item Count Badge -->
                    <span
                        x-show="cartCount > 0"
                        x-text="cartCount"
                        style="display: none;"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-50"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] font-black min-w-[16px] h-4 px-0.5 rounded-full flex items-center justify-center shadow-md">
                    </span>
                </a>

                @if (Route::has('login'))
                    @auth
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:flex px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg shadow shadow-amber-500/25 transition-all items-center gap-1.5 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span>{{ __('Admin Portal') }}</span>
                            </a>
                        @endif

                        <!-- Logged-In User Profile Dropdown -->
                        <div x-data="{ userMenuOpen: false }" class="relative">
                            <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-lg border border-slate-200/60 dark:border-slate-700/60 flex items-center gap-1.5 transition-all cursor-pointer">
                                <div class="w-5 h-5 rounded-full bg-orange-500 text-white flex items-center justify-center text-[10px] font-black shadow-sm shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="max-w-[80px] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <p class="text-[11px] text-slate-400">{{ __('Signed in as') }}</p>
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->email }}</p>
                                </div>
                                
                                <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors">
                                    📦 {{ __('My Orders') }}
                                </a>

                                <a href="{{ route('home') }}#menu" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors">
                                    🍕 {{ __('Explore Menu') }}
                                </a>

                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors">
                                    ⚙️ {{ __('Profile Settings') }}
                                </a>

                                <a href="{{ route('customer.help') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors">
                                    🆘 {{ __('Help & Complaints') }}
                                </a>

                                @if(Auth::user()->isRider())
                                    <a href="{{ route('rider.dashboard') }}" class="block px-4 py-2 text-xs font-semibold text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-slate-800 transition-colors">
                                        🛵 {{ __('Rider Delivery Portal') }}
                                    </a>
                                @endif

                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-800 transition-colors sm:hidden">
                                        🛡️ {{ __('Admin Portal') }}
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')" class="border-t border-slate-100 dark:border-slate-800 mt-1 pt-1">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-slate-800 transition-colors cursor-pointer flex items-center justify-between">
                                        <span>{{ __('Log Out') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest View: Single Clean Log in Button -->
                        <a href="{{ route('login') }}" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg shadow shadow-orange-500/25 transition-all whitespace-nowrap">
                            {{ __('Log in') }}
                        </a>
                    @endauth
                @endif

                <!-- Animated Mobile Hamburger Button -->
                <button @click="open = !open" 
                        type="button"
                        aria-label="Toggle navigation menu"
                        class="md:hidden relative w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-slate-700 flex items-center justify-center border border-slate-200/60 dark:border-slate-700/60 transition-all duration-200 cursor-pointer select-none">
                    <div class="w-4 h-3.5 flex flex-col justify-between items-center">
                        <span :class="open ? 'rotate-45 translate-y-[6px] bg-orange-500' : 'bg-current'" class="w-full h-0.5 rounded-full transition-all duration-300 transform origin-center"></span>
                        <span :class="open ? 'opacity-0 scale-x-0' : 'opacity-100 bg-current'" class="w-full h-0.5 rounded-full transition-all duration-200"></span>
                        <span :class="open ? '-rotate-45 -translate-y-[6px] bg-orange-500' : 'bg-current'" class="w-full h-0.5 rounded-full transition-all duration-300 transform origin-center"></span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu with Glassmorphism & Slide Down Animation -->
    <div x-show="open"
         x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-350 transform origin-top"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition cubic-bezier(0.4, 0, 0.2, 1) duration-200 transform origin-top"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-3 scale-95"
         @click.outside="open = false"
         class="md:hidden absolute top-[72px] left-0 right-0 z-50 w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl border-b border-slate-200/80 dark:border-slate-800/90 shadow-2xl px-4 pt-3 pb-5 space-y-1.5"
         style="display: none;">

        <a href="{{ route('home') }}" 
           @click="open = false"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-500 dark:hover:bg-orange-500/15 dark:hover:text-orange-400 transition-all duration-200 group {{ request()->routeIs('home') ? 'bg-orange-500/10 text-orange-500 dark:text-orange-400' : '' }}">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">🏠</span>
                <span>{{ __('Home') }}</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        <a href="{{ route('home') }}#categories" 
           @click="open = false"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-500 dark:hover:bg-orange-500/15 dark:hover:text-orange-400 transition-all duration-200 group">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">🍽️</span>
                <span>{{ __('Categories') }}</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        <a href="{{ route('home') }}#menu" 
           @click="open = false"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-500 dark:hover:bg-orange-500/15 dark:hover:text-orange-400 transition-all duration-200 group">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">🔥</span>
                <span>{{ __('Popular Menu') }}</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        <a href="{{ route('home') }}#features" 
           @click="open = false"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-500 dark:hover:bg-orange-500/15 dark:hover:text-orange-400 transition-all duration-200 group">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">💡</span>
                <span>{{ __('Why Us') }}</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        @auth
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-1">
                <a href="{{ route('customer.orders.index') }}" 
                   @click="open = false"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-500 dark:hover:bg-orange-500/15 dark:hover:text-orange-400 transition-all duration-200 group">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">📦</span>
                        <span>{{ __('My Orders') }}</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="{{ route('customer.help') }}" 
                   @click="open = false"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-500 dark:hover:bg-orange-500/15 dark:hover:text-orange-400 transition-all duration-200 group">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">🆘</span>
                        <span>{{ __('Help & Complaints') }}</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        @endauth

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ __('Language') }}</span>
            <x-language-switcher variant="compact" />
        </div>
    </div>
</nav>

@if(session('clear_cart'))
<script>
    localStorage.removeItem('foodorder_cart');
</script>
@endif
