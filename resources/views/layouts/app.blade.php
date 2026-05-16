<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'ar' ? 'ar-SA-u-nu-latn' : str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Hospital Revenue Management'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;500;600;700&display=swap"
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
                            sans: ['Inter', 'SF Pro Text', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI',
                                'sans-serif'
                            ],
                            arabic: ['Cairo', 'system-ui', '-apple-system', 'sans-serif'],
                        },
                    }
                }
            }
        </script>
    @endif

    <style>
        * {
            font-family: Inter, 'SF Pro Text', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        [dir="rtl"] * {
            font-family: Cairo, Inter, 'Segoe UI', Tahoma, sans-serif;
        }

        [dir="ltr"] * {
            font-family: Inter, 'SF Pro Text', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* بوردر واضح حول كل الانبوتات + لون نص داكن (عدم استخدام نص أبيض) */
        form input[type="text"],
        form input[type="number"],
        form input[type="email"],
        form input[type="password"],
        form input[type="search"],
        form input[type="tel"],
        form input[type="date"],
        form input[type="datetime-local"],
        form textarea,
        form select {
            border-width: 2px;
            border-style: solid;
            border-color: #94a3b8;
            border-radius: 0.5rem;
            color: #1e293b;
        }

        form input:focus,
        form textarea:focus,
        form select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
        }

        form input[type="file"]:not(.hidden) {
            border: 2px solid #94a3b8;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
        }

        /* حقول التاريخ/الوقت في واجهة RTL: منع انعكاس مقاطع placeholder (مثل شهر → رهش) */
        form input[type="date"],
        form input[type="datetime-local"],
        form input[type="time"] {
            direction: ltr;
            unicode-bidi: isolate;
            text-align: left;
        }

        .date-latin-input {
            direction: ltr;
            unicode-bidi: isolate;
            text-align: left;
            font-variant-numeric: lining-nums tabular-nums;
        }

        .date-latin-input::placeholder {
            color: #64748b;
            opacity: 1;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50">
    <div class="flex min-h-screen">
        @auth
            @include('components.sidebar')
        @endauth

        <main class="flex-1 overflow-x-hidden min-w-0 bg-slate-100">
            {{-- Top Bar --}}
            <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm px-6 md:px-8 py-4">
                <div class="flex items-center justify-between gap-4">
                    <h1 class="text-xl md:text-2xl font-semibold text-slate-800 truncate">@yield('title', 'Operations')</h1>

                    @auth
                        <div class="flex items-center gap-3 md:gap-6 flex-shrink-0">
                            {{-- Notifications Icon (hidden for collector / محصل) --}}
                            @if (!auth()->user()->hasRole('collection'))
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                @endphp
                                <a href="{{ route('notifications.index') }}"
                                    class="relative p-2 text-slate-600 hover:text-slate-800 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    @if ($unreadCount > 0)
                                        <span
                                            class="absolute top-0 right-1 flex items-center justify-center w-[16px] h-[16px] bg-rose-500 text-white text-[9px] font-bold rounded-full border border-white z-20">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </a>
                            @endif

                            {{-- User Info --}}
                            <div class="flex items-center gap-3 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                <div class="flex flex-col {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                    <a href="{{ route('account.edit') }}" class="text-sm font-semibold text-slate-800 hover:text-emerald-600 transition-colors">{{ auth()->user()->name }}</a>
                                    <span class="text-xs text-slate-500">{{ auth()->user()->username }}</span>
                                </div>
                                <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center flex-shrink-0 ring-2 ring-slate-200/80">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Language Switcher --}}
                            <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-1">
                                <a href="{{ route('locale.switch', 'en') }}"
                                    class="px-3 py-1.5 text-sm font-medium rounded transition-all {{ app()->getLocale() === 'en' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-800' }}">
                                    EN
                                </a>
                                <a href="{{ route('locale.switch', 'ar') }}"
                                    class="px-3 py-1.5 text-sm font-medium rounded transition-all {{ app()->getLocale() === 'ar' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-800' }}">
                                    عربي
                                </a>
                            </div>

                            {{-- Logout --}}
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 text-slate-600 hover:text-red-600 transition-colors"
                                    title="{{ __('Logout') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6 md:p-8 main-content-area">
                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>

            {{-- Footer --}}
            <footer class="mt-auto border-t border-slate-200 bg-slate-100">
                <div class="px-6 py-4 text-center text-sm text-slate-600 space-y-1">
                    <p class="m-0">
                        &copy; {{ date('Y') }} <span class="font-semibold text-slate-700"> Asalrwaily@moh.gov.sa
                        {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All Rights Reserved.' }}
                    </p>
                    <p class="m-0 text-slate-500">
                        إيراد: حوكمة | تحول رقمي | استدامة | إشراف وتطوير النظام
                    </p>
                </div>
            </footer>
        </main>
    </div>
    <x-western-numerals-script />
</body>

</html>
