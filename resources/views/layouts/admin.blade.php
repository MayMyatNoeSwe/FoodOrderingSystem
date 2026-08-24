@props([
    'title' => 'Admin Portal - ' . config('app.name', 'Food Ordering System'),
    'active' => 'dashboard',
    'heading' => 'Admin Portal',
    'subheading' => null,
    'badge' => null,
    'breadcrumbs' => null,
    'actions' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth admin-scroll">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    <!-- Theme Initialization (Prevents Flash of Unstyled Content) -->
    <script>
        if (localStorage.getItem('foodorder_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Fonts: Figtree & DM Sans -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|dm-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Custom Scrollbar Styles: Invisible for Page/Modals, Sleek & Visible for Horizontal Tables -->
    <style>
        /* Invisible scrollbars for Window & Modals */
        html,
        body,
        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
            width: 0px !important;
            height: 0px !important;
            background: transparent !important;
        }

        /* Sleek Visible Horizontal Table Scrollbars */
        .overflow-x-auto,
        .table-scroll {
            scrollbar-width: thin !important;
            scrollbar-color: #cbd5e1 #f8fafc !important;
            -ms-overflow-style: auto !important;
        }

        .dark .overflow-x-auto,
        .dark .table-scroll {
            scrollbar-color: #475569 #0f172a !important;
        }

        .overflow-x-auto::-webkit-scrollbar,
        .table-scroll::-webkit-scrollbar {
            display: block !important;
            height: 7px !important;
            width: 7px !important;
            background: #f8fafc !important;
            border-radius: 9999px !important;
        }

        .dark .overflow-x-auto::-webkit-scrollbar,
        .dark .table-scroll::-webkit-scrollbar {
            background: #0f172a !important;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb,
        .table-scroll::-webkit-scrollbar-thumb {
            display: block !important;
            background: #cbd5e1 !important;
            border-radius: 9999px !important;
            border: 1px solid #f8fafc !important;
            cursor: pointer !important;
        }

        .dark .overflow-x-auto::-webkit-scrollbar-thumb,
        .dark .table-scroll::-webkit-scrollbar-thumb {
            background: #475569 !important;
            border: 1px solid #0f172a !important;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover,
        .table-scroll::-webkit-scrollbar-thumb:hover {
            background: #f97316 !important;
            border-color: #f97316 !important;
        }

        .overflow-x-auto::-webkit-scrollbar-track,
        .table-scroll::-webkit-scrollbar-track {
            display: block !important;
            background: #f1f5f9 !important;
            border-radius: 9999px !important;
        }

        .dark .overflow-x-auto::-webkit-scrollbar-track,
        .dark .table-scroll::-webkit-scrollbar-track {
            background: #1e293b !important;
        }
    </style>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    @if(isset($head) && $head)
        {{ $head }}
    @endif
</head>
<body data-admin-portal class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 selection:bg-orange-500 selection:text-white min-h-screen transition-colors duration-200"
      x-data="{ 
          mobileMenuOpen: false,
          darkMode: localStorage.getItem('foodorder_theme') === 'dark',
          toggleTheme() {
              this.darkMode = !this.darkMode;
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('foodorder_theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('foodorder_theme', 'light');
              }
          }
      }">

    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ================= SHARED ADMIN SIDEBAR ================= -->
        <x-admin-sidebar :active="$active" />

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- ================= SHARED ADMIN TOPBAR NAVBAR ================= -->
            <x-admin-navbar 
                :heading="$heading" 
                :subheading="$subheading" 
                :badge="$badge ?? null" 
                :breadcrumbs="$breadcrumbs ?? null" 
                :actions="$actions ?? null" 
            />

            <!-- ================= MAIN PAGE CONTENT SLOT ================= -->
            <main class="flex-1 p-4 sm:p-6 space-y-6 overflow-y-auto">
                
                <!-- Global Flash Toast Handlers -->
                @if(session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: @json(session('success')),
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                                background: '#ffffff',
                                color: '#0f172a',
                                customClass: {
                                    popup: 'border border-emerald-200 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                @if(session('error'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: @json(session('error')),
                                showConfirmButton: false,
                                timer: 4500,
                                timerProgressBar: true,
                                background: '#ffffff',
                                color: '#0f172a',
                                customClass: {
                                    popup: 'border border-red-200 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                @if(session('info'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'info',
                                title: @json(session('info')),
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                                background: '#ffffff',
                                color: '#0f172a',
                                customClass: {
                                    popup: 'border border-blue-200 rounded-2xl shadow-xl'
                                }
                            });
                        });
                    </script>
                @endif

                {{ $slot }}
            </main>

        </div>

    </div>

    <x-scroll-to-top />

</body>
</html>
