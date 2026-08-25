<x-admin-layout 
    active="complaints" 
    title="{{ __('Complaints & Help Management') }} - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Customer Complaints & Help Center') }}"
    subheading="{{ __('Investigate customer issues, reply to tickets, and record resolutions') }}">

    <x-slot:badge>
        @if(($stats['pending'] ?? 0) > 0)
            <span class="bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span>{{ $stats['pending'] }} {{ __('Pending Review') }}</span>
            </span>
        @else
            <span class="bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
                {{ __('All Resolved') }}
            </span>
        @endif
    </x-slot:badge>

    <div x-data="{
        columnDropdownOpen: false,
        moreFiltersOpen: {{ request()->hasAny(['priority', 'sort', 'date_from', 'date_to']) ? 'true' : 'false' }},
        cols: {
            ticket: true,
            customer: true,
            category: true,
            order: true,
            priority: true,
            status: true,
            submitted: true,
            actions: true
        },
        init() {
            const saved = localStorage.getItem('admin_complaints_cols');
            if (saved) {
                try {
                    this.cols = Object.assign(this.cols, JSON.parse(saved));
                } catch (e) {}
            }
        },
        toggleCol(key) {
            this.cols[key] = !this.cols[key];
            localStorage.setItem('admin_complaints_cols', JSON.stringify(this.cols));
        },
        setAllCols(val) {
            Object.keys(this.cols).forEach(k => this.cols[k] = val);
            localStorage.setItem('admin_complaints_cols', JSON.stringify(this.cols));
        },
        resetCols() {
            this.setAllCols(true);
        },
        getActiveColCount() {
            return Object.values(this.cols).filter(Boolean).length;
        },
        getTotalColCount() {
            return Object.keys(this.cols).length;
        }
    }" class="space-y-6">

        <!-- Summary Statistics Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
            <!-- Total -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">{{ __('Total') }}</span>
                <span class="text-2xl font-black text-slate-900 dark:text-white mt-1 block">{{ $stats['total'] }}</span>
            </div>

            <!-- Pending -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-300 dark:border-amber-700/60 p-4 shadow-xs">
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider block">⏳ {{ __('Pending') }}</span>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ $stats['pending'] }}</span>
            </div>

            <!-- In Review -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-purple-300 dark:border-purple-700/60 p-4 shadow-xs">
                <span class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider block">🔍 {{ __('In Review') }}</span>
                <span class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 block">{{ $stats['in_review'] }}</span>
            </div>

            <!-- Resolved -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-emerald-300 dark:border-emerald-700/60 p-4 shadow-xs">
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">✅ {{ __('Resolved') }}</span>
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $stats['resolved'] }}</span>
            </div>

            <!-- Rejected -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-300 dark:border-rose-700/60 p-4 shadow-xs">
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider block">❌ {{ __('Rejected') }}</span>
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ $stats['rejected'] }}</span>
            </div>
        </div>

        <!-- Filter Console & Search Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-4">
            <form action="{{ route('admin.complaints.index') }}" method="GET" class="space-y-4">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Search Details') }}</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="{{ __('Ticket #, name, email, order #...') }}"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Status Column') }}</label>
                        <select name="status" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="all">-- {{ __('All Statuses') }} --</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ {{ __('Pending Review') }}</option>
                            <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>🔍 {{ __('In Review') }}</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>✅ {{ __('Resolved') }}</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>❌ {{ __('Rejected') }}</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Category Column') }}</label>
                        <select name="category" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="all">-- {{ __('All Categories') }} --</option>
                            <option value="order_issue" {{ request('category') === 'order_issue' ? 'selected' : '' }}>📦 {{ __('Order Issue') }}</option>
                            <option value="food_quality" {{ request('category') === 'food_quality' ? 'selected' : '' }}>🍲 {{ __('Food Quality') }}</option>
                            <option value="rider_behavior" {{ request('category') === 'rider_behavior' ? 'selected' : '' }}>🛵 {{ __('Rider Delivery') }}</option>
                            <option value="payment_issue" {{ request('category') === 'payment_issue' ? 'selected' : '' }}>💳 {{ __('Payment / Refund') }}</option>
                            <option value="app_issue" {{ request('category') === 'app_issue' ? 'selected' : '' }}>📱 {{ __('App Bug') }}</option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>💬 {{ __('Other') }}</option>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Priority Column') }}</label>
                        <select name="priority" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="all">-- {{ __('All Priorities') }} --</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>🔥 {{ __('Urgent') }}</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>⚠️ {{ __('High') }}</option>
                            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>🔵 {{ __('Normal') }}</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>🟢 {{ __('Low') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Expandable More Column Filters (Sort By, Date Range) -->
                <div x-show="moreFiltersOpen" x-cloak class="pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Sort By -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Sort Order') }}</label>
                        <select name="sort" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>🕒 {{ __('Newest Submitted First') }}</option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>⏳ {{ __('Oldest Submitted First') }}</option>
                            <option value="priority_high" {{ request('sort') === 'priority_high' ? 'selected' : '' }}>⚡ {{ __('Highest Priority First') }}</option>
                            <option value="ticket_asc" {{ request('sort') === 'ticket_asc' ? 'selected' : '' }}>🔢 {{ __('Ticket Number (A-Z)') }}</option>
                            <option value="ticket_desc" {{ request('sort') === 'ticket_desc' ? 'selected' : '' }}>🔢 {{ __('Ticket Number (Z-A)') }}</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Submitted From') }}</label>
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}" 
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Submitted To') }}</label>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}" 
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>
                </div>

                <!-- Filter Actions Bar -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="moreFiltersOpen = !moreFiltersOpen" class="text-xs font-bold text-slate-500 hover:text-orange-500 dark:text-slate-400 flex items-center gap-1.5 cursor-pointer">
                        <span x-text="moreFiltersOpen ? '▲ {{ __('Hide Advanced Filters') }}' : '▼ {{ __('Show Date Range & Sorting') }}'"></span>
                    </button>

                    <div class="flex items-center gap-2">
                        @if(request()->hasAny(['search', 'status', 'category', 'priority', 'sort', 'date_from', 'date_to']))
                            <a href="{{ route('admin.complaints.index') }}" class="py-2 px-3.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1">
                                <span>✕</span>
                                <span>{{ __('Reset') }}</span>
                            </a>
                        @endif
                        <button type="submit" class="px-5 py-2 bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-orange-500/20 transition-all cursor-pointer flex items-center gap-1.5">
                            <span>🔍</span>
                            <span>{{ __('Apply Filters') }}</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Complaint Tickets Table Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-xs space-y-4">
            
            <!-- Table Header Bar with Column Visibility Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Complaints Record') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ __('Displaying tickets matching current column filter criteria') }}</p>
                </div>

                <!-- Column Visibility Filter Dropdown -->
                <div class="relative" @click.outside="columnDropdownOpen = false">
                    <button type="button" @click="columnDropdownOpen = !columnDropdownOpen"
                            class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                        </svg>
                        <span>{{ __('Columns Filter') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 font-mono text-[10px] font-black border border-orange-200 dark:border-orange-800" x-text="getActiveColCount() + '/' + getTotalColCount()"></span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Popover -->
                    <div x-show="columnDropdownOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl p-3.5 z-40 space-y-2.5">
                        
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>👁️</span>
                                <span>{{ __('Visible Columns') }}</span>
                            </span>
                            <div class="flex items-center gap-1.5 text-[10px] font-bold">
                                <button type="button" @click="setAllCols(true)" class="text-orange-600 dark:text-orange-400 hover:underline cursor-pointer">{{ __('All') }}</button>
                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                <button type="button" @click="resetCols()" class="text-slate-500 dark:text-slate-400 hover:underline cursor-pointer">{{ __('Reset') }}</button>
                            </div>
                        </div>

                        <div class="space-y-1.5 text-xs">
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.ticket" @change="toggleCol('ticket')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">🔖 {{ __('Ticket #') }}</span>
                            </label>
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.customer" @change="toggleCol('customer')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">👤 {{ __('Customer Info') }}</span>
                            </label>
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.category" @change="toggleCol('category')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">🏷️ {{ __('Category & Subject') }}</span>
                            </label>
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.order" @change="toggleCol('order')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">📦 {{ __('Linked Order') }}</span>
                            </label>
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.priority" @change="toggleCol('priority')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">⚡ {{ __('Priority Level') }}</span>
                            </label>
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.status" @change="toggleCol('status')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">📊 {{ __('Status') }}</span>
                            </label>
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.submitted" @change="toggleCol('submitted')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">📅 {{ __('Submitted Date') }}</span>
                            </label>
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                <input type="checkbox" :checked="cols.actions" @change="toggleCol('actions')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">🛠️ {{ __('Action Buttons') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($complaints->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl mb-3">
                        🎉
                    </div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">{{ __('No complaints found') }}</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">{{ __('Either no customer has filed a ticket matching your filter criteria, or all tickets are resolved.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th x-show="cols.ticket" class="py-3.5 px-4">{{ __('Ticket #') }}</th>
                                <th x-show="cols.customer" class="py-3.5 px-4">{{ __('Customer') }}</th>
                                <th x-show="cols.category" class="py-3.5 px-4">{{ __('Category & Subject') }}</th>
                                <th x-show="cols.order" class="py-3.5 px-4">{{ __('Order') }}</th>
                                <th x-show="cols.priority" class="py-3.5 px-4">{{ __('Priority') }}</th>
                                <th x-show="cols.status" class="py-3.5 px-4">{{ __('Status') }}</th>
                                <th x-show="cols.submitted" class="py-3.5 px-4">{{ __('Submitted') }}</th>
                                <th x-show="cols.actions" class="py-3.5 px-4 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($complaints as $c)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                    
                                    <!-- Ticket Number -->
                                    <td x-show="cols.ticket" class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-mono font-black text-orange-600 dark:text-orange-400">{{ $c->ticket_number }}</span>
                                    </td>

                                    <!-- Customer Profile -->
                                    <td x-show="cols.customer" class="py-4 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $c->user->name ?? $c->customer_name ?? 'Guest User' }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $c->user->email ?? '' }}</div>
                                        @if($c->user->phone ?? false)
                                            <div class="text-[10px] text-slate-400">📞 {{ $c->user->phone }}</div>
                                        @endif
                                    </td>

                                    <!-- Category & Subject -->
                                    <td x-show="cols.category" class="py-4 px-4">
                                        <div class="font-bold text-slate-900 dark:text-white max-w-xs truncate">{{ $c->subject }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $c->category_label }}</div>
                                    </td>

                                    <!-- Linked Order -->
                                    <td x-show="cols.order" class="py-4 px-4 whitespace-nowrap">
                                        @if($c->order)
                                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300">#{{ $c->order->order_number }}</span>
                                            <span class="block text-[10px] text-slate-400">{{ number_format($c->order->total_amount) }} MMK ({{ ucfirst($c->order->status) }})</span>
                                        @else
                                            <span class="text-slate-400 italic">{{ __('General') }}</span>
                                        @endif
                                    </td>

                                    <!-- Priority -->
                                    <td x-show="cols.priority" class="py-4 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $c->priority_badge_color }}">
                                            {{ $c->priority }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td x-show="cols.status" class="py-4 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $c->status_badge_color }}">
                                            {{ str_replace('_', ' ', $c->status) }}
                                        </span>
                                    </td>

                                    <!-- Submitted Date -->
                                    <td x-show="cols.submitted" class="py-4 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $c->created_at->format('M d, Y') }}
                                        <span class="block text-[10px]">{{ $c->created_at->format('h:i A') }}</span>
                                    </td>

                                    <!-- Actions -->
                                    <td x-show="cols.actions" class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.complaints.show', $c) }}" 
                                               class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-all shadow-xs">
                                                🔍 {{ __('Investigate & Reply') }}
                                            </a>

                                            <form action="{{ route('admin.complaints.destroy', $c) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this complaint ticket #{{ $c->ticket_number }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="{{ __('Delete') }}" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer">
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

    </div>

</x-admin-layout>
