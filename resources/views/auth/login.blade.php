<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Login') }} - {{ __('Hospital Revenue Management') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { theme: { extend: { fontFamily: { sans: ['Cairo', 'Inter', 'system-ui', 'sans-serif'] } } } };
        </script>
    @endif
    <style>
        * { font-family: 'Cairo', 'Inter', system-ui, sans-serif; }
        [dir="rtl"] * { font-family: 'Cairo', system-ui, sans-serif; }
        [dir="ltr"] * { font-family: 'Inter', system-ui, sans-serif; }
        body { -webkit-font-smoothing: antialiased; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-2 min-h-[600px]">
        {{-- Left Side: Form --}}
        <div
        style="background-color: #F3F7FA;"
        class="flex flex-col justify-center p-8 md:p-20 bg-[#F3F7FA]">
            <div class="max-w-md w-full">
                {{-- Brand --}}
                <p class="text-blue-600 font-bold text-xl mb-12">
                    IRD - إيراد</p>

                {{-- Heading --}}
                <h1 class="text-3xl md:text-4xl font-extrabold text-[#1F2937] leading-tight mb-4">
                    {{ app()->getLocale() === 'ar' ? 'IRAD – Internal Revenue & Development' : 'IRAD – Internal Revenue & Development' }}
                </h1>

                {{-- Welcome Text --}}
                {{-- <p class="text-slate-400 text-sm mb-10">
                    {{ app()->getLocale() === 'ar' ? 'IRAD – Internal Revenue & Development' : 'IRAD – Internal Revenue & Development' }}
                </p> --}}

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="bg-[#EEF2F6] p-6 mb-8">
                        <div class="mb-6">
                            <label for="username" class="block text-[10px]
                            font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">
                            {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}</label>
                            <div class="bg-white ">
                                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="hakeem@digital.com"
                                class="w-full bg-[#1A56DB] text-white py-4 px-6 rounded-xl font-bold bg-blue-700 transition shadow-2xl shadow-blue-200 text-sm tracking-wide">
                            </div>
                        </div>
                        <div>
                            <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">{{ __('Password') }}</label>
                            <div class="bg-white ">
                                <input id="password" type="password" name="password" required placeholder="********************"
                                class="w-full bg-[#1A56DB] text-white py-4 px-6 rounded-xl font-bold bg-blue-700 transition shadow-2xl shadow-blue-200 text-sm tracking-wide">
                            </div>
                        </div>
                    </div>

                    @error('username')<p class="mb-4 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('password')<p class="mb-4 text-xs text-red-600">{{ $message }}</p>@enderror

                    <button type="submit"
                    class="w-full bg-[#1A56DB] text-white py-4 px-6 rounded-xl font-bold bg-blue-700 transition shadow-2xl shadow-blue-200 text-sm tracking-wide">
                        {{ __('Login') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Side: Illustration --}}
        <div class="bg-white flex flex-col">
            <div class="flex-1 flex items-center justify-center p-12">
                <img src="{{ asset('images/login-illustration.png') }}" alt="Illustration" class="max-w-full h-auto">
            </div>
        </div>
    </div>
    {{-- Footer --}}
<footer class="mt-auto px-8 py-6 border-t border-slate-200 bg-white/50">
    <div
        class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500 font-medium">
        <div>
            &copy; {{ date('Y') }} <span class="text-slate-700 font-bold">عبير سليمان الرويلي</span>.
            {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All Rights Reserved.' }}
        </div>
        <div class="flex items-center gap-4 text-slate-400">
            <span class="hover:text-slate-600 transition-colors">إيراد: حوكمة | تحول رقمي | استدامة</span>
            <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
            <span class="hover:text-slate-600 transition-colors">إشراف وتطوير النظام</span>
        </div>
    </div>
</footer>

</body>

</html>
