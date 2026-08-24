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

    <div class="space-y-6">

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
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <form action="{{ route('admin.complaints.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                
                <!-- Search Input -->
                <div class="sm:col-span-1">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Search') }}</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="{{ __('Ticket #, name, order #...') }}"
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Status') }}</label>
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
                    <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">{{ __('Category') }}</label>
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

                <!-- Filter Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-orange-500/20 transition-all cursor-pointer">
                        {{ __('Filter') }}
                    </button>
                    @if(request()->hasAny(['search', 'status', 'category']))
                        <a href="{{ route('admin.complaints.index') }}" class="py-2 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                            ✕
                        </a>
                    @endif
                </div>

            </form>
        </div>

        <!-- Complaint Tickets Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-xs">
            
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
                                <th class="py-3.5 px-4">{{ __('Ticket #') }}</th>
                                <th class="py-3.5 px-4">{{ __('Customer') }}</th>
                                <th class="py-3.5 px-4">{{ __('Category & Subject') }}</th>
                                <th class="py-3.5 px-4">{{ __('Order') }}</th>
                                <th class="py-3.5 px-4">{{ __('Priority') }}</th>
                                <th class="py-3.5 px-4">{{ __('Status') }}</th>
                                <th class="py-3.5 px-4">{{ __('Submitted') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($complaints as $c)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                    
                                    <!-- Ticket Number -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-mono font-black text-orange-600 dark:text-orange-400">{{ $c->ticket_number }}</span>
                                    </td>

                                    <!-- Customer Profile -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $c->user->name ?? $c->customer_name ?? 'Guest User' }}</div>
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
                                               class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-all shadow-xs">
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

    </div>

</x-admin-layout>
