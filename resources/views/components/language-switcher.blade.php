@props([
    'variant' => 'default', // 'default', 'compact', 'sidebar'
    'align' => 'right' // 'right' or 'left'
])

@php
    $currentLocale = app()->getLocale();
    $isMyanmar = ($currentLocale === 'my');
    
    // The flag to show is the OPPOSITE of the current language.
    // If we are in English, we show the Myanmar flag (clicking it switches to Myanmar).
    // If we are in Myanmar, we show the UK flag (clicking it switches to English).
    $targetLocale = $isMyanmar ? 'en' : 'my';
    $tooltip = $isMyanmar ? 'Switch to English' : 'မြန်မာဘာသာသို့ ပြောင်းရန်';
@endphp

<a href="{{ route('lang.switch', $targetLocale) }}" 
   title="{{ $tooltip }}"
   class="w-8 h-8 sm:w-9 sm:h-9 inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 rounded-lg border border-slate-200/60 dark:border-slate-700/60 transition-all duration-200 cursor-pointer shadow-sm group">
    
    @if($isMyanmar)
        <!-- UK Flag in Square Box Structure (Target = English) -->
        <div class="w-5 h-5 rounded-[4px] overflow-hidden flex items-center justify-center shadow-xs shrink-0 border border-slate-300/40 dark:border-slate-600/40 group-hover:scale-105 transition-transform duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 30" preserveAspectRatio="xMidYMid slice" class="w-full h-full object-cover">
                <clipPath id="uk-flag-clip"><path d="M30 15h30v15zv15h-30zh-30v-15zv-15h30z"/></clipPath>
                <path d="M0 0h60v30H0z" fill="#012169"/>
                <path d="M0 0l60 30m0-30L0 30" stroke="#fff" stroke-width="6"/>
                <path d="M0 0l60 30m0-30L0 30" clip-path="url(#uk-flag-clip)" stroke="#C8102E" stroke-width="4"/>
                <path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10"/>
                <path d="M30 0v30M0 15h60" stroke="#C8102E" stroke-width="6"/>
            </svg>
        </div>
    @else
        <!-- Myanmar Flag in Square Box Structure (Target = Myanmar) -->
        <div class="w-5 h-5 rounded-[4px] overflow-hidden flex items-center justify-center shadow-xs shrink-0 border border-slate-300/40 dark:border-slate-600/40 group-hover:scale-105 transition-transform duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" preserveAspectRatio="xMidYMid slice" class="w-full h-full object-cover">
                <rect width="3" height="2" fill="#ea2839"/>
                <rect width="3" height="1.333" fill="#34b233"/>
                <rect width="3" height="0.667" fill="#fecb00"/>
                <polygon points="1.5,0.35 1.84,1.4 0.95,0.76 2.05,0.76 1.16,1.4" fill="#fff"/>
            </svg>
        </div>
    @endif
</a>
