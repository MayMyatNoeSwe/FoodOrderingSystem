<section class="space-y-4">
    <p class="text-xs text-slate-400 leading-relaxed max-w-2xl">
        {{ __('Once your account is deleted, all of its resources, order history, and account data will be permanently removed.') }}
    </p>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold rounded-xl px-5 py-2.5 shadow-lg shadow-red-600/25 transition-all cursor-pointer text-xs"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-slate-900 text-slate-100 border border-slate-800 rounded-3xl">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-white">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-xs text-slate-400 leading-relaxed">
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
                <x-secondary-button x-on:click="$dispatch('close')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700 rounded-xl">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-600/25">
                    {{ __('Permanently Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
