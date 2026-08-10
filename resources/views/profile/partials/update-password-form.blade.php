<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-slate-300 font-medium mb-1.5" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="block w-full" autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <!-- New Password -->
        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" class="text-slate-300 font-medium mb-1.5" />
            <x-text-input id="update_password_password" name="password" type="password" class="block w-full" autocomplete="new-password" placeholder="Minimum 8 characters" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="text-slate-300 font-medium mb-1.5" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full" autocomplete="new-password" placeholder="Re-enter new password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Action Button & Status -->
        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>{{ __('Update Password') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-xs font-semibold text-emerald-400 flex items-center gap-1"
                >
                    <span>✓ {{ __('Password updated successfully.') }}</span>
                </p>
            @endif
        </div>
    </form>
</section>
