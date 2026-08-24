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

        <x-slot:actions>
            <button @click="createModalOpen = true" 
                    type="button"
                    class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-md shadow-orange-500/25 transition-all flex items-center gap-1.5 cursor-pointer">
                <span>+</span>
                <span>{{ __('Create New Rider') }}</span>
            </button>
        </x-slot:actions>

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
            <div class="flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900 dark:text-white">{{ __('Rider Accounts') }}</h2>
            </div>

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

        <!-- ================= CREATE NEW RIDER MODAL ================= -->
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
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center text-xl border border-orange-100 dark:border-orange-900">🛵</div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Create New Rider Account') }}</h3>
                    </div>
                    <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 text-lg font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('admin.riders.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                    
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Rider Full Name') }} <span class="text-orange-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Mg Mg Rider" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Email Address (Login ID)') }} <span class="text-orange-500">*</span></label>
                        <input type="email" name="email" required placeholder="rider@foodorder.com" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Phone Number') }} <span class="text-orange-500">*</span></label>
                        <input type="text" name="phone_number" required placeholder="09xxxxxxxxx" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('City / Zone') }}</label>
                        <input type="text" name="city" value="Yangon" placeholder="Yangon" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Password') }} <span class="text-orange-500">*</span></label>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition-all cursor-pointer">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">{{ __('Create Rider') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= EDIT RIDER MODAL ================= -->
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
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center text-xl border border-orange-100 dark:border-orange-900">✏️</div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Edit Rider Details') }}</h3>
                    </div>
                    <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 text-lg font-bold">✕</button>
                </div>

                <form method="POST" :action="editRiderUrl" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                    
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Rider Full Name') }} <span class="text-orange-500">*</span></label>
                        <input type="text" name="name" x-model="editRiderName" required placeholder="e.g. Mg Mg Rider" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Email Address (Login ID)') }} <span class="text-orange-500">*</span></label>
                        <input type="email" name="email" x-model="editRiderEmail" required placeholder="rider@foodorder.com" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('Phone Number') }} <span class="text-orange-500">*</span></label>
                        <input type="text" name="phone_number" x-model="editRiderPhone" required placeholder="09xxxxxxxxx" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('City / Zone') }}</label>
                        <input type="text" name="city" x-model="editRiderCity" placeholder="Yangon" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">{{ __('New Password') }} <span class="text-slate-500 dark:text-slate-400">({{ __('Leave blank to keep current') }})</span></label>
                        <input type="password" name="password" placeholder="••••••••" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:outline-none text-sm">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition-all cursor-pointer">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</x-admin-layout>
