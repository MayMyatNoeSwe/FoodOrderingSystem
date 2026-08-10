<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-2xl text-slate-100 leading-tight">
                {{ __('Account Settings') }}
            </h2>
            <a href="{{ route('home') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-all">
                ← Back to Storefront
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-950 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- User Header Banner Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-6 relative overflow-hidden">
                <div class="absolute -top-16 -right-16 w-64 h-64 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex items-center gap-5 z-10">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center text-3xl font-black shadow-xl shadow-orange-500/20">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-white">{{ Auth::user()->name }}</h1>
                            @if(Auth::user()->isAdmin())
                                <span class="px-3 py-1 bg-amber-500/20 border border-amber-500/30 text-amber-400 text-xs font-bold rounded-full">
                                    🛡️ Administrator
                                </span>
                            @else
                                <span class="px-3 py-1 bg-orange-500/20 border border-orange-500/30 text-orange-400 text-xs font-bold rounded-full">
                                    👤 Customer
                                </span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-sm mt-1">{{ Auth::user()->email }}</p>
                        <p class="text-slate-500 text-xs mt-1">Member since {{ Auth::user()->created_at ? Auth::user()->created_at->format('M Y') : '2026' }}</p>
                    </div>
                </div>

                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-500/25 transition-all z-10 shrink-0">
                        Open Admin Portal →
                    </a>
                @endif
            </div>

            <!-- Profile Cards Section Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Card 1: Profile Information -->
                <div class="p-6 sm:p-8 bg-slate-900 border border-slate-800 rounded-3xl shadow-xl space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center text-lg">
                            👤
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Profile Information</h3>
                            <p class="text-slate-400 text-xs">Update your name and email address</p>
                        </div>
                    </div>

                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Card 2: Update Password -->
                <div class="p-6 sm:p-8 bg-slate-900 border border-slate-800 rounded-3xl shadow-xl space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center text-lg">
                            🔒
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Update Password</h3>
                            <p class="text-slate-400 text-xs">Ensure your account uses a secure password</p>
                        </div>
                    </div>

                    @include('profile.partials.update-password-form')
                </div>

            </div>

            <!-- Card 3: Danger Zone (Account Deletion) -->
            <div class="p-6 sm:p-8 bg-slate-900 border border-red-900/40 rounded-3xl shadow-xl space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center text-lg">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-400">Danger Zone</h3>
                        <p class="text-slate-400 text-xs">Permanently delete your account and user data</p>
                    </div>
                </div>

                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>
</x-app-layout>
