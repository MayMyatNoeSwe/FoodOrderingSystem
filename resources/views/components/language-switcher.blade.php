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
   class="inline-flex items-center justify-center px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 rounded-xl border border-slate-200/60 dark:border-slate-700/60 transition-all duration-200 cursor-pointer shadow-sm group">
    
    @if($isMyanmar)
        <!-- UK Flag (Target = English) -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 30" class="w-6 h-auto rounded-[2px] shadow-sm group-hover:scale-105 transition-transform">
            <clipPath id="t"><path d="M30 15h30v15zv15h-30zh-30v-15zv-15h30z"/></clipPath>
            <path d="M0 0h60v30H0z" fill="#012169"/>
            <path d="M0 0l60 30m0-30L0 30" stroke="#fff" stroke-width="6"/>
            <path d="M0 0l60 30m0-30L0 30" clip-path="url(#t)" stroke="#C8102E" stroke-width="4"/>
            <path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10"/>
            <path d="M30 0v30M0 15h60" stroke="#C8102E" stroke-width="6"/>
        </svg>
    @else
        <!-- Myanmar Flag (Target = Myanmar) -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" class="w-6 h-auto rounded-[2px] shadow-sm group-hover:scale-105 transition-transform">
            <rect width="3" height="2" fill="#ea2839"/>
            <rect width="3" height="1.333" fill="#34b233"/>
            <rect width="3" height="0.667" fill="#fecb00"/>
            <polygon points="1.5,0.35 1.84,1.4 0.95,0.76 2.05,0.76 1.16,1.4" fill="#fff"/>
        </svg>
    @endif
</a>
