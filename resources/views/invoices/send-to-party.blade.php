@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إرسال الفاتورة لشركة التأمين / الجمعية' : 'Send Invoice to Insurance / Charity')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                {{ app()->getLocale() === 'ar' ? '← العودة لتفاصيل الفاتورة' : '← Back to invoice' }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-slate-200">
            <h2 class="text-xl font-bold text-slate-800 mb-4">
                {{ app()->getLocale() === 'ar' ? 'إرسال الفاتورة لشركة التأمين / الجمعية الخيرية' : 'Send Invoice to Insurance Company / Charity' }}
            </h2>

            <p class="text-slate-600 mb-4">
                {{ app()->getLocale() === 'ar' ? 'الفاتورة رقم' : 'Invoice No' }}: <strong>{{ $invoice->invoice_number }}</strong>
                @if($invoice->patient)
                    — {{ app()->getLocale() === 'ar' ? 'المريض:' : 'Patient:' }} {{ $invoice->patient->name }}
                @endif
            </p>

            @if($invoice->patient && in_array($invoice->patient->payment_type, ['insurance', 'charity']))
                @php
                    $party = $invoice->patient->payment_type === 'insurance' ? $invoice->patient->insuranceCompany : $invoice->patient->charityEntity;
                    $partyName = $party ? (app()->getLocale() === 'ar' && !empty($party->name_ar) ? $party->name_ar : $party->name) : '—';
                    $partyEmail = $party->email ?? null;
                @endphp
                <div class="rounded-lg p-4 bg-slate-50 border border-slate-200 mb-4">
                    <p class="font-semibold text-slate-700 mb-2">
                        {{ $invoice->patient->payment_type === 'insurance' ? (app()->getLocale() === 'ar' ? 'شركة التأمين:' : 'Insurance company:') : (app()->getLocale() === 'ar' ? 'الجمعية الخيرية:' : 'Charity:') }}
                        {{ $partyName }}
                    </p>
                    @if($partyEmail)
                        <p class="text-sm text-slate-600 mb-3">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني:' : 'Email:' }} <span dir="ltr">{{ $partyEmail }}</span></p>
                        <form action="{{ route('invoices.send-to-party.submit', $invoice) }}" method="POST" class="mt-3">
                            @csrf
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'البريد المرسل إليه (يمكن تعديله):' : 'Recipient email (optional override):' }}</label>
                            <input type="email" name="recipient_email" value="{{ old('recipient_email', $partyEmail) }}" required
                                class="w-full rounded border border-slate-300 px-3 py-2 mb-3">
                            <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700">
                                {{ app()->getLocale() === 'ar' ? 'إرسال بريد احترافي مع مرفق عرض السعر (وتأكيد/رفض)' : 'Send professional email with price offer attachment (confirm/reject)' }}
                            </button>
                        </form>
                    @else
                        <p class="text-amber-700 text-sm">{{ app()->getLocale() === 'ar' ? 'لم يتم تسجيل بريد إلكتروني لهذه الجهة. يرجى إرسال الفاتورة يدوياً أو تحديث بيانات الجهة.' : 'No email registered for this party. Please send the invoice manually or update the party details.' }}</p>
                    @endif
                </div>
            @else
                <div class="rounded-lg p-4 bg-amber-50 border border-amber-200 text-amber-800">
                    <p>{{ app()->getLocale() === 'ar' ? 'هذه الفاتورة غير مرتبطة بمريض بدفع تأمين أو جمعية. يمكنك طباعة الفاتورة من صفحة التفاصيل وإرسالها يدوياً لأي جهة.' : 'This invoice is not linked to a patient with insurance or charity payment. You can print the invoice from the details page and send it manually to any party.' }}</p>
                </div>
            @endif

            <div class="mt-6 pt-4 border-t border-slate-200">
                <p class="text-sm text-slate-600 mb-2">{{ app()->getLocale() === 'ar' ? 'يمكنك أيضاً:' : 'You can also:' }}</p>
                <a href="{{ route('invoices.show', $invoice) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-200">
                    {{ app()->getLocale() === 'ar' ? 'فتح صفحة الفاتورة في نافذة جديدة (للطباعة أو النسخ)' : 'Open invoice page in new tab (to print or copy)' }}
                </a>
            </div>
        </div>
    </div>
@endsection
