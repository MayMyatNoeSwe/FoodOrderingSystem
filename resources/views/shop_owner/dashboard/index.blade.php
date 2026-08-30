@extends('layouts.shop_owner')

@section('heading', '📊 Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Shop Info Banner --}}
    <div class="relative rounded-2xl overflow-hidden bg-gradient-to-r from-orange-500 to-amber-400 p-6 text-white shadow-lg">
        @if($shop->cover_image)
            <img src="{{ asset($shop->cover_image) }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-20">
        @endif
        <div class="relative flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-white/20 border-2 border-white/40 overflow-hidden flex items-center justify-center flex-shrink-0 shadow-lg">
                @if($shop->logo)
                    <img src="{{ asset($shop->logo) }}" alt="{{ $shop->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-3xl">🏪</span>
                @endif
            </div>
            <div>
                <h2 class="text-2xl font-black">{{ $shop->name }}</h2>
                @if($shop->description)
                    <p class="text-orange-100 text-sm mt-0.5">{{ $shop->description }}</p>
                @endif
                <div class="flex items-center gap-3 mt-2 text-xs text-orange-100 flex-wrap">
                    <span>📍 {{ $shop->address }}</span>
                    @if($shop->phone)
                        <span>📞 {{ $shop->phone }}</span>
                    @endif
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                        {{ $shop->status === 'active' ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-200' }}">
                        {{ ucfirst($shop->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $totalOrders    = (int)($stats->total_orders ?? 0);
            $totalRevenue   = (float)($stats->total_revenue ?? 0);
            $activeOrders   = (int)($stats->active_orders ?? 0);
            $cancelledOrders= (int)($stats->cancelled_orders ?? 0);
        @endphp

        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-3xl font-black text-orange-500">{{ number_format($totalOrders) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">Total Orders</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-3xl font-black text-emerald-600">{{ number_format($totalRevenue) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">Revenue (MMK)</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-3xl font-black text-blue-500">{{ $activeOrders }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">Active Orders</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-3xl font-black text-slate-800 dark:text-slate-200">{{ $shop->menu_items_count ?? 0 }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">Menu Items</div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('shop_owner.menu-items.index') }}"
           class="flex items-center gap-4 bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-orange-300 dark:hover:border-orange-800 transition-all group">
            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-950/40 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🍽️</div>
            <div>
                <div class="font-bold text-slate-900 dark:text-white">Manage Menu Items</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $shop->menu_items_count ?? 0 }} items in your menu</div>
            </div>
            <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>


    </div>

    {{-- Recent Orders --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-black text-slate-900 dark:text-white">Recent Orders</h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Last 10</span>
        </div>
        @if($recentOrders->isEmpty())
            <div class="p-12 text-center text-slate-400 dark:text-slate-600">
                <div class="text-5xl mb-3">📦</div>
                <div class="text-sm font-medium">No orders yet</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Order #</th>
                            <th class="px-5 py-3 text-left">Customer</th>
                            <th class="px-5 py-3 text-left">Total</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($recentOrders as $order)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-orange-600">{{ $order->order_number }}</td>
                            <td class="px-5 py-3.5 text-slate-800 dark:text-slate-200">{{ $order->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">{{ number_format($order->total_amount) }} <span class="text-xs text-slate-400">MMK</span></td>
                            <td class="px-5 py-3.5">
                                @php
                                    $statusColors = [
                                        'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                                        'confirmed' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',
                                        'preparing' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400',
                                        'delivering'=> 'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400',
                                        'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
                                        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusColors[$order->status] ?? '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">{{ $order->created_at->format('M d, H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
