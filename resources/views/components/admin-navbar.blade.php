@props([
    'heading' => 'Admin Portal',
    'subheading' => null,
    'badge' => null,
    'breadcrumbs' => null,
    'actions' => null,
])

<header class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md sticky top-0 z-30 border-b border-slate-200/80 dark:border-slate-800 px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4 shadow-xs transition-colors duration-200"
        x-data="{ profileDropdownOpen: false }">
    
    <!-- Left Section: Mobile Toggle & Page Headings / Breadcrumbs -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Menu Hamburger Button -->
        <button @click="mobileMenuOpen = true"
                type="button"
                aria-label="{{ __('Open navigation menu') }}"
                class="md:hidden p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div class="min-w-0">
            @if(isset($breadcrumbs) && $breadcrumbs)
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-0.5 truncate">
                    {!! $breadcrumbs !!}
                </div>
            @endif

            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                    {!! $heading !!}
                </h1>

                @if(isset($badge) && $badge)
                    <div class="shrink-0">
                        {!! $badge !!}
                    </div>
                @endif
            </div>

            @if(isset($subheading) && $subheading)
                <p class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block truncate mt-0.5">
                    {!! $subheading !!}
                </p>
            @endif
        </div>
    </div>

    <!-- Right Section: Action Buttons & Global Widgets -->
    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
        
        <!-- Page-specific Actions (e.g. + Add Item, + Add Category, Quick Restock, Sound Alarm) -->
        @if(isset($actions) && $actions)
            <div class="flex items-center gap-2">
                {!! $actions !!}
            </div>
        @endif

        <div class="h-6 w-px bg-slate-200 dark:bg-slate-800 hidden sm:block"></div>

        <!-- Global Language Switcher -->
        <x-language-switcher variant="compact" />

        <!-- Theme Toggle Button (Dark / Light) -->
        <button @click="toggleTheme()"
                type="button"
                title="{{ __('Toggle Dark/Light Mode') }}"
                class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg border border-slate-200/60 dark:border-slate-700/60 transition-all duration-200 cursor-pointer shadow-xs">
            <span x-show="!darkMode" class="text-sm">🌙</span>
            <span x-show="darkMode" class="text-sm" style="display: none;">☀️</span>
        </button>

        <!-- View Storefront Link -->
        <a href="{{ route('home') }}"
           target="_blank"
           title="{{ __('Open Customer Storefront in new tab') }}"
           class="px-2.5 sm:px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg border border-slate-200/60 dark:border-slate-700/60 transition-all flex items-center gap-1.5 shadow-xs">
            <span>🌐</span>
            <span class="hidden md:inline">{{ __('Storefront') }}</span>
            <svg class="w-3 h-3 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
        </a>

        <!-- Admin Profile & Quick Dropdown -->
        <div class="relative" @click.outside="profileDropdownOpen = false">
            <button @click="profileDropdownOpen = !profileDropdownOpen"
                    type="button"
                    class="flex items-center gap-2 p-1 sm:px-2 sm:py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all cursor-pointer">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber-500 to-orange-500 text-white font-black text-xs flex items-center justify-center shadow-xs">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="hidden lg:block text-left">
                    <div class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight truncate max-w-[100px]">
                        {{ Auth::user()->name ?? 'Admin' }}
                    </div>
                    <div class="text-[10px] text-orange-600 dark:text-orange-400 font-semibold leading-tight">
                        {{ __('Administrator') }}
                    </div>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 hidden lg:block transition-transform duration-200"
                     :class="profileDropdownOpen ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="profileDropdownOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-800 py-2 z-50 overflow-hidden">
                
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                    <div class="mt-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        🛡️ {{ __('Full Access Admin') }}
                    </div>
                </div>

                <div class="py-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ __('Account Profile') }}</span>
                    </a>

                    <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>{{ __('Live Storefront') }}</span>
                    </a>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-1">
                    <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left cursor-pointer">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>{{ __('Sign Out') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
