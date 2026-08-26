<x-admin-layout 
    active="riders" 
    title="Rider Management - {{ config('app.name', 'Food Ordering System') }}"
    heading="{{ __('Rider Management System') }}"
    subheading="{{ __('Manage delivery personnel, track active deliveries, add, edit, and remove riders') }}">

    <x-slot:head>
        <script>
            function confirmDeleteRider(form, riderName) {
                Swal.fire({
                    title: 'Delete Rider \'' + riderName + '\'?',
                    html: `Are you sure you want to delete rider <strong class="text-orange-500">'${riderName}'</strong>?<br><span class="text-xs text-slate-500 mt-1 block">Any active assigned orders will become unassigned.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Delete Rider',
                    cancelButtonText: 'Cancel',
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'border border-slate-200 rounded-3xl shadow-2xl',
                        title: 'text-slate-900 font-bold text-lg',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg shadow-red-500/20 cursor-pointer',
                        cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs cursor-pointer'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
                return false;
            }
        </script>
    </x-slot:head>

    <x-slot:badge>
        <span class="bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
            {{ $riders->total() }} {{ __('Riders') }}
        </span>
    </x-slot:badge>

    <div x-data="{ 
        createModalOpen: false,
        editModalOpen: false,
        editRiderId: null,
        editRiderName: '',
        editRiderEmail: '',
        editRiderPhone: '',
        editRiderCity: '',
        editRiderUrl: '',

        openEditModal(id, name, email, phone, city, url) {
            this.editRiderId = id;
            this.editRiderName = name;
            this.editRiderEmail = email;
            this.editRiderPhone = phone;
            this.editRiderCity = city || 'Yangon';
            this.editRiderUrl = url;
            this.editModalOpen = true;
        }
    }" class="space-y-6">

        @if(isset($errors) && $errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-2xl text-red-700 dark:text-red-400 text-xs font-semibold space-y-1 shadow-xs">
                <div class="font-bold mb-1">{{ __('Please fix the following errors:') }}</div>
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total Registered Riders') }}</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $riders->total() }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Active Deliveries Now') }}</p>
                <p class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-2">{{ $riders->sum('active_deliveries_count') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('Total Completed Deliveries') }}</p>
                <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ $riders->sum('completed_deliveries_count') }}</p>
            </div>
        </div>

        <!-- Riders Table Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Rider Accounts') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ __('Manage registered delivery rider fleet and delivery status') }}</p>
                </div>
                <button @click="createModalOpen = true" 
                        type="button"
                        class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>{{ __('Create New Rider') }}</span>
                </button>
            </div>

            <!-- Search & Filter Controls Toolbar -->
            <form method="GET" action="{{ route('admin.riders.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 bg-slate-50/80 dark:bg-slate-800/60 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-700 flex-wrap">
                
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[180px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search"
                           value="{{ $search ?? '' }}" 
                           placeholder="{{ __('Search by name, email, phone, city...') }}"
                           class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                    @if(!empty($search))
                        <a href="{{ route('admin.riders.index', request()->except('search')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</a>
                    @endif
                </div>

                <!-- City Selector -->
                <div class="w-full sm:w-36 shrink-0">
                    <select name="city" onchange="this.form.submit()"
                            class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="all">City: All</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ ($city ?? '') == $c ? 'selected' : '' }}>📍 {{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Delivery Status Selector -->
                <div class="w-full sm:w-36 shrink-0">
                    <select name="status" onchange="this.form.submit()"
                            class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="all" {{ ($status ?? '') === 'all' ? 'selected' : '' }}>Status: All</option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>🛵 Delivering Now</option>
                        <option value="idle" {{ ($status ?? '') === 'idle' ? 'selected' : '' }}>🟢 Idle / Ready</option>
                    </select>
                </div>

                <!-- Sort By Selector -->
                <div class="w-full sm:w-44 shrink-0">
                    <select name="sort_by" onchange="this.form.submit()"
                            class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 focus:border-orange-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        <option value="latest" {{ ($sortBy ?? '') === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                        <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                        <option value="name_asc" {{ ($sortBy ?? '') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ ($sortBy ?? '') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="completed_desc" {{ ($sortBy ?? '') === 'completed_desc' ? 'selected' : '' }}>Completed: High to Low</option>
                        <option value="active_desc" {{ ($sortBy ?? '') === 'active_desc' ? 'selected' : '' }}>Active: High to Low</option>
                    </select>
                </div>

                @if(!empty($search) || ($city && $city !== 'all') || ($status && $status !== 'all') || ($sortBy && $sortBy !== 'latest'))
                    <a href="{{ route('admin.riders.index') }}" class="px-2.5 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5">{{ __('Rider Name / Phone') }}</th>
                            <th class="px-4 py-3.5">{{ __('Email') }}</th>
                            <th class="px-4 py-3.5">{{ __('City / Zone') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Active Deliveries') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Completed Deliveries') }}</th>
                            <th class="px-4 py-3.5">{{ __('Joined Date') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($riders as $rider)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-950/50 border border-orange-100 dark:border-orange-900 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg font-black shrink-0">
                                            🛵
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $rider->name }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">📞 {{ $rider->phone_number ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $rider->email }}</td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[11px] font-bold whitespace-nowrap">
                                        <span>📍</span><span>{{ $rider->city ?? 'Yangon' }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($rider->active_deliveries_count > 0)
                                        <span class="px-3 py-1 bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-black rounded-full text-xs">
                                            🛵 {{ $rider->active_deliveries_count }} {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 text-xs font-semibold">0 ({{ __('Available') }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 font-black rounded-full text-xs">
                                        ✅ {{ $rider->completed_deliveries_count }} {{ __('Done') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-slate-500 dark:text-slate-400 text-[11px]">
                                    {{ $rider->created_at ? $rider->created_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <!-- Actions: Edit & Delete -->
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Trigger -->
                                        <button @click="openEditModal({{ $rider->id }}, '{{ addslashes($rider->name) }}', '{{ addslashes($rider->email) }}', '{{ addslashes($rider->phone_number ?? '') }}', '{{ addslashes($rider->city ?? 'Yangon') }}', '{{ route('admin.riders.update', $rider) }}')" 
                                                type="button"
                                                class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-700 transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                            <span>✏️</span>
                                            <span>{{ __('Edit') }}</span>
                                        </button>

                                        <!-- Delete Form -->
                                        <form method="POST" action="{{ route('admin.riders.destroy', $rider) }}" onsubmit="return confirmDeleteRider(this, '{{ addslashes($rider->name) }}');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                            <button type="submit" 
                                                    class="px-3 py-1.5 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-lg transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                <span>🗑️</span>
                                                <span>{{ __('Delete') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400 font-medium">
                                    {{ __('No riders registered yet. Click "Create New Rider" to add one!') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($riders->hasPages())
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $riders->links() }}
                </div>
            @endif
        </div>

        <!-- ================= CREATE RIDER MODAL ================= -->
        <template x-teleport="body">
            <div x-show="createModalOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                
                <div @click.outside="createModalOpen = false" 
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto no-scrollbar">
                    
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg border border-orange-100 dark:border-orange-900">🛵</div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('Create New Rider Account') }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">{{ __('Add rider personnel credentials for delivery dispatch') }}</p>
                            </div>
                        </div>
                        <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 text-base font-bold cursor-pointer">✕</button>
                    </div>

                    <form method="POST" action="{{ route('admin.riders.store') }}" class="space-y-3.5 text-xs">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                        
                        <!-- 1-Column: Full Name -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Rider Full Name') }} <span class="text-orange-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Mg Mg Rider" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: Email Address -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Email Address') }} <span class="text-orange-500">*</span></label>
                            <input type="email" name="email" required placeholder="rider@foodorder.com" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: Phone Number -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Phone Number') }} <span class="text-orange-500">*</span></label>
                            <input type="text" name="phone_number" required placeholder="09xxxxxxxxx" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: City / Zone -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('City / Zone') }}</label>
                            <input type="text" name="city" value="Yangon" placeholder="Yangon" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: Password -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Password') }} <span class="text-orange-500">*</span></label>
                            <input type="password" name="password" required placeholder="••••••••" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="createModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition-all cursor-pointer">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-5 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">{{ __('Create Rider') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ================= EDIT RIDER MODAL ================= -->
        <template x-teleport="body">
            <div x-show="editModalOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                
                <div @click.outside="editModalOpen = false" 
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto no-scrollbar">
                    
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg border border-orange-100 dark:border-orange-900">✏️</div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('Edit Rider Details') }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">{{ __('Update profile, contact info, or reset password') }}</p>
                            </div>
                        </div>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 text-base font-bold cursor-pointer">✕</button>
                    </div>

                    <form method="POST" :action="editRiderUrl" class="space-y-3.5 text-xs">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                        
                        <!-- 1-Column: Full Name -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Rider Full Name') }} <span class="text-orange-500">*</span></label>
                            <input type="text" name="name" x-model="editRiderName" required placeholder="e.g. Mg Mg Rider" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: Email Address -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Email Address') }} <span class="text-orange-500">*</span></label>
                            <input type="email" name="email" x-model="editRiderEmail" required placeholder="rider@foodorder.com" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: Phone Number -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('Phone Number') }} <span class="text-orange-500">*</span></label>
                            <input type="text" name="phone_number" x-model="editRiderPhone" required placeholder="09xxxxxxxxx" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: City / Zone -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('City / Zone') }}</label>
                            <input type="text" name="city" x-model="editRiderCity" placeholder="Yangon" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <!-- 1-Column: Password -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">{{ __('New Password') }} <span class="text-slate-500 dark:text-slate-400">({{ __('Leave blank to keep current') }})</span></label>
                            <input type="password" name="password" placeholder="••••••••" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-xs transition-colors">
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition-all cursor-pointer">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-5 py-2 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">{{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>

</x-admin-layout>
