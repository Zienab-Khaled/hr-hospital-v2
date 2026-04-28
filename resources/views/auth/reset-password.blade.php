<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Reset Password') }} - {{ __('Hospital Revenue Management') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
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
                            sans: ['Cairo', 'Inter', 'system-ui', 'sans-serif']
                        }
                    }
                }
            };
        </script>
    @endif
    <style>
        * {
            font-family: 'Cairo', 'Inter', system-ui, sans-serif;
        }

        [dir="rtl"] * {
            font-family: 'Cairo', system-ui, sans-serif;
        }

        [dir="ltr"] * {
            font-family: 'Inter', system-ui, sans-serif;
        }

        body {
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-2 min-h-[600px]">
        {{-- Left Side: Form --}}
        <div style="background-color: #F3F7FA;" class="flex flex-col justify-center p-8 md:p-20 bg-[#F3F7FA]">
            <div class="max-w-md w-full">
                {{-- Brand --}}
                <p class="text-blue-600 font-bold text-xl mb-12">
                    IRD - إيراد</p>

                {{-- Heading --}}
                <h1 class="text-3xl md:text-4xl font-extrabold text-[#1F2937] leading-tight mb-4">
                    {{ app()->getLocale() === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Password' }}
                </h1>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="bg-[#EEF2F6] p-6 mb-8">
                        <div class="mb-6">
                            <label for="email"
                                class="block text-[10px]
                            font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">
                                {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}</label>
                            <div class="bg-white ">
                                <input id="email" type="email" name="email" value="{{ old('email', $email) }}"
                                    required autofocus placeholder="hakeem@digital.com"
                                    class="w-full bg-[#1A56DB] text-white py-4 px-6 rounded-xl font-bold bg-blue-700 transition shadow-2xl shadow-blue-200 text-sm tracking-wide">
                            </div>
                            @error('email')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="password"
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">{{ __('Password') }}</label>
                            <div class="bg-white ">
                                <input id="password" type="password" name="password" required
                                    placeholder="********************"
                                    class="w-full bg-[#1A56DB] text-white py-4 px-6 rounded-xl font-bold bg-blue-700 transition shadow-2xl shadow-blue-200 text-sm tracking-wide">
                            </div>
                            @error('password')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation"
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">{{ __('Confirm Password') }}</label>
                            <div class="bg-white ">
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    placeholder="********************"
                                    class="w-full bg-[#1A56DB] text-white py-4 px-6 rounded-xl font-bold bg-blue-700 transition shadow-2xl shadow-blue-200 text-sm tracking-wide">
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-[#1A56DB] text-white py-4 px-6 rounded-xl font-bold bg-blue-700 transition shadow-2xl shadow-blue-200 text-sm tracking-wide">
                        {{ app()->getLocale() === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Password' }}
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
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500 font-medium">
            <div>
                &copy; {{ date('Y') }} <span class="text-slate-700 font-bold"> Asalrwaily@moh.gov.sa</span>.
                {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All Rights Reserved.' }}
            </div>
        </div>
    </footer>

</body>

</html>
