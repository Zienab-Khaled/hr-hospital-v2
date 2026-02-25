<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $action === 'confirm' ? (app()->getLocale() === 'ar' ? 'تأكيد الالتزام بالدفع' : 'Confirm payment') : (app()->getLocale() === 'ar' ? 'رفض' : 'Reject') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-slate-100 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 {{ $action === 'confirm' ? 'bg-green-50 border-b-2 border-green-200' : 'bg-red-50 border-b-2 border-red-200' }}">
                <h1 class="text-2xl font-bold text-center {{ $action === 'confirm' ? 'text-green-900' : 'text-red-900' }}">
                    @if($action === 'confirm')
                        {{ app()->getLocale() === 'ar' ? 'تأكيد الالتزام بالدفع' : 'Confirm payment commitment' }}
                    @else
                        {{ app()->getLocale() === 'ar' ? 'رفض عرض السعر' : 'Reject price offer' }}
                    @endif
                </h1>
                <p class="text-center text-slate-600 mt-2">{{ $partySend->invoice->invoice_number }}</p>
            </div>
            <div class="p-6">
                <div class="bg-slate-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-slate-600"><strong>{{ app()->getLocale() === 'ar' ? 'المريض:' : 'Patient:' }}</strong> {{ $partySend->invoice->patient?->name }}</p>
                    <p class="text-sm text-slate-600"><strong>{{ app()->getLocale() === 'ar' ? 'الإجمالي:' : 'Total:' }}</strong> {{ number_format((float) $partySend->invoice->total_amount, 2) }} {{ app()->getLocale() === 'ar' ? 'ريال' : 'SAR' }}</p>
                </div>
                <form action="{{ route('invoice-party-response.process', $partySend->token) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="{{ $action }}">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-2">
                            @if($action === 'confirm')
                                {{ app()->getLocale() === 'ar' ? 'الموافقة الخطية (نص التأكيد على الالتزام بالدفع) *' : 'Written approval (confirm payment commitment) *' }}
                            @else
                                {{ app()->getLocale() === 'ar' ? 'سبب الرفض / الموافقة الخطية على الرفض *' : 'Rejection reason / written approval *' }}
                            @endif
                        </label>
                        <textarea name="response_text" rows="5" required minlength="3" maxlength="2000"
                            class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ $action === 'confirm' ? (app()->getLocale() === 'ar' ? 'أؤكد التزامنا بدفع المبلغ...' : 'We confirm our commitment to pay...') : (app()->getLocale() === 'ar' ? 'سبب الرفض...' : 'Reason for rejection...') }}">{{ old('response_text') }}</textarea>
                        @error('response_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    @if($action === 'confirm')
                    <div>
                        <label class="block font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'إرفاق ملف الاعتماد / الموافقة (اختياري)' : 'Attach approval document (optional)' }}
                        </label>
                        <input type="file" name="approval_document" accept="image/*,application/pdf"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('approval_document')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endif
                    <button type="submit" class="w-full py-3 rounded-lg font-bold text-lg {{ $action === 'confirm' ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }}">
                        @if($action === 'confirm')
                            {{ app()->getLocale() === 'ar' ? 'إرسال التأكيد' : 'Submit confirmation' }}
                        @else
                            {{ app()->getLocale() === 'ar' ? 'إرسال الرفض' : 'Submit rejection' }}
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="mt-8 text-center text-slate-500 text-sm font-medium py-6 px-4">
        <div class="mb-1">
            &copy; {{ date('Y') }} <span class="text-slate-700 font-bold">Abeer Al-Suleiman</span>. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All Rights Reserved.' }}
        </div>
    </footer>
</body>
</html>
