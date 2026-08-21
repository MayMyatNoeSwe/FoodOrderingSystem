<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Complaint Ticket') }} #{{ $complaint->ticket_number }} - {{ config('app.name', 'Food Ordering System') }}</title>

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
<body class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50/60 dark:bg-slate-950 min-h-screen flex flex-col justify-between transition-colors duration-300" x-data="{ imgModal: false, imgSrc: '' }">

    <!-- Storefront Navigation -->
    <x-storefront-navbar />

    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

        <!-- Navigation Breadcrumbs -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
                <a href="{{ route('home') }}" class="hover:text-orange-500 transition-colors">{{ __('Home') }}</a>
                <span>/</span>
                <a href="{{ route('customer.help') }}" class="hover:text-orange-500 transition-colors">{{ __('Help & Complaints') }}</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">#{{ $complaint->ticket_number }}</span>
            </div>

            <a href="{{ route('customer.help') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-orange-600 shadow-sm transition-all">
                <span>←</span>
                <span>{{ __('Back to Help Center') }}</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm font-semibold flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Status Progress Indicator Banner -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5 mb-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono font-black text-sm px-3 py-1 bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded-full border border-orange-500/20">
                            #{{ $complaint->ticket_number }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $complaint->created_at->format('M d, Y • h:i A') }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $complaint->subject }}</h1>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase border {{ $complaint->status_badge_color }}">
                        {{ str_replace('_', ' ', $complaint->status) }}
                    </span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-black uppercase border {{ $complaint->priority_badge_color }}">
                        {{ $complaint->priority }} {{ __('Priority') }}
                    </span>
                </div>
            </div>

            <!-- Lifecycle Steps -->
            <div class="grid grid-cols-3 gap-2 sm:gap-4 text-center">
                <!-- Step 1 -->
                <div class="p-3 sm:p-4 rounded-2xl {{ in_array($complaint->status, ['pending', 'in_review', 'resolved', 'rejected']) ? 'bg-orange-500/10 border border-orange-500/30' : 'bg-slate-50 dark:bg-slate-800/40' }}">
                    <span class="text-xl">📩</span>
                    <h4 class="font-black text-xs text-slate-900 dark:text-white mt-1">{{ __('1. Submitted') }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Received by Admin') }}</p>
                </div>

                <!-- Step 2 -->
                <div class="p-3 sm:p-4 rounded-2xl {{ in_array($complaint->status, ['in_review', 'resolved', 'rejected']) ? 'bg-purple-500/10 border border-purple-500/30' : 'bg-slate-50 dark:bg-slate-800/40 opacity-50' }}">
                    <span class="text-xl">🔍</span>
                    <h4 class="font-black text-xs text-slate-900 dark:text-white mt-1">{{ __('2. In Review') }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Investigating with kitchen/rider') }}</p>
                </div>

                <!-- Step 3 -->
                <div class="p-3 sm:p-4 rounded-2xl {{ $complaint->status === 'resolved' ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-600' : ($complaint->status === 'rejected' ? 'bg-rose-500/10 border border-rose-500/30 text-rose-600' : 'bg-slate-50 dark:bg-slate-800/40 opacity-50') }}">
                    <span class="text-xl">{{ $complaint->status === 'rejected' ? '❌' : '✅' }}</span>
                    <h4 class="font-black text-xs text-slate-900 dark:text-white mt-1">{{ $complaint->status === 'rejected' ? __('3. Closed / Rejected') : __('3. Resolved') }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $complaint->resolved_at ? $complaint->resolved_at->format('M d, h:i A') : __('Awaiting resolution') }}</p>
                </div>
            </div>
        </div>

        <!-- Official Admin Resolution Note (If provided) -->
        @if($complaint->admin_response)
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-slate-900 rounded-3xl border-2 border-emerald-400 dark:border-emerald-700/80 p-6 sm:p-8 mb-8 shadow-lg">
                <div class="flex items-center gap-3.5 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-500/30 shrink-0">
                        🛡️
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-base">{{ __('Official Admin Response & Resolution') }}</h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">
                            {{ __('Resolved on') }} {{ $complaint->resolved_at ? $complaint->resolved_at->format('M d, Y • h:i A') : $complaint->updated_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-white dark:bg-slate-900 rounded-2xl border border-emerald-200/80 dark:border-slate-800 text-xs sm:text-sm text-slate-800 dark:text-slate-100 leading-relaxed font-medium whitespace-pre-line shadow-sm">
                    {{ $complaint->admin_response }}
                </div>
            </div>
        @else
            <div class="bg-amber-50 dark:bg-amber-950/30 rounded-3xl border border-amber-300 dark:border-amber-700/60 p-5 mb-8 flex items-center gap-3.5 text-xs text-amber-800 dark:text-amber-300">
                <span class="text-2xl shrink-0">⏳</span>
                <div>
                    <h4 class="font-bold text-sm">{{ __('Awaiting Admin Review') }}</h4>
                    <p class="mt-0.5 leading-relaxed">{{ __('Our store manager is reviewing your complaint and investigating with the kitchen and delivery rider. You will receive a resolution response here shortly.') }}</p>
                </div>
            </div>
        @endif

        <!-- Complaint Details & Evidence Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 mb-8">
            <h2 class="text-lg font-black text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-4">
                {{ __('Complaint Information') }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-semibold block">{{ __('Category') }}</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 mt-1 block">{{ $complaint->category_label }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-semibold block">{{ __('Related Order') }}</span>
                    @if($complaint->order)
                        <a href="{{ route('customer.orders.show', $complaint->order) }}" class="font-bold text-orange-600 dark:text-orange-400 hover:underline mt-1 block">
                            📦 #{{ $complaint->order->order_number }} • ({{ number_format($complaint->order->total_amount) }} MMK)
                        </a>
                    @else
                        <span class="text-slate-500 italic mt-1 block">{{ __('Not order-specific') }}</span>
                    @endif
                </div>
            </div>

            <!-- Problem Description -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('Your Description') }}</h3>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/70 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 text-xs sm:text-sm text-slate-800 dark:text-slate-100 leading-relaxed whitespace-pre-line">
                    {{ $complaint->description }}
                </div>
            </div>

            <!-- Attached Photo Evidence -->
            @if($complaint->attachment_photo)
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('Uploaded Photo Evidence') }}</h3>
                    <div class="inline-block relative group cursor-pointer" @click="imgSrc = '{{ asset('storage/' . $complaint->attachment_photo) }}'; imgModal = true;">
                        <img src="{{ asset('storage/' . $complaint->attachment_photo) }}" alt="Complaint Evidence" class="w-44 h-44 object-cover rounded-2xl border-2 border-slate-200 dark:border-slate-700 shadow-md group-hover:scale-105 transition-transform">
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 rounded-2xl transition-colors flex items-center justify-center text-white text-xs font-bold gap-1">
                            <span>🔍</span>
                            <span>{{ __('View Full') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Related Order Breakdown (If applicable) -->
            @if($complaint->order)
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">{{ __('Linked Order Details') }}</h3>
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                        <div class="flex justify-between items-center text-slate-700 dark:text-slate-300">
                            <span><strong>Status:</strong> {{ ucfirst($complaint->order->status) }}</span>
                            <span><strong>Total:</strong> {{ number_format($complaint->order->total_amount) }} MMK</span>
                        </div>
                        @if($complaint->order->rider)
                            <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-slate-600 dark:text-slate-400">
                                <span>🛵 <strong>Rider:</strong> {{ $complaint->order->rider->name }} (📞 {{ $complaint->order->rider->phone_number ?? $complaint->order->rider->phone ?? 'N/A' }})</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </main>

    <!-- Full Image Zoom Modal -->
    <div x-show="imgModal" x-transition class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 max-w-lg w-full relative shadow-2xl space-y-3" @click.outside="imgModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <p class="font-bold text-slate-900 dark:text-white text-sm">{{ __('Evidence Attachment') }}</p>
                <button @click="imgModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center cursor-pointer">✕</button>
            </div>
            <img :src="imgSrc" alt="Evidence" class="w-full h-auto rounded-2xl max-h-[70vh] object-contain mx-auto">
        </div>
    </div>

    <!-- Storefront Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-8 text-center text-xs text-slate-500 dark:text-slate-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 space-y-2">
            <p>© {{ date('Y') }} {{ config('app.name', 'Food Ordering System') }}. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
