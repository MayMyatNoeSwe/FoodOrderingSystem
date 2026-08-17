@props([
    'variant' => 'default', // 'default', 'compact', 'sidebar'
    'align' => 'right' // 'right' or 'left'
])

@php
    $currentLocale = app()->getLocale();
    $isMyanmar = ($currentLocale === 'my');
@endphp

@if($variant === 'compact')
    <!-- Compact Pill Toggle -->
    <div class="lang-switcher-pill inline-flex items-center p-0.5 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60 text-xs font-bold shadow-inner gap-0.5">
        <a href="{{ route('lang.switch', 'en') }}" 
           title="Switch to English"
           class="{{ !$isMyanmar ? 'active-lang bg-white dark:bg-slate-700 text-orange-600 dark:text-orange-400 shadow-sm font-extrabold' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }} px-2.5 py-1 rounded-[10px] flex items-center gap-1.5">
            <span>🇬🇧</span>
            <span>EN</span>
        </a>
        <a href="{{ route('lang.switch', 'my') }}" 
           title="မြန်မာဘာသာသို့ ပြောင်းရန်"
           class="{{ $isMyanmar ? 'active-lang bg-white dark:bg-slate-700 text-orange-600 dark:text-orange-400 shadow-sm font-extrabold' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }} px-2.5 py-1 rounded-[10px] flex items-center gap-1.5">
            <span>🇲🇲</span>
            <span>MM</span>
        </a>
    </div>


@elseif($variant === 'sidebar')
    <!-- Sidebar / Drawer Format -->
    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 space-y-2">
        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider px-1">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                <span>{{ __('Language') }}</span>
            </span>
            <span class="text-orange-500 font-black">{{ $isMyanmar ? 'မြန်မာ' : 'English' }}</span>
        </div>
        <div class="grid grid-cols-2 gap-1.5 text-xs font-bold">
            <a href="{{ route('lang.switch', 'en') }}" 
               class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl transition-all {{ !$isMyanmar ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20 font-black' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-slate-600' }}">
                <span>🇬🇧</span>
                <span>English</span>
            </a>
            <a href="{{ route('lang.switch', 'my') }}" 
               class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl transition-all {{ $isMyanmar ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20 font-black' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-slate-600' }}">
                <span>🇲🇲</span>
                <span>မြန်မာ</span>
            </a>
        </div>
    </div>

@else
    <!-- Default Navbar Dropdown Button -->
    <div x-data="{ langOpen: false }" class="relative inline-block text-left">
        <button @click="langOpen = !langOpen" 
                @click.outside="langOpen = false"
                type="button" 
                title="{{ $isMyanmar ? 'မြန်မာဘာသာ ရွေးချယ်ထားသည်' : 'Language: English' }}"
                class="px-2.5 sm:px-3 py-2 sm:py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all duration-200 cursor-pointer inline-flex items-center gap-1.5 sm:gap-2 border border-slate-200/60 dark:border-slate-700/60 font-bold text-xs sm:text-sm">
            @if($isMyanmar)
                <span class="text-base leading-none">🇲🇲</span>
                <span class="font-extrabold tracking-tight">MM</span>
            @else
                <span class="text-base leading-none">🇬🇧</span>
                <span class="font-extrabold tracking-tight">EN</span>
            @endif
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': langOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown Box -->
        <div x-show="langOpen"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute {{ $align === 'left' ? 'left-0' : 'right-0' }} mt-2 w-44 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-1.5 z-50 overflow-hidden"
             style="display: none;">
            
            <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                {{ __('Language') }} / ဘာသာစကား
            </div>

            <a href="{{ route('lang.switch', 'en') }}" 
               class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-semibold {{ !$isMyanmar ? 'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800' }} transition-colors">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🇬🇧</span>
                    <span>English</span>
                </div>
                @if(!$isMyanmar)
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                @endif
            </a>

            <a href="{{ route('lang.switch', 'my') }}" 
               class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-semibold {{ $isMyanmar ? 'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800' }} transition-colors">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🇲🇲</span>
                    <span>မြန်မာ (Myanmar)</span>
                </div>
                @if($isMyanmar)
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                @endif
            </a>
        </div>
    </div>
@endif
