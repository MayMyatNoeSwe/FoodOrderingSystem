<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Details #{{ $order->order_number }} — {{ config('app.name', 'FoodOrder') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <!-- Theme Initialization (Prevents FOUC) -->
    <script>
        if (localStorage.getItem('foodorder_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Order Tracker JS -->
    <script>
        window.initOrderTracker = function(initialStatus, initialPaymentStatus, initialNotes, initialRiderName, initialRiderPhone, initialDeliveryProofPhoto, jsonUrl, messagesUrl) {
            return {
                imgModal: false,
                imgSrc: '',
                imgTitle: 'Payment Screenshot',
                uploadSlipModal: false,
                currentStatus: initialStatus,
                currentPaymentStatus: initialPaymentStatus,
                currentNotes: initialNotes,
                currentRiderName: initialRiderName,
                currentRiderPhone: initialRiderPhone,
                currentDeliveryProofPhoto: initialDeliveryProofPhoto,
                justApproved: false,
                darkMode: localStorage.getItem('foodorder_theme') === 'dark',
                
                // Live Chat State
                messages: [],
                chatInput: '',
                isSendingChat: false,
                messagesUrl: messagesUrl,
                lastMessageCount: 0,
                isInitialLoad: true,
                chatBoxOpen: false,

                toggleTheme: function() {
                    this.darkMode = !this.darkMode;
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('foodorder_theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('foodorder_theme', 'light');
                    }
                },

                toggleChatBox: function(forceState) {
                    if (forceState !== undefined) {
                        this.chatBoxOpen = forceState;
                    } else {
                        this.chatBoxOpen = !this.chatBoxOpen;
                    }
                    if (this.chatBoxOpen) {
                        this.scrollToChatBottom();
                        this.$nextTick(() => {
                            document.getElementById('order-chat-section')?.scrollIntoView({behavior: 'smooth', block: 'nearest'});
                            document.getElementById('customer-chat-input')?.focus();
                        });
                    }
                },

                scrollToChatBottom: function() {
                    this.$nextTick(() => {
                        const container = document.getElementById('chat-messages-container');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                fetchMessages: function() {
                    const self = this;
                    fetch(this.messagesUrl)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.messages) {
                                const hadNewMessages = data.messages.length > self.lastMessageCount;
                                const prevCount = self.lastMessageCount;
                                self.messages = data.messages;
                                self.lastMessageCount = data.messages.length;
                                if (hadNewMessages) {
                                    self.scrollToChatBottom();

                                    // SweetAlert Toast Alert on new incoming message
                                    if (!self.isInitialLoad && prevCount > 0) {
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
                                                        toast.addEventListener('click', () => {
                                                            self.toggleChatBox(true);
                                                        });
                                                    }
                                                });

                                                Toast.fire({
                                                    icon: 'info',
                                                    title: `💬 New message from ${latest.sender_name} (Click to open):`,
                                                    text: latest.message,
                                                    background: self.darkMode ? '#0f172a' : '#ffffff',
                                                    color: self.darkMode ? '#f8fafc' : '#0f172a'
                                                });
                                            }
                                        }
                                    }
                                }
                                self.isInitialLoad = false;
                            }
                        })
                        .catch(() => {});
                },

                sendChatMessage: function(presetText) {
                    const text = presetText || this.chatInput;
                    if (!text || text.trim() === '' || this.isSendingChat) return;

                    this.isSendingChat = true;
                    const self = this;

                    fetch(this.messagesUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                        },
                        body: JSON.stringify({ message: text.trim() })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success && data.message) {
                            self.messages.push(data.message);
                            self.lastMessageCount = self.messages.length;
                            if (!presetText) {
                                self.chatInput = '';
                            }
                            self.scrollToChatBottom();
                        }
                    })
                    .catch(() => {})
                    .finally(() => {
                        self.isSendingChat = false;
                    });
                },

                init: function() {
                    var self = this;
                    localStorage.removeItem('foodorder_cart');
                    self.fetchMessages();

                    setInterval(function() {
                        // Poll order status
                        fetch(jsonUrl)
                            .then(function(res) { return res.json(); })
                            .then(function(data) {
                                if (data.status) {
                                    if ((data.status === 'confirmed' || data.status === 'preparing') && self.currentStatus === 'pending') {
                                        self.justApproved = true;
                                    }
                                    self.currentStatus = data.status;
                                    self.currentPaymentStatus = data.payment_status;
                                    if (data.notes !== undefined) {
                                        self.currentNotes = data.notes;
                                    }
                                    if (data.rider_name !== undefined) {
                                        self.currentRiderName = data.rider_name;
                                    }
                                    if (data.rider_phone !== undefined) {
                                        self.currentRiderPhone = data.rider_phone;
                                    }
                                    if (data.delivery_proof_photo !== undefined) {
                                        self.currentDeliveryProofPhoto = data.delivery_proof_photo;
                                    }
                                }
                            })
                            .catch(function() {});

                        // Poll chat messages
                        self.fetchMessages();
                    }, 2500);
                }
            };
        };
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 selection:bg-orange-500 selection:text-white"
    x-data="initOrderTracker({{ json_encode($order->status) }}, {{ json_encode($order->payment_status) }}, {{ json_encode($order->notes ?? '') }}, {{ json_encode($order->rider ? $order->rider->name : null) }}, {{ json_encode($order->rider ? ($order->rider->phone_number ?? $order->rider->phone ?? null) : null) }}, {{ json_encode($order->delivery_proof_photo ? asset($order->delivery_proof_photo) : null) }}, '{{ route('customer.orders.json_status', $order) }}', '{{ route('orders.messages.index', $order) }}')">

    <!-- ===== NAVBAR ===== -->
    <x-storefront-navbar />

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- ===== LIVE NOTIFICATION BANNER (State Dependent) ===== -->
        <!-- 1. PENDING STATE BANNER -->
        <div x-show="currentStatus === 'pending'" x-transition class="mb-8 p-5 bg-amber-500 text-white rounded-3xl shadow-xl shadow-amber-500/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-amber-400">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-2xl shrink-0 animate-bounce">
                    ⏳
                </div>
                <div>
                    <h3 class="font-black text-base">Notification sent to Admin.</h3>
                    <p class="text-xs text-amber-100 mt-0.5 leading-relaxed">Your order status will update automatically here as soon as the Admin approves it.</p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-xl text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                <span>Live Checking...</span>
            </div>
        </div>

        <!-- 2. APPROVED / CONFIRMED - WAITING FOR RIDER PICKUP -->
        <div x-show="(currentStatus === 'confirmed' || currentStatus === 'preparing') && !currentRiderName" x-transition class="mb-8 p-5 bg-emerald-600 text-white rounded-3xl shadow-xl shadow-emerald-600/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-emerald-500">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                    👨‍🍳
                </div>
                <div>
                    <h3 class="font-black text-lg">Order Confirmed by Admin!</h3>
                    <p class="text-xs text-emerald-100 mt-0.5">Kitchen is preparing your food. Waiting for a nearby rider to pick up...</p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2 bg-white/20 px-3.5 py-1.5 rounded-xl text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                <span>Rider Pickup Pool Active</span>
            </div>
        </div>

        <!-- 3. RIDER ASSIGNED (PREPARING / HEADING TO PICKUP) -->
        <div x-show="(currentStatus === 'confirmed' || currentStatus === 'preparing') && currentRiderName" x-transition class="mb-8 p-5 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-3xl shadow-xl shadow-orange-500/25 flex items-center justify-between gap-4 border border-orange-400">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                    🛵
                </div>
                <div>
                    <h3 class="font-black text-lg">Rider Assigned: <span x-text="currentRiderName"></span></h3>
                    <p class="text-xs text-orange-100 mt-0.5">Your rider has accepted this order and is heading to the kitchen for pickup!</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="toggleChatBox(true)" class="px-4 py-2 bg-slate-900/25 hover:bg-slate-900/40 text-white font-black text-xs rounded-xl shadow-md flex items-center gap-1.5 transition-colors cursor-pointer border border-white/20">
                    <span>💬 Message Rider</span>
                </button>
                <template x-if="currentRiderPhone">
                    <a :href="'tel:' + currentRiderPhone" class="px-4 py-2 bg-white text-orange-600 font-black text-xs rounded-xl shadow-md flex items-center gap-1.5 hover:bg-orange-50 transition-colors">
                        <span>📞 Call Rider</span>
                    </a>
                </template>
            </div>
        </div>

        <!-- 4. DISPATCHED / DELIVERING BANNER -->
        <div x-show="currentStatus === 'delivering'" x-transition class="mb-8 p-5 bg-purple-600 text-white rounded-3xl shadow-xl shadow-purple-600/20 flex items-center justify-between gap-4 border border-purple-500">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0 animate-pulse">
                    🛵
                </div>
                <div>
                    <h3 class="font-black text-lg">Order is Out for Delivery!</h3>
                    <p class="text-xs text-purple-100 mt-0.5" x-text="currentRiderName ? 'Rider ' + currentRiderName + ' is heading to your address.' : 'Our delivery rider is heading to your location.'"></p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="toggleChatBox(true)" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white font-black text-xs rounded-xl shadow-md flex items-center gap-1.5 transition-colors cursor-pointer border border-white/30">
                    <span>💬 Message Rider</span>
                </button>
                <template x-if="currentRiderPhone">
                    <a :href="'tel:' + currentRiderPhone" class="px-4 py-2 bg-white text-purple-700 font-black text-xs rounded-xl shadow-md flex items-center gap-1.5 hover:bg-purple-50 transition-colors">
                        <span>📞 Call Rider</span>
                    </a>
                </template>
            </div>
        </div>

        <!-- 5. COMPLETED BANNER -->
        <div x-show="currentStatus === 'completed'" x-transition class="mb-8 p-5 bg-blue-600 text-white rounded-3xl shadow-xl shadow-blue-600/20 flex items-center gap-4 border border-blue-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                ✅
            </div>
            <div>
                <h3 class="font-black text-lg">Order Completed &amp; Delivered!</h3>
                <p class="text-xs text-blue-100 mt-0.5">အစားအသောက် ပို့ဆောင်မှု ပြီးစီးပါပြီ။ Thank you for ordering with us. Enjoy your meal!</p>
            </div>
        </div>

        <!-- 6. CANCELLED / REJECTED BANNER -->
        <div x-show="currentStatus === 'cancelled'" x-transition class="mb-8 p-5 bg-red-600 text-white rounded-3xl shadow-xl shadow-red-600/20 flex items-center gap-4 border border-red-500">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0">
                ❌
            </div>
            <div>
                <h3 class="font-black text-lg">Order Cancelled</h3>
                <p class="text-xs text-red-100 mt-0.5" x-text="currentNotes && currentNotes.trim() !== '' ? currentNotes : 'The order was cancelled by the administrator.'"></p>
            </div>
        </div>

        <!-- Header Status Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 mb-8 transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full">Order #{{ $order->order_number }}</span>
                        <span class="text-xs text-slate-400 font-medium">{{ $order->created_at->format('M d, Y • h:i A') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-2">Order Details</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Dynamic Status Badge -->
                    <span class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                        :class="{
                            'bg-amber-100 text-amber-700 border border-amber-200': currentStatus === 'pending',
                            'bg-emerald-100 text-emerald-700 border border-emerald-200': currentStatus === 'confirmed' || currentStatus === 'preparing',
                            'bg-purple-100 text-purple-700 border border-purple-200': currentStatus === 'delivering',
                            'bg-blue-100 text-blue-700 border border-blue-200': currentStatus === 'completed',
                            'bg-red-100 text-red-700 border border-red-200': currentStatus === 'cancelled'
                        }">
                        Order Status: <span x-text="currentStatus.toUpperCase()"></span>
                    </span>

                    <!-- Dynamic Payment Status Badge -->
                    <span class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                        :class="{
                            'bg-green-100 text-green-700 border border-green-200': currentPaymentStatus === 'paid',
                            'bg-purple-100 text-purple-700 border border-purple-200': currentPaymentStatus === 'pending_verification',
                            'bg-orange-100 text-orange-700 border border-orange-200': currentPaymentStatus === 'unpaid'
                        }">
                        Payment: <span x-text="currentPaymentStatus.replace('_', ' ').toUpperCase()"></span>
                    </span>
                </div>
            </div>

            <!-- Grid Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <!-- Delivery Info -->
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">📍 Delivery Info</p>
                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $order->delivery_township ?? 'Yangon' }}</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">{{ $order->delivery_address }}</p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-2">📞 {{ $order->delivery_phone }}</p>
                </div>

                <!-- Payment Method & Payslip Info -->
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">💳 Payment Method</p>
                            <a href="{{ route('orders.payslip', $order) }}" target="_blank" class="text-[11px] font-bold text-[#D70F64] hover:underline flex items-center gap-1">
                                <span>🧾 Foodpanda Payslip &rarr;</span>
                            </a>
                        </div>
                        <p class="font-black text-slate-900 dark:text-white uppercase text-base mb-1">
                            @if($order->payment_method === 'cod') 💵 Cash on Delivery
                            @elseif($order->payment_method === 'kbzpay') 📱 KBZPay
                            @elseif($order->payment_method === 'wavepay') 🌊 WavePay
                            @else {{ $order->payment_method }} @endif
                        </p>
                        
                        @if(in_array($order->payment_method, ['kbzpay', 'wavepay']))
                            @if($order->payment_screenshot)
                                <div class="mt-2.5 flex items-center gap-2">
                                    <button type="button" @click="imgTitle = 'Payment Transfer Screenshot'; imgModal = true; imgSrc = '{{ asset($order->payment_screenshot) }}'"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800/60 rounded-lg text-xs font-bold hover:bg-orange-100 transition-colors cursor-pointer">
                                        <span>🧾 Transfer Screenshot</span>
                                        <span>🔍</span>
                                    </button>
                                </div>
                            @else
                                <p class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-1">⚠️ No transfer screenshot uploaded yet</p>
                            @endif
                        @else
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pay cash upon delivery (COD)</p>
                        @endif
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex flex-col gap-2">
                        <!-- Direct Link to Foodpanda Official Printable Payslip -->
                        <a href="{{ route('orders.payslip', $order) }}" target="_blank"
                           class="w-full py-2 bg-gradient-to-r from-[#D70F64] to-[#E21B70] hover:from-[#c20d5a] hover:to-[#cb1864] text-white font-bold rounded-xl text-xs shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                            <span>🧾</span>
                            <span>Official Foodpanda Payslip / Receipt</span>
                        </a>

                        @if(in_array($order->payment_method, ['kbzpay', 'wavepay']))
                            <button type="button" @click="uploadSlipModal = true" 
                                    class="w-full py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>📸</span>
                                <span>{{ $order->payment_screenshot ? 'Update Transfer Slip' : 'Upload Transfer Slip' }}</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Assigned Rider / Notes -->
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">🛵 Delivery Rider</p>
                    <div x-show="currentRiderName">
                        <p class="font-bold text-slate-900 dark:text-white" x-text="currentRiderName"></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="currentRiderPhone ? '📞 ' + currentRiderPhone : ''"></p>
                    </div>
                    <div x-show="!currentRiderName">
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">Waiting for pickup...</p>
                    </div>

                    <!-- Direct Chat Shortcut Button -->
                    <div class="mt-2.5 flex items-center gap-2">
                        <button type="button" @click="toggleChatBox(true)" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold rounded-xl text-xs shadow-sm transition-all cursor-pointer active:scale-95">
                            <span>💬 View Rider Chat</span>
                            <template x-if="messages.length > 0">
                                <span class="px-1.5 py-0.2 rounded-full bg-white text-purple-700 text-[10px] font-black" x-text="messages.length"></span>
                            </template>
                        </button>
                    </div>

                    <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-400">
                        <span class="font-bold text-slate-500">Notes: </span>
                        <span x-text="currentNotes && currentNotes.trim() !== '' ? currentNotes : 'None'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== LIVE ORDER CHAT & HISTORY (Always accessible, read-only when completed) ===== -->
        <div id="order-chat-section" 
             class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 sm:p-7 mb-8 transition-colors">
            
            <!-- Interactive Header (Click to Open / Close) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer select-none"
                 :class="{ 'border-b border-slate-100 dark:border-slate-800 pb-5 mb-5': chatBoxOpen }"
                 @click="toggleChatBox()">
                
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-purple-500/20 shrink-0">
                        💬
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">
                                <span x-show="currentStatus !== 'completed' && currentStatus !== 'cancelled'">Message Your Rider</span>
                                <span x-show="currentStatus === 'completed' || currentStatus === 'cancelled'">Rider Chat History</span>
                            </h2>
                            
                            <!-- Live status indicator vs Archived badge -->
                            <template x-if="currentStatus !== 'completed' && currentStatus !== 'cancelled'">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-700 text-emerald-600 dark:text-emerald-400 font-bold text-[10px] flex items-center gap-1.5 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Live Chat</span>
                                </span>
                            </template>
                            <template x-if="currentStatus === 'completed'">
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-700 text-blue-600 dark:text-blue-400 font-bold text-[10px] flex items-center gap-1 shadow-sm">
                                    <span>✓ Delivered (Chat Archived)</span>
                                </span>
                            </template>

                            <template x-if="messages.length > 0">
                                <span class="px-2 py-0.5 rounded-full bg-orange-100 dark:bg-orange-950/80 text-orange-600 dark:text-orange-400 font-bold text-[10px]" x-text="messages.length + ' msgs'"></span>
                            </template>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Direct communication with <strong class="text-slate-800 dark:text-slate-200" x-text="currentRiderName || 'Delivery Rider'"></strong> • <span x-text="chatBoxOpen ? 'Tap to minimize' : 'Tap to open chat box'"></span>
                        </p>
                    </div>
                </div>

                <!-- Right Controls: Call Rider + Toggle Open/Close Button -->
                <div class="flex items-center gap-2.5 self-start sm:self-auto" @click.stop>
                    <template x-if="currentRiderPhone">
                        <a :href="'tel:' + currentRiderPhone" 
                           class="px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-xl transition-all flex items-center gap-2 cursor-pointer">
                            <span>📞</span>
                            <span>Call <span x-text="currentRiderName || 'Rider'"></span></span>
                        </a>
                    </template>

                    <button type="button" 
                            @click="toggleChatBox()" 
                            class="px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer shadow-sm"
                            :class="chatBoxOpen 
                                ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700' 
                                : 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-orange-500/25 hover:from-orange-600 hover:to-amber-600 active:scale-95'">
                        <span x-text="chatBoxOpen ? '▲ Minimize' : '💬 Open Chat'"></span>
                    </button>
                </div>
            </div>

            <!-- Collapsible Chat Box Body (Expands when clicked) -->
            <div x-show="chatBoxOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" style="display: none;">
                
                <!-- Quick Preset Reply Chips (Active when delivery in progress) -->
                <div x-show="currentStatus !== 'completed' && currentStatus !== 'cancelled'" class="mb-4">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Quick Messages (1-Tap):</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="sendChatMessage('👋 Hello, I am waiting for the delivery!')" class="px-3 py-1.5 bg-slate-100 hover:bg-orange-100 dark:bg-slate-800 dark:hover:bg-orange-950/50 text-slate-700 dark:text-slate-300 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl transition-all cursor-pointer active:scale-95">
                            👋 Waiting for delivery
                        </button>
                        <button type="button" @click="sendChatMessage('🚪 Please leave the package at my door / gate.')" class="px-3 py-1.5 bg-slate-100 hover:bg-orange-100 dark:bg-slate-800 dark:hover:bg-orange-950/50 text-slate-700 dark:text-slate-300 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl transition-all cursor-pointer active:scale-95">
                            🚪 Leave at door / gate
                        </button>
                        <button type="button" @click="sendChatMessage('⏳ Hi! How long until you arrive?')" class="px-3 py-1.5 bg-slate-100 hover:bg-orange-100 dark:bg-slate-800 dark:hover:bg-orange-950/50 text-slate-700 dark:text-slate-300 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl transition-all cursor-pointer active:scale-95">
                            ⏳ How long until arrival?
                        </button>
                        <button type="button" @click="sendChatMessage('📞 Please give me a call when you arrive downstairs.')" class="px-3 py-1.5 bg-slate-100 hover:bg-orange-100 dark:bg-slate-800 dark:hover:bg-orange-950/50 text-slate-700 dark:text-slate-300 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl transition-all cursor-pointer active:scale-95">
                            📞 Call when you arrive
                        </button>
                    </div>
                </div>

                <!-- Scrollable Message Feed -->
                <div id="chat-messages-container" 
                     class="h-72 sm:h-80 overflow-y-auto p-4 sm:p-5 bg-slate-50/80 dark:bg-slate-950/70 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 space-y-3.5 flex flex-col mb-4">
                    
                    <!-- Welcome / Empty Banner -->
                    <template x-if="messages.length === 0">
                        <div class="m-auto text-center py-8 px-4 space-y-2">
                            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto text-2xl">
                                💬
                            </div>
                            <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                                <span x-show="currentStatus !== 'completed' && currentStatus !== 'cancelled'">Direct Message Channel</span>
                                <span x-show="currentStatus === 'completed' || currentStatus === 'cancelled'">No Messages Recorded</span>
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                <span x-show="currentStatus !== 'completed' && currentStatus !== 'cancelled'">Send instructions, delivery tips, or questions directly to your assigned rider. Messages update in real time.</span>
                                <span x-show="currentStatus === 'completed' || currentStatus === 'cancelled'">No conversation was recorded for this order.</span>
                            </p>
                        </div>
                    </template>

                    <!-- Message Bubbles -->
                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex flex-col" :class="msg.is_me ? 'items-end' : 'items-start'">
                            
                            <!-- Sender Label & Role Badge -->
                            <div class="flex items-center gap-1.5 mb-1 px-1 text-[11px] text-slate-400">
                                <span class="font-bold text-slate-600 dark:text-slate-300" x-text="msg.is_me ? 'You' : msg.sender_name"></span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase"
                                      :class="{
                                          'bg-purple-500/20 text-purple-600 dark:text-purple-300': msg.sender_role === 'rider',
                                          'bg-orange-500/20 text-orange-600 dark:text-orange-300': msg.sender_role === 'customer',
                                          'bg-blue-500/20 text-blue-600 dark:text-blue-300': msg.sender_role === 'admin'
                                      }"
                                      x-text="msg.sender_role">
                                </span>
                                <span class="text-[10px] text-slate-400" x-text="msg.time_formatted"></span>
                            </div>

                            <!-- Bubble Box -->
                            <div class="max-w-[85%] sm:max-w-[75%] px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-sm break-words"
                                 :class="msg.is_me 
                                     ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-tr-sm font-medium shadow-orange-500/10' 
                                     : (msg.sender_role === 'rider' 
                                         ? 'bg-purple-600 text-white rounded-tl-sm font-medium shadow-purple-600/10' 
                                         : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-tl-sm')">
                                <p x-text="msg.message"></p>
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Chat Input Bar (Active during delivery) -->
                <div x-show="currentStatus !== 'completed' && currentStatus !== 'cancelled'">
                    <form @submit.prevent="sendChatMessage()" class="flex items-center gap-2">
                        <input type="text" 
                               id="customer-chat-input"
                               x-model="chatInput" 
                               placeholder="Write a message to your rider..."
                               :disabled="isSendingChat"
                               class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                        
                        <button type="submit" 
                                :disabled="isSendingChat || !chatInput.trim()"
                                class="px-5 py-3 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all flex items-center gap-2 cursor-pointer shrink-0">
                            <span x-show="!isSendingChat">Send</span>
                            <span x-show="isSendingChat" class="animate-spin">⏳</span>
                            <svg x-show="!isSendingChat" class="w-4 h-4 rotate-45 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Completed / Archived Chat Notice -->
                <div x-show="currentStatus === 'completed' || currentStatus === 'cancelled'" 
                     class="p-4 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-600 dark:text-slate-300">
                    <div class="flex items-center gap-2 font-medium">
                        <span class="text-base">🔒</span>
                        <span>This order has been completed & delivered. Chat session is archived for your records.</span>
                    </div>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 self-start sm:self-auto shrink-0">✓ Delivery Completed</span>
                </div>

            </div>

        </div>

        <!-- Items Table Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 mb-8 transition-colors">
            <h2 class="text-lg font-black text-slate-900 dark:text-white mb-6">Ordered Items</h2>

            <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 mb-6">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3 text-center">Quantity</th>
                            <th class="px-4 py-3 text-right">Unit Price</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-800 dark:text-slate-200 font-medium">
                        @foreach ($order->orderItems as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden shrink-0">
                                            <img src="{{ $item->menuItem?->image_url ?? asset('images/hero_food.png') }}"
                                                 alt="{{ $item->menuItem?->name }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ $item->menuItem?->name ?? 'Menu Item' }}</h3>
                                            <p class="text-xs text-slate-400 font-medium">{{ $item->menuItem?->category?->name ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold font-mono text-slate-700 dark:text-slate-300">
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">{{ $item->quantity }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-600 dark:text-slate-400">
                                    {{ number_format($item->unit_price) }} MMK
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-slate-900 dark:text-white">
                                    {{ number_format($item->subtotal) }} MMK
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cost Summary Footer -->
            @php
                $itemsSubtotal = $order->orderItems->sum('subtotal');
                if ($itemsSubtotal == 0) {
                    $itemsSubtotal = $order->total_amount - $order->delivery_fee - ($order->tax_amount ?? 0);
                }
                $displayTax = $order->tax_amount > 0 ? $order->tax_amount : round($itemsSubtotal * 0.05);
            @endphp
            <div class="border-t border-slate-100 dark:border-slate-800 pt-6 mt-6 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>{{ __('Subtotal') }}</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($itemsSubtotal) }} MMK</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <span>{{ __('Tax (5%)') }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold uppercase">{{ __('Tax') }}</span>
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">+{{ number_format($displayTax) }} MMK</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>{{ __('Delivery Fee') }}</span>
                    <span class="font-bold text-slate-900 dark:text-white">+{{ number_format($order->delivery_fee) }} MMK</span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between items-center">
                    <span class="font-black text-slate-900 dark:text-white text-base">{{ __('Total Amount') }}</span>
                    <span class="font-black text-orange-500 text-xl">{{ number_format($order->total_amount) }} MMK</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
            <a href="/" class="w-full sm:w-auto px-6 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-black text-sm rounded-2xl shadow-lg shadow-orange-500/25 transition-all text-center">
                ← Back to Menu
            </a>
            <a href="{{ route('customer.orders.index') }}" class="w-full sm:w-auto px-6 py-3.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-sm rounded-2xl transition-all text-center">
                📦 View My Orders
            </a>
            <a href="{{ route('customer.complaints.create', ['order_id' => $order->id]) }}" class="w-full sm:w-auto px-6 py-3.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 font-black text-sm rounded-2xl transition-all text-center shadow-sm flex items-center justify-center gap-2">
                <span>🚨</span>
                <span>Report Issue to Admin</span>
            </a>
        </div>

    </main>

    <!-- Floating Chat Launcher Button (Active during delivery) -->
    <div x-show="currentStatus !== 'completed' && currentStatus !== 'cancelled'" 
         x-transition 
         class="fixed bottom-6 right-6 z-40">
        <button type="button" 
                @click="toggleChatBox(true)"
                class="flex items-center gap-2.5 px-5 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white font-black text-xs sm:text-sm rounded-full shadow-2xl shadow-orange-500/40 hover:scale-105 transition-all cursor-pointer border-2 border-white/20">
            <span class="text-base animate-bounce">💬</span>
            <span>Message Rider</span>
            <template x-if="messages.length > 0">
                <span class="px-2 py-0.5 rounded-full bg-white text-orange-600 text-[10px] font-black" x-text="messages.length"></span>
            </template>
        </button>
    </div>

    <!-- Screenshot & Proof Modal -->
    <div x-show="imgModal" x-transition class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" style="display:none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 max-w-lg w-full relative shadow-2xl space-y-3" @click.outside="imgModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <p class="font-bold text-slate-900 dark:text-white text-sm" x-text="imgTitle"></p>
                <button @click="imgModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center cursor-pointer">✕</button>
            </div>
            <img :src="imgSrc" :alt="imgTitle" class="w-full h-auto rounded-2xl border border-slate-100 dark:border-slate-800 max-h-[70vh] object-contain mx-auto">
        </div>
    </div>

    <!-- Upload / Replace Payslip Modal for Customer -->
    <div x-show="uploadSlipModal" 
         x-cloak 
         x-transition 
         class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" 
         style="display:none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-md w-full relative shadow-2xl space-y-4" @click.outside="uploadSlipModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm">
                        🧾
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-sm">Upload Payment Payslip</h3>
                        <p class="text-[11px] text-slate-400">Order #{{ $order->order_number }}</p>
                    </div>
                </div>
                <button @click="uploadSlipModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white font-bold flex items-center justify-center transition-colors cursor-pointer">✕</button>
            </div>

            <!-- Merchant Transfer Details Box -->
            <div class="p-3.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl text-xs text-blue-950 dark:text-blue-200 space-y-1.5">
                <div class="flex items-center justify-between font-black">
                    <span>Payable: {{ number_format($order->total_amount) }} MMK</span>
                    <span class="uppercase text-[10px] px-2 py-0.5 bg-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full font-black">{{ strtoupper($order->payment_method) }}</span>
                </div>
                <p class="text-[11px] opacity-80">Food Express Account: <strong class="font-mono">09-987654321</strong> (Food Express MM)</p>
            </div>

            <form method="POST" action="{{ route('customer.orders.upload_payslip', $order) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Select Payslip / Transfer Screenshot <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="payment_screenshot" required accept="image/*"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-orange-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-500 file:text-white cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Supported formats: JPG, PNG, WEBP, GIF (Max 5MB)</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="uploadSlipModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                        Upload &amp; Submit 🚀
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
