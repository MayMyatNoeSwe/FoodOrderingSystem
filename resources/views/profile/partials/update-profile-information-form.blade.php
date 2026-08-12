<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <!-- Full Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="text-slate-700 font-bold mb-1.5" />
            <x-text-input id="name" name="name" type="text" class="block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-slate-700 font-bold mb-1.5" />
            <x-text-input id="email" name="email" type="email" class="block w-full" :value="old('email', $user->email)" required autocomplete="username" placeholder="name@example.com" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-xs text-amber-800 font-medium">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-xs text-amber-700 hover:text-amber-900 font-bold ms-1">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-bold text-xs text-emerald-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Action Button & Status -->
        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>{{ __('Save Profile Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-xs font-bold text-emerald-600 flex items-center gap-1"
                >
                    <span>✓ {{ __('Saved successfully.') }}</span>
                </p>
            @endif
        </div>
    </form>
</section>
