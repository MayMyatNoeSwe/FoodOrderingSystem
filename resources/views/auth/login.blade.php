<x-guest-layout>
    <!-- Header Greeting -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Welcome Back! 👋</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sign in to order your favorite delicious meals</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-slate-700 dark:text-slate-300 font-medium mb-1.5" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" :value="__('Password')" class="text-slate-700 dark:text-slate-300 font-medium" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium transition-colors" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-orange-500 shadow-sm focus:ring-orange-500 dark:focus:ring-orange-600 dark:focus:ring-offset-slate-900" name="remember">
                <span class="ms-2.5 text-sm text-slate-600 dark:text-slate-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <x-primary-button>
                {{ __('Sign In to Order') }}
            </x-primary-button>
        </div>

        <!-- Register Redirect -->
        <div class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-5">
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" class="font-semibold text-orange-600 hover:text-orange-700 dark:text-orange-400 ms-1 transition-colors">
                {{ __('Create an account') }}
            </a>
        </div>
    </form>
</x-guest-layout>

