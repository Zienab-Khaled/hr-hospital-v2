<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'ar' ? 'ar-SA-u-nu-latn' : app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'شكراً لردكم' : 'Thank you' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden text-center p-8">
            @if ($partySend->response_action === 'confirmed')
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-green-900 mb-3">
                    {{ app()->getLocale() === 'ar' ? 'تم تأكيد الالتزام بالدفع' : 'Payment commitment confirmed' }}</h1>
            @else
                <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-red-900 mb-3">
                    {{ app()->getLocale() === 'ar' ? 'تم تسجيل الرفض' : 'Rejection recorded' }}</h1>
            @endif
            <p class="text-slate-600 mb-4">
                {{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice No:' }}
                <strong>{{ $partySend->invoice->invoice_number }}</strong>
            </p>
            <div class="bg-slate-50 rounded-lg p-4 mb-4 text-start">
                <p class="text-sm text-slate-700">{{ $partySend->response_text }}</p>
            </div>
            <p class="text-slate-600 text-sm">
                {{ app()->getLocale() === 'ar' ? 'شكراً لردكم. تم تسجيل الرد في نظام المستشفى.' : 'Thank you for your response. It has been recorded in the hospital system.' }}
            </p>
        </div>

        {{-- Footer --}}
        <footer class="mt-8 text-center text-slate-500 text-sm font-medium py-6 px-4">
            <div class="mb-1">
                &copy; {{ date('Y') }} <span class="text-slate-700 font-bold">Abeer Suleiman</span>.
                {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All Rights Reserved.' }}
            </div>

        </footer>

    </div>

</body>

</html>
