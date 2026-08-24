<x-admin-layout 
    active="complaints" 
    title="{{ __('Investigate Complaint') }} #{{ $complaint->ticket_number }} - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Investigate Complaint') }} #{{ $complaint->ticket_number }}"
    subheading="{{ __('Review customer complaint details, inspect evidence attachments, and record resolution') }}">

    <x-slot:breadcrumbs>
        <a href="{{ route('admin.complaints.index') }}" class="hover:text-orange-500 transition-colors">{{ __('Complaints') }}</a>
        <span>/</span>
        <span class="text-slate-800 dark:text-slate-200 font-mono">#{{ $complaint->ticket_number }}</span>
    </x-slot:breadcrumbs>

    <x-slot:actions>
        <a href="{{ route('admin.complaints.index') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 transition-all flex items-center gap-1.5">
            <span>←</span>
            <span>{{ __('Back to List') }}</span>
        </a>
    </x-slot:actions>

    <div x-data="{ imgModal: false, imgSrc: '' }" class="space-y-6">

        <!-- Ticket Header Summary Banner -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5 mb-5">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono font-black text-sm px-3.5 py-1 bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded-full border border-orange-500/20">
                            #{{ $complaint->ticket_number }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $complaint->created_at->format('M d, Y • h:i A') }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $complaint->subject }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">{{ $complaint->category_label }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase border {{ $complaint->status_badge_color }}">
                        {{ str_replace('_', ' ', $complaint->status) }}
                    </span>
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase border {{ $complaint->priority_badge_color }}">
                        {{ $complaint->priority }} {{ __('Priority') }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Customer Card -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-1.5">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">👤 {{ __('Customer') }}</span>
                    <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $complaint->user->name ?? 'Customer' }}</div>
                    <div class="text-slate-500 dark:text-slate-400">{{ $complaint->user->email ?? '' }}</div>
                    @if($complaint->user->phone ?? false)
                        <div class="pt-1">
                            <a href="tel:{{ $complaint->user->phone }}" class="inline-flex items-center gap-1 text-orange-600 dark:text-orange-400 font-bold hover:underline">
                                <span>📞 {{ $complaint->user->phone }}</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Related Order Card -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-1.5">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">📦 {{ __('Linked Order') }}</span>
                    @if($complaint->order)
                        <div class="font-mono font-bold text-slate-900 dark:text-white">#{{ $complaint->order->order_number }}</div>
                        <div class="text-slate-600 dark:text-slate-400">
                            <span>{{ number_format($complaint->order->total_amount) }} MMK</span> • 
                            <span class="capitalize font-semibold">{{ $complaint->order->status }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            <span>{{ $complaint->order->delivery_address }}</span>
                        </div>
                    @else
                        <div class="text-slate-500 dark:text-slate-400 italic mt-2">{{ __('General Complaint / Not tied to order') }}</div>
                    @endif
                </div>

                <!-- Resolver Card -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-1.5">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">🛡️ {{ __('Resolution Record') }}</span>
                    @if($complaint->resolved_at)
                        <div class="text-emerald-600 dark:text-emerald-400 font-bold">✓ {{ __('Resolved by') }}: {{ $complaint->resolver->name ?? 'Admin' }}</div>
                        <div class="text-slate-400 text-[11px]">{{ $complaint->resolved_at->format('M d, Y • h:i A') }}</div>
                    @else
                        <div class="text-amber-600 dark:text-amber-400 font-semibold">⏳ {{ __('Pending Resolution') }}</div>
                        <div class="text-slate-400 text-[11px]">{{ __('Awaiting response from admin') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Two Column Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Complaint Details, Message, Evidence & Order Info -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Customer Message Body -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 shadow-xs space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="text-xl">💬</span>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Customer Description') }}</h2>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl text-xs sm:text-sm text-slate-700 dark:text-slate-200 leading-relaxed font-normal whitespace-pre-line border border-slate-100 dark:border-slate-800">
                        {{ $complaint->description }}
                    </div>

                    <!-- Evidence Attachment Photos -->
                    @if(!empty($complaint->attachments) && is_array($complaint->attachments))
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">📸 {{ __('Evidence Attachments') }} ({{ count($complaint->attachments) }})</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($complaint->attachments as $att)
                                    <div class="group relative rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 aspect-video cursor-pointer"
                                         @click="imgSrc = '{{ asset($att) }}'; imgModal = true;">
                                        <img src="{{ asset($att) }}" alt="Evidence" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1">
                                            <span>🔍 {{ __('Zoom') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Linked Order Details Box (If tied to an order) -->
                @if($complaint->order)
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xl">📦</span>
                                <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Order Details') }} (#{{ $complaint->order->order_number }})</h2>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $complaint->order->status }}
                            </span>
                        </div>

                        <!-- Ordered Items List -->
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            @foreach($complaint->order->orderItems as $item)
                                <div class="py-2.5 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden shrink-0">
                                            @if($item->menuItem && $item->menuItem->image)
                                                <img src="{{ asset($item->menuItem->image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-400">🍽️</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $item->menuItem->name ?? 'Dish item' }}</div>
                                            <div class="text-slate-400 text-[11px]">{{ $item->quantity }} × {{ number_format($item->unit_price ?? $item->price ?? 0) }} MMK</div>
                                        </div>
                                    </div>
                                    <div class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ number_format(($item->quantity) * ($item->unit_price ?? $item->price ?? 0)) }} MMK
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Order Rider Info -->
                        @if($complaint->order->rider)
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-400 font-bold block">{{ __('Assigned Rider') }}</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $complaint->order->rider->name }}</span>
                                </div>
                                @if($complaint->order->rider->phone_number ?? $complaint->order->rider->phone ?? false)
                                    <a href="tel:{{ $complaint->order->rider->phone_number ?? $complaint->order->rider->phone }}" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                        <span>📞 {{ __('Call Rider') }}</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            <!-- Right Column: Admin Resolution Console -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xl p-6 sm:p-7 space-y-5 sticky top-24">
                    
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <span class="text-xl">🛠️</span>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Resolution Console') }}</h2>
                    </div>

                    <form action="{{ route('admin.complaints.update', $complaint) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Status Selector -->
                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                {{ __('Update Ticket Status') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 font-bold focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                                <option value="pending" {{ $complaint->status === 'pending' ? 'selected' : '' }}>⏳ {{ __('Pending Review') }}</option>
                                <option value="in_review" {{ $complaint->status === 'in_review' ? 'selected' : '' }}>🔍 {{ __('In Review (Investigating)') }}</option>
                                <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>✅ {{ __('Resolved (Completed)') }}</option>
                                <option value="rejected" {{ $complaint->status === 'rejected' ? 'selected' : '' }}>❌ {{ __('Rejected / Invalid') }}</option>
                            </select>
                        </div>

                        <!-- Admin Official Response Message -->
                        <div>
                            <label for="admin_response" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                {{ __('Admin Official Resolution / Reply') }}
                            </label>
                            <textarea name="admin_response" 
                                      id="admin_response" 
                                      rows="6" 
                                      maxlength="4000"
                                      placeholder="{{ __('Write an explanation, apology, refund confirmation, or kitchen investigation summary to the customer...') }}"
                                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all leading-relaxed">{{ old('admin_response', $complaint->admin_response) }}</textarea>
                            <p class="text-[11px] text-slate-400 mt-1">{{ __('This response will be visible immediately to the customer on their Help Center portal.') }}</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                                <span>💾</span>
                                <span>{{ __('Save & Send Resolution') }}</span>
                            </button>
                        </div>

                    </form>

                    <!-- Danger Zone: Delete Ticket -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400">{{ __('Permanently remove ticket') }}</span>
                        <form action="{{ route('admin.complaints.destroy', $complaint) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this complaint?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 hover:underline font-bold cursor-pointer">
                                🗑️ {{ __('Delete Ticket') }}
                            </button>
                        </form>
                    </div>

                </div>

            </div>

        </div>

        <!-- Zoom Image Modal -->
        <div x-show="imgModal" x-transition class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 max-w-2xl w-full relative shadow-2xl space-y-3" @click.outside="imgModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <p class="font-bold text-slate-900 dark:text-white text-sm">{{ __('Evidence Attachment') }}</p>
                    <button @click="imgModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center cursor-pointer">✕</button>
                </div>
                <img :src="imgSrc" alt="Evidence" class="w-full h-auto rounded-2xl max-h-[75vh] object-contain mx-auto">
            </div>
        </div>

    </div>

</x-admin-layout>
