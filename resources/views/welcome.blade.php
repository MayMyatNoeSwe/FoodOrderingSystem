<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Food Ordering System') }} - Delicious Meals Delivered Fast</title>

    <!-- Theme Initialization (Prevents FOUC) -->
    <script>
        if (localStorage.getItem('foodorder_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Fonts: DM Sans & Cabinet Grotesk -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:300,400,500,600,700,800|cabinet-grotesk:500,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(1deg); }
        }
        @keyframes floatReverse {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(8px) rotate(-1deg); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.35; transform: scale(1); }
            50% { opacity: 0.65; transform: scale(1.08); }
        }
        .animate-float-slow {
            animation: floatSlow 5s ease-in-out infinite;
        }
        .animate-float-reverse {
            animation: floatReverse 6s ease-in-out infinite;
        }
        .animate-pulse-glow {
            animation: pulseGlow 5s ease-in-out infinite;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .dark .glass-card {
            background: rgba(15, 23, 42, 0.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .glass-badge {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .glass-badge {
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>

    <script>
        window.welcomeApp = function(items) {
            return {
                darkMode: localStorage.getItem('foodorder_theme') === 'dark',
                itemsList: items || [],
                searchQuery: '',
                activeCategory: 'all',
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
                matchesSearch(name, desc, cat) {
                    if (!this.searchQuery || !this.searchQuery.trim()) return true;
                    const q = this.searchQuery.toLowerCase().trim();
                    const n = (name || '').toLowerCase();
                    const d = (desc || '').toLowerCase();
                    const c = (cat || '').toLowerCase();
                    return n.includes(q) || d.includes(q) || c.includes(q);
                },
                filteredCount() {
                    return this.itemsList.filter(item => {
                        const searchActive = this.searchQuery && this.searchQuery.trim() !== '';
                        const catMatch = searchActive || this.activeCategory === 'all' || item.category_slug === this.activeCategory;
                        const searchMatch = this.matchesSearch(item.name, item.description, item.category_name);
                        return catMatch && searchMatch;
                    }).length;
                },
                clearSearch() {
                    this.searchQuery = '';
                },
                scrollToMenu() {
                    const menuEl = document.getElementById('menu');
                    if (menuEl) {
                        const navOffset = 80;
                        const targetY = menuEl.getBoundingClientRect().top + window.pageYOffset - navOffset;
                        window.scrollTo({ top: targetY, behavior: 'smooth' });
                    }
                },
                getCart() {
                    try {
                        const stored = localStorage.getItem('foodorder_cart');
                        if (stored && stored !== 'undefined') {
                            const parsed = JSON.parse(stored);
                            return Array.isArray(parsed) ? parsed : [];
                        }
                    } catch(e) {}
                    return [];
                },
                cartCount: 0,
                toastVisible: false,
                toastName: '',
                init() {
                    this.cartCount = this.getCart().reduce((s,i) => s + (i.qty || 0), 0);
                },
                addToCart(item) {
                    const cart = this.getCart();
                    const existing = cart.find(i => i.id === item.id);
                    const maxStock = (item.stock !== undefined && item.stock !== null) ? Number(item.stock) : 999;
                    if (maxStock <= 0) {
                        alert('Sorry, "' + item.name + '" is currently out of stock!');
                        return;
                    }
                    if (existing) {
                        if (existing.qty < maxStock) {
                            existing.qty++;
                            existing.stock = maxStock;
                        } else {
                            alert('Cannot add more. Available stock limit for "' + item.name + '" is ' + maxStock + '!');
                            return;
                        }
                    } else {
                        cart.push({ ...item, qty: 1, stock: maxStock });
                    }
                    localStorage.setItem('foodorder_cart', JSON.stringify(cart));
                    this.cartCount = cart.reduce((s,i) => s + (i.qty || 0), 0);
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    this.toastName = item.name;
                    this.toastVisible = true;
                    setTimeout(() => { this.toastVisible = false; }, 2500);
                }
            };
        };
    </script>
</head>
@php
    $itemsJson = $menuItems->map(function($i) {
        return [
            'id' => $i->id,
            'name' => $i->name,
            'description' => $i->description ?? '',
            'category_slug' => $i->category ? $i->category->slug : 'all',
            'category_name' => $i->category ? $i->category->name : 'Special',
        ];
    });
@endphp
<body x-data="welcomeApp(@js($itemsJson))" class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50/50 dark:bg-slate-950 selection:bg-orange-500 selection:text-white transition-colors duration-300 min-h-screen flex flex-col justify-between">

    <!-- ================= NAVBAR ================= -->
    <x-storefront-navbar />

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col">

        <!-- ================= HERO SECTION ================= -->
        <section id="hero" class="relative overflow-hidden pt-8 pb-16 lg:pt-14 lg:pb-24 transition-colors duration-300">
            <!-- Ambient Background Glow Accents -->
            <div class="pointer-events-none absolute -top-28 -left-28 w-[500px] h-[500px] bg-orange-400/20 dark:bg-orange-500/10 rounded-full blur-3xl animate-pulse-glow"></div>
            <div class="pointer-events-none absolute top-1/3 -right-28 w-[450px] h-[450px] bg-amber-400/20 dark:bg-amber-500/10 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: 2s;"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 w-[400px] h-[400px] bg-rose-400/15 dark:bg-rose-500/10 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: 3.5s;"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    
                    <!-- Hero Left Content -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        
                        <!-- Promo Tag with Pulse Indicator -->
                        <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-gradient-to-r from-orange-500/10 via-amber-500/10 to-orange-500/10 dark:from-orange-500/20 dark:via-amber-500/20 dark:to-orange-500/20 border border-orange-500/30 text-orange-600 dark:text-orange-400 text-xs font-extrabold tracking-wide uppercase shadow-sm hover:scale-105 transition-transform duration-300">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-orange-500"></span>
                            </span>
                            <span>🔥 20% OFF • Code: FIRST20</span>
                        </div>

                        <!-- Main Headline with Gradient Display Text -->
                        <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                            {{ __('Hero Title Start') }}
                            <span class="block mt-1 bg-gradient-to-r from-orange-500 via-amber-500 to-rose-500 bg-clip-text text-transparent drop-shadow-sm">
                                {{ __('Hero Title End') }}
                            </span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-slate-600 dark:text-slate-300 text-base sm:text-lg max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed">
                            {{ __('Hero Description') }}
                        </p>

                        <!-- Search Bar Component (Floating Glass Console) -->
                        <div class="glass-card p-2 sm:p-2.5 rounded-2xl sm:rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-slate-950/60 border border-slate-200/80 dark:border-slate-800/90 flex flex-col sm:flex-row items-center gap-2 max-w-2xl mx-auto lg:mx-0 transition-all duration-300 focus-within:ring-4 focus-within:ring-orange-500/20 focus-within:border-orange-400">
                            <div class="flex items-center gap-3 w-full px-3.5 py-2">
                                <div class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 flex items-center justify-center shrink-0 text-orange-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text"
                                       x-model="searchQuery"
                                       @input="if(searchQuery.trim()) activeCategory = 'all'"
                                       @keydown.enter.prevent="scrollToMenu()"
                                       placeholder="{{ __('Search dishes...') }}"
                                       class="w-full text-sm sm:text-base text-slate-800 dark:text-slate-100 placeholder-slate-400 bg-transparent border-none focus:outline-none focus:ring-0 p-0 font-medium">
                                <button x-show="searchQuery"
                                        @click="clearSearch()"
                                        type="button"
                                        title="Clear Search"
                                        class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 flex items-center justify-center text-xs font-bold transition-colors cursor-pointer shrink-0"
                                        style="display: none;">
                                    ✕
                                </button>
                            </div>
                            <button @click="scrollToMenu()"
                                    type="button"
                                    class="w-full sm:w-auto px-7 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white font-bold text-sm rounded-xl sm:rounded-2xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/40 transition-all duration-300 cursor-pointer shrink-0 flex items-center justify-center gap-2 group">
                                <span>{{ __('Explore Dishes') }}</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Stats & Trust Badges Strip -->
                        <div class="pt-4 grid grid-cols-3 gap-3 sm:gap-6 max-w-xl mx-auto lg:mx-0">
                            <!-- Stat 1 -->
                            <div class="glass-card p-3 sm:p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm text-center lg:text-left transition-all duration-300 hover:scale-[1.02]">
                                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">10k+</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ __('Active Foodies') }}</div>
                            </div>
                            <!-- Stat 2 -->
                            <div class="glass-card p-3 sm:p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm text-center lg:text-left transition-all duration-300 hover:scale-[1.02]">
                                <div class="text-xl sm:text-2xl font-black text-orange-500 tracking-tight">30 Min</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ __('15-30 Min Delivery') }}</div>
                            </div>
                            <!-- Stat 3 -->
                            <div class="glass-card p-3 sm:p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm text-center lg:text-left transition-all duration-300 hover:scale-[1.02]">
                                <div class="text-xl sm:text-2xl font-black text-amber-500 tracking-tight">4.9 ★</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ __('Top Rated') }}</div>
                            </div>
                        </div>

                    </div>

                    <!-- Hero Right Visual Showcase & Floating Glass Badges -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                            
                            <!-- Ambient Visual Glow Aura Behind Image -->
                            <div class="absolute -inset-3 rounded-[2.5rem] bg-gradient-to-tr from-orange-500/30 via-amber-400/20 to-rose-500/30 blur-2xl opacity-70"></div>

                            <!-- Main Hero Image Showcase -->
                            <div class="relative z-10 rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white/90 dark:border-slate-800/90 bg-slate-900 group">
                                <img src="/images/hero_food.png" alt="Delicious Food Showcase" class="w-full h-[320px] sm:h-[380px] lg:h-[420px] object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>
                            </div>

                            <!-- Floating Badge 1 (Rating - Top Left) -->
                            <div class="animate-float-slow absolute -top-5 -left-4 sm:-left-6 z-20 glass-badge p-3 sm:p-3.5 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-slate-950/50 border border-white/80 dark:border-slate-700/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center text-lg shadow-md shadow-amber-500/30 shrink-0">
                                    ⭐
                                </div>
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">4.9 Rating</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ __('100% Fresh & Clean') }}</div>
                                </div>
                            </div>

                            <!-- Floating Badge 2 (Ultra Fast Delivery - Bottom Right) -->
                            <div class="animate-float-reverse absolute -bottom-5 -right-4 sm:-right-6 z-20 glass-badge p-3 sm:p-3.5 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-slate-950/50 border border-white/80 dark:border-slate-700/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-rose-500 text-white flex items-center justify-center text-lg shadow-md shadow-orange-500/30 shrink-0">
                                    🚀
                                </div>
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">{{ __('Ultra Fast Delivery') }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ __('Fast Delivery') }}</div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ================= CATEGORIES SECTION ================= -->
        <section id="categories" class="py-10 bg-white/70 dark:bg-slate-900/50 backdrop-blur-md border-y border-slate-200/60 dark:border-slate-800/80 transition-colors duration-300 scroll-mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                        <span class="text-orange-600 dark:text-orange-400 text-xs font-black tracking-widest uppercase">{{ __('Browse by Category') }}</span>
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-r from-slate-200 dark:from-slate-800 to-transparent"></div>
                </div>

                <!-- Horizontal Category Filter Pills -->
                <div class="flex flex-wrap gap-2.5 sm:gap-3.5 justify-center sm:justify-start">

                    <!-- All Dishes Pill -->
                    <button
                        @click="activeCategory = 'all'; searchQuery = '';"
                        :class="activeCategory === 'all' && !searchQuery
                            ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-lg shadow-orange-500/30 ring-2 ring-orange-400/50 scale-[1.03]'
                            : 'glass-card text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-800 hover:border-orange-300 dark:hover:border-orange-700 hover:bg-white dark:hover:bg-slate-800 hover:shadow-md hover:scale-[1.02]'"
                        class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl font-bold text-xs sm:text-sm cursor-pointer transition-all duration-300 select-none group"
                    >
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 transition-transform duration-300 group-hover:rotate-12"
                             :class="activeCategory === 'all' && !searchQuery ? 'bg-white/25 text-white' : 'bg-orange-500/10 text-orange-500 dark:bg-orange-500/20'">
                            🍽️
                        </div>
                        <span>{{ __('All Dishes') }}</span>
                        <span
                            :class="activeCategory === 'all' && !searchQuery ? 'bg-white/30 text-white' : 'bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                            class="text-[11px] font-extrabold px-2 py-0.5 rounded-full transition-colors">
                            {{ $menuItems->count() }}
                        </span>
                    </button>

                    @foreach($categories as $category)
                        @php
                            $icon = '🍽️';
                            $img = $category->menuItems->first() ? $category->menuItems->first()->image_url : asset('images/hero_food.png');
                            if(str_contains(strtolower($category->name), 'pizza')) { $icon = '🍕'; }
                            elseif(str_contains(strtolower($category->name), 'burger')) { $icon = '🍔'; }
                            elseif(str_contains(strtolower($category->name), 'noodle')) { $icon = '🍜'; }
                            elseif(str_contains(strtolower($category->name), 'beverage') || str_contains(strtolower($category->name), 'drink')) { $icon = '🍹'; }
                            elseif(str_contains(strtolower($category->name), 'dessert')) { $icon = '🍰'; }
                        @endphp

                        <button
                            @click="activeCategory = '{{ $category->slug }}'; searchQuery = '';"
                            :class="activeCategory === '{{ $category->slug }}' && !searchQuery
                                ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-lg shadow-orange-500/30 ring-2 ring-orange-400/50 scale-[1.03]'
                                : 'glass-card text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-800 hover:border-orange-300 dark:hover:border-orange-700 hover:bg-white dark:hover:bg-slate-800 hover:shadow-md hover:scale-[1.02]'"
                            class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl font-bold text-xs sm:text-sm cursor-pointer transition-all duration-300 select-none group"
                        >
                            <!-- Mini Circular Category Image -->
                            <div class="w-7 h-7 rounded-xl overflow-hidden shrink-0 ring-1 ring-orange-500/30 flex items-center justify-center bg-orange-100 dark:bg-slate-800">
                                <img src="{{ $img }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            </div>
                            <span>{{ $icon }} {{ $category->name }}</span>
                            <span
                                :class="activeCategory === '{{ $category->slug }}' && !searchQuery ? 'bg-white/30 text-white' : 'bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                                class="text-[11px] font-extrabold px-2 py-0.5 rounded-full transition-colors">
                                {{ $category->menu_items_count }}
                            </span>
                        </button>
                    @endforeach

                </div>

            </div>
        </section>

        <!-- ================= POPULAR MENU SECTION ================= -->
        <section id="menu" class="py-16 lg:py-20 transition-colors duration-300 scroll-mt-20 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Section Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 text-xs font-black tracking-widest uppercase mb-2">
                            <span>🔥</span>
                            <span>{{ __('Popular Menu') }}</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ __("Popular Dishes & Chef's Picks") }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <template x-if="searchQuery">
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-orange-50 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800/80 rounded-2xl text-xs font-bold shadow-sm animate-pulse">
                                <span>🔍 "<span x-text="searchQuery"></span>"</span>
                                <span class="text-[11px] opacity-75">(<span x-text="filteredCount()"></span> results)</span>
                                <button @click="clearSearch()" class="hover:text-orange-800 dark:hover:text-orange-200 ml-1 font-extrabold cursor-pointer text-sm" title="Clear Search">✕</button>
                            </div>
                        </template>
                        <span class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 max-w-md text-left md:text-right" x-show="!searchQuery">
                            {{ __('Menu Subtitle') }}
                        </span>
                    </div>
                </div>

                <!-- Dynamic Food Menu Bento Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    @forelse($menuItems as $item)
                        @php
                            $catSlug = $item->category ? $item->category->slug : 'all';
                            $catName = $item->category ? $item->category->name : 'Special';
                            $icon = '🍽️';
                            if(str_contains(strtolower($catName), 'pizza')) { $icon = '🍕'; }
                            elseif(str_contains(strtolower($catName), 'burger')) { $icon = '🍔'; }
                            elseif(str_contains(strtolower($catName), 'noodle')) { $icon = '🍜'; }
                            elseif(str_contains(strtolower($catName), 'beverage') || str_contains(strtolower($catName), 'drink')) { $icon = '🍹'; }
                            elseif(str_contains(strtolower($catName), 'dessert')) { $icon = '🍰'; }
                        @endphp

                        <div x-show="(searchQuery.trim() !== '' || activeCategory === 'all' || activeCategory === '{{ $catSlug }}') && matchesSearch(@js($item->name), @js($item->description ?? ''), @js($catName))"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="group relative flex flex-col justify-between rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/90 shadow-sm hover:shadow-2xl hover:shadow-orange-500/10 hover:border-orange-300/80 dark:hover:border-orange-500/40 hover:-translate-y-1.5 transition-all duration-300 overflow-hidden">
                            
                            <div>
                                <!-- Food Image Showcase -->
                                <div class="relative h-48 sm:h-52 overflow-hidden bg-slate-100 dark:bg-slate-800">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-black/20"></div>
                                    
                                    <!-- Category Pill Tag -->
                                    <span class="absolute top-3.5 left-3.5 glass-badge text-slate-900 dark:text-white text-xs font-extrabold px-3 py-1 rounded-full shadow-md border border-white/40 dark:border-slate-700/60 flex items-center gap-1.5">
                                        <span>{{ $icon }}</span>
                                        <span>{{ $catName }}</span>
                                    </span>

                                    <!-- Rating Pill -->
                                    <span class="absolute top-3.5 right-3.5 glass-badge text-amber-500 dark:text-amber-400 text-xs font-black px-2.5 py-1 rounded-full shadow-md border border-white/40 dark:border-slate-700/60 flex items-center gap-1">
                                        ⭐ 4.9
                                    </span>
                                </div>

                                <!-- Card Body Information -->
                                <div class="p-4 sm:p-5">
                                    <!-- Stock & Delivery Time Status -->
                                    <div class="flex items-center justify-between text-xs font-bold mb-2">
                                        @if($item->stock <= 0 || !$item->is_available)
                                            <span class="text-red-500 dark:text-red-400 flex items-center gap-1.5 bg-red-50 dark:bg-red-950/50 px-2 py-0.5 rounded-lg border border-red-200 dark:border-red-900/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                <span>{{ __('Out of Stock') }}</span>
                                            </span>
                                        @elseif($item->stock <= 10)
                                            <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1.5 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-lg border border-amber-200 dark:border-amber-900/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>{{ __('Only') }} {{ $item->stock }} {{ __('left') }}</span>
                                            </span>
                                        @else
                                            <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-900/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>{{ __('In Stock') }}</span>
                                            </span>
                                        @endif

                                        <span class="text-slate-400 dark:text-slate-500 text-[11px] font-semibold flex items-center gap-1">
                                            <span>⏱️ 20 min</span>
                                        </span>
                                    </div>

                                    <!-- Dish Name & Description -->
                                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white group-hover:text-orange-500 transition-colors line-clamp-1">
                                        {{ $item->name }}
                                    </h3>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 line-clamp-2 leading-relaxed font-normal">
                                        {{ $item->description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Card Bottom Pricing & Action Button -->
                            <div class="p-4 sm:p-5 pt-0 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 mt-2">
                                <div>
                                    <span class="text-[10px] text-slate-400 font-semibold block uppercase tracking-wider">{{ __('Unit Price') }}</span>
                                    <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                        {{ number_format($item->price) }} <span class="text-xs font-bold text-orange-500">MMK</span>
                                    </span>
                                </div>

                                @if($item->stock <= 0 || !$item->is_available)
                                    <button disabled class="px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs rounded-xl cursor-not-allowed border border-slate-200 dark:border-slate-700">
                                        {{ __('Sold Out') }}
                                    </button>
                                @else
                                    <button
                                        @click="addToCart({{ json_encode(['id' => $item->id, 'name' => $item->name, 'price' => $item->price, 'image' => $item->image_url, 'category' => $catName, 'stock' => $item->stock]) }}); window.location.href='{{ route('cart') }}';"
                                        class="px-3.5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white font-extrabold text-xs rounded-xl sm:rounded-2xl shadow-md shadow-orange-500/25 hover:shadow-lg hover:shadow-orange-500/40 flex items-center gap-1.5 transition-all duration-200 cursor-pointer">
                                        @if(app()->getLocale() === 'my')
                                            <span class="font-bold">🛒 ဝယ်ရန်</span>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span>{{ __('Add to Cart') }}</span>
                                        @endif
                                    </button>
                                @endif
                            </div>

                        </div>
                    @empty
                    @endforelse

                    <!-- Zero Search Results State -->
                    <div x-show="filteredCount() === 0"
                         style="display: none;"
                         class="col-span-1 sm:col-span-2 lg:col-span-4 text-center py-16 px-4 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xl space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center text-3xl mx-auto">
                            🔍
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('No dishes found matching your criteria.') }}</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Try searching for different keywords or clear your search filter.</p>
                        </div>
                        <button @click="clearSearch(); activeCategory = 'all';" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-xs font-bold rounded-xl shadow-md shadow-orange-500/25 transition-all cursor-pointer">
                            {{ __('All Dishes') }}
                        </button>
                    </div>

                </div>

            </div>
        </section>

        <!-- ================= FEATURES ("WHY FOODORDER?") SECTION ================= -->
        <section id="features" class="py-16 lg:py-24 bg-slate-100/60 dark:bg-slate-900/40 border-t border-slate-200/60 dark:border-slate-800/80 transition-colors duration-300 relative overflow-hidden">
            <!-- Decorative Subtle Backdrop -->
            <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-orange-500/5 rounded-full blur-3xl"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                
                <div class="text-center max-w-2xl mx-auto mb-14 space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 text-xs font-black tracking-widest uppercase">
                        <span>✨</span>
                        <span>{{ __('Why Choose Us Subtitle') }}</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('Why FoodOrder?') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    
                    <!-- Feature Card 1 -->
                    <div class="group relative p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-2xl hover:shadow-orange-500/10 hover:border-orange-300 dark:hover:border-orange-700/60 hover:-translate-y-1.5 transition-all duration-300 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-400 text-white flex items-center justify-center text-3xl mb-6 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform duration-300">
                            📱
                        </div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Finest Ingredients') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm mt-3 leading-relaxed font-normal">{{ __('Finest Ingredients Desc') }}</p>
                    </div>

                    <!-- Feature Card 2 -->
                    <div class="group relative p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-2xl hover:shadow-orange-500/10 hover:border-orange-300 dark:hover:border-orange-700/60 hover:-translate-y-1.5 transition-all duration-300 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-3xl mb-6 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform duration-300">
                            🚚
                        </div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Ultra Fast Delivery') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm mt-3 leading-relaxed font-normal">{{ __('Fast Delivery Desc') }}</p>
                    </div>

                    <!-- Feature Card 3 -->
                    <div class="group relative p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-2xl hover:shadow-orange-500/10 hover:border-orange-300 dark:hover:border-orange-700/60 hover:-translate-y-1.5 transition-all duration-300 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-rose-500 to-orange-500 text-white flex items-center justify-center text-3xl mb-6 shadow-lg shadow-rose-500/30 group-hover:scale-110 transition-transform duration-300">
                            💳
                        </div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Multi-Channel Payment') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm mt-3 leading-relaxed font-normal">{{ __('Multi Payment Desc') }}</p>
                    </div>

                </div>

            </div>
        </section>

    </div>

    <!-- ================= FOOTER ================= -->
    <footer class="bg-slate-950 text-slate-400 py-12 lg:py-16 border-t border-slate-800 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12 mb-12">
                
                <!-- Col 1: Brand -->
                <div class="space-y-4">
                    <a href="/" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 flex items-center justify-center text-white text-lg shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                            🍕
                        </div>
                        <span class="text-2xl font-black text-white tracking-tight">Food<span class="text-orange-500">Order</span></span>
                    </a>
                    <p class="text-xs leading-relaxed text-slate-400 font-medium">{{ __('Footer Rights') }}</p>
                </div>

                <!-- Col 2: Navigation Links -->
                <div>
                    <h4 class="text-white text-sm font-black tracking-wide uppercase mb-4">{{ __('Categories') }}</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        <li><a href="#hero" class="hover:text-orange-400 transition-colors flex items-center gap-1.5"><span>→</span> {{ __('Home') }}</a></li>
                        <li><a href="#categories" class="hover:text-orange-400 transition-colors flex items-center gap-1.5"><span>→</span> {{ __('Categories') }}</a></li>
                        <li><a href="#menu" class="hover:text-orange-400 transition-colors flex items-center gap-1.5"><span>→</span> {{ __('Popular Menu') }}</a></li>
                    </ul>
                </div>

                <!-- Col 3: Payment Methods -->
                <div>
                    <h4 class="text-white text-sm font-black tracking-wide uppercase mb-4">{{ __('Payment Method') }}</h4>
                    <div class="flex flex-wrap gap-2 text-xs font-bold text-slate-300">
                        <span class="px-3 py-1.5 bg-slate-900 rounded-xl border border-slate-800 flex items-center gap-1.5 shadow-sm">💵 {{ __('Cash on Delivery') }}</span>
                        <span class="px-3 py-1.5 bg-slate-900 rounded-xl border border-slate-800 flex items-center gap-1.5 shadow-sm">📱 KBZPay</span>
                        <span class="px-3 py-1.5 bg-slate-900 rounded-xl border border-slate-800 flex items-center gap-1.5 shadow-sm">🌊 WavePay</span>
                    </div>
                </div>

                <!-- Col 4: Contact & Location -->
                <div>
                    <h4 class="text-white text-sm font-black tracking-wide uppercase mb-4">{{ __('Why Us') }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed font-medium">
                        📍 123 Main Street, Yangon, Myanmar<br />
                        📞 +95 9 123 456 789<br />
                        ✉️ support@foodorder.com
                    </p>
                </div>

            </div>

            <div class="border-t border-slate-800/80 pt-8 text-center text-xs text-slate-500 font-medium">
                &copy; {{ date('Y') }} FoodOrderingSystem. {{ __('All rights reserved.') }}
            </div>
        </div>
    </footer>

    <!-- ===== ADD TO CART FLOATING TOAST ===== -->
    <div
        x-show="toastVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3.5 bg-slate-900/95 dark:bg-slate-900/95 backdrop-blur-xl text-white px-5 py-4 rounded-2xl shadow-2xl border border-slate-700/80 max-w-sm"
        style="display:none;">
        <div class="w-9 h-9 bg-gradient-to-tr from-orange-500 to-amber-500 rounded-xl flex items-center justify-center shrink-0 text-sm shadow-md shadow-orange-500/30">
            🛒
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs text-slate-400 font-semibold">{{ __('Added to cart!') }}</p>
            <p class="text-sm font-bold truncate text-white" x-text="toastName"></p>
        </div>
        <a href="{{ route('cart') }}" class="text-xs font-extrabold text-orange-400 hover:text-orange-300 shrink-0 transition-colors px-2.5 py-1 bg-orange-500/10 rounded-lg border border-orange-500/20">
            View →
        </a>
    </div>

    @if(session('clear_cart'))
    <script>
        localStorage.removeItem('foodorder_cart');
    </script>
    @endif

</body>
</html>
