<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Investigate Complaint') }} #{{ $complaint->ticket_number }} - {{ config('app.name', 'Food Ordering System') }}</title>

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
<body class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50/60 dark:bg-slate-950 min-h-screen flex transition-colors duration-300" x-data="{ mobileMenuOpen: false, imgModal: false, imgSrc: '' }">

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
                <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
                    <a href="{{ route('admin.complaints.index') }}" class="hover:text-orange-500 transition-colors">{{ __('Complaints') }}</a>
                    <span>/</span>
                    <span class="text-slate-800 dark:text-slate-200 font-mono">#{{ $complaint->ticket_number }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.complaints.index') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 transition-all flex items-center gap-1.5">
                    <span>←</span>
                    <span>{{ __('Back to List') }}</span>
                </a>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto space-y-6">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-semibold flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Ticket Header Summary Banner -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
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
                                <a href="tel:{{ $complaint->user->phone }}" class="inline-flex items-center gap-1 text-orange-600 font-bold hover:underline">
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
                            <div class="text-[11px] text-slate-500">
                                <span>{{ $complaint->order->delivery_address }}</span>
                            </div>
                        @else
                            <div class="text-slate-500 italic mt-2">{{ __('General Complaint / Not tied to order') }}</div>
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

            <!-- Two Column Layout: Complaint Evidence (Left) & Resolution Console (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Left Column: Customer Description & Attached Evidence & Linked Order Items -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Customer Description -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                        <h2 class="text-base font-black text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
                            {{ __('Customer Problem Description') }}
                        </h2>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/70 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 text-xs sm:text-sm text-slate-800 dark:text-slate-100 leading-relaxed whitespace-pre-line font-medium">
                            {{ $complaint->description }}
                        </div>

                        <!-- Photo Evidence -->
                        @if($complaint->attachment_photo)
                            <div class="pt-2">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('Photo Proof Uploaded by Customer') }}</h3>
                                <div class="inline-block relative group cursor-pointer" @click="imgSrc = '{{ asset('storage/' . $complaint->attachment_photo) }}'; imgModal = true;">
                                    <img src="{{ asset('storage/' . $complaint->attachment_photo) }}" alt="Proof" class="w-48 h-48 object-cover rounded-2xl border-2 border-orange-500 shadow-md group-hover:scale-105 transition-transform">
                                    <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 rounded-2xl transition-colors flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <span>🔍</span>
                                        <span>{{ __('Click to Zoom') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Linked Order Breakdown (If attached) -->
                    @if($complaint->order)
                        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h2 class="text-base font-black text-slate-900 dark:text-white">
                                    {{ __('Order #') }}{{ $complaint->order->order_number }} {{ __('Items') }}
                                </h2>
                                <a href="{{ route('admin.orders.index') }}?search={{ $complaint->order->order_number }}" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline">
                                    {{ __('View Full Order') }} →
                                </a>
                            </div>

                            <div class="space-y-2">
                                @foreach($complaint->order->orderItems as $item)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-xs">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-6 h-6 rounded-lg bg-orange-500/10 text-orange-600 font-bold flex items-center justify-center text-xs">
                                                {{ $item->quantity }}x
                                            </span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item->menuItem->name ?? 'Item' }}</span>
                                        </div>
                                        <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ number_format($item->subtotal) }} MMK</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Rider Information if Assigned -->
                            @if($complaint->order->rider)
                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="text-slate-400 font-bold block">{{ __('Assigned Rider') }}</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $complaint->order->rider->name }}</span>
                                    </div>
                                    @if($complaint->order->rider->phone_number ?? $complaint->order->rider->phone ?? false)
                                        <a href="tel:{{ $complaint->order->rider->phone_number ?? $complaint->order->rider->phone }}" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                            <span>📞 Call Rider</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                <!-- Right Column: Admin Resolution Console -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl p-6 sm:p-7 space-y-5 sticky top-24">
                        
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
                                    <option value="pending" {{ $complaint->status === 'pending' ? 'selected' : '' }}>⏳ Pending Review</option>
                                    <option value="in_review" {{ $complaint->status === 'in_review' ? 'selected' : '' }}>🔍 In Review (Investigating)</option>
                                    <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>✅ Resolved (Completed)</option>
                                    <option value="rejected" {{ $complaint->status === 'rejected' ? 'selected' : '' }}>❌ Rejected / Invalid</option>
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
                                          placeholder="Write an explanation, apology, refund confirmation, or kitchen investigation summary to the customer..."
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

        </main>

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

</body>
</html>
