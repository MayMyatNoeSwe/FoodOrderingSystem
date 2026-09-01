<x-admin-layout 
    active="orders" 
    title="Orders Dispatch & Operations Hub - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Orders Dispatch & Operations Hub') }}"
    subheading="{{ __('Live dispatch queue with instant sound alarms, kitchen operations, and payslips') }}">

    <x-slot:head>
        <script>
            function adminOrderPoller() {
                return {
                    nowMs: Date.now(),
                    detailsModalOpen: false,
                    activeOrder: null,
                    rejectModalOpen: false,
                    activeRejectOrder: null,
                    activeRejectReason: 'Out of Stock',
                    proofModalOpen: false,
                    proofModalSrc: '',
                    proofModalTitle: '',
                    columnDropdownOpen: false,
                    moreFiltersOpen: false,
                    
                    // Polling baseline & reloading state
                    initialized: false,
                    isReloading: false,
                    knownOrdersMap: {},
                    maxKnownId: 0,

                    // Column Visibility Controls
                    cols: {
                        order_date: true,
                        customer: true,
                        items: true,
                        payment: true,
                        status: true,
                        quick_action: true,
                        actions: true
                    },

                    init() {
                        const savedCols = localStorage.getItem('admin_orders_cols');
                        if (savedCols) {
                            try {
                                this.cols = Object.assign(this.cols, JSON.parse(savedCols));
                            } catch (e) {}
                        }

                        // Periodic timer for live relative countdowns
                        setInterval(() => {
                            this.nowMs = Date.now();
                        }, 1000);

                        // Initial baseline snapshot poll (no alert, no reload)
                        this.pollOrders(true);

                        // Real-time background order polling every 5 seconds
                        setInterval(() => {
                            this.pollOrders(false);
                        }, 5000);
                    },

                    toggleCol(colName) {
                        this.cols[colName] = !this.cols[colName];
                        localStorage.setItem('admin_orders_cols', JSON.stringify(this.cols));
                    },

                    setAllCols(val) {
                        for (let k in this.cols) {
                            this.cols[k] = val;
                        }
                        localStorage.setItem('admin_orders_cols', JSON.stringify(this.cols));
                    },

                    resetCols() {
                        this.cols = {
                            order_date: true,
                            customer: true,
                            items: true,
                            payment: true,
                            status: true,
                            quick_action: true,
                            actions: true
                        };
                        localStorage.removeItem('admin_orders_cols');
                    },

                    getActiveColCount() {
                        return Object.values(this.cols).filter(Boolean).length;
                    },

                    getTotalColCount() {
                        return Object.keys(this.cols).length;
                    },

                    getRemainingSeconds(isoDate) {
                        if (!isoDate) return 0;
                        const approvedTime = new Date(isoDate).getTime();
                        const elapsed = Math.floor((this.nowMs - approvedTime) / 1000);
                        const remaining = 30 - elapsed;
                        return remaining > 0 ? remaining : 0;
                    },

                    pollOrders(isInitial = false) {
                        if (this.isReloading) return;

                        fetch('{{ route('admin.orders.json_list') }}')
                            .then(res => res.json())
                            .then(data => {
                                if (!data.orders || !Array.isArray(data.orders)) return;

                                // First run: build baseline snapshot without triggering reload
                                if (isInitial || !this.initialized) {
                                    data.orders.forEach(o => {
                                        this.knownOrdersMap[o.id] = {
                                            status: o.status,
                                            payment_status: o.payment_status,
                                            rider_id: o.rider_id,
                                            updated_at: o.updated_at
                                        };
                                        if (o.id > this.maxKnownId) {
                                            this.maxKnownId = o.id;
                                        }
                                    });
                                    this.initialized = true;
                                    return;
                                }

                                // Subsequent runs: check for brand new incoming orders or status modifications
                                let hasNewOrder = false;
                                let hasStatusChange = false;

                                for (let order of data.orders) {
                                    if (order.id > this.maxKnownId) {
                                        hasNewOrder = true;
                                        break;
                                    }
                                    const prev = this.knownOrdersMap[order.id];
                                    if (prev && (prev.status !== order.status || prev.payment_status !== order.payment_status || prev.rider_id !== order.rider_id)) {
                                        hasStatusChange = true;
                                        break;
                                    }
                                }

                                if (hasNewOrder || hasStatusChange) {
                                    this.isReloading = true;
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            toast: true,
                                            position: 'top-end',
                                            icon: hasNewOrder ? 'success' : 'info',
                                            title: hasNewOrder ? '🛒 {{ __('New Order Received!') }}' : '🔄 {{ __('Orders Updated!') }}',
                                            text: hasNewOrder ? '{{ __('New customer order added to dispatch queue.') }}' : '{{ __('Dispatch queue refreshed.') }}',
                                            showConfirmButton: false,
                                            timer: 2000,
                                            timerProgressBar: true
                                        });
                                    }
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1200);
                                }
                            })
                            .catch(() => {});
                    },

                    openDetailsModal(orderData) {
                        this.activeOrder = orderData;
                        this.detailsModalOpen = true;
                    },

                    openRejectModal(id, number) {
                        this.activeRejectOrder = { id: id, number: number };
                        this.rejectModalOpen = true;
                    },

                    openProofPhoto(src, title) {
                        if (!src) return;
                        this.proofModalSrc = src;
                        this.proofModalTitle = title || 'Delivery Proof Photo (သက်သေဓာတ်ပုံ)';
                        this.proofModalOpen = true;

                        // Instant guaranteed popup via SweetAlert2 lightbox
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: `<span class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center justify-center gap-2">📸 ${this.proofModalTitle}</span>`,
                                html: `<div class="mt-2 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-950 flex items-center justify-center p-2">
                                         <img src="${src}" alt="Delivery Proof" class="max-h-[65vh] w-auto max-w-full rounded-xl object-contain shadow-lg">
                                       </div>
                                       <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-3">✓ သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိမှု အတည်ပြု ဓာတ်ပုံ (Delivery Proof Confirmed)</p>`,
                                showCloseButton: true,
                                showCancelButton: true,
                                confirmButtonText: 'Open Full Size ↗',
                                cancelButtonText: 'Close',
                                confirmButtonColor: '#059669',
                                cancelButtonColor: '#64748b',
                                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                                color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                                customClass: {
                                    popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-4 sm:p-6 max-w-2xl w-full',
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open(src, '_blank');
                                }
                            });
                        }
                    }
                };
            }

            // Expose globally for instant button access
            window.openProofPhoto = function(src, title) {
                if (!src) return;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `<span class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center justify-center gap-2">📸 ${title || 'Delivery Proof Photo'}</span>`,
                        html: `<div class="mt-2 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-950 flex items-center justify-center p-2">
                                 <img src="${src}" alt="Delivery Proof" class="max-h-[65vh] w-auto max-w-full rounded-xl object-contain shadow-lg">
                               </div>
                               <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-3">✓ သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိမှု အတည်ပြု ဓာတ်ပုံ (Delivery Proof Confirmed)</p>`,
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Open Full Size ↗',
                        cancelButtonText: 'Close',
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#64748b',
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                        customClass: {
                            popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-4 sm:p-6 max-w-2xl w-full',
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(src, '_blank');
                        }
                    });
                }
            };
        </script>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </x-slot:head>

    <x-slot:badge>
        <span class="bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 text-xs font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-xs">
            <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
            <span>{{ __('Live Dispatch Queue') }}</span>
        </span>
    </x-slot:badge>

    <div x-data="adminOrderPoller()" class="space-y-6">

        <!-- Stat Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Metric 1: Total Orders -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total Orders') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-base border border-orange-100 dark:border-orange-900">
                        📦
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ number_format($totalOrdersCount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
                    <span>{{ __('All-time customer transactions') }}</span>
                </div>
            </div>

            <!-- Metric 2: Active & Pending Orders -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Active In-Progress') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-base border border-amber-100 dark:border-amber-900">
                        ⏳
                    </div>
                </div>
                <div class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ number_format($activeCount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Pending, Preparing & Delivery') }}</div>
            </div>

            <!-- Metric 3: Completed Orders -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Completed Orders') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-base border border-emerald-100 dark:border-emerald-900">
                        ✅
                    </div>
                </div>
                <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($completedCount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Delivered & fulfilled') }}</div>
            </div>

            <!-- Metric 4: Total Sales Revenue -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md transition-all shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total Sales Revenue') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-base border border-blue-100 dark:border-blue-900">
                        💰
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 truncate">{{ number_format($totalRevenue) }} <span class="text-xs text-orange-600 dark:text-orange-400 font-bold">MMK</span></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">{{ __('Revenue generated') }}</div>
            </div>

        </div>

        <!-- Orders Management Table Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
            
            <!-- Search & Filter Controls Toolbar -->
            <div class="space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <span>{{ __('Real-Time Dispatch Queue') }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold">
                                {{ $orders->total() }} {{ __('Orders') }}
                            </span>
                        </h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ __('Filter by column values, toggle visible table columns, and manage orders') }}</p>
                    </div>

                    <!-- Right Controls: Column Filter Dropdown & Quick Search -->
                    <div class="flex items-center flex-wrap gap-2.5">

                        <!-- Column Visibility Filter Dropdown -->
                        <div class="relative" @click.outside="columnDropdownOpen = false">
                            <button type="button" @click="columnDropdownOpen = !columnDropdownOpen"
                                    class="px-3.5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                                </svg>
                                <span>{{ __('Columns Filter') }}</span>
                                <span class="px-1.5 py-0.2 rounded-full bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 font-mono text-[10px] font-black border border-orange-200 dark:border-orange-800" x-text="getActiveColCount() + '/' + getTotalColCount()"></span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Popover -->
                            <div x-show="columnDropdownOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl p-3.5 z-40 space-y-2.5">
                                
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                                    <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <span>👁️</span>
                                        <span>{{ __('Visible Columns') }}</span>
                                    </span>
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold">
                                        <button type="button" @click="setAllCols(true)" class="text-orange-600 dark:text-orange-400 hover:underline cursor-pointer">{{ __('All') }}</button>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <button type="button" @click="resetCols()" class="text-slate-500 dark:text-slate-400 hover:underline cursor-pointer">{{ __('Reset') }}</button>
                                    </div>
                                </div>

                                <div class="space-y-1.5 text-xs">
                                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                        <input type="checkbox" :checked="cols.order_date" @change="toggleCol('order_date')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">🔖 {{ __('Order # / Date') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                        <input type="checkbox" :checked="cols.customer" @change="toggleCol('customer')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">👤 {{ __('Customer Info') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                        <input type="checkbox" :checked="cols.items" @change="toggleCol('items')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">🍽️ {{ __('Items Ordered') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                        <input type="checkbox" :checked="cols.payment" @change="toggleCol('payment')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">💰 {{ __('Total & Payment') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                        <input type="checkbox" :checked="cols.status" @change="toggleCol('status')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">📊 {{ __('Order Status & Dispatch') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                        <input type="checkbox" :checked="cols.quick_action" @change="toggleCol('quick_action')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">⚡ {{ __('Quick Action (1-Click)') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 cursor-pointer select-none">
                                        <input type="checkbox" :checked="cols.actions" @change="toggleCol('actions')" class="rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-0">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">🛠️ {{ __('Actions & Payslip') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Toggle More Filters Expand Button -->
                        <button type="button" @click="moreFiltersOpen = !moreFiltersOpen"
                                class="px-3.5 py-2.5 text-xs font-bold rounded-xl border transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
                                :class="moreFiltersOpen ? 'bg-orange-50 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800' : 'bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span x-text="moreFiltersOpen ? '{{ __('Hide Filter Bar') }}' : '{{ __('Filter By Columns') }}'"></span>
                        </button>

                    </div>
                </div>

                <!-- Comprehensive Column Filter Form -->
                <form method="GET" action="{{ route('admin.orders.index') }}" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-2.5">
                        
                        <!-- Search Column Filter (Order #, Customer, Phone, Address) -->
                        <div class="relative xl:col-span-2">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="{{ __('Search Order #, customer, phone...') }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3.5 py-2.5 pl-9 focus:ring-0 transition-all placeholder-slate-400 shadow-xs">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <!-- Status Column Filter -->
                        <select name="status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2.5 focus:ring-0 transition-all cursor-pointer shadow-xs">
                            <option value="">{{ __('Status: All') }}</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ {{ __('Pending') }}</option>
                            <option value="preparing" {{ $status === 'preparing' ? 'selected' : '' }}>👨‍🍳 {{ __('Preparing') }}</option>
                            <option value="delivering" {{ $status === 'delivering' ? 'selected' : '' }}>🛵 {{ __('Delivering') }}</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>✅ {{ __('Completed') }}</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>❌ {{ __('Cancelled') }}</option>
                        </select>

                        <!-- Payment Method Column Filter -->
                        <select name="payment_method" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2.5 focus:ring-0 transition-all cursor-pointer shadow-xs">
                            <option value="">{{ __('Method: All') }}</option>
                            <option value="cod" {{ $paymentMethod === 'cod' ? 'selected' : '' }}>💵 {{ __('Cash on Delivery') }}</option>
                            <option value="kbzpay" {{ $paymentMethod === 'kbzpay' ? 'selected' : '' }}>📱 {{ __('KBZPay') }}</option>
                            <option value="wavepay" {{ $paymentMethod === 'wavepay' ? 'selected' : '' }}>🌊 {{ __('WavePay') }}</option>
                        </select>

                        <!-- Payment Status Column Filter -->
                        <select name="payment_status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2.5 focus:ring-0 transition-all cursor-pointer shadow-xs">
                            <option value="">{{ __('Payment: All') }}</option>
                            <option value="paid" {{ ($paymentStatus ?? '') === 'paid' ? 'selected' : '' }}>✓ {{ __('Paid') }}</option>
                            <option value="unpaid" {{ ($paymentStatus ?? '') === 'unpaid' ? 'selected' : '' }}>⏳ {{ __('Unpaid') }}</option>
                            <option value="pending_verification" {{ ($paymentStatus ?? '') === 'pending_verification' ? 'selected' : '' }}>🔍 {{ __('Verification') }}</option>
                        </select>

                        <!-- Sort By Column Filter -->
                        <select name="sort_by" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-orange-500 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2.5 focus:ring-0 transition-all cursor-pointer shadow-xs">
                            <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>{{ __('Sort: Newest First') }}</option>
                            <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>{{ __('Sort: Oldest First') }}</option>
                            <option value="amount_high" {{ ($sortBy ?? '') === 'amount_high' ? 'selected' : '' }}>{{ __('Sort: Highest Price') }}</option>
                            <option value="amount_low" {{ ($sortBy ?? '') === 'amount_low' ? 'selected' : '' }}>{{ __('Sort: Lowest Price') }}</option>
                        </select>

                    </div>

                    <!-- Additional Expandable Column Filters (Rider, Date Range & Shop) -->
                    <div x-show="moreFiltersOpen || '{{ $riderId ?? '' }}' !== '' || '{{ $dateRange ?? '' }}' !== '' || ('{{ $shopId ?? '' }}' !== '' && '{{ $shopId ?? '' }}' !== 'all')" 
                         x-cloak
                         class="p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl grid grid-cols-1 sm:grid-cols-4 gap-2.5 text-xs">
                        
                        <!-- Shop Column Filter -->
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">🏪 {{ __('Filter by Shop') }}</label>
                            <select name="shop_id" onchange="this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-0 cursor-pointer">
                                <option value="all">{{ __('All Shops') }}</option>
                                @foreach($shops as $s)
                                    <option value="{{ $s->id }}" {{ ($shopId ?? '') == $s->id ? 'selected' : '' }}>🏪 {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rider Column Filter -->
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">🛵 {{ __('Filter by Assigned Rider') }}</label>
                            <select name="rider_id" onchange="this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-0 cursor-pointer">
                                <option value="">{{ __('All Riders') }}</option>
                                <option value="unassigned" {{ ($riderId ?? '') === 'unassigned' ? 'selected' : '' }}>⚠️ {{ __('Unassigned Orders Only') }}</option>
                                @foreach($riders as $r)
                                    <option value="{{ $r->id }}" {{ ($riderId ?? '') == $r->id ? 'selected' : '' }}>🛵 {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range Column Filter -->
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">📅 {{ __('Filter by Date Range') }}</label>
                            <select name="date_range" onchange="this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-0 cursor-pointer">
                                <option value="">{{ __('All Time') }}</option>
                                <option value="today" {{ ($dateRange ?? '') === 'today' ? 'selected' : '' }}>📅 {{ __('Today') }}</option>
                                <option value="yesterday" {{ ($dateRange ?? '') === 'yesterday' ? 'selected' : '' }}>📅 {{ __('Yesterday') }}</option>
                                <option value="this_week" {{ ($dateRange ?? '') === 'this_week' ? 'selected' : '' }}>📅 {{ __('This Week') }}</option>
                                <option value="this_month" {{ ($dateRange ?? '') === 'this_month' ? 'selected' : '' }}>📅 {{ __('This Month') }}</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-1 cursor-pointer">
                                <span>🔍 {{ __('Apply') }}</span>
                            </button>
                            @if($search || $status || $paymentMethod || ($paymentStatus ?? null) || ($riderId ?? null) || ($dateRange ?? null) || (($shopId ?? null) && $shopId !== 'all'))
                                <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold rounded-xl transition-all flex items-center justify-center gap-1">
                                    <span>✕</span>
                                </a>
                            @endif
                        </div>

                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800 select-none">
                        <tr>
                            <th x-show="cols.order_date" class="px-4 py-3.5">{{ __('Order # / Date') }}</th>
                            <th x-show="cols.customer" class="px-4 py-3.5">{{ __('Customer Info') }}</th>
                            <th x-show="cols.items" class="px-4 py-3.5">{{ __('Items Ordered') }}</th>
                            <th x-show="cols.payment" class="px-4 py-3.5">{{ __('Total & Payment') }}</th>
                            <th x-show="cols.status" class="px-4 py-3.5">{{ __('Order Status & Dispatch') }}</th>
                            <th x-show="cols.quick_action" class="px-4 py-3.5 text-center">{{ __('Quick Action (1-Click)') }}</th>
                            <th x-show="cols.actions" class="px-4 py-3.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($orders as $order)
                            @php
                                // Status badge colors
                                $statusClass = 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
                                $statusLabel = ucfirst($order->status);
                                $statusIcon = '📦';

                                if ($order->status === 'pending') {
                                    $statusClass = 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800';
                                    $statusIcon = '⏳';
                                } elseif ($order->status === 'preparing') {
                                    $statusClass = 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800';
                                    $statusIcon = '👨‍🍳';
                                } elseif ($order->status === 'delivering') {
                                    $statusClass = 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800';
                                    $statusIcon = '🛵';
                                } elseif ($order->status === 'completed') {
                                    $statusClass = 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
                                    $statusIcon = '✅';
                                } elseif ($order->status === 'cancelled') {
                                    $statusClass = 'bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800';
                                    $statusIcon = '❌';
                                }

                                // Payment Method formatting
                                $pmLabel = strtoupper($order->payment_method);
                                $pmIcon = '💳';
                                if ($order->payment_method === 'cod') { $pmIcon = '💵'; $pmLabel = 'Cash on Delivery'; }
                                elseif ($order->payment_method === 'kbzpay') { $pmIcon = '📱'; $pmLabel = 'KBZPay'; }
                                elseif ($order->payment_method === 'wavepay') { $pmIcon = '🌊'; $pmLabel = 'WavePay'; }

                                $isNewPending = ($order->status === 'pending');
                            @endphp

                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors {{ $isNewPending ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}">
                                
                                <!-- Order # & Date -->
                                <td x-show="cols.order_date" class="px-4 py-4">
                                    <div class="font-mono text-sm font-black text-orange-600 dark:text-orange-400 flex items-center gap-2">
                                        <span>#{{ $order->order_number }}</span>
                                        @if($isNewPending)
                                            <span class="px-1.5 py-0.5 bg-amber-500 text-white font-black text-[9px] uppercase rounded shadow-xs">{{ __('NEW') }}</span>
                                        @endif
                                    </div>
                                    @if($order->shop)
                                        <div class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-orange-50 dark:bg-orange-950/50 border border-orange-200/80 dark:border-orange-800/60 rounded text-[10px] font-extrabold text-orange-700 dark:text-orange-300 mt-1">
                                            <span>🏪</span>
                                            <span>{{ $order->shop->name }}</span>
                                        </div>
                                    @endif
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                        {{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : 'N/A' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">
                                        {{ $order->created_at ? $order->created_at->diffForHumans() : '' }}
                                    </div>
                                </td>

                                <!-- Customer Info -->
                                <td x-show="cols.customer" class="px-4 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">
                                        {{ $order->user ? $order->user->name : 'Guest Customer' }}
                                    </div>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                        <span>📞</span>
                                        <span>{{ $order->delivery_phone }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-[200px] mt-0.5" title="{{ $order->delivery_address }}">
                                        📍 {{ $order->delivery_address }}
                                    </div>
                                </td>

                                <!-- Items Ordered -->
                                <td x-show="cols.items" class="px-4 py-4">
                                    <div class="space-y-1.5 min-w-[220px]">
                                        <div class="flex items-center justify-between">
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-[10px] font-bold text-slate-700 dark:text-slate-300 inline-block">
                                                {{ $order->orderItems->sum('quantity') }} {{ __('items') }}
                                            </span>
                                            <a href="{{ route('admin.orderItems.index', ['search' => $order->order_number]) }}" class="text-[10px] font-bold text-orange-600 dark:text-orange-400 hover:underline">
                                                {{ __('Table View') }} &rarr;
                                            </a>
                                        </div>
                                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-slate-50/80 dark:bg-slate-800/60 divide-y divide-slate-200 dark:divide-slate-700">
                                            @foreach($order->orderItems->take(2) as $item)
                                                <div class="p-1.5 flex items-center justify-between text-[11px]">
                                                    <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[130px]">{{ $item->menuItem->name ?? 'Dish' }}</span>
                                                    <span class="text-slate-500 dark:text-slate-400 font-mono">x{{ $item->quantity }}</span>
                                                </div>
                                            @endforeach
                                            @if($order->orderItems->count() > 2)
                                                <div class="p-1 text-center text-[10px] text-slate-500 dark:text-slate-400 font-medium bg-slate-100 dark:bg-slate-800">
                                                    +{{ $order->orderItems->count() - 2 }} {{ __('more dishes') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Total & Payment -->
                                <td x-show="cols.payment" class="px-4 py-4">
                                    <div class="text-sm font-black text-slate-900 dark:text-white">
                                        {{ number_format($order->total_amount) }} <span class="text-[10px] text-orange-600 dark:text-orange-400 font-bold">MMK</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 space-y-0.5 max-w-[120px]">
                                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-800 pb-0.5">
                                            <span>{{ __('Commission') }}:</span>
                                            <span class="font-semibold">{{ number_format($order->commission_amount) }}</span>
                                        </div>
                                        <div class="flex justify-between pt-0.5">
                                            <span>{{ __('Shop Earn') }}:</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($order->shop_earning) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-2">
                                        <span class="text-xs">{{ $pmIcon }}</span>
                                        <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">{{ $pmLabel }}</span>
                                    </div>
                                    <div class="mt-1">
                                        @if($order->payment_status === 'paid')
                                            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold rounded-full">
                                                ✓ {{ __('Paid') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-bold rounded-full">
                                                ⏳ {{ __('Unpaid') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Order Status & Rider Dispatch Column -->
                                <td x-show="cols.status" class="px-4 py-4 space-y-2.5 min-w-[220px]">
                                    <!-- Status Selector Form -->
                                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="block">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="relative">
                                            <select name="status" 
                                                    onchange="this.form.submit()" 
                                                    class="w-full text-xs font-bold px-3 py-1.5 rounded-xl border {{ $statusClass }} focus:ring-0 cursor-pointer appearance-none pr-7">
                                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ {{ __('Pending') }}</option>
                                                <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>👨‍🍳 {{ __('Preparing') }}</option>
                                                <option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>🛵 {{ __('Delivering') }}</option>
                                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>✅ {{ __('Completed') }}</option>
                                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ {{ __('Cancelled') }}</option>
                                            </select>
                                            <div class="pointer-events-none absolute right-2 top-2.5 text-slate-500 text-[10px]">
                                                ▼
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Rider Assignment & 30-Second Countdown Pool State -->
                                    @if(in_array($order->status, ['confirmed', 'preparing']))
                                        @if(!$order->rider_id)
                                            @php
                                                $orderApprovedTimestamp = $order->updated_at ? $order->updated_at->toISOString() : $order->created_at->toISOString();
                                            @endphp
                                            <div class="space-y-1.5 p-2.5 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 rounded-xl">
                                                <!-- Live 30s Countdown Condition -->
                                                <div x-show="getRemainingSeconds('{{ $orderApprovedTimestamp }}') > 0" class="flex items-center gap-1.5 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                    <span>{{ __('Waiting Rider') }} (<span class="font-mono font-black" x-text="getRemainingSeconds('{{ $orderApprovedTimestamp }}')"></span>s)</span>
                                                </div>

                                                <div x-show="getRemainingSeconds('{{ $orderApprovedTimestamp }}') === 0" class="space-y-1">
                                                    <div class="flex items-center gap-1.5 text-[10px] font-black text-red-600 dark:text-red-400 animate-pulse">
                                                        <span>⚠️</span>
                                                        <span>{{ __('30s Elapsed! No Rider Yet') }}</span>
                                                    </div>
                                                    <p class="text-[9px] text-slate-500 dark:text-slate-400 font-semibold">{{ __('Assign rider manually:') }}</p>
                                                </div>

                                                <form method="POST" action="{{ route('admin.orders.assignRider', $order) }}" class="block">
                                                    @csrf
                                                    <div class="relative">
                                                        <select name="rider_id" onchange="this.form.submit()" 
                                                                class="w-full text-[11px] font-bold px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-amber-300 dark:border-amber-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-orange-500 cursor-pointer appearance-none pr-5">
                                                            <option value="">🛵 {{ __('Select Rider...') }}</option>
                                                            @foreach($riders as $riderItem)
                                                                <option value="{{ $riderItem->id }}">
                                                                    🛵 {{ $riderItem->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="pointer-events-none absolute right-2 top-1.5 text-slate-500 text-[9px]">
                                                            ▼
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @else
                                            <div class="p-2 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center justify-between gap-1">
                                                <div class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1">
                                                    <span>🛵</span>
                                                    <span>{{ $order->rider->name }}</span>
                                                </div>
                                                <!-- Re-assign / change rider form -->
                                                <form method="POST" action="{{ route('admin.orders.assignRider', $order) }}">
                                                    @csrf
                                                    <select name="rider_id" onchange="this.form.submit()" class="text-[10px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1 py-0.5 font-semibold text-slate-600 dark:text-slate-300 cursor-pointer">
                                                        <option value="{{ $order->rider_id }}">{{ __('Assigned') }}</option>
                                                        <option value="">✕ {{ __('Unassign') }}</option>
                                                        @foreach($riders as $riderItem)
                                                            @if($riderItem->id != $order->rider_id)
                                                                <option value="{{ $riderItem->id }}">🛵 {{ $riderItem->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </div>
                                        @endif
                                    @elseif($order->status === 'delivering' && $order->rider)
                                        <div class="text-[11px] font-bold text-purple-700 dark:text-purple-300 flex items-center gap-1.5 px-2.5 py-1.5 bg-purple-50 dark:bg-purple-950/50 rounded-xl border border-purple-200 dark:border-purple-800">
                                            <span>🛵</span> <span>{{ $order->rider->name }} ({{ __('Delivering') }})</span>
                                        </div>
                                    @elseif($order->rider)
                                        <div class="text-[11px] font-bold text-slate-600 dark:text-slate-400 flex items-center gap-1 px-2 py-1">
                                            <span>🛵</span> <span>{{ $order->rider->name }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- 1-Click Accept / Reject Action Column -->
                                <td x-show="cols.quick_action" class="px-4 py-4 text-center">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        @if($order->status === 'pending')
                                            <!-- Accept Form -->
                                            <form method="POST" action="{{ route('admin.orders.accept', $order) }}" class="w-full max-w-[120px]">
                                                @csrf
                                                <button type="submit" class="w-full px-2.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-bold text-[11px] rounded-lg shadow-md shadow-emerald-500/20 transition-all flex items-center justify-center gap-1 cursor-pointer"
                                                        title="{{ in_array($order->payment_method, ['kbzpay', 'wavepay']) ? 'Approve Payment Slip & Generate PAID Digital Slip' : 'Confirm Cash on Delivery Order' }}">
                                                    <span>✓</span>
                                                    <span>{{ in_array($order->payment_method, ['kbzpay', 'wavepay']) ? __('Approve & Paid') : __('Accept COD') }}</span>
                                                </button>
                                            </form>

                                            <!-- Reject Button -->
                                            <button @click="openRejectModal({{ $order->id }}, '{{ $order->order_number }}')" 
                                                    class="w-full max-w-[90px] px-3 py-1.5 bg-red-50 dark:bg-red-950/50 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 font-bold text-[11px] rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                 <span>✕</span>
                                                <span>{{ __('Reject') }}</span>
                                            </button>
                                        @elseif($order->status === 'preparing')
                                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="w-full max-w-[90px]">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="delivering">
                                                <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                                <button type="submit" class="w-full px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white font-bold text-[11px] rounded-lg shadow-lg shadow-purple-500/20 transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                    <span>🛵 {{ __('Dispatch') }}</span>
                                                </button>
                                            </form>
                                        @elseif($order->status === 'delivering')
                                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="w-full max-w-[90px]">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="completed">
                                                <input type="hidden" name="payment_status" value="paid">
                                                <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                                <button type="submit" class="w-full px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[11px] rounded-lg shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                    <span>✅ {{ __('Complete') }}</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 text-[11px] font-medium">-</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td x-show="cols.actions" class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Direct Payslip Photo Button (If available) -->
                                        @if($order->payment_screenshot)
                                            <button type="button"
                                                    @click.stop="openProofPhoto('{{ asset($order->payment_screenshot) }}', 'Order #{{ $order->order_number }} - Payment Payslip (ငွေလွှဲပြေစာ)')"
                                                    onclick="window.openProofPhoto && window.openProofPhoto('{{ asset($order->payment_screenshot) }}', 'Order #{{ $order->order_number }} - Payment Payslip (ငွေလွှဲပြေစာ)')"
                                                    title="{{ __('View Payment Screenshot') }}"
                                                    class="px-2.5 py-1.5 bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-xs font-bold rounded-xl border border-blue-200 dark:border-blue-800 transition-all flex items-center gap-1 cursor-pointer shadow-xs active:scale-95">
                                                <span>📱</span>
                                                <span class="hidden sm:inline text-[11px]">{{ __('Payslip') }}</span>
                                            </button>
                                        @endif

                                        <!-- Direct Proof Photo Button (If available) -->
                                        @if($order->delivery_proof_photo)
                                            <button type="button"
                                                    @click.stop="openProofPhoto('{{ asset($order->delivery_proof_photo) }}', 'Order #{{ $order->order_number }}')"
                                                    onclick="window.openProofPhoto && window.openProofPhoto('{{ asset($order->delivery_proof_photo) }}', 'Order #{{ $order->order_number }}')"
                                                    title="{{ __('View Delivery Proof Photo') }}"
                                                    class="px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs font-bold rounded-xl border border-emerald-200 dark:border-emerald-800 transition-all flex items-center gap-1 cursor-pointer shadow-xs active:scale-95">
                                                <span>📸</span>
                                                <span class="hidden sm:inline text-[11px]">{{ __('Proof') }}</span>
                                            </button>
                                        @endif

                                        <!-- FoodOrder Payslip & Tax Invoice Button (Only after approved) -->
                                        @if($order->status !== 'pending')
                                            <a href="{{ route('orders.payslip', $order) }}" target="_blank" title="{{ __('View & Print Official Payslip') }}"
                                               class="px-2.5 py-1.5 bg-pink-50 dark:bg-pink-950/50 hover:bg-pink-100 dark:hover:bg-pink-900/50 text-[#D70F64] text-xs font-bold rounded-xl border border-pink-200 dark:border-pink-800 transition-all flex items-center gap-1 cursor-pointer shadow-xs">
                                                <span>🧾</span>
                                                <span class="hidden xl:inline text-[11px]">{{ __('Payslip') }}</span>
                                            </a>
                                        @endif

                                        <!-- View Details Button -->
                                        @php
                                            $subtotalVal = $order->orderItems->sum('subtotal');
                                            $taxVal = $order->tax_amount > 0 ? $order->tax_amount : round($subtotalVal * 0.05);
                                        @endphp
                                        <button @click="openDetailsModal({{ json_encode([
                                                    'id' => $order->id,
                                                    'order_number' => $order->order_number,
                                                    'customer_name' => $order->user ? $order->user->name : 'Guest',
                                                    'customer_email' => $order->user ? $order->user->email : 'N/A',
                                                    'rider_name' => $order->rider ? $order->rider->name : 'Unassigned',
                                                    'rider_email' => $order->rider ? $order->rider->email : null,
                                                    'delivery_phone' => $order->delivery_phone,
                                                    'delivery_address' => $order->delivery_address,
                                                    'subtotal' => number_format($subtotalVal),
                                                    'delivery_fee' => number_format($order->delivery_fee),
                                                    'tax_amount' => number_format($taxVal),
                                                    'total_amount' => number_format($order->total_amount),
                                                    'payment_method' => $order->payment_method,
                                                    'payment_status' => $order->payment_status,
                                                    'payment_screenshot' => $order->payment_screenshot ? asset($order->payment_screenshot) : null,
                                                    'delivery_proof_photo' => $order->delivery_proof_photo ? asset($order->delivery_proof_photo) : null,
                                                    'status' => $order->status,
                                                    'notes' => $order->notes ?? 'No notes provided',
                                                    'created_at' => $order->created_at ? $order->created_at->format('M d, Y • h:i A') : 'N/A',
                                                    'payslip_url' => route('orders.payslip', $order),
                                                    'items' => $order->orderItems->map(function($i) {
                                                        $unitPrice = $i->unit_price ?? ($i->menuItem ? $i->menuItem->price : 0);
                                                        $itemSubtotal = $i->subtotal ?? ($unitPrice * $i->quantity);
                                                        return [
                                                            'name' => $i->menuItem ? $i->menuItem->name : 'Dish Item',
                                                            'quantity' => $i->quantity,
                                                            'price' => number_format($unitPrice),
                                                            'subtotal' => number_format($itemSubtotal),
                                                            'image' => $i->menuItem ? $i->menuItem->image_url : null
                                                        ];
                                                    })
                                                ]) }})"
                                                class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span>{{ __('Details') }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td :colspan="getActiveColCount()" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                        📦
                                    </div>
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ __('No orders found') }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Try resetting search keywords or status filters') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $orders->links() }}
            </div>

        </div>

        <!-- ================= RECEIPT & ORDER DETAILS MODAL ================= -->
        <div x-show="detailsModalOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            
            <div @click.outside="detailsModalOpen = false" 
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-lg border border-orange-100 dark:border-orange-900">
                            🧾
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Order Receipt Details') }}</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs" x-text="activeOrder ? 'Order #' + activeOrder.order_number + ' • ' + activeOrder.created_at : ''"></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <template x-if="activeOrder && activeOrder.status !== 'pending'">
                            <a :href="activeOrder.payslip_url" target="_blank"
                               class="px-3 py-1.5 bg-gradient-to-r from-[#D70F64] to-[#E21B70] hover:from-[#c20d5a] hover:to-[#cb1864] text-white font-black text-xs rounded-xl shadow-md shadow-[#D70F64]/20 transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                                <span>🧾</span>
                                <span>{{ __('Digital Slip (ဒစ်ဂျစ်တယ် ပြေစာ)') }}</span>
                            </a>
                        </template>
                        <button @click="detailsModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white p-1 text-lg font-bold">✕</button>
                    </div>
                </div>

                <!-- Modal Content -->
                <template x-if="activeOrder">
                    <div class="space-y-6 text-xs">
                        
                        <!-- Customer & Delivery Info Box -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider block mb-1">{{ __('Customer Info') }}</span>
                                <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="activeOrder.customer_name"></div>
                                <div class="text-slate-600 dark:text-slate-400 mt-0.5" x-text="'✉️ ' + activeOrder.customer_email"></div>
                                <div class="text-slate-600 dark:text-slate-400 mt-0.5 font-mono" x-text="'📞 ' + activeOrder.delivery_phone"></div>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider block mb-1">{{ __('Delivery Address') }}</span>
                                <div class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed" x-text="'📍 ' + activeOrder.delivery_address"></div>
                                <div class="text-amber-700 dark:text-amber-400 font-medium mt-2" x-text="'📝 Notes: ' + activeOrder.notes"></div>
                            </div>
                        </div>

                        <!-- Ordered Items Table UI -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider">{{ __('Order Items Table') }}</span>
                                <a href="{{ route('admin.orderItems.index') }}" class="text-orange-600 dark:text-orange-400 text-xs font-bold hover:underline">{{ __('View All Order Items Table') }} &rarr;</a>
                            </div>
                            <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                                        <tr>
                                            <th class="px-3.5 py-2.5">{{ __('Item') }}</th>
                                            <th class="px-3.5 py-2.5 text-center">{{ __('Qty') }}</th>
                                            <th class="px-3.5 py-2.5 text-right">{{ __('Price') }}</th>
                                            <th class="px-3.5 py-2.5 text-right">{{ __('Subtotal') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                        <template x-for="item in activeOrder.items" :key="item.name">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-3.5 py-2.5">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shrink-0 flex items-center justify-center text-slate-400 font-bold text-xs">
                                                            <template x-if="item.image">
                                                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                                            </template>
                                                            <template x-if="!item.image">
                                                                <span>🍕</span>
                                                            </template>
                                                        </div>
                                                        <span class="font-bold text-slate-900 dark:text-white text-xs" x-text="item.name"></span>
                                                    </div>
                                                </td>
                                                <td class="px-3.5 py-2.5 text-center font-mono font-bold text-slate-800 dark:text-slate-200" x-text="'x' + item.quantity"></td>
                                                <td class="px-3.5 py-2.5 text-right font-mono text-slate-600 dark:text-slate-400" x-text="item.price + ' MMK'"></td>
                                                <td class="px-3.5 py-2.5 text-right font-bold text-orange-600 dark:text-orange-400" x-text="item.subtotal + ' MMK'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Total Amount, Tax & Payment Summary -->
                        <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                <span class="font-bold uppercase tracking-wider text-[11px]">{{ __('Payment Channel') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white uppercase" x-text="activeOrder.payment_method + ' (' + activeOrder.payment_status + ')'"></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                <span>{{ __('Subtotal') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="activeOrder.subtotal + ' MMK'"></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                <span class="flex items-center gap-1">
                                    <span>{{ __('Tax (5%)') }}</span>
                                    <span class="text-[9px] px-1 py-0.2 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold uppercase">Tax</span>
                                </span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="'+' + activeOrder.tax_amount + ' MMK'"></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                <span>{{ __('Delivery Fee') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="'+' + activeOrder.delivery_fee + ' MMK'"></span>
                            </div>
                            <div class="border-t border-slate-200 dark:border-slate-700 pt-2 flex items-center justify-between">
                                <span class="font-black text-slate-900 dark:text-white uppercase tracking-wider text-xs">{{ __('Total Amount') }}</span>
                                <div class="text-lg font-black text-orange-600 dark:text-orange-400" x-text="activeOrder.total_amount + ' MMK'"></div>
                            </div>
                        </div>

                        <!-- Customer Payment Payslip Verification Card -->
                        <template x-if="activeOrder.payment_screenshot || (activeOrder.payment_method === 'kbzpay' || activeOrder.payment_method === 'wavepay')">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800/80 border-2 border-blue-300 dark:border-blue-700 rounded-2xl p-4 sm:p-5 space-y-3 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5 text-blue-950 dark:text-blue-200 font-black text-sm">
                                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-base shadow-xs">
                                            🧾
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span>{{ __('Customer Payslip Verification (ငွေလွှဲပြေစာ စစ်ဆေးခြင်း)') }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase"
                                                      :class="activeOrder.payment_status === 'paid' ? 'bg-emerald-600 text-white' : (activeOrder.payment_status === 'pending_verification' ? 'bg-purple-600 text-white' : 'bg-amber-500 text-white')"
                                                      x-text="activeOrder.payment_status.replace('_', ' ').toUpperCase()"></span>
                                            </div>
                                            <p class="text-[11px] text-blue-700 dark:text-blue-300 font-medium">Channel: <strong class="uppercase" x-text="activeOrder.payment_method"></strong> &bull; Payable: <span class="font-bold font-mono text-orange-600 dark:text-orange-400" x-text="activeOrder.total_amount + ' MMK'"></span></p>
                                        </div>
                                    </div>
                                </div>

                                <template x-if="activeOrder.payment_screenshot">
                                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-1">
                                        <div @click="openProofPhoto(activeOrder.payment_screenshot, 'Order #' + activeOrder.order_number + ' - Payment Payslip (ငွေလွှဲပြေစာ)')"
                                             class="w-full sm:w-24 h-24 rounded-xl overflow-hidden border-2 border-blue-400 shrink-0 bg-slate-900 group relative cursor-pointer shadow-md">
                                            <img :src="activeOrder.payment_screenshot" alt="Payment Payslip" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors flex items-center justify-center text-white text-xs font-bold gap-1">
                                                <span>🔍</span>
                                                <span>Zoom</span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-slate-700 dark:text-slate-300 space-y-2 flex-1 w-full">
                                            <p class="text-blue-950 dark:text-blue-200 font-bold leading-relaxed">
                                                {{ __('Customer attached mobile banking transfer slip. Please verify the amount and Transaction ID.') }}
                                            </p>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button" 
                                                        @click="openProofPhoto(activeOrder.payment_screenshot, 'Order #' + activeOrder.order_number + ' - Payment Payslip (ငွေလွှဲပြေစာ)')"
                                                        class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                                    <span>🔍</span>
                                                    <span>{{ __('View Full-Screen (ပြေစာအပြည့်ကြည့်ရန်)') }}</span>
                                                </button>

                                                <!-- Form to Mark Paid & Confirm Order -->
                                                <template x-if="activeOrder.payment_status !== 'paid'">
                                                    <form method="POST" :action="'/admin/orders/' + activeOrder.id">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="payment_status" value="paid">
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" 
                                                                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition-all inline-flex items-center gap-1 cursor-pointer">
                                                            <span>✓ {{ __('Approve & Mark Paid') }}</span>
                                                        </button>
                                                    </form>
                                                </template>

                                                <!-- Form to Mark Unpaid / Reject -->
                                                <template x-if="activeOrder.payment_status === 'paid'">
                                                    <form method="POST" :action="'/admin/orders/' + activeOrder.id">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="payment_status" value="unpaid">
                                                        <button type="submit" 
                                                                class="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl transition-all cursor-pointer">
                                                            <span>{{ __('Mark as Unpaid') }}</span>
                                                        </button>
                                                    </form>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!activeOrder.payment_screenshot">
                                    <div class="p-3 bg-amber-100/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-900 dark:text-amber-200 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span>⚠️</span>
                                            <span>{{ __('Customer has not attached a payment payslip screenshot yet.') }}</span>
                                        </div>
                                        <form method="POST" :action="'/admin/orders/' + activeOrder.id">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="payment_status" value="paid">
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg cursor-pointer">
                                                {{ __('Force Mark Paid') }}
                                            </button>
                                        </form>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Proof of Delivery Photo Card with Full-Screen Preview -->
                        <template x-if="activeOrder.delivery_proof_photo">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-slate-800 dark:to-slate-800/80 border-2 border-emerald-300 dark:border-emerald-700 rounded-2xl p-4 sm:p-5 space-y-3 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5 text-emerald-900 dark:text-emerald-200 font-black text-sm">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-base shadow-xs">
                                            📸
                                        </div>
                                        <div>
                                            <span>{{ __('Delivery Proof Photo (သုံးစွဲသူထံ ရောက်ရှိမှု အတည်ပြု ဓာတ်ပုံ)') }}</span>
                                            <p class="text-[11px] text-emerald-700 dark:text-emerald-300 font-medium">{{ __('Captured & submitted by rider upon delivery') }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 bg-emerald-600 text-white text-[10px] font-black rounded-full uppercase shadow-xs">
                                        ✓ {{ __('Photo Verified') }}
                                    </span>
                                </div>

                                <div class="flex flex-col sm:flex-row items-center gap-4 pt-1">
                                    <div @click="openProofPhoto(activeOrder.delivery_proof_photo, 'Order #' + activeOrder.order_number + ' - Delivery Proof Photo (သက်သေဓာတ်ပုံ)')"
                                         class="w-full sm:w-24 h-24 rounded-xl overflow-hidden border-2 border-emerald-400 shrink-0 bg-slate-900 group relative cursor-pointer shadow-md">
                                        <img :src="activeOrder.delivery_proof_photo" alt="Delivery Proof" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors flex items-center justify-center text-white text-xs font-bold gap-1">
                                            <span>🔍</span>
                                            <span>Zoom</span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-slate-700 dark:text-slate-300 space-y-2 flex-1 w-full">
                                        <p class="text-emerald-950 dark:text-emerald-200 font-bold leading-relaxed">
                                            ✓ {{ __('Verified photo proof captured upon food delivery completion.') }}
                                        </p>
                                        <button type="button" 
                                                @click="openProofPhoto(activeOrder.delivery_proof_photo, 'Order #' + activeOrder.order_number + ' - Delivery Proof Photo (သက်သေဓာတ်ပုံ)')"
                                                class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer">
                                            <span>🔍</span>
                                            <span>{{ __('View Full-Screen (ပြေစာအပြည့်ကြည့်ရန်)') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Modal Footer Action Toolbar -->
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                            <a :href="activeOrder.payslip_url" target="_blank" class="px-4 py-2 bg-pink-50 dark:bg-pink-950/50 hover:bg-pink-100 dark:hover:bg-pink-900/50 text-[#D70F64] font-bold text-xs rounded-xl border border-pink-200 dark:border-pink-800 transition-all flex items-center gap-1.5 cursor-pointer">
                                <span>🧾</span>
                                <span>{{ __('Print Payslip') }}</span>
                            </a>
                            <button type="button" @click="detailsModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition-all cursor-pointer">
                                {{ __('Close') }}
                            </button>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        <!-- ================= REJECT ORDER MODAL ================= -->
        <div x-show="rejectModalOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            
            <div @click.outside="rejectModalOpen = false" 
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center text-lg font-bold border border-red-100 dark:border-red-900">
                            ❌
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Reject Order') }}</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs" x-text="activeRejectOrder ? 'Order #' + activeRejectOrder.number : ''"></p>
                        </div>
                    </div>
                    <button @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white p-1 text-lg font-bold">✕</button>
                </div>

                <!-- Modal Form -->
                <template x-if="activeRejectOrder">
                    <form method="POST" :action="'/admin/orders/' + activeRejectOrder.id + '/reject'" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">
                                {{ __('Select Rejection Reason') }} <span class="text-orange-500">*</span>
                            </label>

                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600">
                                    <input type="radio" name="reason" value="Out of Stock" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">🚫 {{ __('Out of Stock (Dishes unavailable)') }}</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600">
                                    <input type="radio" name="reason" value="Kitchen Busy" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">👨‍🍳 {{ __('Kitchen Extremely Busy') }}</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600">
                                    <input type="radio" name="reason" value="Delivery Area Unavailable" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">🛵 {{ __('Delivery Area Unavailable') }}</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600">
                                    <input type="radio" name="reason" value="Store Closing Soon" x-model="activeRejectReason" class="text-orange-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">🕒 {{ __('Store Closing Soon') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="rejectModalOpen = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 active:bg-red-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-red-500/25 transition-all cursor-pointer">
                                {{ __('Confirm Rejection') }}
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- ================= FULL-SCREEN DELIVERY PROOF MODAL ================= -->
        <div x-show="proofModalOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6">
            
            <div @click.outside="proofModalOpen = false" 
                 class="bg-slate-900 border border-slate-700 rounded-3xl p-5 sm:p-6 max-w-3xl w-full shadow-2xl space-y-4 max-h-[95vh] flex flex-col">
                
                <div class="flex items-center justify-between border-b border-slate-800 pb-3 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-500/30">
                            📸
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white" x-text="proofModalTitle"></h3>
                            <p class="text-xs text-slate-400">သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိမှု အတည်ပြု သက်သေဓာတ်ပုံ</p>
                        </div>
                    </div>
                    <button @click="proofModalOpen = false" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white font-bold flex items-center justify-center transition-colors cursor-pointer">✕</button>
                </div>

                <div class="flex-1 overflow-hidden flex items-center justify-center bg-slate-950/80 rounded-2xl border border-slate-800 p-2">
                    <img :src="proofModalSrc" :alt="proofModalTitle" class="max-h-[72vh] w-auto max-w-full object-contain rounded-xl shadow-2xl">
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-800 shrink-0 text-xs">
                    <span class="text-emerald-400 font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Photo Verification Confirmed
                    </span>
                    <a :href="proofModalSrc" target="_blank" class="text-orange-400 hover:text-orange-300 font-bold flex items-center gap-1 hover:underline">
                        <span>Open Original Tab</span> ↗
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
