<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FoodOrder') }}</title>

        <!-- Theme Initialization (Prevents FOUC) -->
        <script>
            if (localStorage.getItem('foodorder_theme') === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Fonts: DM Sans & Cabinet Grotesk -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:300,400,500,600,700,800|cabinet-grotesk:500,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    {{-- 60% dominant: warm off-white background --}}
    <body class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 selection:bg-orange-500 selection:text-white transition-colors duration-300">
        <div class="min-h-screen flex flex-col justify-between">
            @include('layouts.navigation')

            {{-- Page Heading: 30% charcoal structural --}}
            @isset($header)
                <header class="bg-slate-900 border-b border-slate-800/80 shadow-lg">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="mb-auto">
                {{ $slot }}
            </main>
        </div>
        @if(session('clear_cart'))
        <script>
            localStorage.removeItem('foodorder_cart');
        </script>
        @endif
    </body>
</html>
