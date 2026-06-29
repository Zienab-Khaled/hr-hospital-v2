@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل وإرسال الإيميل للجمعية' : 'Edit and send email to charity')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('invoices.show', $invoice) }}"
                class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                {{ app()->getLocale() === 'ar' ? '← العودة لتفاصيل الفاتورة' : '← Back to invoice' }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg border-2 border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-xl font-bold text-slate-800">
                    {{ app()->getLocale() === 'ar' ? 'تعديل عرض السعر ثم الإرسال للجمعية' : 'Edit price offer then send to charity' }}
                </h2>
                <p class="text-slate-600 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'الفاتورة' : 'Invoice' }}:
                    <strong>{{ $invoice->invoice_number }}</strong>
                    — {{ $invoice->patient?->charityEntity?->name_ar ?? ($invoice->patient?->charityEntity?->name ?? '—') }}
                </p>
                <p class="text-sm text-blue-700 mt-2">
                    {{ app()->getLocale() === 'ar' ? 'عدّلي الحقول أدناه ثم اضغطي «تحديث المعاينة». أزرار الموافقة/الرفض تظهر للجمعية فقط في الإيميل المرسل.' : 'Edit the fields below then click «Refresh preview». Confirm/Reject buttons appear for the charity only in the sent email.' }}
                </p>
            </div>

            <form id="charity-email-form" action="{{ route('invoices.send-charity-price-offer', $invoice) }}" method="POST"
                enctype="multipart/form-data" class="p-6 border-b border-slate-200">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'موضوع الإيميل' : 'Email subject' }}
                        </label>
                        <input type="text" name="subject" id="field_subject" value="{{ old('subject', $defaultSubject) }}"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('subject') border-red-500 @enderror">
                        @error('subject')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'اسم المرسل (يظهر في التوقيع) *' : 'Sender name (shown in signature) *' }}
                        </label>
                        <input type="text" name="sender_name" id="field_sender_name" required
                            value="{{ old('sender_name', $defaultSenderName) }}"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('sender_name') border-red-500 @enderror"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'اسمك كما يظهر في الخطاب' : 'Your name as shown on the letter' }}">
                        @error('sender_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'مدة العلاج (اختياري)' : 'Treatment duration (optional)' }}
                        </label>
                        <input type="text" name="treatment_duration" id="field_treatment_duration"
                            value="{{ old('treatment_duration') }}"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('treatment_duration') border-red-500 @enderror"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: أسبوعان، 3 جلسات...' : 'e.g. 2 weeks, 3 sessions...' }}">
                        @error('treatment_duration')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'نص الرسالة' : 'Message body' }}
                        </label>
                        <textarea name="custom_intro" id="field_custom_intro" rows="6"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('custom_intro') border-red-500 @enderror"
                            placeholder="{{ $defaultIntro }}">{{ old('custom_intro', $defaultIntro) }}</textarea>
                        @error('custom_intro')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'مرفقات إضافية (مثل التقرير الطبي)' : 'Additional attachments (e.g. medical report)' }}
                        </label>
                        <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="w-full rounded border border-slate-300 px-3 py-2 @error('attachments.*') border-red-500 @enderror">
                        <p class="text-xs text-slate-500 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'سيُرفق عرض السعر تلقائياً كـ PDF. يمكنك إرفاق التقرير الطبي أو غيره.' : 'Price offer PDF is attached automatically. You can add medical report or other files.' }}
                        </p>
                        @error('attachments.*')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" id="refresh-preview-btn"
                        class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg font-semibold hover:bg-slate-200">
                        👁️ {{ app()->getLocale() === 'ar' ? 'تحديث المعاينة' : 'Refresh preview' }}
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700">
                        ✉️ {{ app()->getLocale() === 'ar' ? 'إرسال الإيميل للجمعية' : 'Send email to charity' }}
                    </button>
                </div>
            </form>

            <div class="p-4 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'معاينة شكل الإيميل' : 'Email preview' }}
                </h3>
                <form id="preview-form" action="{{ route('invoices.charity-email-preview', $invoice) }}" method="POST"
                    target="email-preview-frame" class="hidden">
                    @csrf
                    <input type="hidden" name="custom_intro" id="preview_custom_intro">
                    <input type="hidden" name="treatment_duration" id="preview_treatment_duration">
                    <input type="hidden" name="sender_name" id="preview_sender_name">
                </form>
                <iframe name="email-preview-frame" id="email-preview-frame" title="email preview"
                    class="w-full bg-white border-2 border-slate-300 rounded-lg"
                    style="min-height: 900px;"></iframe>
            </div>
        </div>
    </div>

    <script>
        function syncPreviewFields() {
            document.getElementById('preview_custom_intro').value =
                document.getElementById('field_custom_intro').value || '';
            document.getElementById('preview_treatment_duration').value =
                document.getElementById('field_treatment_duration').value || '';
            document.getElementById('preview_sender_name').value =
                document.getElementById('field_sender_name').value || '';
        }

        function refreshPreview() {
            syncPreviewFields();
            document.getElementById('preview-form').submit();
        }

        document.getElementById('refresh-preview-btn').addEventListener('click', refreshPreview);

        ['field_custom_intro', 'field_treatment_duration', 'field_sender_name'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function() {
                    clearTimeout(window.charityPreviewTimer);
                    window.charityPreviewTimer = setTimeout(refreshPreview, 600);
                });
            }
        });

        document.addEventListener('DOMContentLoaded', refreshPreview);
    </script>
@endsection
