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

    <script>
        window.welcomeApp = function() {
            return {
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
                activeCategory: 'all',
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
<body x-data="welcomeApp()" class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-950 selection:bg-orange-500 selection:text-white transition-colors duration-300">

    <!-- 60% Dominant Base Container -->
    <div class="min-h-screen flex flex-col justify-between">

        <!-- ================= NAVBAR ================= -->
        <x-storefront-navbar />

        <!-- ================= HERO SECTION ================= -->
        <section id="hero" class="relative py-6 lg:py-10 bg-white dark:bg-slate-950 transition-colors duration-300 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center">
                    
                    <!-- Hero Text Content -->
                    <div class="lg:col-span-7 space-y-4 text-center lg:text-left">
                        
                        <!-- Promo Tag -->
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-orange-100/80 dark:bg-orange-950/60 border border-orange-200 dark:border-orange-800/60 rounded-full text-orange-700 dark:text-orange-300 text-xs font-bold tracking-wide uppercase shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                            🔥 20% OFF • Code: FIRST20
                        </div>

                        <!-- Main Headline -->
                        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-snug">
                            {{ __('Hero Title Start') }} <br class="hidden sm:inline" />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600">{{ __('Hero Title End') }}</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed">
                            {{ __('Hero Description') }}
                        </p>

                        <!-- Search Bar Component (30% Secondary Card) -->
                        <div class="bg-white dark:bg-slate-900 p-2 sm:p-3 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center gap-2 max-w-2xl mx-auto lg:mx-0">
                            <div class="flex items-center gap-2 w-full px-3 py-2">
                                <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text" placeholder="{{ __('Search dishes...') }}" class="w-full text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 bg-transparent border-none focus:outline-none focus:ring-0">
                            </div>
                            <a href="#menu" class="w-full sm:w-auto px-6 py-3.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/30 transition-all cursor-pointer shrink-0 flex items-center justify-center gap-2">
                                <span>{{ __('Explore Dishes') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </a>
                        </div>

                        <!-- Stats & Trust Badges -->
                        <div class="pt-3 flex items-center justify-center lg:justify-start gap-6 border-t border-slate-200/60 dark:border-slate-800/80">
                            <div>
                                <div class="text-xl font-extrabold text-slate-900 dark:text-white">10k+</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Active Foodies') }}</div>
                            </div>
                            <div class="h-8 w-px bg-slate-200 dark:bg-slate-800"></div>
                            <div>
                                <div class="text-xl font-extrabold text-slate-900 dark:text-white">30 Min</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('15-30 Min Delivery') }}</div>
                            </div>
                            <div class="h-8 w-px bg-slate-200 dark:bg-slate-800"></div>
                            <div>
                                <div class="text-xl font-extrabold text-slate-900 dark:text-white">4.9 ★</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Top Rated') }}</div>
                            </div>
                        </div>

                    </div>

                    <!-- Hero Visual Image & Floating Badges -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                            
                            <!-- Main Hero Image Container -->
                            <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl border-4 border-white dark:border-slate-800 bg-slate-900 group">
                                <img src="/images/hero_food.png" alt="Delicious Food Showcase" class="w-full h-[300px] lg:h-[360px] object-cover group-hover:scale-105 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                            </div>

                            <!-- Floating Badge 1 (Rating) -->
                            <div class="absolute -top-6 -left-6 z-20 bg-white dark:bg-slate-900 p-3.5 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                                    ⭐
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">4.9 Rating</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ __('100% Fresh & Clean') }}</div>
                                </div>
                            </div>

                            <!-- Floating Badge 2 (Delivery Speed) -->
                            <div class="absolute -bottom-6 -right-6 z-20 bg-white dark:bg-slate-900 p-3.5 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg">
                                    🚀
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Ultra Fast Delivery') }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ __('Fast Delivery') }}</div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

         <!-- ================= CATEGORIES SECTION ================= -->
        <section id="categories" class="py-8 bg-white dark:bg-slate-950 transition-colors duration-300">
            <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex items-center gap-3 mb-6">
                    <span class="text-orange-500 text-xs font-black tracking-widest uppercase">{{ __('Browse by Category') }}</span>
                    <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                </div>

                <!-- Horizontal Pill Tabs -->
                <div class="flex flex-wrap gap-3 justify-center">

                    <!-- All Dishes Tab -->
                    <button
                        @click="activeCategory = 'all'"
                        :class="activeCategory === 'all'
                            ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30 scale-105'
                            : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600'"
                        class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl font-semibold text-sm cursor-pointer transition-all duration-200 border border-transparent"
                    >
                        <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 ring-2 flex items-center justify-center font-bold text-sm"
                             :class="activeCategory === 'all' ? 'bg-white/20 text-white ring-white/40' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 ring-slate-200 dark:ring-slate-700'">
                            🍽️
                        </div>
                        <span>{{ __('All Dishes') }}</span>
                        <span
                            :class="activeCategory === 'all' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                            class="text-xs font-bold px-2 py-0.5 rounded-full">
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
                            @click="activeCategory = '{{ $category->slug }}'"
                            :class="activeCategory === '{{ $category->slug }}'
                                ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30 scale-105'
                                : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600'"
                            class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl font-semibold text-sm cursor-pointer transition-all duration-200 border border-transparent"
                        >
                            <!-- Tiny circular image -->
                            <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 ring-2"
                                 :class="activeCategory === '{{ $category->slug }}' ? 'ring-white/40' : 'ring-slate-200 dark:ring-slate-700'">
                                <img src="{{ $img }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                            </div>
                            <span>{{ $icon }} {{ $category->name }}</span>
                            <span
                                :class="activeCategory === '{{ $category->slug }}' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                                class="text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ $category->menu_items_count }}
                            </span>
                        </button>
                    @endforeach

                </div>

            </div>
        </section>

        <!-- ================= POPULAR MENU SECTION ================= -->
        <section id="menu" class="py-16 bg-white dark:bg-slate-950 transition-colors duration-300">
            <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                    <div>
                        <span class="text-orange-500 text-xs font-bold tracking-widest uppercase">{{ __('Popular Menu') }}</span>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ __("Popular Dishes & Chef's Picks") }}</h2>
                    </div>
                    <div class="mt-4 md:mt-0 flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Menu Subtitle') }}</span>
                    </div>
                </div>

                <!-- Dynamic Menu Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

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

                        <div x-show="activeCategory === 'all' || activeCategory === '{{ $catSlug }}'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-100 dark:border-slate-800/80 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col justify-between">
                            <div>
                                <div class="relative h-46 overflow-hidden bg-slate-100 dark:bg-slate-800">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <span class="absolute top-4 left-4 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                        {{ $icon }} {{ $catName }}
                                    </span>
                                    <span class="absolute top-4 right-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md text-slate-900 dark:text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-md flex items-center gap-1">
                                        ⭐ 4.9
                                    </span>
                                </div>
                                <div class="p-3">
                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-semibold mb-1">
                                        @if($item->stock <= 0 || !$item->is_available)
                                            <span class="text-red-500 font-bold flex items-center gap-1">🚫 {{ __('Out of Stock') }}</span>
                                        @elseif($item->stock <= 10)
                                            <span class="text-amber-500 font-bold flex items-center gap-1">⚠️ {{ __('Only') }} {{ $item->stock }} {{ __('left') }}</span>
                                        @else
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">✓ {{ __('In Stock') }}</span>
                                        @endif
                                        <span>⏱️ 20 min</span>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-orange-600 transition-colors">{{ $item->name }}</h3>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 line-clamp-1 leading-relaxed">{{ $item->description }}</p>
                                </div>
                            </div>
                            <div class="p-3 pt-0 flex items-center justify-between border-t border-slate-50 dark:border-slate-800/60 mt-2">
                                <div>
                                    <span class="text-xs text-slate-400 font-medium block">{{ __('Unit Price') }}</span>
                                    <span class="text-base font-black text-slate-900 dark:text-white">{{ number_format($item->price) }} MMK</span>
                                </div>
                                @if($item->stock <= 0 || !$item->is_available)
                                    <button disabled class="px-3 py-2 bg-slate-200 dark:bg-slate-800 text-slate-400 font-bold text-xs rounded-xl cursor-not-allowed">
                                        {{ __('Sold Out') }}
                                    </button>
                                @else
                                    <button
                                        @click="addToCart({{ json_encode(['id' => $item->id, 'name' => $item->name, 'price' => $item->price, 'image' => $item->image_url, 'category' => $catName, 'stock' => $item->stock]) }}); window.location.href='{{ route('cart') }}';"
                                        class="px-3 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 flex items-center gap-1.5 transition-all cursor-pointer">
                                        @if(app()->getLocale() === 'my')
                                            <span class="font-bold">🛒 ဝယ်ရန်</span>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span>{{ __('Add to Cart') }}</span>
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-4 text-center py-12 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <p class="text-slate-500 dark:text-slate-400 font-medium">{{ __('No dishes found matching your criteria.') }}</p>
                        </div>
                    @endforelse

                </div>

            </div>
        </section>


        <!-- ================= FEATURES SECTION ================= -->
        <section id="features" class="py-16 bg-white dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800/80 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-xl mx-auto mb-12">
                    <span class="text-orange-500 text-xs font-bold tracking-widest uppercase">{{ __('Why Choose Us Subtitle') }}</span>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ __('Why FoodOrder?') }}</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- Feature 1 -->
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-8 rounded-3xl border border-slate-100 dark:border-slate-800/80 text-center hover:shadow-lg transition-all">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl mx-auto mb-6 shadow-lg shadow-orange-500/30">
                            📱
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Finest Ingredients') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm mt-2 leading-relaxed">{{ __('Finest Ingredients Desc') }}</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-8 rounded-3xl border border-slate-100 dark:border-slate-800/80 text-center hover:shadow-lg transition-all">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl mx-auto mb-6 shadow-lg shadow-orange-500/30">
                            🚚
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Ultra Fast Delivery') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm mt-2 leading-relaxed">{{ __('Fast Delivery Desc') }}</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-8 rounded-3xl border border-slate-100 dark:border-slate-800/80 text-center hover:shadow-lg transition-all">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl mx-auto mb-6 shadow-lg shadow-orange-500/30">
                            💳
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Multi-Channel Payment') }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm mt-2 leading-relaxed">{{ __('Multi Payment Desc') }}</p>
                    </div>

                </div>

            </div>
        </section>

        <!-- ================= FOOTER ================= -->
        <footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 py-12 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    
                    <!-- Col 1 -->
                    <div>
                        <a href="/" class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center text-white font-bold">
                                🍕
                            </div>
                            <span class="text-xl font-bold text-white tracking-tight">Food<span class="text-orange-500">Order</span></span>
                        </a>
                        <p class="text-xs leading-relaxed text-slate-400">{{ __('Footer Rights') }}</p>
                    </div>

                    <!-- Col 2 -->
                    <div>
                        <h4 class="text-white text-sm font-bold mb-4">{{ __('Categories') }}</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#hero" class="hover:text-white transition-colors">{{ __('Home') }}</a></li>
                            <li><a href="#categories" class="hover:text-white transition-colors">{{ __('Categories') }}</a></li>
                            <li><a href="#menu" class="hover:text-white transition-colors">{{ __('Popular Menu') }}</a></li>
                        </ul>
                    </div>

                    <!-- Col 3 -->
                    <div>
                        <h4 class="text-white text-sm font-bold mb-4">{{ __('Payment Method') }}</h4>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-300">
                            <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">💵 {{ __('Cash on Delivery') }}</span>
                            <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">📱 KBZPay</span>
                            <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">🌊 WavePay</span>
                        </div>
                    </div>

                    <!-- Col 4 -->
                    <div>
                        <h4 class="text-white text-sm font-bold mb-4">{{ __('Why Us') }}</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">📍 123 Main Street, Yangon, Myanmar<br />📞 +95 9 123 456 789<br />✉️ support@foodorder.com</p>
                    </div>

                </div>

                <div class="border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
                    &copy; {{ date('Y') }} FoodOrderingSystem. {{ __('All rights reserved.') }}
                </div>
            </div>
        </footer>

    </div>


<!-- ===== ADD TO CART TOAST ===== -->
<div
    x-show="toastVisible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700 max-w-xs"
    style="display:none;">
    <div class="w-8 h-8 bg-orange-500 rounded-xl flex items-center justify-center shrink-0 text-sm">🛒</div>
    <div class="flex-1 min-w-0">
        <p class="text-xs text-slate-400 font-medium">Added to cart!</p>
        <p class="text-sm font-bold truncate" x-text="toastName"></p>
    </div>
    <a href="{{ route('cart') }}" class="text-xs font-bold text-orange-400 hover:text-orange-300 shrink-0 transition-colors">View →</a>
</div>

@if(session('clear_cart'))
<script>
    localStorage.removeItem('foodorder_cart');
</script>
@endif
</body>

</html>
