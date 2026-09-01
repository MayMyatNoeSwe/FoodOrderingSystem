<x-admin-layout 
    active="rider.settlements" 
    title="Rider Settlements - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Rider Settlements & Payouts') }}"
    subheading="{{ __('Track, manage, and settle delivery fee earnings with your fleet') }}">

    <x-slot:badge>
        <span class="bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-xs">
            <span>🛵 {{ __('Financials') }}</span>
        </span>
    </x-slot:badge>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold rounded-2xl flex items-center gap-3 shadow-sm">
            <span class="text-lg">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 text-sm font-bold rounded-2xl flex items-center gap-3 shadow-sm">
            <span class="text-lg">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-6">
        @foreach($riders as $rider)
            @php
                $riderOrdersByDate = $settlements->get($rider->id, collect());
                $totalUnpaid = 0;
                $totalPaid = 0;
                
                // Pre-calculate totals for this rider
                foreach($riderOrdersByDate as $date => $orders) {
                    foreach($orders as $o) {
                        if($o->is_rider_settled) {
                            $totalPaid += $o->delivery_fee;
                        } else {
                            $totalUnpaid += $o->delivery_fee;
                        }
                    }
                }
            @endphp
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs hover:shadow-md transition-shadow">
                <!-- Rider Header -->
                <div class="bg-slate-50 dark:bg-slate-800/80 p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-black text-xl shadow-inner">
                            🛵
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $rider->name }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $rider->email }} • {{ $rider->phone ?? 'No Phone' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-right">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Unpaid (Owed to Rider)') }}</p>
                            <p class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($totalUnpaid) }} MMK</p>
                        </div>
                        <div class="hidden sm:block w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
                        <div class="hidden sm:block">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Settled (Paid out)') }}</p>
                            <p class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($totalPaid) }} MMK</p>
                        </div>
                    </div>
                </div>

                <!-- Settlement Details (Day by Day) -->
                <div class="p-5">
                    @if($riderOrdersByDate->isEmpty())
                        <div class="text-center py-6 text-slate-400">
                            <span class="text-2xl mb-2 block">🤷</span>
                            <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No completed deliveries yet.</p>
                        </div>
                    @else
                        <form action="{{ route('admin.rider.settlements.mark-paid') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                @foreach($riderOrdersByDate as $date => $orders)
                                    @php
                                        $dayTotalUnpaid = $orders->where('is_rider_settled', false)->sum('delivery_fee');
                                        $dayTotalPaid = $orders->where('is_rider_settled', true)->sum('delivery_fee');
                                    @endphp
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-4 bg-white dark:bg-slate-900">
                                        <div class="flex items-center justify-between mb-3 border-b border-slate-100 dark:border-slate-800 pb-2">
                                            <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm flex items-center gap-2">
                                                📅 {{ \Carbon\Carbon::parse($date)->format('l, M d, Y') }}
                                                <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded-md">
                                                    {{ $orders->count() }} Orders
                                                </span>
                                            </h3>
                                            <div class="text-xs font-bold">
                                                @if($dayTotalUnpaid > 0)
                                                    <span class="text-rose-600 dark:text-rose-400">Unpaid: {{ number_format($dayTotalUnpaid) }}</span>
                                                @endif
                                                @if($dayTotalPaid > 0)
                                                    <span class="text-emerald-600 dark:text-emerald-400 ml-2">Paid: {{ number_format($dayTotalPaid) }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-xs">
                                                <thead class="text-slate-500 dark:text-slate-400 font-bold uppercase">
                                                    <tr>
                                                        <th class="py-2 w-10 text-center">Settled?</th>
                                                        <th class="py-2">Order #</th>
                                                        <th class="py-2">Completed At</th>
                                                        <th class="py-2">Payment Method</th>
                                                        <th class="py-2 text-right">Delivery Fee Earned</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                                    @foreach($orders as $order)
                                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                                            <td class="py-2 text-center align-middle">
                                                                @if($order->is_rider_settled)
                                                                    <span class="text-emerald-500 font-bold" title="Settled at: {{ $order->rider_settled_at }}">✅</span>
                                                                @else
                                                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer w-4 h-4 mt-1">
                                                                @endif
                                                            </td>
                                                            <td class="py-2 font-mono font-bold text-slate-700 dark:text-slate-300">#{{ $order->order_number }}</td>
                                                            <td class="py-2 text-slate-500">{{ $order->updated_at->format('h:i A') }}</td>
                                                            <td class="py-2 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-bold">
                                                                {{ $order->payment_method }}
                                                            </td>
                                                            <td class="py-2 text-right font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                                                {{ number_format($order->delivery_fee) }} MMK
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($totalUnpaid > 0)
                                <div class="mt-4 flex justify-end">
                                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-2">
                                        <span>💸</span>
                                        <span>Mark Selected as Settled / Paid</span>
                                    </button>
                                </div>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
