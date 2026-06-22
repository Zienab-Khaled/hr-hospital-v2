@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل وإرسال الإيميل للجمعية' : 'Edit and send email to charity')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('invoices.show', $invoice) }}"
                class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                {{ app()->getLocale() === 'ar' ? '← العودة لتفاصيل الفاتورة' : '← Back to invoice' }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg border-2 border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-xl font-bold text-slate-800">
                    {{ app()->getLocale() === 'ar' ? 'تعديل الإيميل ثم الإرسال للجمعية' : 'Edit email then send to charity' }}
                </h2>
                <p class="text-slate-600 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'الفاتورة' : 'Invoice' }}:
                    <strong>{{ $invoice->invoice_number }}</strong>
                    — {{ $invoice->patient?->charityEntity?->name_ar ?? ($invoice->patient?->charityEntity?->name ?? '—') }}
                </p>
            </div>

            <form action="{{ route('invoices.send-charity-price-offer', $invoice) }}" method="POST"
                enctype="multipart/form-data" class="p-6">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'موضوع الإيميل' : 'Email subject' }}</label>
                        <input type="text" name="subject" value="{{ old('subject', $defaultSubject) }}"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('subject') border-red-500 @enderror"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'موضوع الإيميل' : 'Email subject' }}">
                        @error('subject')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'مدة العلاج (اختياري)' : 'Treatment duration (optional)' }}</label>
                        <input type="text" name="treatment_duration" value="{{ old('treatment_duration') }}"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('treatment_duration') border-red-500 @enderror"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: أسبوعان، 3 جلسات...' : 'e.g. 2 weeks, 3 sessions...' }}">
                        @error('treatment_duration')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'نص الرسالة (اختياري — إن تركت فارغاً يُستخدم النص الافتراضي)' : 'Message body (optional — leave blank for default)' }}</label>
                        <textarea name="custom_intro" rows="5"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('custom_intro') border-red-500 @enderror"
                            placeholder="{{ $defaultIntro }}">{{ old('custom_intro') }}</textarea>
                        @error('custom_intro')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'مرفقات إضافية (مثل التقرير الطبي)' : 'Additional attachments (e.g. medical report)' }}</label>
                        <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('attachments.*') border-red-500 @enderror">
                        <p class="text-xs text-slate-500 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'سيُرفق عرض السعر تلقائياً كـ PDF. يمكنك إرفاق التقرير الطبي أو غيره (PDF، Word، صور).' : 'Price offer PDF is attached automatically. You can add medical report or other files (PDF, Word, images).' }}
                        </p>
                        @error('attachments.*')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('invoices.charity-email-preview', $invoice) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg font-semibold hover:bg-slate-200">
                        👁️ {{ app()->getLocale() === 'ar' ? 'معاينة شكل الإيميل' : 'Preview email' }}
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700">
                        ✉️ {{ app()->getLocale() === 'ar' ? 'إرسال الإيميل للجمعية' : 'Send email to charity' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
