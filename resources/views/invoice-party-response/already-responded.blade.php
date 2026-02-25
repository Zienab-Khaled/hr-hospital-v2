<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'تم الرد مسبقاً' : 'Already responded' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-50 to-orange-50 min-h-screen flex items-center justify-center py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden text-center p-8">
            <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-3">{{ app()->getLocale() === 'ar' ? 'تم الرد مسبقاً' : 'Already responded' }}</h1>
            <p class="text-slate-600 mb-4">{{ app()->getLocale() === 'ar' ? 'تم تسجيل ردكم على هذا العرض ولا يمكن تعديله.' : 'Your response to this offer has already been recorded and cannot be changed.' }}</p>
            <div class="bg-slate-50 rounded-lg p-4">
                <p class="text-sm"><strong>{{ $partySend->invoice->invoice_number }}</strong> — {{ $partySend->response_action === 'confirmed' ? (app()->getLocale() === 'ar' ? 'مؤكد' : 'Confirmed') : (app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected') }}</p>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="mt-8 text-center text-slate-500 text-sm font-medium py-6 px-4">
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
