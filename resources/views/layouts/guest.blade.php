<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Food Ordering System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-orange-500 selection:text-white">
        <!-- 60% Dominant Background: Warm Soft Gradient -->
        <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-orange-50/80 via-slate-50 to-amber-50/60 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 relative overflow-hidden">
            
            <!-- Decorative Subtle Food Background Elements -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-orange-400/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Brand Header -->
            <div class="mb-6 text-center z-10">
                <a href="/" class="inline-flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Food<span class="text-orange-500">Order</span></span>
                </a>
            </div>

            <!-- 30% Secondary Container: Clean Card -->
            <div class="w-full sm:max-w-md bg-white dark:bg-slate-900 shadow-xl shadow-slate-200/50 dark:shadow-none border border-orange-100/80 dark:border-slate-800 rounded-2xl p-8 z-10 transition-all duration-300">
                {{ $slot }}
            </div>

            <!-- Footer Copy -->
            <p class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400">
                &copy; {{ date('Y') }} FoodOrderingSystem. Tasty food delivered fast.
            </p>
        </div>
    </body>
</html>

