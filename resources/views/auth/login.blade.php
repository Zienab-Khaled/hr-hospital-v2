<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __("Login") }} - {{ __("Hospital Revenue Management") }}</title>

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
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-xl font-semibold text-center text-slate-800 mb-6">{{ __("Login") }}</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">{{ __("Username") }}</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full rounded-lg border-2 border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 @enderror">
                @error('username')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">{{ __("Password") }}</label>
                <input id="password" type="password" name="password" required
                    class="w-full rounded-lg border-2 border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-between mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-slate-300">
                    <span class="ms-2 text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'تذكرني' : 'Remember me' }}</span>
                </label>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded font-medium hover:bg-blue-700">
                {{ __("Login") }}
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-slate-500">
            <a href="{{ url()->current() }}?lang=ar" class="me-2">{{ __("Arabic") }}</a>
            <a href="{{ url()->current() }}?lang=en">{{ __("English") }}</a>
        </p>
    </div>

    {{-- Footer for login page --}}
    <footer class="fixed bottom-0 left-0 w-full py-6 text-center text-slate-500 text-sm font-medium">
        <div class="mb-1">
            &copy; {{ date('Y') }} <span class="text-slate-700 font-bold">Abeer Alrwaily</span>. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All Rights Reserved.' }}
        </div>
        <div>
            {{ app()->getLocale() === 'ar' ? 'تم التطوير بواسطة' : 'Developed by' }}
            <span class="text-indigo-600 font-bold">Zienab Khaled</span>
        </div>
    </footer>
</body>
</html>
