<x-guest-layout>
    <div>
        <!-- Header Greeting -->
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Welcome Back 👋</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sign in to access your account</p>
        </div>

        <!-- Demo Accounts (Temporary) -->
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-xs text-blue-800 dark:text-blue-300 shadow-sm">
            <p class="font-bold mb-2 flex items-center gap-1.5"><span>🔑</span> Demo Accounts (Password: password)</p>
            <ul class="list-disc pl-5 space-y-1 font-medium">
                <li><span class="font-bold">Admin:</span> <span class="font-mono bg-blue-100 dark:bg-blue-900/50 px-1 rounded">admin@foodorder.com</span></li>
                <li><span class="font-bold">Pizza Palace:</span> <span class="font-mono bg-blue-100 dark:bg-blue-900/50 px-1 rounded">owner1@pizzapalace.com</span></li>
                <li><span class="font-bold">Burger Station:</span> <span class="font-mono bg-blue-100 dark:bg-blue-900/50 px-1 rounded">owner2@burgerstation.com</span></li>
                <li><span class="font-bold">Noodle House:</span> <span class="font-mono bg-blue-100 dark:bg-blue-900/50 px-1 rounded">owner3@noodlehouse.com</span></li>
            </ul>
        </div>
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Single Unified Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5" autocomplete="off">
            @csrf

            @if(request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif

            <!-- Dummy fields to prevent aggressive browser autofill -->
            <input type="text" class="hidden" name="prevent_autofill" autocomplete="off" />
            <input type="password" class="hidden" name="prevent_autofill_pwd" autocomplete="off" />

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" class="text-slate-700 dark:text-slate-300 font-medium mb-1.5" />
                <x-text-input id="email" 
                              class="block w-full" 
                              type="email" 
                              name="email" 
                              :value="old('email')" 
                              required 
                              autofocus 
                              autocomplete="off" 
                              placeholder="name@example.com" />
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

                <x-text-input id="password" 
                              class="block w-full"
                              type="password"
                              name="password"
                              required 
                              autocomplete="new-password"
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
                    {{ __('Sign In') }}
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
    </div>
</x-guest-layout>
