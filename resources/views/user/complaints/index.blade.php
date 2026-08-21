<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Help & Complaints Center') }} - {{ config('app.name', 'Food Ordering System') }}</title>

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
<body class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50/60 dark:bg-slate-950 min-h-screen flex flex-col justify-between transition-colors duration-300">

    <!-- Storefront Navigation -->
    <x-storefront-navbar />

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

        <!-- Flash Messages with SweetAlert / Toast -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm font-semibold flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-sm font-semibold flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header Hero Banner -->
        <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-rose-500 rounded-3xl p-6 sm:p-10 text-white shadow-xl shadow-orange-500/20 mb-8 relative overflow-hidden">
            <div class="pointer-events-none absolute -top-12 -right-12 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider mb-3">
                        <span>🛡️</span>
                        <span>{{ __('Customer Support & Resolution') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black font-display tracking-tight">{{ __('Help & Complaints Center') }}</h1>
                    <p class="text-xs sm:text-sm text-orange-100 max-w-xl mt-1.5 leading-relaxed">
                        {{ __('Experiencing issues with your food, delivery rider, payment, or order? Submit a complaint ticket and our Admin team will investigate and resolve it promptly.') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('customer.complaints.create') }}" class="px-6 py-3.5 bg-white hover:bg-orange-50 text-orange-600 font-black text-sm rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                        <span>✍️</span>
                        <span>{{ __('Submit New Complaint') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center text-2xl font-black shrink-0">
                    📑
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ __('Total Tickets') }}</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $stats['total'] }}</h3>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-2xl font-black shrink-0">
                    ⏳
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ __('Under Review / Pending') }}</p>
                    <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-0.5">{{ $stats['pending'] }}</h3>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-2xl font-black shrink-0">
                    ✅
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ __('Resolved Issues') }}</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $stats['resolved'] }}</h3>
                </div>
            </div>
        </div>

        <!-- My Complaints & Support Tickets Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 mb-12">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5 mb-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ __('My Complaint Tickets') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Track investigation progress, review admin responses, and resolution status.') }}</p>
                </div>

                <a href="{{ route('customer.complaints.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl shadow-md shadow-orange-500/20 transition-all cursor-pointer">
                    <span>+</span>
                    <span>{{ __('File New Issue') }}</span>
                </a>
            </div>

            @if($complaints->isEmpty())
                <div class="text-center py-12 px-4 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto shadow-inner">
                        🛡️
                    </div>
                    <h3 class="font-black text-base text-slate-800 dark:text-slate-200">{{ __('No Complaints Filed') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                        {{ __('You have not submitted any complaints yet. If you ever face an issue with your food or delivery rider, submit a ticket here!') }}
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('customer.complaints.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition-all shadow-md">
                            ✍️ {{ __('File Your First Complaint') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">{{ __('Ticket #') }}</th>
                                <th class="py-3.5 px-4">{{ __('Category & Subject') }}</th>
                                <th class="py-3.5 px-4">{{ __('Related Order') }}</th>
                                <th class="py-3.5 px-4">{{ __('Priority') }}</th>
                                <th class="py-3.5 px-4">{{ __('Status') }}</th>
                                <th class="py-3.5 px-4">{{ __('Date') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach($complaints as $c)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-4 px-4 font-mono font-bold text-orange-600 dark:text-orange-400 whitespace-nowrap">
                                        #{{ $c->ticket_number }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-slate-900 dark:text-white max-w-xs truncate">{{ $c->subject }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $c->category_label }}</div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($c->order)
                                            <a href="{{ route('customer.orders.show', $c->order) }}" class="inline-flex items-center gap-1 font-bold text-slate-700 dark:text-slate-300 hover:text-orange-500 transition-colors">
                                                <span>📦 #{{ $c->order->order_number }}</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic">{{ __('General') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $c->priority_badge_color }}">
                                            {{ $c->priority }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $c->status_badge_color }}">
                                            {{ str_replace('_', ' ', $c->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $c->created_at->format('M d, Y • h:i A') }}
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('customer.complaints.show', $c) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-orange-50 dark:bg-slate-800 dark:hover:bg-orange-950/40 text-slate-800 dark:text-slate-200 hover:text-orange-600 font-bold rounded-xl transition-all cursor-pointer">
                                            <span>🔍</span>
                                            <span>{{ __('View Details') }}</span>
                                        </a>
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

        <!-- FAQ & Self-Service Guide -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8" x-data="{ activeFaq: null }">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-50 dark:bg-orange-950/60 border border-orange-200 dark:border-orange-800 text-orange-600 dark:text-orange-400 text-xs font-bold uppercase mb-2">
                    <span>💡</span>
                    <span>{{ __('Self-Service Guide') }}</span>
                </div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ __('Frequently Asked Questions') }}</h2>
            </div>

            <div class="space-y-3">
                <!-- FAQ 1 -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                    <button @click="activeFaq = (activeFaq === 1 ? null : 1)" class="w-full p-4 text-left font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <span>🍱 {{ __('What should I do if an item is missing or damaged?') }}</span>
                        <span x-text="activeFaq === 1 ? '▲' : '▼'" class="text-xs text-slate-400"></span>
                    </button>
                    <div x-show="activeFaq === 1" x-transition class="p-4 bg-slate-50/60 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 leading-relaxed space-y-1.5">
                        <p>{{ __('Please file a complaint ticket right away by selecting the issue category "Order Issue", choose your order, and attach a photo of your receipt or delivered food. Our team will verify and resolve it immediately.') }}</p>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                    <button @click="activeFaq = (activeFaq === 2 ? null : 2)" class="w-full p-4 text-left font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <span>🛵 {{ __('How can I communicate directly with my delivery rider?') }}</span>
                        <span x-text="activeFaq === 2 ? '▲' : '▼'" class="text-xs text-slate-400"></span>
                    </button>
                    <div x-show="activeFaq === 2" x-transition class="p-4 bg-slate-50/60 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 leading-relaxed space-y-1.5">
                        <p>{{ __('When an order is being prepared or out for delivery, visit the Order Tracking page where you can chat in real time with your rider or call them directly using the "Call Rider" button.') }}</p>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                    <button @click="activeFaq = (activeFaq === 3 ? null : 3)" class="w-full p-4 text-left font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <span>💳 {{ __('How do refunds and payment issues work?') }}</span>
                        <span x-text="activeFaq === 3 ? '▲' : '▼'" class="text-xs text-slate-400"></span>
                    </button>
                    <div x-show="activeFaq === 3" x-transition class="p-4 bg-slate-50/60 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 leading-relaxed space-y-1.5">
                        <p>{{ __('For digital payments (KBZPay / WavePay), if an order was rejected or cancelled, the store administrator will review your transaction screenshot and process the refund or store credit note.') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Storefront Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-8 text-center text-xs text-slate-500 dark:text-slate-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 space-y-2">
            <p>© {{ date('Y') }} {{ config('app.name', 'Food Ordering System') }}. All rights reserved.</p>
            <p>{{ __('Direct customer support hotline: 📞 09-959012345 • Available Daily 9:00 AM - 10:00 PM') }}</p>
        </div>
    </footer>

</body>
</html>
