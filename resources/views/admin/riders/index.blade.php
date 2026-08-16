<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rider Management - {{ config('app.name', 'Food Ordering System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white min-h-screen"
      x-data="{ 
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
      }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= ADMIN SIDEBAR ================= -->
        <x-admin-sidebar active="riders" />

        <!-- ================= MAIN CONTENT AREA ================= -->
        <main class="flex-1 flex flex-col min-w-0">

            <!-- Top Header -->
            <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-6 py-4 flex items-center justify-between shadow-sm">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                        <span>🛵</span> Rider Management System
                    </h1>
                    <p class="text-slate-500 text-xs mt-0.5 font-medium">Manage delivery personnel, track active orders, add, edit, and remove riders</p>
                </div>

                <button @click="createModalOpen = true" class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center gap-2 cursor-pointer">
                    <span>+</span>
                    <span>Create New Rider</span>
                </button>
            </header>

            <div class="p-6 sm:p-8 space-y-8 flex-1">

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold rounded-2xl flex items-center gap-3 shadow-sm">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-semibold space-y-1 shadow-sm">
                        <div class="font-bold mb-1">Please fix the following errors:</div>
                        @foreach($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm hover:border-slate-300 transition-all">
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Registered Riders</p>
                        <p class="text-3xl font-black text-slate-900 mt-2">{{ $riders->total() }}</p>
                    </div>
                    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm hover:border-slate-300 transition-all">
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active Deliveries Now</p>
                        <p class="text-3xl font-black text-purple-600 mt-2">{{ $riders->sum('active_deliveries_count') }}</p>
                    </div>
                    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm hover:border-slate-300 transition-all">
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Completed Deliveries</p>
                        <p class="text-3xl font-black text-emerald-600 mt-2">{{ $riders->sum('completed_deliveries_count') }}</p>
                    </div>
                </div>

                <!-- Riders Table -->
                <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-900">Rider Accounts</h2>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3.5">Rider Name / Phone</th>
                                    <th class="px-4 py-3.5">Email</th>
                                    <th class="px-4 py-3.5">City / Zone</th>
                                    <th class="px-4 py-3.5 text-center">Active Deliveries</th>
                                    <th class="px-4 py-3.5 text-center">Completed Deliveries</th>
                                    <th class="px-4 py-3.5">Joined Date</th>
                                    <th class="px-4 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                @forelse($riders as $rider)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-2xl bg-orange-50 border border-orange-100 text-orange-600 flex items-center justify-center text-lg font-black shrink-0">
                                                    🛵
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900 text-sm">{{ $rider->name }}</div>
                                                    <div class="text-[11px] text-slate-500">📞 {{ $rider->phone_number ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 font-mono text-slate-600 whitespace-nowrap">{{ $rider->email }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 text-slate-700 rounded-lg text-[11px] font-bold whitespace-nowrap">
                                                <span>📍</span><span>{{ $rider->city ?? 'Yangon' }}</span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @if($rider->active_deliveries_count > 0)
                                                <span class="px-3 py-1 bg-purple-50 border border-purple-200 text-purple-700 font-black rounded-full text-xs">
                                                    🛵 {{ $rider->active_deliveries_count }} Active
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-xs font-semibold">0 (Available)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 font-black rounded-full text-xs">
                                                ✅ {{ $rider->completed_deliveries_count }} Done
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-slate-500 text-[11px]">
                                            {{ $rider->created_at ? $rider->created_at->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <!-- Actions: Edit & Delete -->
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Edit Trigger -->
                                                <button @click="openEditModal({{ $rider->id }}, '{{ addslashes($rider->name) }}', '{{ addslashes($rider->email) }}', '{{ addslashes($rider->phone_number ?? '') }}', '{{ addslashes($rider->city ?? 'Yangon') }}', '{{ route('admin.riders.update', $rider) }}')" 
                                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 rounded-lg border border-slate-200 transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                    <span>✏️</span>
                                                    <span>Edit</span>
                                                </button>

                                                <!-- Delete Form -->
                                                <form method="POST" 
                                                      action="{{ route('admin.riders.destroy', $rider) }}" 
                                                      onsubmit="return confirmDeleteRider(this, '{{ addslashes($rider->name) }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg transition-all text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                        <span>🗑️</span>
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center text-slate-500 font-medium">
                                            No riders registered yet. Click "Create New Rider" to add one!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4">
                        {{ $riders->links() }}
                    </div>
                </div>

            </div>
        </main>
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
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl border border-orange-100">🛵</div>
                    <h3 class="text-lg font-black text-slate-900">Create New Rider Account</h3>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('admin.riders.store') }}" class="space-y-4 text-xs">
                @csrf
                
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Rider Full Name <span class="text-orange-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Mg Mg Rider" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Email Address (Login ID) <span class="text-orange-500">*</span></label>
                    <input type="email" name="email" required placeholder="rider@foodorder.com" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Phone Number <span class="text-orange-500">*</span></label>
                    <input type="text" name="phone_number" required placeholder="09xxxxxxxxx" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">City / Zone</label>
                    <input type="text" name="city" value="Yangon" placeholder="Yangon" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Password <span class="text-orange-500">*</span></label>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">Create Rider</button>
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
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl border border-orange-100">✏️</div>
                    <h3 class="text-lg font-black text-slate-900">Edit Rider Details</h3>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 text-lg font-bold">✕</button>
            </div>

            <form method="POST" :action="editRiderUrl" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Rider Full Name <span class="text-orange-500">*</span></label>
                    <input type="text" name="name" x-model="editRiderName" required placeholder="e.g. Mg Mg Rider" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Email Address (Login ID) <span class="text-orange-500">*</span></label>
                    <input type="email" name="email" x-model="editRiderEmail" required placeholder="rider@foodorder.com" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Phone Number <span class="text-orange-500">*</span></label>
                    <input type="text" name="phone_number" x-model="editRiderPhone" required placeholder="09xxxxxxxxx" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">City / Zone</label>
                    <input type="text" name="city" x-model="editRiderCity" placeholder="Yangon" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider">New Password <span class="text-slate-500">(Leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="••••••••" 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white text-slate-900 focus:border-orange-500 focus:outline-none text-sm">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
