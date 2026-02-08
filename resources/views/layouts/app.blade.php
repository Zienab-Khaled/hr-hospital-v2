<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Hospital Revenue Management'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Cairo', 'Inter', 'system-ui', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        * { font-family: 'Cairo','Inter',system-ui,sans-serif }
        [dir="rtl"] * { font-family: 'Cairo',system-ui,sans-serif }
        [dir="ltr"] * { font-family: 'Inter',system-ui,sans-serif }
    </style>
</head>

<body class="min-h-screen bg-slate-100">

    {{-- HEADER --}}
    <header class="sticky top-0 z-20 bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <h1 class="text-lg font-semibold text-slate-800">
                    {{ __('Hospital Revenue Management') }}
                </h1>

                <div class="flex items-center gap-4">
                    <div class="flex gap-2">
                        <a href="{{ url()->current() }}?lang=ar"
                           class="px-3 py-1 rounded text-sm {{ app()->getLocale()==='ar' ? 'bg-blue-600 text-white' : 'bg-slate-200' }}">
                            Arabic
                        </a>
                        <a href="{{ url()->current() }}?lang=en"
                           class="px-3 py-1 rounded text-sm {{ app()->getLocale()==='en' ? 'bg-blue-600 text-white' : 'bg-slate-200' }}">
                            English
                        </a>
                    </div>

                    <span class="text-sm text-slate-600">
                        {{ auth()->user()->employee?->name ?? auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm text-red-600 hover:underline">
                            {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @hasSection('tabs')
            <div class="border-t border-slate-100">
                <div class="max-w-7xl mx-auto px-4">
                    <nav class="flex gap-1 py-2">
                        @yield('tabs')
                    </nav>
                </div>
            </div>
        @endif
    </header>

    {{-- MAIN LAYOUT --}}
    <div class="flex min-h-[calc(100vh-4rem)]">

        @auth
            @include('components.sidebar')
        @endauth

        <main class="flex-1 bg-slate-50 px-4 sm:px-6 lg:px-8 py-6 pb-12">
            <div class="max-w-7xl mx-auto">
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

    </div>

    {{-- Alpine.js for interactive components --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Custom scripts from components --}}
    @stack('scripts')
</body>
</html>
