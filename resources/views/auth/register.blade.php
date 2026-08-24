<x-guest-layout>
    <!-- Header Greeting -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Create Your Account 🍕</h2>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Join us and start ordering delicious food in minutes</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" autocomplete="off">
        @csrf

        @if(request('redirect'))
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
        @endif

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="text-slate-700 dark:text-slate-300 font-bold text-xs uppercase tracking-wider mb-1.5" />
            <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="off" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-slate-700 dark:text-slate-300 font-bold text-xs uppercase tracking-wider mb-1.5" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autocomplete="off" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-slate-700 dark:text-slate-300 font-bold text-xs uppercase tracking-wider mb-1.5" />
            <x-text-input id="password" class="block w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="At least 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-slate-700 dark:text-slate-300 font-bold text-xs uppercase tracking-wider mb-1.5" />
            <x-text-input id="password_confirmation" class="block w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password"
                            placeholder="Re-enter your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Submit Button -->
        <div class="pt-3">
            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-black text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span>Create Account 🍕</span>
            </button>
        </div>

        <!-- Login Redirect -->
        <div class="mt-6 text-center text-xs text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-5">
            {{ __('Already have an account?') }}
            <a href="{{ route('login', request('redirect') ? ['redirect' => request('redirect')] : []) }}" class="font-bold text-orange-600 hover:text-orange-700 dark:text-orange-400 ms-1 transition-colors">
                {{ __('Sign In here') }}
            </a>
        </div>
    </form>
</x-guest-layout>
