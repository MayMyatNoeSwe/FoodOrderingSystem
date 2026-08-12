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
        <!-- Phone Number -->
        <div>
            <x-input-label for="phone_number" :value="__('Phone Number')" class="text-slate-700 font-bold mb-1.5" />
            <x-text-input id="phone_number" name="phone_number" type="tel" class="block w-full" :value="old('phone_number', $user->phone_number)" placeholder="0912345678" />
            <x-input-error class="mt-1.5" :messages="$errors->get('phone_number')" />
        </div>

        <!-- City / Township -->
        <div>
            <x-input-label for="city" :value="__('City / Township (မြို့နယ်)')" class="text-slate-700 font-bold mb-1.5" />
            <select id="city" name="city"
                class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50/50 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:bg-white rounded-xl shadow-sm transition-all duration-200 outline-none text-sm font-medium">
                <option value="">-- မြို့နယ် ရွေးချယ်ပါ --</option>
                <optgroup label="── Zone 1 ── 2,000 MMK (မြို့ပြလယ်)">
                    <option value="Kyauktada" @selected(old('city', $user->city) === 'Kyauktada')>ကျောက်တံတား (Kyauktada)</option>
                    <option value="Pabedan" @selected(old('city', $user->city) === 'Pabedan')>ပန်းဘဲတန်း (Pabedan)</option>
                    <option value="Lanmadaw" @selected(old('city', $user->city) === 'Lanmadaw')>လမ်းမတော် (Lanmadaw)</option>
                    <option value="Latha" @selected(old('city', $user->city) === 'Latha')>လသာ (Latha)</option>
                    <option value="Botahtaung" @selected(old('city', $user->city) === 'Botahtaung')>ဗိုလ်တထောင် (Botahtaung)</option>
                    <option value="Pazundaung" @selected(old('city', $user->city) === 'Pazundaung')>ပုဇွန်တောင် (Pazundaung)</option>
                    <option value="Mingalar Taung Nyunt" @selected(old('city', $user->city) === 'Mingalar Taung Nyunt')>မင်္ဂလာတောင်ညွှန့် (Mingalar Taung Nyunt)</option>
                    <option value="Ahlone" @selected(old('city', $user->city) === 'Ahlone')>အလုံ (Ahlone)</option>
                </optgroup>
                <optgroup label="── Zone 2 ── 3,000 MMK (မြို့အလယ်)">
                    <option value="Kamaryut" @selected(old('city', $user->city) === 'Kamaryut')>ကမာရွတ် (Kamaryut)</option>
                    <option value="Bahan" @selected(old('city', $user->city) === 'Bahan')>ဗဟန်း (Bahan)</option>
                    <option value="Tamwe" @selected(old('city', $user->city) === 'Tamwe')>တာမွေ (Tamwe)</option>
                    <option value="Dagon" @selected(old('city', $user->city) === 'Dagon')>ဒဂုံ (Dagon)</option>
                    <option value="Yankin" @selected(old('city', $user->city) === 'Yankin')>ရန်ကင်း (Yankin)</option>
                    <option value="Sanchaung" @selected(old('city', $user->city) === 'Sanchaung')>စမ်းချောင်း (Sanchaung)</option>
                    <option value="Hlaing" @selected(old('city', $user->city) === 'Hlaing')>လှိုင် (Hlaing)</option>
                    <option value="Mayangone" @selected(old('city', $user->city) === 'Mayangone')>မရမ်းကုန်း (Mayangone)</option>
                    <option value="Insein" @selected(old('city', $user->city) === 'Insein')>အင်းစိန် (Insein)</option>
                    <option value="Thaketa" @selected(old('city', $user->city) === 'Thaketa')>သာကေတ (Thaketa)</option>
                    <option value="Thingangyun" @selected(old('city', $user->city) === 'Thingangyun')>သင်္ဃန်းကျွန်း (Thingangyun)</option>
                </optgroup>
                <optgroup label="── Zone 3 ── 5,000 MMK (မြို့ပြင်)">
                    <option value="Shwepyithar" @selected(old('city', $user->city) === 'Shwepyithar')>ရွှေပြည်သာ (Shwepyithar)</option>
                    <option value="Hlaingtharyar" @selected(old('city', $user->city) === 'Hlaingtharyar')>လှိုင်သာယာ (Hlaingtharyar)</option>
                    <option value="North Okkalapa" @selected(old('city', $user->city) === 'North Okkalapa')>မြောက်ဥက္ကလာပ (North Okkalapa)</option>
                    <option value="South Okkalapa" @selected(old('city', $user->city) === 'South Okkalapa')>တောင်ဥက္ကလာပ (South Okkalapa)</option>
                    <option value="East Dagon" @selected(old('city', $user->city) === 'East Dagon')>အရှေ့ဒဂုံ (East Dagon)</option>
                    <option value="North Dagon" @selected(old('city', $user->city) === 'North Dagon')>မြောက်ဒဂုံ (North Dagon)</option>
                    <option value="South Dagon" @selected(old('city', $user->city) === 'South Dagon')>တောင်ဒဂုံ (South Dagon)</option>
                    <option value="Dagon Seikkan" @selected(old('city', $user->city) === 'Dagon Seikkan')>ဒဂုံဆိပ်ကမ်း (Dagon Seikkan)</option>
                </optgroup>
                <optgroup label="── Zone 4 ── 7,000 MMK (ဝေးသောမြို့နယ်)">
                    <option value="Dala" @selected(old('city', $user->city) === 'Dala')>ဒလ (Dala)</option>
                    <option value="Twante" @selected(old('city', $user->city) === 'Twante')>တွံတေး (Twante)</option>
                    <option value="Cocogyun" @selected(old('city', $user->city) === 'Cocogyun')>ကိုကိုးကျွန်း (Cocogyun) — 10,000 MMK</option>
                </optgroup>
            </select>
            <x-input-error class="mt-1.5" :messages="$errors->get('city')" />
        </div>

        <!-- Detail Address -->
        <div>
            <x-input-label for="detail_address" :value="__('Detailed Delivery Address (အသေးစိတ်လိပ်စာ)')" class="text-slate-700 font-bold mb-1.5" />
            <textarea id="detail_address" name="detail_address" rows="3"
                class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50/50 text-slate-900 placeholder-slate-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:bg-white rounded-xl shadow-sm transition-all duration-200 outline-none resize-none text-sm"
                placeholder="အမှတ်၊ လမ်း၊ ရပ်ကွက်/ကျောင်းဆောင် ...">{{ old('detail_address', $user->detail_address) }}</textarea>
            <x-input-error class="mt-1.5" :messages="$errors->get('detail_address')" />
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
