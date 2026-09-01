<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rider Delivery Portal - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.riderPortalApp = function(initialTab) {
            return {
                activeTab: initialTab || 'available',
                openOrdersCount: {{ $availableOrders->count() }},
                proofModalOpen: false,
                proofModalSrc: '',
                
                // Chat State
                chatModalOpen: false,
                activeChatOrderId: null,
                activeChatOrderNumber: '',
                activeChatCustomerName: '',
                activeChatCustomerPhone: '',
                activeChatDeliveryAddress: '',
                chatMessages: [],
                chatInput: '',
                isSendingChat: false,
                lastMessageCount: 0,

                openChatModal: function(orderId, orderNum, customerName, customerPhone, deliveryAddress) {
                    this.activeChatOrderId = orderId;
                    this.activeChatOrderNumber = orderNum;
                    this.activeChatCustomerName = customerName;
                    this.activeChatCustomerPhone = customerPhone;
                    this.activeChatDeliveryAddress = deliveryAddress;
                    this.chatMessages = [];
                    this.lastMessageCount = 0;
                    this.chatInput = '';
                    this.chatModalOpen = true;
                    this.fetchChatMessages();
                },

                closeChatModal: function() {
                    this.chatModalOpen = false;
                    this.activeChatOrderId = null;
                },

                scrollChatToBottom: function() {
                    this.$nextTick(() => {
                        const container = document.getElementById('rider-chat-messages-container');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                fetchChatMessages: function() {
                    if (!this.activeChatOrderId) return;
                    const self = this;
                    fetch('/orders/' + this.activeChatOrderId + '/messages')
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.messages) {
                                const hadNewMessages = data.messages.length > self.lastMessageCount;
                                const prevCount = self.lastMessageCount;
                                self.chatMessages = data.messages;
                                self.lastMessageCount = data.messages.length;
                                if (hadNewMessages) {
                                    self.scrollChatToBottom();

                                    // SweetAlert Toast Alert for Rider
                                    if (prevCount > 0) {
                                        const newIncoming = data.messages.slice(prevCount).filter(m => !m.is_me);
                                        if (newIncoming.length > 0) {
                                            const latest = newIncoming[newIncoming.length - 1];
                                            if (typeof Swal !== 'undefined') {
                                                const Toast = Swal.mixin({
                                                    toast: true,
                                                    position: 'top-end',
                                                    showConfirmButton: false,
                                                    timer: 5000,
                                                    timerProgressBar: true,
                                                    didOpen: (toast) => {
                                                        toast.onmouseenter = Swal.stopTimer;
                                                        toast.onmouseleave = Swal.resumeTimer;
                                                    }
                                                });

                                                Toast.fire({
                                                    icon: 'info',
                                                    title: `💬 ${latest.sender_name} (${self.activeChatOrderNumber}):`,
                                                    text: latest.message,
                                                    background: '#ffffff',
                                                    color: '#0f172a',
                                                    customClass: {
                                                        popup: 'border border-slate-200 rounded-2xl shadow-xl'
                                                    }
                                                });
                                            }
                                        }
                                    }
                                }
                            }
                        })
                        .catch(() => {});
                },

                sendChatMessage: function(presetText) {
                    const textToSend = presetText || this.chatInput;
                    if (!textToSend || !textToSend.trim() || !this.activeChatOrderId || this.isSendingChat) return;

                    this.isSendingChat = true;
                    const self = this;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch('/orders/' + this.activeChatOrderId + '/messages', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message: textToSend.trim() })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success && data.message) {
                            self.chatMessages.push(data.message);
                            self.lastMessageCount = self.chatMessages.length;
                            if (!presetText) {
                                self.chatInput = '';
                            }
                            self.scrollChatToBottom();
                        }
                    })
                    .catch(() => {})
                    .finally(() => {
                        self.isSendingChat = false;
                    });
                },

                // Notification State
                lastNotificationId: 0,
                isInitialNotifLoaded: false,
                notifiedMessageIds: [],

                checkIncomingCustomerMessages: function() {
                    const self = this;
                    const url = '{{ route('rider.messages.notifications') }}' + (self.lastNotificationId > 0 ? '?since_id=' + self.lastNotificationId : '');

                    fetch(url)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.success) {
                                if (!self.isInitialNotifLoaded) {
                                    self.lastNotificationId = data.latest_id || 0;
                                    self.isInitialNotifLoaded = true;
                                    return;
                                }

                                if (data.notifications && data.notifications.length > 0) {
                                    data.notifications.forEach(notif => {
                                        if (self.notifiedMessageIds.includes(notif.id)) return;
                                        self.notifiedMessageIds.push(notif.id);

                                        // If chat modal is already open for this order, just update messages
                                        if (self.chatModalOpen && self.activeChatOrderId === notif.order_id) {
                                            self.fetchChatMessages();
                                            return;
                                        }

                                        // Trigger rich SweetAlert Box popup
                                        if (typeof Swal !== 'undefined') {
                                            Swal.fire({
                                                title: '💬 New Message from Customer!',
                                                html: `
                                                    <div style="text-align:left; background:#f8fafc; padding:16px; border-radius:16px; border:1px solid #e2e8f0; margin:12px 0;">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:12px;">
                                                            <span style="font-weight:900; color:#ea580c;">${notif.order_number}</span>
                                                            <span style="color:#64748b; font-size:11px;">${notif.time_formatted}</span>
                                                        </div>
                                                        <div style="font-weight:bold; color:#0f172a; font-size:14px; margin-bottom:6px;">
                                                            👤 ${notif.customer_name}
                                                        </div>
                                                        <div style="background:#ffffff; padding:12px; border-radius:12px; border:1px solid #cbd5e1; color:#1e293b; font-size:13px; line-height:1.5;">
                                                            "${notif.message}"
                                                        </div>
                                                        ${notif.customer_phone ? `<div style="margin-top:8px; font-size:11px; color:#64748b;">📞 Phone: <span style="color:#0284c7; font-weight:bold;">${notif.customer_phone}</span></div>` : ''}
                                                    </div>
                                                `,
                                                icon: 'info',
                                                showCancelButton: true,
                                                confirmButtonText: '💬 Open Chat & Reply',
                                                cancelButtonText: 'Dismiss',
                                                confirmButtonColor: '#9333ea',
                                                cancelButtonColor: '#64748b',
                                                background: '#ffffff',
                                                color: '#0f172a',
                                                customClass: {
                                                    popup: 'rounded-3xl border border-slate-200 shadow-2xl'
                                                }
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    self.openChatModal(notif.order_id, notif.order_number, notif.customer_name, notif.customer_phone, notif.delivery_address);
                                                }
                                            });
                                        }
                                    });

                                    if (data.latest_id > self.lastNotificationId) {
                                        self.lastNotificationId = data.latest_id;
                                    }
                                }
                            }
                        })
                        .catch(() => {});
                },

                init: function() {
                    var self = this;
                    // Initial fetch to get baseline message ID
                    self.checkIncomingCustomerMessages();

                    // Auto-poll for new unassigned orders & chat messages
                    setInterval(function() {
                        fetch('{{ route('admin.orders.json_list') }}')
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data && data.orders) {
                                    // Check if there are confirmed orders without rider
                                    var currentUnassigned = data.orders.filter(function(o) {
                                        return (o.status === 'confirmed' || o.status === 'preparing') && !o.rider_id;
                                    }).length;
                                    if (currentUnassigned !== self.openOrdersCount) {
                                        window.location.reload();
                                    }
                                }
                            })
                            .catch(function() {});

                        // Check incoming customer chat messages across all active deliveries
                        self.checkIncomingCustomerMessages();

                        // If modal is open, keep message feed refreshed
                        if (self.chatModalOpen && self.activeChatOrderId) {
                            self.fetchChatMessages();
                        }
                    }, 2500);
                }
            };
        };
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-orange-500 selection:text-white min-h-screen pb-16"
      x-data="riderPortalApp('{{ $availableOrders->count() > 0 ? 'available' : ($activeDeliveries->count() > 0 ? 'active' : 'available') }}')">

    <!-- Top Header -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 py-4 shadow-sm">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#D70F64] flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-pink-500/20">
                    🐼
                </div>
                <div>
                    <h1 class="text-lg font-black text-slate-900 leading-tight flex items-center gap-1.5">
                        <span class="text-[#D70F64] font-black">Food<span class="text-slate-900">Order</span></span>
                        <span class="text-xs px-2 py-0.5 rounded-md bg-pink-50 text-[#D70F64] font-bold border border-pink-200">Rider Fleet</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-semibold">Welcome back, <span class="font-bold text-slate-800">{{ $rider->name }}</span> 👋</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-language-switcher variant="compact" />
                <a href="{{ route('home') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
                    {{ __('View Storefront') }}
                </a>
                <form method="POST" action="{{ route('logout') }}" onsubmit="localStorage.removeItem('foodorder_cart')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition-all cursor-pointer">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 space-y-6">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold rounded-2xl flex items-center gap-3 shadow-sm">
                <span class="text-lg">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm font-bold rounded-2xl flex items-center gap-3 shadow-sm">
                <span class="text-lg">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 text-center">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ready for Pick Up</span>
                <span class="text-2xl sm:text-3xl font-black text-amber-600 mt-1 block">{{ $availableOrders->count() }}</span>
                @if($availableOrders->count() > 0)
                    <span class="absolute top-2 right-2 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                @endif
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">My Active</span>
                <span class="text-2xl sm:text-3xl font-black text-purple-600 mt-1 block">{{ $activeDeliveries->count() }}</span>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Delivered Today</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1 block">{{ $stats['completed_today'] }}</span>
            </div>
            <!-- Earnings Today Card -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-4 shadow-lg shadow-emerald-500/20 text-white">
                <span class="block text-[10px] font-bold text-emerald-100 uppercase tracking-wider">Earnings Today</span>
                <span class="text-2xl sm:text-3xl font-black mt-1 block">{{ number_format($stats['total_earnings_today']) }} <span class="text-sm">MMK</span></span>
            </div>
        </div>

        <!-- Navigation Tabs (3 Tabs: Open Orders Pool, My Active, Completed) -->
        <div class="flex items-center gap-2 p-1.5 bg-slate-200/70 border border-slate-200 rounded-2xl">
            <button @click="activeTab = 'available'"
                    :class="activeTab === 'available' ? 'bg-amber-500 text-white font-black shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60 font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer relative">
                <span>📢 Open Pickups</span>
                <span class="px-2 py-0.5 rounded-full text-[10px]" :class="activeTab === 'available' ? 'bg-amber-700 text-white font-black' : 'bg-slate-300 text-slate-700 font-bold'">
                    {{ $availableOrders->count() }}
                </span>
            </button>

            <button @click="activeTab = 'active'"
                    :class="activeTab === 'active' ? 'bg-orange-500 text-white font-black shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60 font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span>🛵 My Deliveries</span>
                <span class="px-2 py-0.5 rounded-full text-[10px]" :class="activeTab === 'active' ? 'bg-orange-700 text-white font-black' : 'bg-slate-300 text-slate-700 font-bold'">
                    {{ $activeDeliveries->count() }}
                </span>
            </button>

            <button @click="activeTab = 'completed'"
                    :class="activeTab === 'completed' ? 'bg-orange-500 text-white font-black shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60 font-bold'"
                    class="flex-1 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span>✅ History</span>
                <span class="px-2 py-0.5 rounded-full text-[10px]" :class="activeTab === 'completed' ? 'bg-orange-700 text-white font-black' : 'bg-slate-300 text-slate-700 font-bold'">
                    {{ $completedDeliveries->count() }}
                </span>
            </button>
        </div>

        <!-- 1. OPEN PICKUPS TAB (ORDERS WAITING FOR RIDER) -->
        <div x-show="activeTab === 'available'" class="space-y-4">
            @if($availableOrders->count() > 0)
                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between text-xs text-amber-900">
                    <span class="flex items-center gap-2 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                        <span>{{ $availableOrders->count() }} new order(s) approved by kitchen &amp; ready for rider pickup!</span>
                    </span>
                    <span class="text-[11px] text-amber-700 font-semibold">First Come First Serve</span>
                </div>
            @endif

            @forelse($availableOrders as $order)
                <div class="bg-white border-2 border-amber-200 hover:border-amber-400 rounded-3xl p-5 sm:p-6 shadow-sm hover:shadow-md space-y-4 transition-all">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 font-mono font-black text-xs rounded-full">
                                #{{ $order->order_number }}
                            </span>
                            <span class="text-[11px] text-slate-500 font-medium">
                                Approved: {{ $order->updated_at ? $order->updated_at->diffForHumans() : 'Just now' }}
                            </span>
                        </div>

                        <span class="px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 font-black text-[11px] uppercase tracking-wider rounded-xl animate-pulse flex items-center gap-1.5">
                            <span>🍳 Ready for Pickup</span>
                        </span>
                    </div>

                    <!-- Delivery & Customer Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 space-y-1.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">📍 Delivery Destination</p>
                            <p class="font-bold text-orange-600 text-sm">{{ $order->delivery_township ?? 'Yangon' }}</p>
                            <p class="text-slate-700 leading-relaxed">{{ $order->delivery_address }}</p>
                        </div>

                        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 space-y-1.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">👤 Customer &amp; Payment</p>
                            <p class="font-bold text-slate-900">{{ $order->user->name ?? 'Customer' }}</p>
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-slate-600 uppercase font-semibold">
                                        @if($order->payment_method === 'cod') 💵 Cash on Delivery
                                        @elseif($order->payment_method === 'kbzpay') 📱 KBZPay
                                        @elseif($order->payment_method === 'wavepay') 🌊 WavePay
                                        @else {{ $order->payment_method }} @endif
                                    </span>
                                    @if($order->payment_screenshot)
                                        <button type="button" 
                                                @click="proofModalSrc = '{{ asset($order->payment_screenshot) }}'; proofModalOpen = true;"
                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 font-bold rounded text-[9px] transition-colors cursor-pointer">
                                            <span>📸 Transfer Screenshot</span>
                                        </button>
                                    @endif
                                </div>
                                <span class="font-black text-emerald-600 font-mono text-sm">
                                    {{ number_format($order->total_amount) }} MMK
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Ordered Items Summary -->
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs text-slate-700 flex items-center justify-between">
                        <span>🍽️ <strong>{{ $order->orderItems->sum('quantity') }} items:</strong> {{ $order->orderItems->map(fn($i) => $i->menuItem->name ?? 'Dish')->take(3)->implode(', ') }}</span>
                        <span class="text-orange-600 font-bold">+{{ number_format($order->delivery_fee) }} MMK Fee</span>
                    </div>

                    <!-- Action Buttons: Digital Slip + Pickup CTA -->
                    <div class="flex flex-col sm:flex-row items-center gap-2 pt-1">
                        <a href="{{ route('orders.payslip', $order) }}" target="_blank"
                           class="w-full sm:w-auto px-4 py-3.5 bg-pink-50 hover:bg-pink-100 border border-pink-200 text-[#D70F64] font-black text-xs rounded-2xl transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                            <span>🧾</span>
                            <span>{{ __('View Digital Slip (ပြေစာ)') }}</span>
                        </a>

                        <form method="POST" action="{{ route('rider.orders.pickup', $order) }}" class="flex-1 w-full">
                            @csrf
                            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 active:scale-95 text-white font-black text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span class="text-base">🛵</span>
                                <span>Accept &amp; Pick Up This Order</span>
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="bg-white border border-slate-200/80 rounded-3xl p-12 text-center text-slate-500 space-y-3 shadow-sm">
                    <div class="text-4xl">🛵</div>
                    <p class="font-bold text-slate-800 text-base">No Open Orders Waiting for Pickup</p>
                    <p class="text-xs text-slate-500">When customers order and admin approves, available orders will appear here in real-time!</p>
                </div>
            @endforelse
        </div>

        <!-- 2. MY ACTIVE DELIVERIES TAB -->
        <div x-show="activeTab === 'active'" class="space-y-4" style="display: none;">
            @forelse($activeDeliveries as $order)
                <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4 transition-all hover:shadow-md">
                    
                    <!-- Card Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-orange-50 border border-orange-200 text-orange-700 font-mono font-black text-xs rounded-full">
                                #{{ $order->order_number }}
                            </span>
                            <span class="text-[11px] text-slate-500 font-medium">
                                {{ $order->created_at ? $order->created_at->diffForHumans() : '' }}
                            </span>
                        </div>

                        <!-- Status Badge -->
                        @if($order->status === 'delivering')
                            <span class="px-3 py-1 bg-purple-50 border border-purple-200 text-purple-700 font-black text-[11px] uppercase tracking-wider rounded-xl animate-pulse flex items-center gap-1.5">
                                <span>🛵 Out for Delivery</span>
                            </span>
                        @elseif($order->status === 'preparing')
                            <span class="px-3 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 font-black text-[11px] uppercase tracking-wider rounded-xl flex items-center gap-1.5">
                                <span>👨‍🍳 Kitchen Preparing</span>
                            </span>
                        @else
                            <span class="px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 font-black text-[11px] uppercase tracking-wider rounded-xl">
                                <span>✓ Picked Up</span>
                            </span>
                        @endif
                    </div>

                    <!-- Customer & Delivery Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        
                        <!-- Customer Contact -->
                        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 space-y-2">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">👤 Customer Details</p>
                            <p class="font-bold text-slate-900 text-sm">{{ $order->user->name ?? 'Guest Customer' }}</p>
                            
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <button type="button" 
                                        @click="openChatModal({{ $order->id }}, '#{{ $order->order_number }}', '{{ addslashes($order->user->name ?? 'Customer') }}', '{{ addslashes($order->delivery_phone ?? '') }}', '{{ addslashes($order->delivery_address ?? '') }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold rounded-xl text-xs shadow-sm transition-all cursor-pointer">
                                    <span>💬 Chat</span>
                                </button>
                                @if($order->delivery_phone)
                                    <a href="tel:{{ $order->delivery_phone }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs transition-colors cursor-pointer">
                                        <span>📞 Call</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 space-y-1.5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">📍 Delivery Address</p>
                            <p class="font-bold text-orange-600">{{ $order->delivery_township ?? 'Yangon' }}</p>
                            <p class="text-slate-700 leading-relaxed">{{ $order->delivery_address }}</p>
                        </div>
                    </div>

                    <!-- Payment & Amount Info -->
                    <div class="flex items-center justify-between bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs">
                        <div class="flex items-center flex-wrap gap-1.5">
                            <span class="text-slate-500 font-medium">Payment: </span>
                            <span class="font-bold text-slate-900 uppercase">
                                @if($order->payment_method === 'cod') 💵 Cash on Delivery
                                @elseif($order->payment_method === 'kbzpay') 📱 KBZPay
                                @elseif($order->payment_method === 'wavepay') 🌊 WavePay
                                @else {{ $order->payment_method }} @endif
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $order->payment_status }}
                            </span>

                            <!-- FoodOrder Delivery Slip -->
                            <a href="{{ route('orders.payslip', $order) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-pink-50 hover:bg-pink-100 border border-pink-200 text-[#D70F64] font-black rounded-lg text-[10px] transition-colors cursor-pointer shadow-sm">
                                <span>🧾 Delivery Slip</span>
                            </a>

                            @if($order->payment_screenshot)
                                <button type="button" 
                                        @click="proofModalSrc = '{{ asset($order->payment_screenshot) }}'; proofModalOpen = true;"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 font-bold rounded-md text-[10px] transition-colors cursor-pointer">
                                    <span>📸 Transfer Screenshot</span>
                                </button>
                            @endif
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <div class="text-right bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-200">
                                <span class="text-slate-500 font-bold text-[10px] uppercase">Cash to Collect: </span>
                                <span class="text-sm font-black text-slate-900 font-mono">
                                    {{ $order->payment_status === 'paid' ? '0 MMK (Paid)' : number_format($order->total_amount) . ' MMK' }}
                                </span>
                            </div>
                            
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl">
                                <span class="text-lg">✨</span>
                                <div class="text-left">
                                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-wider">Expected Earnings</p>
                                    <p class="font-black text-emerald-700 font-mono text-sm">+{{ number_format($order->delivery_fee) }} <span class="text-[10px]">MMK</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ordered Items List -->
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 divide-y divide-slate-200/60 text-xs">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">🍽️ Ordered Items ({{ $order->orderItems->sum('quantity') }})</p>
                        @foreach($order->orderItems as $item)
                            <div class="py-1.5 flex items-center justify-between text-slate-700">
                                <span class="font-medium">• {{ $item->menuItem->name ?? 'Dish' }}</span>
                                <span class="font-mono text-orange-600 font-bold">x{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Action Buttons & Delivery Proof Section -->
                    <div class="pt-2">
                        @if($order->status !== 'delivering')
                            <form method="POST" action="{{ route('rider.orders.start', $order) }}">
                                @csrf
                                <button type="submit" class="w-full py-3.5 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold text-xs rounded-2xl shadow-lg shadow-purple-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <span>🛵 Start Delivery Route</span>
                                </button>
                            </form>
                        @else
                            <!-- PROOF OF DELIVERY FORM WITH PHOTO -->
                            <form method="POST" action="{{ route('rider.orders.complete', $order) }}" enctype="multipart/form-data" 
                                  x-data="{ photoPreview: null, isUploading: false }" 
                                  @submit="isUploading = true"
                                  class="space-y-3 p-4 bg-gradient-to-br from-purple-50/50 to-indigo-50/50 rounded-2xl border border-purple-200 shadow-sm">
                                @csrf
                                
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                            <span>📸</span>
                                            <span>သုံးစွဲသူထံ အစားအသောက် ရောက်ရှိကြောင်း ဓာတ်ပုံ (Photo Proof)</span>
                                        </label>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold">Photo Verification</span>
                                    </div>

                                    <!-- Camera & File capture box -->
                                    <div class="relative border-2 border-dashed border-slate-300 hover:border-purple-500 rounded-2xl p-4 text-center transition-all bg-white group cursor-pointer">
                                        <input type="file" name="delivery_proof_photo" accept="image/*" capture="environment" required
                                               @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }"
                                               class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                                        
                                        <template x-if="!photoPreview">
                                            <div class="py-2 space-y-1.5 pointer-events-none">
                                                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center mx-auto text-xl group-hover:scale-110 transition-transform">
                                                    📷
                                                </div>
                                                <p class="text-xs font-bold text-slate-900">Tap to Take or Upload Delivery Photo</p>
                                                <p class="text-[10px] text-slate-500">သုံးစွဲသူထံ အစားအသောက် ပေးအပ်သည့် ဓာတ်ပုံ ရိုက်ကူးပါ</p>
                                            </div>
                                        </template>

                                        <template x-if="photoPreview">
                                            <div class="relative inline-block">
                                                <img :src="photoPreview" alt="Delivery Proof" class="max-h-44 mx-auto rounded-xl border border-purple-300 object-cover shadow-lg">
                                                <span class="absolute bottom-2 right-2 px-2.5 py-1 bg-emerald-600 text-white font-black text-[10px] rounded-lg shadow-md flex items-center gap-1">
                                                    ✓ Photo Ready
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                @if($order->payment_method === 'cod')
                                    <!-- Explicit COD Cash Collection Confirmation Card -->
                                    <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-2xl space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-black text-amber-900 uppercase tracking-wide flex items-center gap-1.5">
                                                <span>💵</span> <span>COD Cash to Collect (ငွေသား ကောက်ခံရန်)</span>
                                            </span>
                                            <span class="text-base font-black text-amber-700 font-mono">
                                                {{ number_format($order->total_amount) }} MMK
                                            </span>
                                        </div>
                                        <label class="flex items-start gap-2.5 text-xs font-bold text-amber-950 cursor-pointer pt-2 border-t border-amber-200">
                                            <input type="checkbox" name="confirm_cash_collected" value="1" required
                                                   class="rounded border-amber-400 text-amber-600 focus:ring-amber-500 mt-0.5 w-4 h-4 cursor-pointer">
                                            <span class="leading-relaxed">Customer ထံမှ ငွေသား {{ number_format($order->total_amount) }} MMK အပြည့်အဝ လက်ခံရရှိပြီးကြောင်း အတည်ပြုပါသည် (I confirm exact cash collected).</span>
                                        </label>
                                    </div>

                                    <button type="submit" 
                                            :disabled="isUploading"
                                            class="w-full py-3.5 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-700 active:scale-98 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                        <template x-if="isUploading">
                                            <span>⏳ Confirming Cash &amp; Issuing Receipt...</span>
                                        </template>
                                        <template x-if="!isUploading">
                                            <span>💵 Confirm Cash Received &amp; Issue Receipt (ငွေလက်ခံရရှိပြီး ပြေစာထုတ်မည်)</span>
                                        </template>
                                    </button>
                                @else
                                    <!-- Online Prepaid Status Box -->
                                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs font-bold text-emerald-900">
                                        <span class="flex items-center gap-1.5">
                                            <span>✅</span> <span>Online Pre-paid ({{ strtoupper($order->payment_method) }}) — Already Paid</span>
                                        </span>
                                        <span class="font-mono text-emerald-700 font-black">0 MMK to Collect</span>
                                    </div>

                                    <button type="submit" 
                                            :disabled="isUploading"
                                            class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 active:scale-98 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                        <template x-if="isUploading">
                                            <span>⏳ Uploading &amp; Confirming Delivery...</span>
                                        </template>
                                        <template x-if="!isUploading">
                                            <span>✅ Confirm Delivery Complete (အစားအသောက် ပို့ဆောင်မှု ပြီးစီးကြောင်း အတည်ပြုမည်)</span>
                                        </template>
                                    </button>
                                @endif
                            </form>
                        @endif
                    </div>

                </div>
            @empty
                <div class="bg-white border border-slate-200/80 rounded-3xl p-12 text-center text-slate-500 space-y-3 shadow-sm">
                    <div class="text-4xl">🛵</div>
                    <p class="font-bold text-slate-800 text-base">No Active Deliveries Right Now!</p>
                    <p class="text-xs text-slate-500">Pick up orders from the <strong>Open Pickups</strong> tab to start delivering.</p>
                </div>
            @endforelse
        </div>

        <!-- 3. COMPLETED HISTORY TAB -->
        <div x-show="activeTab === 'completed'" class="space-y-4" style="display: none;">
            @forelse($completedDeliveries as $order)
                <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-emerald-700">#{{ $order->order_number }}</span>
                            <span class="px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-full text-[10px]">✅ Delivered</span>
                        </div>
                        <p class="font-bold text-slate-900 mt-1">{{ $order->user->name ?? 'Customer' }} • {{ $order->delivery_township }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $order->updated_at ? $order->updated_at->format('M d, Y • h:i A') : '' }}</p>
                        
                        @if($order->delivery_proof_photo)
                            <div class="mt-2 flex items-center gap-2">
                                <button type="button" @click="proofModalSrc = '{{ asset($order->delivery_proof_photo) }}'; proofModalOpen = true;"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 text-[11px] font-bold rounded-xl transition-colors cursor-pointer">
                                    <span>📸 View Photo Proof (သက်သေဓာတ်ပုံ)</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-1.5">
                        <div class="text-[10px] text-slate-500 font-bold">Order Value: {{ number_format($order->total_amount) }} MMK</div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <span class="text-lg">💰</span>
                            <div class="text-left">
                                <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-wider">Earned</p>
                                <p class="font-black text-emerald-700 font-mono text-sm">+{{ number_format($order->delivery_fee) }} <span class="text-[10px]">MMK</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200/80 rounded-3xl p-12 text-center text-slate-500 space-y-2 shadow-sm">
                    <div class="text-3xl">🎉</div>
                    <p class="font-bold text-slate-800">No Completed Deliveries Yet</p>
                </div>
            @endforelse
        </div>

    </main>

    <!-- Photo Proof Viewer Modal -->
    <div x-show="proofModalOpen" x-transition class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
        <div class="bg-white border border-slate-200 rounded-3xl p-5 max-w-lg w-full relative shadow-2xl space-y-3" @click.outside="proofModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📸</span>
                    <h3 class="font-black text-slate-900 text-sm">Delivery Proof Photo (သက်သေဓာတ်ပုံ)</h3>
                </div>
                <button @click="proofModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 font-bold flex items-center justify-center cursor-pointer">✕</button>
            </div>
            <img :src="proofModalSrc" alt="Delivery Proof" class="w-full h-auto rounded-2xl border border-slate-100 max-h-[70vh] object-contain mx-auto">
        </div>
    </div>

    <!-- Rider Live Chat Modal -->
    <div x-show="chatModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="chatModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 @click="closeChatModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="chatModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 flex flex-col max-h-[85vh]">
                
                <!-- Chat Modal Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-lg font-black shadow-md">
                            💬
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-slate-900 text-sm" x-text="activeChatCustomerName"></h3>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[10px]" x-text="activeChatOrderNumber"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 truncate max-w-[240px]" x-text="activeChatDeliveryAddress"></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="activeChatCustomerPhone">
                            <a :href="'tel:' + activeChatCustomerPhone" 
                               class="p-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 rounded-xl transition-colors text-xs font-bold flex items-center gap-1">
                                <span>📞</span>
                            </a>
                        </template>
                        <button type="button" @click="closeChatModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 font-bold flex items-center justify-center cursor-pointer">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Quick Presets for Rider on the move -->
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 overflow-x-auto shrink-0">
                    <div class="flex items-center gap-1.5 min-w-max text-[11px]">
                        <span class="text-[10px] font-bold text-slate-400 uppercase mr-1">Quick:</span>
                        <button type="button" @click="sendChatMessage('🛵 I have picked up your order and I am on the way!')" class="px-2.5 py-1 bg-white hover:bg-purple-50 text-slate-700 hover:text-purple-700 rounded-lg border border-slate-200 font-medium transition-all cursor-pointer shadow-xs">
                            🛵 On the way!
                        </button>
                        <button type="button" @click="sendChatMessage('📍 I have arrived downstairs at your building / gate.')" class="px-2.5 py-1 bg-white hover:bg-purple-50 text-slate-700 hover:text-purple-700 rounded-lg border border-slate-200 font-medium transition-all cursor-pointer shadow-xs">
                            📍 Arrived downstairs
                        </button>
                        <button type="button" @click="sendChatMessage('⏳ Slight traffic delay, arriving in approx 5 mins.')" class="px-2.5 py-1 bg-white hover:bg-purple-50 text-slate-700 hover:text-purple-700 rounded-lg border border-slate-200 font-medium transition-all cursor-pointer shadow-xs">
                            ⏳ 5 mins away
                        </button>
                        <button type="button" @click="sendChatMessage('🚪 Order delivered. Thank you!')" class="px-2.5 py-1 bg-white hover:bg-purple-50 text-slate-700 hover:text-purple-700 rounded-lg border border-slate-200 font-medium transition-all cursor-pointer shadow-xs">
                            🚪 Delivered!
                        </button>
                    </div>
                </div>

                <!-- Scrollable Messages Container -->
                <div id="rider-chat-messages-container" class="p-4 sm:p-5 flex-1 overflow-y-auto space-y-3 bg-slate-50/70 text-xs min-h-[220px]">
                    <template x-if="chatMessages.length === 0">
                        <div class="text-center py-10 text-slate-400 space-y-1">
                            <p class="text-sm font-bold text-slate-600">No Messages Yet</p>
                            <p class="text-[11px]">Send a quick update to the customer about their delivery status.</p>
                        </div>
                    </template>

                    <template x-for="msg in chatMessages" :key="msg.id">
                        <div class="flex flex-col" :class="msg.is_me ? 'items-end' : 'items-start'">
                            
                            <div class="flex items-center gap-1.5 mb-1 px-1 text-[10px] text-slate-400">
                                <span class="font-bold text-slate-700" x-text="msg.is_me ? 'You (Rider)' : msg.sender_name"></span>
                                <span class="px-1 py-0.2 rounded text-[8px] font-black uppercase"
                                      :class="{
                                          'bg-purple-100 text-purple-700': msg.sender_role === 'rider',
                                          'bg-orange-100 text-orange-700': msg.sender_role === 'customer',
                                          'bg-blue-100 text-blue-700': msg.sender_role === 'admin'
                                      }"
                                      x-text="msg.sender_role">
                                </span>
                                <span x-text="msg.time_formatted"></span>
                            </div>

                            <div class="max-w-[85%] px-3.5 py-2 rounded-2xl text-xs leading-relaxed shadow-sm break-words"
                                 :class="msg.is_me 
                                     ? 'bg-purple-600 text-white rounded-tr-sm font-medium' 
                                     : (msg.sender_role === 'customer' 
                                         ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-tl-sm font-medium' 
                                         : 'bg-white text-slate-800 border border-slate-200 rounded-tl-sm')">
                                <p x-text="msg.message"></p>
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Input Footer -->
                <form @submit.prevent="sendChatMessage()" class="p-3 bg-white border-t border-slate-100 flex items-center gap-2 shrink-0">
                    <input type="text" 
                           x-model="chatInput" 
                           placeholder="Type a message to customer..."
                           :disabled="isSendingChat"
                           class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                    
                    <button type="submit" 
                            :disabled="isSendingChat || !chatInput.trim()"
                            class="px-4 py-2.5 bg-purple-600 hover:bg-purple-500 active:scale-95 disabled:opacity-50 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                        <span x-show="!isSendingChat">Send</span>
                        <span x-show="isSendingChat" class="animate-spin">⏳</span>
                        <svg x-show="!isSendingChat" class="w-3.5 h-3.5 rotate-45 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>

            </div>
        </div>
    </div>

    <x-scroll-to-top />

</body>
</html>
