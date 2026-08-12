<section class="space-y-4">
    <p class="text-xs text-slate-500 font-medium leading-relaxed max-w-2xl">
        {{ __('Once your account is deleted, all of its resources, order history, and account data will be permanently removed.') }}
    </p>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold rounded-xl px-5 py-2.5 shadow-lg shadow-red-600/25 transition-all cursor-pointer text-xs"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8 bg-white text-slate-900 border border-slate-200 rounded-3xl shadow-2xl space-y-4">
            @csrf
            @method('delete')

            <h2 class="text-lg font-black text-slate-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="text-xs text-slate-500 font-medium leading-relaxed">
                {{ __('Once your account is deleted, all of your resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-4">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-full"
                    placeholder="{{ __('Enter password to confirm') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200 rounded-xl font-bold text-xs">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-600/25 text-xs">
                    {{ __('Permanently Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
