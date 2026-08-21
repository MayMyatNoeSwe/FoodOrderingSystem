<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('File a Complaint / Report Issue') }} - {{ config('app.name', 'Food Ordering System') }}</title>

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

    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10" x-data="{
        category: '{{ old('category', 'order_issue') }}',
        priority: '{{ old('priority', 'medium') }}',
        photoPreview: null,
        handlePhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.photoPreview = URL.createObjectURL(file);
            } else {
                this.photoPreview = null;
            }
        }
    }">

        <!-- Navigation Breadcrumb -->
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-6 font-semibold">
            <a href="{{ route('home') }}" class="hover:text-orange-500 transition-colors">{{ __('Home') }}</a>
            <span>/</span>
            <a href="{{ route('customer.help') }}" class="hover:text-orange-500 transition-colors">{{ __('Help & Complaints') }}</a>
            <span>/</span>
            <span class="text-slate-800 dark:text-slate-200">{{ __('File a Complaint') }}</span>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-semibold space-y-1">
                <p class="font-bold">⚠️ Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl p-6 sm:p-10">
            
            <div class="border-b border-slate-100 dark:border-slate-800 pb-5 mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center text-2xl shrink-0">
                        ✍️
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ __('Submit a Complaint Ticket') }}</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('Provide accurate details and photos so our Admin team can quickly investigate and resolve your issue.') }}
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('customer.complaints.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- 1. Issue Category Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        {{ __('Issue Category') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 select-none"
                               :class="category === 'order_issue' ? 'border-orange-500 bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="category" value="order_issue" x-model="category" class="hidden">
                            <span class="text-xl shrink-0">📦</span>
                            <span class="text-xs">{{ __('Order Issue / Missing Items') }}</span>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 select-none"
                               :class="category === 'food_quality' ? 'border-orange-500 bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="category" value="food_quality" x-model="category" class="hidden">
                            <span class="text-xl shrink-0">🍲</span>
                            <span class="text-xs">{{ __('Food Quality / Cold Food') }}</span>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 select-none"
                               :class="category === 'rider_behavior' ? 'border-orange-500 bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="category" value="rider_behavior" x-model="category" class="hidden">
                            <span class="text-xl shrink-0">🛵</span>
                            <span class="text-xs">{{ __('Rider Conduct / Delivery') }}</span>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 select-none"
                               :class="category === 'payment_issue' ? 'border-orange-500 bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="category" value="payment_issue" x-model="category" class="hidden">
                            <span class="text-xl shrink-0">💳</span>
                            <span class="text-xs">{{ __('Payment / Refund') }}</span>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 select-none"
                               :class="category === 'app_issue' ? 'border-orange-500 bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="category" value="app_issue" x-model="category" class="hidden">
                            <span class="text-xl shrink-0">📱</span>
                            <span class="text-xs">{{ __('App / Technical Bug') }}</span>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 select-none"
                               :class="category === 'other' ? 'border-orange-500 bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="category" value="other" x-model="category" class="hidden">
                            <span class="text-xl shrink-0">💬</span>
                            <span class="text-xs">{{ __('Other Inquiry') }}</span>
                        </label>
                    </div>
                </div>

                <!-- 2. Related Order Dropdown (Optional) -->
                <div>
                    <label for="order_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        {{ __('Related Order') }} <span class="text-slate-400 font-normal">({{ __('Optional if not order specific') }})</span>
                    </label>
                    <select name="order_id" id="order_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                        <option value="">-- {{ __('No specific order / General issue') }} --</option>
                        @foreach($recentOrders as $ro)
                            <option value="{{ $ro->id }}" {{ (old('order_id', $selectedOrderId) == $ro->id) ? 'selected' : '' }}>
                                📦 Order #{{ $ro->order_number }} • {{ number_format($ro->total_amount) }} MMK • {{ $ro->created_at->format('M d, Y') }} ({{ ucfirst($ro->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Priority Level -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        {{ __('Urgency / Priority') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <label class="p-3 rounded-xl border text-center cursor-pointer transition-all select-none text-xs font-bold"
                               :class="priority === 'low' ? 'bg-slate-500/15 text-slate-700 dark:text-slate-200 border-slate-500 ring-2 ring-slate-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="priority" value="low" x-model="priority" class="hidden">
                            <span>🟢 {{ __('Low') }}</span>
                        </label>

                        <label class="p-3 rounded-xl border text-center cursor-pointer transition-all select-none text-xs font-bold"
                               :class="priority === 'medium' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="priority" value="medium" x-model="priority" class="hidden">
                            <span>🟡 {{ __('Medium') }}</span>
                        </label>

                        <label class="p-3 rounded-xl border text-center cursor-pointer transition-all select-none text-xs font-bold"
                               :class="priority === 'high' ? 'bg-orange-500/15 text-orange-600 dark:text-orange-400 border-orange-500 ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="priority" value="high" x-model="priority" class="hidden">
                            <span>🟠 {{ __('High') }}</span>
                        </label>

                        <label class="p-3 rounded-xl border text-center cursor-pointer transition-all select-none text-xs font-bold"
                               :class="priority === 'urgent' ? 'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500 ring-2 ring-red-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <input type="radio" name="priority" value="urgent" x-model="priority" class="hidden">
                            <span>🔴 {{ __('Urgent') }}</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Subject Line -->
                <div>
                    <label for="subject" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        {{ __('Complaint Title / Subject') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="subject" 
                           id="subject" 
                           value="{{ old('subject') }}"
                           required 
                           maxlength="150"
                           placeholder="e.g. Missing Fried Rice from Order, Rider arrived 45 mins late..."
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                </div>

                <!-- 5. Description Textarea -->
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        {{ __('Detailed Description of the Issue') }} <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="5" 
                              required 
                              maxlength="3000"
                              placeholder="Please describe exactly what happened (e.g. which dish was missing, packaging condition, delivery notes)..."
                              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all leading-relaxed">{{ old('description') }}</textarea>
                </div>

                <!-- 6. Attachment Photo Proof -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        {{ __('Photo Evidence / Screenshot Proof') }} <span class="text-slate-400 font-normal">({{ __('Optional but recommended') }})</span>
                    </label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <label class="px-5 py-3 bg-slate-100 hover:bg-orange-50 dark:bg-slate-800 dark:hover:bg-slate-700 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-200 transition-all flex items-center gap-2">
                            <span>📷</span>
                            <span>{{ __('Upload Photo Evidence') }}</span>
                            <input type="file" name="attachment_photo" accept="image/*" class="hidden" @change="handlePhoto($event)">
                        </label>

                        <span class="text-[11px] text-slate-400">{{ __('Supports JPG, PNG, WEBP (Max 5MB)') }}</span>
                    </div>

                    <!-- Live Image Preview -->
                    <template x-if="photoPreview">
                        <div class="mt-4 relative inline-block">
                            <img :src="photoPreview" alt="Evidence Preview" class="w-32 h-32 object-cover rounded-2xl border-2 border-orange-500 shadow-md">
                            <button type="button" @click="photoPreview = null; $el.closest('form').querySelector('input[name=attachment_photo]').value = '';" class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-500 text-white font-bold text-xs flex items-center justify-center shadow-lg hover:scale-110 transition-transform">✕</button>
                        </div>
                    </template>
                </div>

                <!-- Submit Button Bar -->
                <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <a href="{{ route('customer.help') }}" class="w-full sm:w-auto px-6 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-2xl transition-all text-center">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs sm:text-sm rounded-2xl shadow-xl shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                        <span>📤</span>
                        <span>{{ __('Submit Complaint to Admin') }}</span>
                    </button>
                </div>

            </form>

        </div>

    </main>

    <!-- Storefront Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-8 text-center text-xs text-slate-500 dark:text-slate-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 space-y-2">
            <p>© {{ date('Y') }} {{ config('app.name', 'Food Ordering System') }}. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
