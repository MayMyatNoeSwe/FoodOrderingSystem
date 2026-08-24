<x-admin-layout 
    active="dashboard" 
    title="Admin Dashboard - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Admin Kitchen Operations & Control') }}"
    subheading="{{ __('Kitchen order operations & key performance revenue summary') }}">

    <x-slot:badge>
        <span class="bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>{{ __('Live Synced') }}</span>
        </span>
    </x-slot:badge>

    <!-- QUICK BUSINESS OVERVIEW (4 KEY ESSENTIAL STATS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Stat 1: Today's Revenue -->
        <div data-reveal="fade-up" data-reveal-delay="0" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __("Today's Revenue") }}</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-base border border-emerald-100 dark:border-emerald-900">
                    💰
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 dark:text-white mt-2 truncate">
                {{ number_format($todaysRevenue) }} <span class="text-xs text-orange-600 dark:text-orange-400 font-bold">MMK</span>
            </div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-2 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>{{ __('Completed sales today') }}</span>
            </div>
        </div>

        <!-- Stat 2: Today's Orders -->
        <div data-reveal="fade-up" data-reveal-delay="80" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __("Today's Orders") }}</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-base border border-blue-100 dark:border-blue-900">
                    📦
                </div>
            </div>
            <div class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">
                {{ number_format($todaysOrdersCount) }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">{{ __('Orders') }}</span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Incoming customer orders') }}</div>
        </div>

        <!-- Stat 3: Pending Orders -->
        <div data-reveal="fade-up" data-reveal-delay="160" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Pending Orders') }}</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-base border border-amber-100 dark:border-amber-900">
                    👨‍🍳
                </div>
            </div>
            <div class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-2">
                {{ number_format($pendingOrdersCount) }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">{{ __('Active') }}</span>
            </div>
            <div class="text-xs text-amber-700 dark:text-amber-400 font-medium mt-2">{{ __('Waiting for kitchen / delivery dispatch') }}</div>
        </div>

        <!-- Stat 4: Cancellation Rate -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Cancellation Rate') }}</span>
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center font-bold text-base border border-red-100 dark:border-red-900">
                    ⚠️
                </div>
            </div>
            <div class="text-3xl font-black text-red-600 dark:text-red-400 mt-2">
                {{ $cancellationRate }}%
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Percentage of rejected/cancelled orders') }}</div>
        </div>

    </div>

    <!-- OPERATIONS & QUICK NAVIGATION HUBS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- CALL-TO-ACTION CARD FOR ORDERS DISPATCH PAGE -->
        <div class="bg-gradient-to-br from-orange-50 via-amber-50 to-white dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border border-orange-200 dark:border-slate-700 rounded-3xl p-6 flex flex-col justify-between gap-6 shadow-xs hover:shadow-md transition-all">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-orange-500/30 shrink-0">
                    ⚡
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Orders Dispatch & Operations Hub') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 text-xs mt-1 leading-relaxed">{{ __('Accept, reject with reasons, and manage kitchen order dispatching with real-time sound alarms on the Orders page.') }}</p>
                </div>
            </div>

            <a href="{{ route('admin.orders.index') }}" class="px-5 py-3 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-between gap-2 shrink-0 cursor-pointer">
                <span>{{ __('Open Orders Dispatch Page') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>

        <!-- CALL-TO-ACTION CARD FOR INVENTORY & MENU SWITCH PAGE -->
        <div class="bg-gradient-to-br from-amber-50 via-orange-50/40 to-white dark:from-slate-900 dark:via-slate-800/80 dark:to-slate-900 border border-amber-200 dark:border-slate-700 rounded-3xl p-6 flex flex-col justify-between gap-6 shadow-xs hover:shadow-md transition-all">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-amber-500/30 shrink-0">
                    📦
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Instant Inventory & Menu Switch') }}</h2>
                        <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold rounded-full">
                            {{ __('1-Click Control') }}
                        </span>
                    </div>
                    <p class="text-slate-600 dark:text-slate-300 text-xs mt-1 leading-relaxed">{{ __('Manage stock quantities, replenish low-stock dishes, and instantly toggle dish availability on the new dedicated Inventory page.') }}</p>
                </div>
            </div>

            <a href="{{ route('admin.inventory.index') }}" class="px-5 py-3 bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 active:bg-slate-950 text-white font-bold text-xs rounded-xl shadow-lg shadow-slate-900/20 transition-all flex items-center justify-between gap-2 shrink-0 cursor-pointer">
                <span>{{ __('Open Inventory & Menu Switch') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>

    </div>

</x-admin-layout>
