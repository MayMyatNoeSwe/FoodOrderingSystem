<x-admin-layout 
    active="customers" 
    title="Customer Management - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Customer Management') }}"
    subheading="{{ __('View customer directory, monitor ordering activity, and manage account ban/active status') }}">

    <x-slot:head>
        <script>
            function confirmToggleBan(form, customerName, isCurrentlyBanned) {
                const actionText = isCurrentlyBanned ? 'Unban & Activate' : 'Ban & Suspend';
                const actionIcon = isCurrentlyBanned ? 'question' : 'warning';
                const confirmBtnColor = isCurrentlyBanned ? '#10b981' : '#ef4444';
                const infoText = isCurrentlyBanned 
                    ? 'This customer\'s account will be restored to active status and they will be allowed to log in and order food.'
                    : 'This customer will be restricted from logging in and placing any new orders.';

                Swal.fire({
                    title: actionText + ' \'' + customerName + '\'?',
                    html: `<div class="text-sm text-slate-600 space-y-2">
                            <p>Are you sure you want to <strong>${actionText}</strong> account for <span class="text-orange-600 font-bold">'${customerName}'</span>?</p>
                            <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200">${infoText}</p>
                           </div>`,
                    icon: actionIcon,
                    showCancelButton: true,
                    confirmButtonColor: confirmBtnColor,
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, ' + actionText,
                    cancelButtonText: 'Cancel',
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'border border-slate-200 rounded-3xl shadow-2xl',
                        title: 'text-slate-900 font-bold text-lg',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg cursor-pointer',
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
    </x-slot:head>

    <x-slot:badge>
        <span class="bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
            {{ number_format($totalCustomers) }} {{ __('Customers') }}
        </span>
    </x-slot:badge>

    <div class="space-y-6">

        @if(isset($errors) && $errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-2xl text-red-700 dark:text-red-400 text-xs font-semibold space-y-1 shadow-xs">
                <div class="font-bold mb-1">{{ __('Please fix the following validation errors:') }}</div>
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Overview Stat Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Metric 1: Total Registered Customers -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total Customers') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-base border border-orange-100 dark:border-orange-900">
                        👥
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ number_format($totalCustomers) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                    <span>{{ __('Registered customer accounts') }}</span>
                </div>
            </div>

            <!-- Metric 2: Active Customers -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Active Customers') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-base border border-emerald-100 dark:border-emerald-900">
                        🟢
                    </div>
                </div>
                <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($activeCustomers) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Can log in & place orders') }}</div>
            </div>

            <!-- Metric 3: Banned Customers -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Banned Accounts') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center font-bold text-base border border-red-100 dark:border-red-900">
                        🚫
                    </div>
                </div>
                <div class="text-3xl font-black text-red-600 dark:text-red-400 mt-2">{{ number_format($bannedCustomers) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Restricted / Suspended') }}</div>
            </div>

            <!-- Metric 4: Total Customer Orders -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Customer Orders') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-base border border-blue-100 dark:border-blue-900">
                        📦
                    </div>
                </div>
                <div class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ number_format($totalCustomerOrders) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Total lifetime volume') }}</div>
            </div>

        </div>

        <!-- Customers Directory Header & Controls -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
            
            <!-- Search & Filter Toolbar -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">{{ __('Customer Directory') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ __('Manage customer account access and ban/unban permissions') }}</p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Search & Filter Form -->
                    <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        
                        <div class="relative min-w-[240px]">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="{{ __('Search name, email, phone...') }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-400">
                            
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            @if($search)
                                <a href="{{ route('admin.customers.index', ['status' => $status]) }}" title="{{ __('Clear Search') }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 dark:hover:text-white p-0.5 text-xs font-bold rounded-full">✕</a>
                            @endif
                        </div>

                        <!-- Status Filter Dropdown -->
                        <select name="status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer font-medium">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('All Statuses') }}</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>🟢 {{ __('Active Only') }}</option>
                            <option value="banned" {{ $status === 'banned' ? 'selected' : '' }}>🔴 {{ __('Banned Only') }}</option>
                        </select>

                        <!-- Sort By Dropdown -->
                        <select name="sort_by" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-0 transition-all cursor-pointer font-medium">
                            <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                            <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                            <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="spent_desc" {{ ($sortBy ?? '') === 'spent_desc' ? 'selected' : '' }}>Total Spent: High to Low</option>
                            <option value="orders_desc" {{ ($sortBy ?? '') === 'orders_desc' ? 'selected' : '' }}>Orders Count: High to Low</option>
                        </select>

                        @if($search || $status !== 'all' || ($sortBy && $sortBy !== 'latest'))
                            <a href="{{ route('admin.customers.index') }}" class="px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-1">
                                <span>✕</span>
                                <span>{{ __('Reset') }}</span>
                            </a>
                        @endif
                    </form>

                </div>
            </div>

            <!-- Customers Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5">{{ __('Customer Profile') }}</th>
                            <th class="px-4 py-3.5">{{ __('Contact Details') }}</th>
                            <th class="px-4 py-3.5">{{ __('City / Location') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Account Status') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Orders Placed') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Total Spent') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($customers as $customer)
                            @php
                                $initial = strtoupper(substr($customer->name, 0, 1));
                                $isBanned = $customer->isBanned();
                            @endphp

                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors {{ $isBanned ? 'bg-red-50/30 dark:bg-red-950/20' : '' }}">
                                <!-- Profile (Name & ID) -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl font-black text-sm flex items-center justify-center border shrink-0 {{ $isBanned ? 'bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800' : 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-800' }}">
                                            {{ $initial }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-1.5">
                                                <span>{{ $customer->name }}</span>
                                                @if($isBanned)
                                                    <span class="px-1.5 py-0.5 bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 text-[10px] font-black rounded">{{ __('BANNED') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                ID: #{{ $customer->id }} • {{ __('Joined') }} {{ $customer->created_at ? $customer->created_at->format('M Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Info -->
                                <td class="px-4 py-4">
                                    <div class="space-y-0.5">
                                        <div class="font-mono text-slate-700 dark:text-slate-300 font-bold flex items-center gap-1">
                                            <span>✉️</span>
                                            <span>{{ $customer->email }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <span>📞</span>
                                            <span>{{ $customer->phone_number ?? 'No Phone Provided' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[11px] font-bold">
                                        <span>📍</span>
                                        <span>{{ $customer->city ?? 'Yangon' }}</span>
                                    </span>
                                </td>

                                <!-- Account Status (Active vs Banned) -->
                                <td class="px-4 py-4 text-center">
                                    @if($isBanned)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="px-3 py-1 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 font-extrabold rounded-full text-[11px] flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                <span>{{ __('Suspended / Banned') }}</span>
                                            </span>
                                            @if($customer->banned_at)
                                                <span class="text-[9px] text-red-600 dark:text-red-400 mt-0.5 font-medium">{{ __('Since') }} {{ $customer->banned_at->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 font-extrabold rounded-full text-[11px] inline-flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>{{ __('Active (Allowed)') }}</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Orders Count -->
                                <td class="px-4 py-4 text-center">
                                    @if($customer->orders_count > 0)
                                        <a href="{{ route('admin.orders.index', ['search' => $customer->email]) }}" 
                                           title="{{ __('View customer orders') }}"
                                           class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 font-bold rounded-lg text-xs hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors inline-block">
                                            {{ $customer->orders_count }} {{ __('Orders') }} ↗
                                        </a>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 text-xs">0 {{ __('Orders') }}</span>
                                    @endif
                                </td>

                                <!-- Total Spent -->
                                <td class="px-4 py-4 text-right font-mono">
                                    <div class="font-black text-slate-900 dark:text-white text-xs">
                                        {{ number_format($customer->orders_sum_total_amount ?? 0) }}
                                        <span class="text-[10px] text-orange-600 dark:text-orange-400 font-bold">MMK</span>
                                    </div>
                                </td>

                                <!-- Actions (Ban / Unban Toggle) -->
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.customers.toggle-status', $customer) }}" onsubmit="return confirmToggleBan(this, '{{ addslashes($customer->name) }}', {{ $isBanned ? 'true' : 'false' }});">
                                            @csrf
                                            <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                            
                                            @if($isBanned)
                                                <button type="submit" 
                                                        title="{{ __('Unban this customer') }}"
                                                        class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 active:bg-emerald-200 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 rounded-lg transition-all text-[11px] font-extrabold flex items-center gap-1 cursor-pointer shadow-xs">
                                                    <span>✅</span>
                                                    <span>{{ __('Unban') }}</span>
                                                </button>
                                            @else
                                                <button type="submit" 
                                                        title="{{ __('Ban this customer from ordering') }}"
                                                        class="px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 active:bg-rose-200 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-lg transition-all text-[11px] font-extrabold flex items-center gap-1 cursor-pointer shadow-xs">
                                                    <span>🚫</span>
                                                    <span>{{ __('Ban') }}</span>
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-3">
                                        <div class="text-3xl">👥</div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ __('No Customers Found') }}</div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            @if($search || $status !== 'all')
                                                {{ __('No customer accounts matching current search/status criteria.') }}
                                            @else
                                                {{ __('No customer accounts registered yet.') }}
                                            @endif
                                        </p>
                                        @if($search || $status !== 'all')
                                            <a href="{{ route('admin.customers.index') }}" class="inline-block px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-200">{{ __('Clear Filter') }}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination Footer -->
            @if($customers->hasPages())
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    {{ $customers->links() }}
                </div>
            @endif

        </div>

    </div>

</x-admin-layout>
