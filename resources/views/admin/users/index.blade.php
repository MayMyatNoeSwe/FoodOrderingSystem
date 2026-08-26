<x-admin-layout 
    active="users" 
    title="User Accounts - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('User Accounts') }}"
    subheading="{{ __('View registered customer profiles, riders, and administrative team accounts') }}">

    <x-slot:badge>
        <span class="bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
            {{ number_format($totalUsersCount) }} {{ __('Users') }}
        </span>
    </x-slot:badge>

    <div class="space-y-6">

        <!-- Overview Stat Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Metric 1: Total Users -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total User Accounts') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-base border border-orange-100 dark:border-orange-900">
                        👥
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ number_format($totalUsersCount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>{{ __('Registered database users') }}</span>
                </div>
            </div>

            <!-- Metric 2: System Administrators -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('System Admins') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-base border border-amber-100 dark:border-amber-900">
                        👑
                    </div>
                </div>
                <div class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ number_format($adminCount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Administrative privileges') }}</div>
            </div>

            <!-- Metric 3: Shop Owners -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Shop Owners') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-base border border-orange-100 dark:border-orange-900">
                        🏪
                    </div>
                </div>
                <div class="text-3xl font-black text-orange-600 dark:text-orange-400 mt-2">{{ number_format($shopOwnerCount ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Vendor partners') }}</div>
            </div>

            <!-- Metric 4: Riders -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Delivery Riders') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-base border border-purple-100 dark:border-purple-900">
                        🛵
                    </div>
                </div>
                <div class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-2">{{ number_format($riderCount ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Fleet delivery staff') }}</div>
            </div>

            <!-- Metric 5: Customer Accounts -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Customer Accounts') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-base border border-blue-100 dark:border-blue-900">
                        🛒
                    </div>
                </div>
                <div class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ number_format($customerCount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Food ordering buyers') }}</div>
            </div>

        </div>

        <!-- Users Directory Header & Controls -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
            
            <!-- Search & Action Toolbar -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">{{ __('User Account Directory') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ __('View-only directory of all user accounts') }}</p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Search & Filter Form -->
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        
                        <div class="relative min-w-[220px]">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="{{ __('Search name or email...') }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3.5 py-2.5 pl-9 pr-8 focus:ring-0 transition-all placeholder-slate-400">
                            
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            @if($search)
                                <a href="{{ route('admin.users.index', ['role' => $role]) }}" title="{{ __('Clear Search') }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 dark:hover:text-white p-0.5 text-xs font-bold rounded-full">✕</a>
                            @endif
                        </div>

                        <!-- Role Filter Dropdown -->
                        <select name="role" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-0 transition-all cursor-pointer font-medium">
                            <option value="" {{ empty($role) ? 'selected' : '' }}>{{ __('All Roles') }}</option>
                            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>👑 {{ __('Admins') }}</option>
                            <option value="shop_owner" {{ $role === 'shop_owner' ? 'selected' : '' }}>🏪 {{ __('Shop Owners') }}</option>
                            <option value="rider" {{ $role === 'rider' ? 'selected' : '' }}>🛵 {{ __('Riders') }}</option>
                            <option value="user" {{ ($role === 'user' || $role === 'customer') ? 'selected' : '' }}>🛒 {{ __('Customers') }}</option>
                        </select>

                        <!-- Sort By Dropdown -->
                        <select name="sort_by" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-0 transition-all cursor-pointer font-medium">
                            <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                            <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                            <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="orders_desc" {{ ($sortBy ?? '') === 'orders_desc' ? 'selected' : '' }}>Orders Count</option>
                        </select>

                        @if($search || $role || ($sortBy && $sortBy !== 'latest'))
                            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-1">
                                <span>✕</span>
                                <span>{{ __('Reset') }}</span>
                            </a>
                        @endif
                    </form>

                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5">{{ __('User Details') }}</th>
                            <th class="px-4 py-3.5">{{ __('Email Address') }}</th>
                            <th class="px-4 py-3.5">{{ __('Role Access') }}</th>
                            <th class="px-4 py-3.5">{{ __('Orders Placed') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Registered Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($users as $user)
                            @php
                                $initial = strtoupper(substr($user->name, 0, 1));
                                $isAdmin = $user->is_admin;
                                $isRider = ($user->role ?? '') === 'rider';
                            @endphp

                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <!-- Profile (Name & ID) -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl font-black text-sm flex items-center justify-center border shrink-0 {{ $isAdmin ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-700' : ($isRider ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-300 dark:border-purple-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700') }}">
                                            {{ $initial }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $user->name }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">ID: #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Email -->
                                <td class="px-4 py-4 font-mono text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $user->email }}
                                </td>

                                <!-- Role Badge -->
                                <td class="px-4 py-4">
                                    @if($isAdmin)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 rounded-full font-bold text-xs">
                                            <span>👑</span>
                                            <span>{{ __('Administrator') }}</span>
                                        </span>
                                    @elseif($user->role === 'shop_owner')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800 rounded-full font-bold text-xs">
                                            <span>🏪</span>
                                            <span>{{ __('Shop Owner') }}</span>
                                            @if($user->ownedShop)
                                                <span class="text-[10px] text-orange-600 dark:text-orange-400 font-normal">({{ $user->ownedShop->name }})</span>
                                            @endif
                                        </span>
                                    @elseif($isRider)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 rounded-full font-bold text-xs">
                                            <span>🛵</span>
                                            <span>{{ __('Rider') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-full font-bold text-xs">
                                            <span>🛒</span>
                                            <span>{{ __('Customer') }}</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Orders Placed -->
                                <td class="px-4 py-4">
                                    @if($isAdmin)
                                        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[11px] font-semibold rounded-lg">
                                            — {{ __('N/A (Admin)') }}
                                        </span>
                                    @elseif($isRider)
                                        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[11px] font-semibold rounded-lg">
                                            — {{ __('N/A (Rider)') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                                            {{ $user->orders_count }} {{ __('Orders') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Registered Date -->
                                <td class="px-4 py-4 text-right text-slate-500 dark:text-slate-400 text-[11px]">
                                    <div>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">{{ $user->created_at ? $user->created_at->diffForHumans() : '' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-3">
                                        <div class="text-3xl">👥</div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ __('No Users Found') }}</div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            @if($search || $role)
                                                {{ __('No user accounts matching current filter criteria. Try clearing search keyword.') }}
                                            @else
                                                {{ __('No user accounts registered yet.') }}
                                            @endif
                                        </p>
                                        @if($search || $role)
                                            <a href="{{ route('admin.users.index') }}" class="inline-block px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-200">{{ __('Clear Search Filter') }}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination Footer -->
            @if($users->hasPages())
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    {{ $users->links() }}
                </div>
            @endif

        </div>

    </div>

</x-admin-layout>
