<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __("Hospital Revenue Management"))</title>

    {{-- خطوط Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
        * {
            font-family: 'Cairo', 'Inter', system-ui, -apple-system, sans-serif;
            font-weight: 500;
        }

        [dir="rtl"] * {
            font-family: 'Cairo', system-ui, sans-serif;
            font-weight: 600;
        }

        [dir="ltr"] * {
            font-family: 'Inter', system-ui, sans-serif;
            font-weight: 500;
        }

        body {
            font-feature-settings: 'kern' 1;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6, .font-bold, .font-semibold {
            font-weight: 700 !important;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <h1 class="text-lg font-semibold text-slate-800">{{ __("Hospital Revenue Management") }}</h1>
                <div class="flex items-center gap-4">
                    <div class="flex gap-2">
                        <a href="{{ url()->current() }}?lang=ar" class="px-3 py-1 rounded text-sm {{ app()->getLocale() === 'ar' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' }}">{{ __("Arabic") }}</a>
                        <a href="{{ url()->current() }}?lang=en" class="px-3 py-1 rounded text-sm {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' }}">{{ __("English") }}</a>
                    </div>
                    <span class="text-sm text-slate-600" title="{{ app()->getLocale() === 'ar' ? 'الموظف المسجل' : 'Logged-in employee' }}">
                        {{ auth()->user()->employee?->name ?: auth()->user()->name ?: auth()->user()->username }}
                        @php
                            $isManagerRole = auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager');
                            $roleLabel = $isManagerRole ? (app()->getLocale() === 'ar' ? 'مدير' : 'Manager') : (app()->getLocale() === 'ar' ? 'موظف' : 'Employee');
                        @endphp
                        <span class="text-slate-500">({{ $roleLabel }})</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:underline">{{ __("Logout") }}</button>
                    </form>
                </div>
            </div>
        </div>
        @hasSection('tabs')
        <div class="max-w-7xl mx-auto px-4 border-t border-slate-100">
            <nav class="flex gap-1 pt-2">
                @yield('tabs')
            </nav>
        </div>
        @endif
    </header>
    <div class="flex max-w-7xl mx-auto p-4">
        @auth
            @include('components.sidebar')
        @endauth
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 {{ auth()->check() ? '' : 'max-w-7xl mx-auto' }}">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif
        @yield('content')
        </main>
    </div>
    <script>
    (function() {
        var key = 'app_locale';
        var params = new URLSearchParams(window.location.search);
        var lang = params.get('lang');
        if (lang && (lang === 'ar' || lang === 'en')) {
            try { localStorage.setItem(key, lang); } catch (e) {}
        }
        window.addEventListener('storage', function(e) {
            if (e.key === key && e.newValue) {
                var url = new URL(window.location.href);
                url.searchParams.set('lang', e.newValue);
                window.location.href = url.toString();
            }
        });
    })();
    </script>
</body>
</html>
