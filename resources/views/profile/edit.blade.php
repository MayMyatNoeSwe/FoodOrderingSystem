<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-white leading-tight flex items-center gap-2.5">
                <span>⚙️</span> {{ __('Account Settings') }}
            </h2>
            <a href="{{ route('home') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-all flex items-center gap-1.5">
                <span>&larr;</span> Back to Storefront
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- User Header Hero Banner Card (30% Black Structural Hero) -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6 relative overflow-hidden">
                <div class="absolute -top-16 -right-16 w-64 h-64 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex items-center gap-5 z-10">
                    <!-- 10% Orange User Avatar Badge -->
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-orange-500 via-amber-500 to-orange-600 text-white flex items-center justify-center text-3xl font-black shadow-xl shadow-orange-500/30 ring-4 ring-orange-500/20">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-black text-white tracking-tight">{{ Auth::user()->name }}</h1>
                            @if(Auth::user()->isAdmin())
                                <span class="px-3 py-1 bg-amber-500/20 border border-amber-500/30 text-amber-400 text-xs font-black rounded-full">
                                    🛡️ Administrator
                                </span>
                            @else
                                <span class="px-3 py-1 bg-orange-500/20 border border-orange-500/30 text-orange-400 text-xs font-black rounded-full">
                                    👤 Customer
                                </span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-sm font-medium mt-1">{{ Auth::user()->email }}</p>
                        <p class="text-slate-500 text-xs mt-1 font-semibold">Member since {{ Auth::user()->created_at ? Auth::user()->created_at->format('M Y') : '2026' }}</p>
                    </div>
                </div>

                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs rounded-xl shadow-lg shadow-amber-500/25 transition-all z-10 shrink-0 flex items-center gap-2">
                        <span>Open Admin Portal</span>
                        <span>&rarr;</span>
                    </a>
                @endif
            </div>

            <!-- Profile Cards Section Grid (60% White Dominant Base Cards) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Card 1: Profile Information -->
                <div class="p-6 sm:p-8 bg-white border border-slate-200/80 rounded-3xl shadow-sm sm:shadow-md hover:shadow-lg transition-shadow duration-300 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-orange-50 border border-orange-100 text-orange-600 flex items-center justify-center text-lg shadow-sm">
                            👤
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Profile Information</h3>
                            <p class="text-slate-500 text-xs font-medium">Update your account name and email address</p>
                        </div>
                    </div>

                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Card 2: Update Password -->
                <div class="p-6 sm:p-8 bg-white border border-slate-200/80 rounded-3xl shadow-sm sm:shadow-md hover:shadow-lg transition-shadow duration-300 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-orange-50 border border-orange-100 text-orange-600 flex items-center justify-center text-lg shadow-sm">
                            🔒
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Update Password</h3>
                            <p class="text-slate-500 text-xs font-medium">Ensure your account uses a strong, secure password</p>
                        </div>
                    </div>

                    @include('profile.partials.update-password-form')
                </div>

            </div>

            <!-- Card 3: Danger Zone (Account Deletion) -->
            <div class="p-6 sm:p-8 bg-white border border-red-200/80 rounded-3xl shadow-sm sm:shadow-md space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 border border-red-100 text-red-600 flex items-center justify-center text-lg shadow-sm">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-red-600 tracking-tight">Danger Zone</h3>
                        <p class="text-slate-500 text-xs font-medium">Permanently delete your account and all associated order history</p>
                    </div>
                </div>

                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>
</x-app-layout>
