<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Food Categories - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(form, itemName, type = 'category') {
            Swal.fire({
                title: 'Delete ' + (type === 'category' ? 'Category' : 'Food Item') + '?',
                html: `Are you sure you want to delete category <strong class="text-orange-400">'${itemName}'</strong>?<br><span class="text-xs text-slate-400 mt-1 block">This will remove it from the catalog.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: 'Yes, Delete Category',
                cancelButtonText: 'Cancel',
                background: '#0f172a',
                color: '#f8fafc',
                customClass: {
                    popup: 'border border-slate-800 rounded-3xl shadow-2xl',
                    title: 'text-white font-bold text-lg',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-red-500/20 cursor-pointer',
                    cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs cursor-pointer'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ 
          mobileMenuOpen: false,
          createModalOpen: {{ $errors->any() && !old('_method') ? 'true' : 'false' }}, 
          editModalOpen: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }}, 
          editCategoryId: {{ old('edit_category_id') ? old('edit_category_id') : 'null' }}, 
          createCategoryName: '{{ old('name') && !old('_method') ? addslashes(old('name')) : '' }}',
          editCategoryName: '{{ old('name') && old('_method') === 'PUT' ? addslashes(old('name')) : '' }}', 
          editCategoryUrl: '{{ old('edit_category_url', '') }}',
          
          slugify(text) {
              return text.toString().toLowerCase()
                  .trim()
                  .replace(/\s+/g, '-')           // Replace spaces with -
                  .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                  .replace(/\-\-+/g, '-');        // Replace multiple - with single -
          },
          openEditModal(id, name, url) {
              this.editCategoryId = id;
              this.editCategoryName = name;
              this.editCategoryUrl = url;
              this.editModalOpen = true;
          }
      }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= DESKTOP SIDEBAR ================= -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 hidden md:flex flex-col justify-between p-6 shrink-0 sticky top-0 h-screen">
            <div class="space-y-8">
                <!-- Admin Brand -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                        🍕
                    </div>
                    <div>
                        <span class="text-lg font-black text-white tracking-tight">Food<span class="text-orange-500">Order</span></span>
                        <span class="block text-[10px] text-amber-400 font-bold uppercase tracking-widest">Admin Portal</span>
                    </div>
                </a>

                <!-- Navigation Links -->
                <nav class="space-y-1.5 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Categories</span>
                        <span class="ms-auto bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $categories->total() }}</span>
                    </a>

                    <a href="{{ route('admin.menuItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Menu Items</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navMenuItemCount }}</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navOrderCount }}</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Users</span>
                        <span class="ms-auto bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full">{{ $navUserCount }}</span>
                    </a>
                </nav>
            </div>

            <!-- Admin Profile Quick Footer -->
            <div class="border-t border-slate-800 pt-4 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 font-bold text-sm shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="text-xs truncate">
                        <div class="font-bold text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="text-amber-400 font-medium">System Admin</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-red-400 transition-colors cursor-pointer rounded-lg hover:bg-slate-800">
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
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 md:hidden"
             @click="mobileMenuOpen = false"></div>

        <aside x-show="mobileMenuOpen"
               x-transition:enter="transition transform ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition transform ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 w-72 bg-slate-900 border-r border-slate-800 p-6 flex flex-col justify-between z-50 md:hidden">
            
            <div class="space-y-8">
                <!-- Header & Close -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black shadow-lg">
                            🍕
                        </div>
                        <div>
                            <span class="text-base font-black text-white">Food<span class="text-orange-500">Order</span></span>
                            <span class="block text-[9px] text-amber-400 font-bold uppercase tracking-widest">Admin Portal</span>
                        </div>
                    </a>
                    <button @click="mobileMenuOpen = false" class="text-slate-400 hover:text-white p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-2 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Categories</span>
                        <span class="ms-auto bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $categories->total() }}</span>
                    </a>

                    <a href="{{ route('admin.menuItems.index') }}" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Menu Items</span>
                    </a>

                    <a href="{{ route('admin.dashboard') }}#orders" class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition-all font-medium">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Orders</span>
                    </a>
                </nav>
            </div>

            <div class="border-t border-slate-800 pt-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="text-xs">
                        <div class="font-bold text-white">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="text-amber-400 font-medium">System Admin</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Topbar Header -->
            <header class="bg-slate-900/90 backdrop-blur-md sticky top-0 z-30 border-b border-slate-800 px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger Toggle -->
                    <button @click="mobileMenuOpen = true" class="md:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-xl font-black text-white tracking-tight flex items-center gap-2.5">
                            <span>Food Categories</span>
                            <span class="hidden sm:inline-flex bg-orange-500/20 text-orange-400 border border-orange-500/30 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                Management
                            </span>
                        </h1>
                        <p class="text-xs text-slate-400 hidden sm:block">Organize menu catalog categories and item classifications</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-all flex items-center gap-2">
                        <span>Storefront</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Main Scrollable Dashboard Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-6 overflow-y-auto">

                <!-- Success Alert Toast -->
                @if(session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: @json(session('success')),
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                                background: '#0f172a',
                                color: '#f8fafc',
                                customClass: {
                                    popup: 'border border-emerald-500/30 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                <!-- Validation Errors Banner -->
                @if($errors->any())
                    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-2xl text-red-400 text-xs font-semibold space-y-1.5 shadow-lg shadow-red-500/5">
                        <div class="flex items-center gap-2 text-red-300 font-bold mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Please fix the following validation errors:</span>
                        </div>
                        @foreach($errors->all() as $error)
                            <p class="pl-6">• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Overview Stat Metric Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    
                    <!-- Metric Card 1: Total Categories -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Categories</span>
                            <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center font-bold text-base">
                                📂
                            </div>
                        </div>
                        <div class="text-3xl font-black text-white mt-2">{{ $categories->total() }}</div>
                        <div class="text-xs text-slate-400 font-medium mt-2 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                            <span>Active food groupings</span>
                        </div>
                    </div>

                    <!-- Metric Card 2: Total Menu Items Categorized -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Linked Food Items</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-base">
                                🍕
                            </div>
                        </div>
                        <div class="text-3xl font-black text-amber-400 mt-2">
                            {{ $categories->sum('menu_items_count') }} Items
                        </div>
                        <div class="text-xs text-slate-400 font-medium mt-2">Mapped to categories</div>
                    </div>

                    <!-- Metric Card 3: Filter / Active State Summary -->
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition-all sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Search Filter Status</span>
                            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-base">
                                🔍
                            </div>
                        </div>
                        <div class="text-lg font-bold text-white mt-2 truncate">
                            @if($search)
                                Filtered: "<span class="text-orange-400">{{ $search }}</span>"
                            @else
                                Showing All Categories
                            @endif
                        </div>
                        <div class="text-xs text-slate-400 font-medium mt-2 flex items-center justify-between">
                            <span>Page {{ $categories->currentPage() }} of {{ max(1, $categories->lastPage()) }}</span>
                            @if($search)
                                <a href="{{ route('admin.categories.index') }}" class="text-orange-400 hover:text-orange-300 font-bold underline text-[11px]">Clear Filter</a>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Categories Management Header & Controls -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-6">
                    
                    <!-- Search & Action Toolbar -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-white tracking-tight">Category List</h3>
                            <p class="text-slate-400 text-xs mt-0.5">Manage category titles, slugs, and menu assignments</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <!-- Search Form -->
                            <form method="GET" action="{{ route('admin.categories.index') }}" class="relative min-w-[220px]">
                                <input type="text" 
                                       name="search" 
                                       value="{{ $search }}" 
                                       placeholder="Search category name or slug..." 
                                       class="w-full bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-200 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-500">
                                
                                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>

                                @if($search)
                                    <a href="{{ route('admin.categories.index') }}" 
                                       title="Clear Search" 
                                       class="absolute right-2.5 top-2.5 text-slate-500 hover:text-white p-0.5 text-xs font-bold rounded-full">
                                        ✕
                                    </a>
                                @endif
                            </form>

                            <!-- Add Category Trigger Button -->
                            <button @click="createModalOpen = true" 
                                    class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Add Category</span>
                            </button>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3.5 w-16">ID</th>
                                    <th class="px-4 py-3.5">Category Name</th>
                                    <th class="px-4 py-3.5">URL Slug</th>
                                    <th class="px-4 py-3.5">Items Count</th>
                                    <th class="px-4 py-3.5">Created Date</th>
                                    <th class="px-4 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                @forelse($categories as $category)
                                    @php
                                        $nameLower = strtolower($category->name);
                                        $icon = '🍽️';
                                        if (str_contains($nameLower, 'pizza')) { $icon = '🍕'; }
                                        elseif (str_contains($nameLower, 'burger') || str_contains($nameLower, 'sandwich')) { $icon = '🍔'; }
                                        elseif (str_contains($nameLower, 'noodle') || str_contains($nameLower, 'pasta') || str_contains($nameLower, 'ramen')) { $icon = '🍜'; }
                                        elseif (str_contains($nameLower, 'beverage') || str_contains($nameLower, 'drink') || str_contains($nameLower, 'coffee') || str_contains($nameLower, 'juice')) { $icon = '🍹'; }
                                        elseif (str_contains($nameLower, 'dessert') || str_contains($nameLower, 'cake') || str_contains($nameLower, 'ice cream')) { $icon = '🍰'; }
                                        elseif (str_contains($nameLower, 'rice') || str_contains($nameLower, 'asian') || str_contains($nameLower, 'bento')) { $icon = '🍱'; }
                                        elseif (str_contains($nameLower, 'chicken') || str_contains($nameLower, 'bbq') || str_contains($nameLower, 'meat')) { $icon = '🍗'; }
                                        elseif (str_contains($nameLower, 'salad') || str_contains($nameLower, 'veggie')) { $icon = '🥗'; }
                                        elseif (str_contains($nameLower, 'seafood') || str_contains($nameLower, 'fish')) { $icon = '🦐'; }
                                    @endphp

                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <!-- ID -->
                                        <td class="px-4 py-4 font-mono text-slate-500">
                                            #{{ $category->id }}
                                        </td>

                                        <!-- Name -->
                                        <td class="px-4 py-4 font-bold text-white">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 text-orange-400 flex items-center justify-center text-sm shadow-sm shrink-0">
                                                    {{ $icon }}
                                                </div>
                                                <span class="text-sm font-extrabold">{{ $category->name }}</span>
                                            </div>
                                        </td>

                                        <!-- Slug -->
                                        <td class="px-4 py-4 font-mono text-[11px]">
                                            <span class="px-2.5 py-1 bg-slate-950 rounded-md border border-slate-800 text-slate-400 font-semibold inline-block">
                                                {{ $category->slug }}
                                            </span>
                                        </td>

                                        <!-- Items Count -->
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 rounded-full border border-amber-500/20 text-[11px] font-bold inline-flex items-center gap-1.5">
                                                <span>{{ $category->menu_items_count }}</span>
                                                <span class="text-amber-500/70 font-normal">Food Items</span>
                                            </span>
                                        </td>

                                        <!-- Created Date -->
                                        <td class="px-4 py-4 text-slate-400 text-[11px]">
                                            {{ $category->created_at ? $category->created_at->format('M d, Y') : 'N/A' }}
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Edit Trigger -->
                                                <button @click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ route('admin.categories.update', $category) }}')" 
                                                        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-lg border border-slate-700 transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                    <span>✏️</span>
                                                    <span>Edit</span>
                                                </button>

                                                <!-- Delete Form -->
                                                <form method="POST" 
                                                      action="{{ route('admin.categories.destroy', $category) }}" 
                                                      onsubmit="return confirmDelete(this, '{{ addslashes($category->name) }}', 'category');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 rounded-lg transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                        <span>🗑️</span>
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                            <div class="max-w-xs mx-auto space-y-3">
                                                <div class="text-3xl">🍽️</div>
                                                <div class="font-bold text-slate-300 text-sm">No Food Categories Found</div>
                                                <p class="text-xs text-slate-500">
                                                    @if($search)
                                                        No category matching "<span class="text-orange-400">{{ $search }}</span>". Try clearing your search keyword.
                                                    @else
                                                        Get started by creating your first food category for your restaurant menu.
                                                    @endif
                                                </p>
                                                @if($search)
                                                    <a href="{{ route('admin.categories.index') }}" class="inline-block px-4 py-2 bg-slate-800 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 hover:text-white">Clear Search</a>
                                                @else
                                                    <button @click="createModalOpen = true" class="inline-block px-4 py-2 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/20">Add First Category</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Custom Pagination Footer -->
                    @if($categories->hasPages())
                        <div class="pt-2 border-t border-slate-800">
                            {{ $categories->links() }}
                        </div>
                    @endif

                </div>

            </main>
        </div>

    </div>

    <!-- ================= CREATE CATEGORY MODAL ================= -->
    <div x-show="createModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="createModalOpen = false" 
             class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center text-lg font-bold">
                        ➕
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Create New Category</h3>
                        <p class="text-slate-400 text-xs">Add a new food category to organize menu items</p>
                    </div>
                </div>
                <button @click="createModalOpen = false" class="text-slate-500 hover:text-white p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Modal Form -->
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="create_category_name" class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">
                        Category Name <span class="text-orange-500">*</span>
                    </label>
                    <input type="text" 
                           id="create_category_name" 
                           name="name" 
                           x-model="createCategoryName"
                           required 
                           autofocus
                           placeholder="e.g. Italian Pasta, Tacos, Refreshing Drinks" 
                           class="w-full bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all placeholder-slate-600">
                </div>

                <!-- Slug Preview Indicator -->
                <div class="p-3 bg-slate-950/70 rounded-xl border border-slate-800/80 space-y-1">
                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Auto-Generated URL Slug</span>
                    <div class="font-mono text-xs text-orange-400 font-bold truncate">
                        <span x-text="createCategoryName ? slugify(createCategoryName) : 'category-slug-preview'"></span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-800">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= EDIT CATEGORY MODAL ================= -->
    <div x-show="editModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.outside="editModalOpen = false" 
             class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center text-lg font-bold">
                        ✏️
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Edit Category</h3>
                        <p class="text-slate-400 text-xs">Update existing food category details</p>
                    </div>
                </div>
                <button @click="editModalOpen = false" class="text-slate-500 hover:text-white p-1 text-lg font-bold">✕</button>
            </div>

            <!-- Modal Form -->
            <form method="POST" :action="editCategoryUrl" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Hidden inputs to retain state on validation failure -->
                <input type="hidden" name="edit_category_id" :value="editCategoryId">
                <input type="hidden" name="edit_category_url" :value="editCategoryUrl">

                <div>
                    <label for="edit_category_name_input" class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">
                        Category Name <span class="text-orange-500">*</span>
                    </label>
                    <input type="text" 
                           id="edit_category_name_input" 
                           name="name" 
                           x-model="editCategoryName" 
                           required 
                           class="w-full bg-slate-950 border border-slate-800 focus:border-orange-500 text-slate-100 text-sm rounded-xl px-4 py-3 focus:ring-0 transition-all">
                </div>

                <!-- Slug Preview Indicator -->
                <div class="p-3 bg-slate-950/70 rounded-xl border border-slate-800/80 space-y-1">
                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Updated URL Slug</span>
                    <div class="font-mono text-xs text-orange-400 font-bold truncate">
                        <span x-text="editCategoryName ? slugify(editCategoryName) : 'category-slug-preview'"></span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-800">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
