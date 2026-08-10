<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Food Ordering System') }} - Delicious Meals Delivered Fast</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white">

    <!-- 60% Dominant Base Container -->
    <div class="min-h-screen flex flex-col justify-between">

        <!-- ================= NAVBAR ================= -->
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 shadow-sm transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    
                    <!-- Brand Logo -->
                    <a href="/" class="flex items-center gap-3 group">
                        <div class="w-11 h-11 rounded-2xl bg-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <span class="text-2xl font-black tracking-tight text-slate-900">Food<span class="text-orange-500">Order</span></span>
                    </a>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold">
                        <a href="#hero" class="text-orange-500 font-bold">Home</a>
                        <a href="#categories" class="text-slate-600 hover:text-orange-500 transition-colors">Categories</a>
                        <a href="#menu" class="text-slate-600 hover:text-orange-500 transition-colors">Popular Menu</a>
                        <a href="#features" class="text-slate-600 hover:text-orange-500 transition-colors">Why Us</a>
                    </nav>

                    <!-- Header Actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Shopping Cart Button -->
                        <button class="relative p-2.5 bg-slate-100 hover:bg-orange-50 text-slate-700 hover:text-orange-600 rounded-xl transition-all duration-200 cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <!-- Cart Item Count Badge -->
                            <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                                3
                            </span>
                        </button>

                        @if (Route::has('login'))
                            @auth
                                @if (Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-amber-500/25 transition-all flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <span>Admin Portal</span>
                                    </a>
                                @endif

                                <!-- Logged-In User Profile Dropdown -->
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" @click.outside="open = false" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 text-slate-800 font-bold text-xs sm:text-sm rounded-xl border border-slate-200 flex items-center gap-2 transition-all cursor-pointer">
                                        <div class="w-7 h-7 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-black shadow-sm">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                        <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <!-- Dropdown Menu Box -->
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                                        <div class="px-4 py-2 border-b border-slate-100">
                                            <p class="text-[11px] text-slate-400">Signed in as</p>
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->email }}</p>
                                        </div>
                                        
                                        <a href="#menu" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                            🍕 Explore Menu
                                        </a>

                                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                            ⚙️ Profile Settings
                                        </a>

                                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1 pt-1">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors cursor-pointer flex items-center justify-between">
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


                    </div>
                </div>
            </div>
        </header>

        <!-- ================= HERO SECTION ================= -->
        <section id="hero" class="relative py-12 lg:py-20 bg-gradient-to-b from-orange-50/60 via-white to-slate-50 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Hero Text Content -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        
                        <!-- Promo Tag -->
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-orange-100/80 border border-orange-200 rounded-full text-orange-700 text-xs font-bold tracking-wide uppercase shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                            🔥 20% OFF On First Order • Code: FIRST20
                        </div>

                        <!-- Main Headline -->
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-none">
                            Delicious Food <br class="hidden sm:inline" />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600">Delivered Fast</span> To Your Door
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-slate-600 text-base sm:text-lg max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed">
                            Satisfy your cravings with top-rated local dishes. Freshly prepared by expert chefs and delivered piping hot in 30 minutes.
                        </p>

                        <!-- Search Bar Component (30% Secondary Card) -->
                        <div class="bg-white p-2 sm:p-3 rounded-2xl shadow-xl shadow-slate-200/70 border border-slate-100 flex flex-col sm:flex-row items-center gap-2 max-w-2xl mx-auto lg:mx-0">
                            <div class="flex items-center gap-2 w-full px-3 py-2">
                                <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text" placeholder="Search pizza, burger, noodles, drinks..." class="w-full text-sm text-slate-800 placeholder-slate-400 bg-transparent border-none focus:outline-none focus:ring-0">
                            </div>
                            <button class="w-full sm:w-auto px-6 py-3.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/30 transition-all cursor-pointer shrink-0 flex items-center justify-center gap-2">
                                <span>Find Food</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Stats & Trust Badges -->
                        <div class="pt-4 flex items-center justify-center lg:justify-start gap-8 border-t border-slate-200/60">
                            <div>
                                <div class="text-2xl font-extrabold text-slate-900">10k+</div>
                                <div class="text-xs text-slate-500 font-medium">Satisfied Customers</div>
                            </div>
                            <div class="h-8 w-px bg-slate-200"></div>
                            <div>
                                <div class="text-2xl font-extrabold text-slate-900">30 Min</div>
                                <div class="text-xs text-slate-500 font-medium">Average Delivery Time</div>
                            </div>
                            <div class="h-8 w-px bg-slate-200"></div>
                            <div>
                                <div class="text-2xl font-extrabold text-slate-900">4.9 ★</div>
                                <div class="text-xs text-slate-500 font-medium">Over 2,500 Reviews</div>
                            </div>
                        </div>

                    </div>

                    <!-- Hero Visual Image & Floating Badges -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                            
                            <!-- Main Hero Image Container -->
                            <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl border-4 border-white bg-slate-900 group">
                                <img src="/images/hero_food.png" alt="Delicious Food Showcase" class="w-full h-[420px] object-cover group-hover:scale-105 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                            </div>

                            <!-- Floating Badge 1 (Rating) -->
                            <div class="absolute -top-6 -left-6 z-20 bg-white p-3.5 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                                    ⭐
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900">4.9 Rating</div>
                                    <div class="text-xs text-slate-500">Top Food Quality</div>
                                </div>
                            </div>

                            <!-- Floating Badge 2 (Delivery Speed) -->
                            <div class="absolute -bottom-6 -right-6 z-20 bg-white p-3.5 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-lg">
                                    🚀
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900">Super Fast</div>
                                    <div class="text-xs text-slate-500">Express Delivery</div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
         <!-- ================= CATEGORIES SECTION ================= -->
        <section id="categories" class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-xl mx-auto mb-10">
                    <h2 class="text-3xl font-extrabold text-slate-900">Explore Food Categories</h2>
                    <p class="mt-2 text-slate-600 text-sm">Select a category to filter your favorite dish options in real-time</p>
                </div>

                <!-- Dynamic Category Cards Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6">
                    
                    <!-- All Items Button -->
                    <div @click="activeCategory = 'all'" 
                         :class="activeCategory === 'all' ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-500/25 scale-105' : 'bg-slate-50 hover:bg-orange-50 text-slate-800 border-slate-100 hover:border-orange-200'"
                         class="border rounded-3xl p-4 text-center cursor-pointer transition-all duration-300 shadow-sm group flex flex-col items-center justify-center min-h-[140px]">
                        <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-3xl mb-2">
                            🍽️
                        </div>
                        <div class="font-bold text-sm sm:text-base">All Items</div>
                        <div class="text-xs opacity-80 mt-1">{{ count($menuItems) }} Total Dishes</div>
                    </div>

                    @foreach($categories as $category)
                        @php
                            $icon = '🍽️';
                            $img = $category->menuItems->first() ? $category->menuItems->first()->image : '/images/hero_food.png';
                            if(str_contains(strtolower($category->name), 'pizza')) { $icon = '🍕'; }
                            elseif(str_contains(strtolower($category->name), 'burger')) { $icon = '🍔'; }
                            elseif(str_contains(strtolower($category->name), 'noodle')) { $icon = '🍜'; }
                            elseif(str_contains(strtolower($category->name), 'beverage') || str_contains(strtolower($category->name), 'drink')) { $icon = '🍹'; }
                            elseif(str_contains(strtolower($category->name), 'dessert')) { $icon = '🍰'; }
                        @endphp

                        <div @click="activeCategory = '{{ $category->slug }}'" 
                             :class="activeCategory === '{{ $category->slug }}' ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-500/25 scale-105' : 'bg-slate-50 hover:bg-orange-50 text-slate-800 border-slate-100 hover:border-orange-200'"
                             class="border rounded-3xl p-4 text-center cursor-pointer transition-all duration-300 shadow-sm group">
                            <div class="relative w-20 h-20 mx-auto mb-3 rounded-2xl overflow-hidden shadow-md">
                                <img src="{{ $img }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <div class="font-bold text-sm sm:text-base flex items-center justify-center gap-1">
                                <span>{{ $icon }} {{ $category->name }}</span>
                            </div>
                            <div class="text-xs opacity-80 mt-1">{{ $category->menu_items_count }} Choice Items</div>
                        </div>
                    @endforeach

                </div>

            </div>
        </section>

        <!-- ================= POPULAR MENU SECTION ================= -->
        <section id="menu" class="py-16 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                    <div>
                        <span class="text-orange-500 text-xs font-bold tracking-widest uppercase">Delicious Selections</span>
                        <h2 class="text-3xl font-black text-slate-900 mt-1">Featured Menu Items</h2>
                    </div>
                    <div class="mt-4 md:mt-0 flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500">Showing dishes from MySQL Database</span>
                    </div>
                </div>

                <!-- Dynamic Menu Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

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
                             class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col justify-between">
                            <div>
                                <div class="relative h-56 overflow-hidden bg-slate-100">
                                    <img src="{{ $item->image }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <span class="absolute top-4 left-4 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                        {{ $icon }} {{ $catName }}
                                    </span>
                                    <span class="absolute top-4 right-4 bg-white/90 backdrop-blur-md text-slate-900 text-xs font-bold px-2.5 py-1 rounded-full shadow-md flex items-center gap-1">
                                        ⭐ 4.9
                                    </span>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-1">
                                        <span>Available Now</span>
                                        <span>⏱️ 20 min</span>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 group-hover:text-orange-600 transition-colors">{{ $item->name }}</h3>
                                    <p class="text-slate-500 text-xs mt-2 line-clamp-2 leading-relaxed">{{ $item->description }}</p>
                                </div>
                            </div>
                            <div class="p-6 pt-0 flex items-center justify-between border-t border-slate-50 mt-4">
                                <div>
                                    <span class="text-xs text-slate-400 font-medium block">Price</span>
                                    <span class="text-2xl font-black text-slate-900">${{ number_format($item->price, 2) }}</span>
                                </div>
                                <button @click="cartCount++" class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 flex items-center gap-2 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span>Add to Cart</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-12 bg-white rounded-3xl border border-slate-100">
                            <p class="text-slate-500 font-medium">No menu items found in database.</p>
                        </div>
                    @endforelse

                </div>

            </div>
        </section>


        <!-- ================= FEATURES SECTION ================= -->
        <section id="features" class="py-16 bg-white border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-xl mx-auto mb-12">
                    <span class="text-orange-500 text-xs font-bold tracking-widest uppercase">How We Serve You</span>
                    <h2 class="text-3xl font-black text-slate-900 mt-1">Why Choose FoodOrder?</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- Feature 1 -->
                    <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100 text-center hover:shadow-lg transition-all">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl mx-auto mb-6 shadow-lg shadow-orange-500/30">
                            📱
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Easy Online Ordering</h3>
                        <p class="text-slate-600 text-sm mt-2 leading-relaxed">Browse through hundreds of fresh dishes, customize your order, and place it seamlessly in seconds.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100 text-center hover:shadow-lg transition-all">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl mx-auto mb-6 shadow-lg shadow-orange-500/30">
                            🚚
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Super Fast Delivery</h3>
                        <p class="text-slate-600 text-sm mt-2 leading-relaxed">Our dedicated delivery fleet ensures your food arrives hot, fresh, and on time at your exact address.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100 text-center hover:shadow-lg transition-all">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-2xl mx-auto mb-6 shadow-lg shadow-orange-500/30">
                            💳
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Flexible Payment Options</h3>
                        <p class="text-slate-600 text-sm mt-2 leading-relaxed">Pay conveniently via Cash on Delivery (COD), KBZPay, or WavePay with full transaction security.</p>
                    </div>

                </div>

            </div>
        </section>

        <!-- ================= FOOTER ================= -->
        <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
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
                        <p class="text-xs leading-relaxed text-slate-400">Bringing the finest meals from top local kitchens straight to your doorstep.</p>
                    </div>

                    <!-- Col 2 -->
                    <div>
                        <h4 class="text-white text-sm font-bold mb-4">Quick Links</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#hero" class="hover:text-white transition-colors">Home</a></li>
                            <li><a href="#categories" class="hover:text-white transition-colors">Categories</a></li>
                            <li><a href="#menu" class="hover:text-white transition-colors">Featured Menu</a></li>
                        </ul>
                    </div>

                    <!-- Col 3 -->
                    <div>
                        <h4 class="text-white text-sm font-bold mb-4">Payment Methods</h4>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-300">
                            <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">💵 Cash on Delivery</span>
                            <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">📱 KBZPay</span>
                            <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">🌊 WavePay</span>
                        </div>
                    </div>

                    <!-- Col 4 -->
                    <div>
                        <h4 class="text-white text-sm font-bold mb-4">Contact Us</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">📍 123 Main Street, Yangon, Myanmar<br />📞 +95 9 123 456 789<br />✉️ support@foodorder.com</p>
                    </div>

                </div>

                <div class="border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
                    &copy; {{ date('Y') }} FoodOrderingSystem. All rights reserved.
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
