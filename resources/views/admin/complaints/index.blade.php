<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Complaints & Help Management') }} - {{ config('app.name', 'Food Ordering System') }}</title>

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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50/60 dark:bg-slate-950 min-h-screen flex transition-colors duration-300" x-data="{ mobileMenuOpen: false }">

    <!-- Admin Sidebar Component -->
    <x-admin-sidebar active="complaints" />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Top Header Navigation Bar -->
        <header class="bg-white dark:bg-slate-900 border-b border-slate-200/80 dark:border-slate-800 sticky top-0 z-30 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div>
                    <h1 class="text-xl font-black text-slate-900 dark:text-white">{{ __('Customer Complaints & Help Center') }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Investigate customer issues, reply to tickets, and record resolutions') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-language-switcher variant="compact" />
                <a href="{{ route('home') }}" target="_blank" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 transition-all flex items-center gap-1.5">
                    <span>🌐</span>
                    <span class="hidden sm:inline">{{ __('View Store') }}</span>
                </a>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto space-y-6">

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-semibold flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Summary Statistics Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
                <!-- Total -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">{{ __('Total') }}</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white mt-1 block">{{ $stats['total'] }}</span>
                </div>

                <!-- Pending -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-300 dark:border-amber-700/60 p-4 shadow-sm">
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider block">⏳ {{ __('Pending') }}</span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ $stats['pending'] }}</span>
                </div>

                <!-- In Review -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-purple-300 dark:border-purple-700/60 p-4 shadow-sm">
                    <span class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider block">🔍 {{ __('In Review') }}</span>
                    <span class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 block">{{ $stats['in_review'] }}</span>
                </div>

                <!-- Resolved -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-emerald-300 dark:border-emerald-700/60 p-4 shadow-sm">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">✅ {{ __('Resolved') }}</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $stats['resolved'] }}</span>
                </div>

                <!-- Rejected -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-300 dark:border-rose-700/60 p-4 shadow-sm">
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider block">❌ {{ __('Rejected') }}</span>
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ $stats['rejected'] }}</span>
                </div>
            </div>

            <!-- Filter Console & Search Bar -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                <form action="{{ route('admin.complaints.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-1">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Search') }}</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Ticket #, name, order #..."
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Status') }}</label>
                        <select name="status" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="all">-- {{ __('All Statuses') }} --</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending Review</option>
                            <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>🔍 In Review</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Category') }}</label>
                        <select name="category" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="all">-- {{ __('All Categories') }} --</option>
                            <option value="order_issue" {{ request('category') === 'order_issue' ? 'selected' : '' }}>📦 Order Issue</option>
                            <option value="food_quality" {{ request('category') === 'food_quality' ? 'selected' : '' }}>🍲 Food Quality</option>
                            <option value="rider_behavior" {{ request('category') === 'rider_behavior' ? 'selected' : '' }}>🛵 Rider Delivery</option>
                            <option value="payment_issue" {{ request('category') === 'payment_issue' ? 'selected' : '' }}>💳 Payment / Refund</option>
                            <option value="app_issue" {{ request('category') === 'app_issue' ? 'selected' : '' }}>📱 App Bug</option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>💬 Other</option>
                        </select>
                    </div>

                    <!-- Filter Action Buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 py-2 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl shadow-md shadow-orange-500/20 transition-all cursor-pointer">
                            🔍 {{ __('Filter') }}
                        </button>
                        <a href="{{ route('admin.complaints.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition-all">
                            ↺ {{ __('Reset') }}
                        </a>
                    </div>

                </form>
            </div>

            <!-- Complaints Table Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 overflow-hidden">
                
                @if($complaints->isEmpty())
                    <div class="text-center py-12 px-4 space-y-2">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-2xl mx-auto">
                            🎉
                        </div>
                        <h3 class="font-black text-base text-slate-800 dark:text-slate-200">{{ __('No Complaints Found') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ request()->hasAny(['search', 'status', 'category', 'priority']) ? __('No tickets matched your filter criteria.') : __('All customer complaints have been resolved!') }}
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">{{ __('Ticket #') }}</th>
                                    <th class="py-3.5 px-4">{{ __('Customer Info') }}</th>
                                    <th class="py-3.5 px-4">{{ __('Category & Subject') }}</th>
                                    <th class="py-3.5 px-4">{{ __('Linked Order') }}</th>
                                    <th class="py-3.5 px-4">{{ __('Priority') }}</th>
                                    <th class="py-3.5 px-4">{{ __('Status') }}</th>
                                    <th class="py-3.5 px-4">{{ __('Submitted') }}</th>
                                    <th class="py-3.5 px-4 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach($complaints as $c)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $c->status === 'pending' ? 'bg-amber-50/30 dark:bg-amber-950/20' : '' }}">
                                        
                                        <!-- Ticket # -->
                                        <td class="py-4 px-4 font-mono font-bold text-orange-600 dark:text-orange-400 whitespace-nowrap">
                                            #{{ $c->ticket_number }}
                                            @if($c->attachment_photo)
                                                <span title="Has photo attachment" class="ms-1">📷</span>
                                            @endif
                                        </td>

                                        <!-- Customer Info -->
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $c->user->name ?? 'Customer' }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $c->user->email ?? '' }}</div>
                                            @if($c->user->phone ?? false)
                                                <div class="text-[10px] text-slate-400">📞 {{ $c->user->phone }}</div>
                                            @endif
                                        </td>

                                        <!-- Category & Subject -->
                                        <td class="py-4 px-4">
                                            <div class="font-bold text-slate-900 dark:text-white max-w-xs truncate">{{ $c->subject }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $c->category_label }}</div>
                                        </td>

                                        <!-- Linked Order -->
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if($c->order)
                                                <span class="font-mono font-bold text-slate-700 dark:text-slate-300">#{{ $c->order->order_number }}</span>
                                                <span class="block text-[10px] text-slate-400">{{ number_format($c->order->total_amount) }} MMK ({{ ucfirst($c->order->status) }})</span>
                                            @else
                                                <span class="text-slate-400 italic">{{ __('General') }}</span>
                                            @endif
                                        </td>

                                        <!-- Priority -->
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $c->priority_badge_color }}">
                                                {{ $c->priority }}
                                            </span>
                                        </td>

                                        <!-- Status -->
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $c->status_badge_color }}">
                                                {{ str_replace('_', ' ', $c->status) }}
                                            </span>
                                        </td>

                                        <!-- Submitted Date -->
                                        <td class="py-4 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                            {{ $c->created_at->format('M d, Y') }}
                                            <span class="block text-[10px]">{{ $c->created_at->format('h:i A') }}</span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('admin.complaints.show', $c) }}" 
                                                   class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-all shadow-sm">
                                                    🔍 {{ __('Investigate & Reply') }}
                                                </a>

                                                <form action="{{ route('admin.complaints.destroy', $c) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this complaint ticket #{{ $c->ticket_number }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="{{ __('Delete') }}" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $complaints->links() }}
                    </div>
                @endif

            </div>

        </main>

    </div>

</body>
</html>
