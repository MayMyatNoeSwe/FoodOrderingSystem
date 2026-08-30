<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Food Ordering System') }}</title>

        <!-- Fonts: DM Sans & Cabinet Grotesk -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:300,400,500,600,700,800|cabinet-grotesk:500,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-orange-500 selection:text-white">
        <!-- 60% Dominant Background: Warm Soft Gradient -->
        <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-orange-50/80 via-slate-50 to-amber-50/60 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 relative overflow-hidden">
            
            <!-- Top Right Language Switcher -->
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-20 flex items-center gap-2">
                <x-language-switcher variant="compact" />
            </div>

            <!-- Decorative Subtle Food Background Elements -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-orange-400/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Brand Header -->
            <div class="mb-6 text-center z-10">
                <a href="/" class="inline-flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-[#D70F64] flex items-center justify-center text-white text-3xl shadow-lg shadow-pink-500/30 group-hover:scale-105 transition-transform duration-300">
                        🐼
                    </div>
                    <span class="text-3xl font-black tracking-tight text-[#D70F64] ">Food<span class="text-slate-900 dark:text-white">Order</span></span>
                </a>
            </div>

            <!-- 30% Secondary Container: Clean Card -->
            <div class="card-lift w-full sm:max-w-md bg-white dark:bg-slate-900 shadow-xl shadow-slate-200/50 dark:shadow-none border border-pink-100/80 dark:border-slate-800 rounded-2xl p-8 z-10">
                {{ $slot }}
            </div>

            <!-- Footer Copy -->
            <p class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400">
                &copy; {{ date('Y') }} FoodOrder. {{ __('Tasty food delivered fast.') }}
            </p>
        </div>
    </body>
</html>

