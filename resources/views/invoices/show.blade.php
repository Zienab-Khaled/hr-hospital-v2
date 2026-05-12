@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الفاتورة' : 'Invoice Details')

@section('content')
    @php
        $hasInsuranceCoverage = $invoice->items->contains(fn($i) => !empty($i->insurance_coverage_type));
        $isInsuranceOrCharity = in_array($invoice->payment_type ?? $invoice->patient?->payment_type ?? '', ['insurance', 'charity']);
        $totalInsuranceCovered = $invoice->items->sum(fn($i) => (float) $i->insurance_covered_amount);
        $totalPatientShare = $invoice->items->sum(fn($i) => (float) $i->patient_amount);
        // المتبقي للمريض = حصة المريض − ما دفعه المريض فقط (لا نحتسب دفعات التأمين/الجمعية)
        $patientPaidOnly = (float) ($invoice->payments ?? collect())->whereNotIn('payment_type', ['insurance', 'charity'])->sum('amount');
        $effectiveRemaining = ($hasInsuranceCoverage || $isInsuranceOrCharity)
            ? max(0, round($totalPatientShare - $patientPaidOnly, 2))
            : (float) $invoice->remaining_amount;
        // المتبقي الإجمالي (يدخل فيه جزء التأمين حتى تُدفع المطالبة)
        $totalRemaining = (float) $invoice->remaining_amount;
        $isEntryFeeOnly = $invoice->isEntryFeeOnly();
    @endphp
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                {{ app()->getLocale() === 'ar' ? 'تفاصيل الفاتورة' : 'Invoice Details' }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoices.index') }}"
                    class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-semibold hover:bg-slate-300">
                    {{ app()->getLocale() === 'ar' ? '← قائمة الفواتير' : '← Invoices List' }}
                </a>
                @if (!$isEntryFeeOnly)
                    @can('invoices.edit')
                        <a href="{{ route('invoices.edit', $invoice) }}"
                            class="bg-blue-600  px-4 py-2 rounded-lg font-semibold hover:bg-blue-700">
                            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                        </a>
                    @endcan
                @endif
            </div>
        </div>

        @if ($isEntryFeeOnly)
            {{-- فاتورة كشفية فقط: نعرض تسجيل الدفعة فقط --}}
            <div class="mb-6 p-4 rounded-lg border-2 border-emerald-200 bg-emerald-50/70">
                <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'فاتورة كشفية دخول' : 'Entry fee invoice' }}</h3>
                <div class="flex flex-wrap gap-2">
                    @if ($effectiveRemaining > 0 && (auth()->user()->can('payments.create') || auth()->user()->can('invoices.edit')))
                        <button type="button" onclick="openPaymentModal()"
                            class="inline-flex items-center gap-2 bg-green-600  px-4 py-2 rounded-lg font-semibold hover:bg-green-700 shadow-md">
                            💰 {{ app()->getLocale() === 'ar' ? 'تسجيل دفعة (كاش / شبكة)' : 'Record Payment (Cash/POS)' }}
                        </button>
                    @endif
                </div>
            </div>
        @else
        {{-- الخطوات التالية بعد إنشاء الفاتورة --}}
        <div class="mb-6 p-4 rounded-lg border-2 border-blue-200 bg-blue-50/70">
            <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'الخطوات التالية' : 'Next steps' }}
            </h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoices.print-commitment', $invoice) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 bg-white border-2 border-slate-400 text-slate-800 px-4 py-2 rounded-lg font-semibold hover:bg-slate-100 hover:border-slate-500">
                    {{ app()->getLocale() === 'ar' ? '🖨️ طباعة محضر تعهد' : 'Print commitment form' }}
                </a>
                <a href="{{ route('invoices.print-non-commitment', $invoice) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 bg-white border-2 border-slate-400 text-slate-800 px-4 py-2 rounded-lg font-semibold hover:bg-slate-100 hover:border-slate-500">
                    {{ app()->getLocale() === 'ar' ? '🖨️ طباعة محضر إقرار بعدم التوقيع' : 'Print non-commitment form' }}
                </a>
                {{-- إرسال للجمعية: يظهر لجميع فواتير مرضى الجمعية (المريض مرتبط بجمعية خيرية) --}}
                @if (($invoice->payment_type === 'charity' || $invoice->patient?->payment_type === 'charity') && $invoice->patient?->charityEntity)
                    @if ($invoice->patient?->charityEntity?->email)
                        <a href="{{ route('invoices.charity-email-preview', $invoice) }}"
                            target="_blank"
                            title="{{ app()->getLocale() === 'ar' ? 'معاينة شكل الإيميل قبل الإرسال' : 'Preview email before sending' }}"
                            class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg font-semibold hover:bg-slate-200">
                            👁️ {{ app()->getLocale() === 'ar' ? 'معاينة الميل قبل الإرسال' : 'Preview email' }}
                        </a>
                        <a href="{{ route('invoices.charity-email-compose', $invoice) }}"
                            class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700">
                            {{ app()->getLocale() === 'ar' ? '✉️ تعديل ثم إرسال عرض السعر للجمعية' : '✉️ Edit then send price offer to charity' }}
                        </a>
                        <form method="POST" action="{{ route('invoices.send-charity-payment-reminder', $invoice) }}"
                            class="inline"
                            onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'إرسال تذكير بالسداد للجمعية؟' : 'Send payment reminder to charity?' }}');">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 bg-amber-500  px-4 py-2 rounded-lg font-semibold hover:bg-amber-600">
                                {{ app()->getLocale() === 'ar' ? '💰 إرسال تذكير بالسداد للجمعية' : '💰 Send payment reminder to charity' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('invoices.send-to-party', $invoice) }}"
                            class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700">
                            {{ app()->getLocale() === 'ar' ? '✉️ إرسال عرض السعر / الفاتورة للجمعية (إدخال البريد)' : '✉️ Send price offer / invoice to charity (enter email)' }}
                        </a>
                    @endif
                @endif

                {{-- Record Payment button for Cash/Partially Paid (المحصل/الاستقبال: payments.create) --}}
                @if ($effectiveRemaining > 0 && (auth()->user()->can('payments.create') || auth()->user()->can('invoices.edit')))
                    <button type="button" onclick="openPaymentModal()"
                        class="inline-flex items-center gap-2 bg-green-600  px-4 py-2 rounded-lg font-semibold hover:bg-green-700 shadow-md">
                        💰 {{ app()->getLocale() === 'ar' ? 'تسجيل دفعة (كاش / شبكة)' : 'Record Payment (Cash/POS)' }}
                    </button>
                @endif

                {{-- زرار إشعار الجمعية باكتمال الخدمات — يظهر فقط لمرضى الجمعية بعد تنفيذ كل الخدمات --}}
                @if (
                    ($invoice->payment_type === 'charity' || $invoice->patient?->payment_type === 'charity') &&
                        $invoice->isFullyCompleted() &&
                        $invoice->patient?->charityEntity?->email)
                    @can('invoices.edit')
                        <form method="POST" action="{{ route('invoices.notify-charity-completed', $invoice) }}"
                            onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل تريد إرسال إيميل للجمعية بأن جميع الخدمات قد نُفِّذت؟' : 'Send completion email to charity?' }}')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 bg-teal-600  px-4 py-2 rounded-lg font-semibold hover:bg-teal-700 shadow-md ring-2 ring-teal-300 animate-pulse">
                                ✉️
                                {{ app()->getLocale() === 'ar' ? 'إشعار الجمعية باكتمال الخدمات' : 'Notify charity of completion' }}
                            </button>
                        </form>
                    @endcan
                @elseif(
                    ($invoice->payment_type === 'charity' || $invoice->patient?->payment_type === 'charity') &&
                        !$invoice->isFullyCompleted())
                    <span
                        class="inline-flex items-center gap-2 bg-amber-50 border-2 border-amber-300 text-amber-800 px-4 py-2 rounded-lg font-semibold text-sm">
                        ⏳
                        {{ app()->getLocale() === 'ar' ? 'في انتظار تنفيذ جميع الخدمات' : 'Waiting for all services to be executed' }}
                        ({{ $invoice->items->where('status', 'completed')->count() }}/{{ $invoice->items->count() }})
                    </span>
                @endif
            </div>

            {{-- Action Flags --}}
            <div class="mt-4 flex flex-wrap gap-2 border-t border-blue-200 pt-3">
                @if ($invoice->sent_to_charity_mail_at)
                    <span
                        class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-xs font-bold px-2 py-1 rounded border border-emerald-300">
                        📧 {{ app()->getLocale() === 'ar' ? 'تم إرسال ميل للجمعية' : 'Mail sent to charity' }}
                        ({{ $invoice->sent_to_charity_mail_at->format('Y-m-d H:i') }})
                    </span>
                @endif
                @if ($invoice->printed_commitment_at)
                    <span
                        class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded border border-blue-300">
                        📄 {{ app()->getLocale() === 'ar' ? 'تم طباعة محضر التعهد' : 'Commitment form printed' }}
                        ({{ $invoice->printed_commitment_at->format('Y-m-d H:i') }})
                    </span>
                @endif
                @if ($invoice->printed_non_commitment_at)
                    <span
                        class="inline-flex items-center gap-1 bg-slate-100 text-slate-800 text-xs font-bold px-2 py-1 rounded border border-slate-300">
                        📄
                        {{ app()->getLocale() === 'ar' ? 'تم طباعة محضر إقرار بعدم التوقيع' : 'Non-commitment form printed' }}
                        ({{ $invoice->printed_non_commitment_at->format('Y-m-d H:i') }})
                    </span>
                @endif
            </div>

            {{-- Communication History (Charity/Insurance Responses) --}}
            @if ($invoice->partySends->isNotEmpty())
                <div class="mt-4 border-t border-blue-200 pt-3">
                    <h4 class="text-xs font-bold text-slate-700 uppercase mb-2">
                        {{ app()->getLocale() === 'ar' ? 'سجل التواصل والاستجابة' : 'Communication & Response History' }}
                    </h4>
                    <div class="space-y-2">
                        @foreach ($invoice->partySends->sortByDesc('created_at') as $send)
                            <div
                                class="p-2 rounded border {{ $send->response_action === 'confirmed' ? 'bg-emerald-50 border-emerald-200' : ($send->response_action === 'rejected' ? 'bg-red-50 border-red-200' : 'bg-white border-slate-200') }}">
                                <div class="flex justify-between items-start">
                                    <span
                                        class="text-xs font-bold {{ $send->response_action === 'confirmed' ? 'text-emerald-800' : ($send->response_action === 'rejected' ? 'text-red-800' : 'text-slate-600') }}">
                                        {{ $send->recipient_name }} ({{ $send->recipient_email }})
                                    </span>
                                    <span
                                        class="text-[10px] text-slate-500">{{ $send->sent_at?->format('Y-m-d H:i') }}</span>
                                </div>

                                @if ($send->response_action)
                                    <div class="mt-1 text-xs">
                                        <span
                                            class="font-bold {{ $send->response_action === 'confirmed' ? 'text-emerald-700' : 'text-red-700' }}">
                                            {{ $send->response_action === 'confirmed' ? (app()->getLocale() === 'ar' ? '✅ تمت الموافقة' : '✅ Approved') : (app()->getLocale() === 'ar' ? '❌ تم الرفض' : '❌ Rejected') }}
                                        </span>
                                        <p class="text-slate-700 mt-1 italic">"{{ $send->response_text }}"</p>
                                        <span
                                            class="text-[10px] text-slate-500 block mt-1">{{ $send->response_at?->format('Y-m-d H:i') }}</span>
                                    </div>
                                @else
                                    <span
                                        class="text-[10px] bg-blue-100 text-blue-700 px-1 py-0.5 rounded font-bold mt-1 inline-block">
                                        {{ app()->getLocale() === 'ar' ? '⏳ بانتظار الرد' : '⏳ Awaiting response' }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Link to latest Approval Document if exists --}}
                    @if ($invoice->patient?->hasMedia('charity-approvals'))
                        <div class="mt-3">
                            <h5 class="text-xs font-bold text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? '📎 مستندات الاعتماد المرفوعة:' : '📎 Uploaded Approval Documents:' }}
                            </h5>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($invoice->patient->getMedia('charity-approvals') as $media)
                                    <a href="{{ $media->getUrl() }}" target="_blank"
                                        class="inline-flex items-center gap-1 bg-emerald-600  text-[10px] px-2 py-1 rounded hover:bg-emerald-700 font-bold">
                                        📄 {{ $media->file_name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($invoice->hasCharityClaim())
                        <div class="mt-3 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                            <a href="{{ route('charity-claims.show', $invoice->charityClaim()) }}"
                                class="text-blue-700 font-bold text-xs hover:underline flex items-center gap-1">
                                📋
                                {{ app()->getLocale() === 'ar' ? 'عرض تفاصيل مطالبة الجمعية' : 'View Charity Claim Details' }}
                                →
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        @endif
    </div>


    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        {{-- Invoice header --}}
        <div class="p-6 border-b-2 border-slate-200 bg-slate-50">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <span
                        class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice No:' }}</span>
                    <p class="text-lg font-bold text-slate-900">{{ $invoice->invoice_number }}</p>
                </div>
                <div>
                    <span
                        class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</span>
                    <p class="text-lg font-medium text-slate-900">{{ $invoice->invoice_date?->format('Y-m-d') }}</p>
                </div>
                <div>
                    <span
                        class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'الحالة:' : 'Status:' }}</span>
                    <p class="text-lg font-medium text-slate-900">
                        {{ $invoice->status_label }}
                        @if (in_array($invoice->invoice_type, ['eligibility', 'charity_treatment_free'], true))
                            <span
                                class="ms-2 {{ $invoice->invoice_type === 'eligibility' ? 'bg-purple-100 text-purple-800' : 'bg-teal-100 text-teal-900' }} px-2 py-0.5 rounded text-xs font-bold">{{ $invoice->invoice_type_label }}</span>
                        @endif
                    </p>
                </div>
                <div>
                    <span
                        class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'طريقة الدفع:' : 'Payment Type:' }}</span>
                    <p class="text-lg font-bold text-slate-900">
                        @if ($invoice->payment_type === 'charity')
                            <span
                                class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-sm">{{ app()->getLocale() === 'ar' ? 'جمعية خيرية' : 'Charity' }}</span>
                        @elseif($invoice->payment_type === 'insurance')
                            <span
                                class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-sm">{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</span>
                        @else
                            <span
                                class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-sm">{{ app()->getLocale() === 'ar' ? 'كاش (نقدي)' : 'Cash' }}</span>
                        @endif
                    </p>
                </div>
                @if ($invoice->visit?->referral_number)
                    <div>
                        <span
                            class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الإحالة:' : 'Referral No:' }}</span>
                        <p class="text-lg font-medium text-slate-900">{{ $invoice->visit->referral_number }}</p>
                    </div>
                @endif
                @if ($invoice->visit?->registeredBy)
                    <div>
                        <span
                            class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'أنشئت بواسطة:' : 'Created by:' }}</span>
                        <p class="text-lg font-medium text-slate-900">
                            {{ $invoice->visit->registeredBy->name ?? ($invoice->visit->registeredBy->username ?? '—') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Patient --}}
        @if ($invoice->patient)
            <div class="p-6 border-b border-slate-200 bg-blue-50/50">
                <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'معلومات المريض' : 'Patient' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                    <p><span
                            class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span>
                        {{ $invoice->patient->name }}</p>
                    @if ($invoice->patient->fullArabicName())
                        <p dir="rtl"><span
                                class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي):' : 'Name (AR):' }}</span>
                            {{ $invoice->patient->fullArabicName() }}</p>
                    @endif
                    <p><span
                            class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span>
                        {{ $invoice->patient->file_number }}</p>
                    <p><span
                            class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'نوع الدفع:' : 'Payment:' }}</span>
                        {{ $invoice->patient->payment_type ?? '—' }}</p>
                    @if ($invoice->patient->payment_type === 'insurance' && $invoice->patient->insuranceCompany)
                        <p><span
                                class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'شركة التأمين:' : 'Insurance company:' }}</span>
                            {{ app()->getLocale() === 'ar' ? ($invoice->patient->insuranceCompany->name_ar ?? $invoice->patient->insuranceCompany->name) : $invoice->patient->insuranceCompany->name }}</p>
                    @endif
                    @if ($invoice->patient->payment_type === 'charity' && $invoice->patient->charityEntity)
                        <p><span
                                class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الجمعية:' : 'Charity entity:' }}</span>
                            {{ $invoice->patient->charityEntity->name_ar ?: $invoice->patient->charityEntity->name }}</p>
                    @endif
                    @if ($invoice->patient->phone)
                        <p><span
                                class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الهاتف:' : 'Phone:' }}</span>
                            {{ $invoice->patient->phone }}</p>
                    @endif
                    @if ($invoice->patient->identity_value)
                        <p><span
                                class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الهوية/الفيزا:' : 'ID/Visa:' }}</span>
                            {{ $invoice->patient->identity_value }}</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Services table (الخدمات المقدمة) --}}
        <div class="p-6">
            <h3 class="font-bold text-slate-800 mb-3">
                {{ app()->getLocale() === 'ar' ? 'الخدمات المقدمة' : 'Provided Services' }}</h3>

            @if ($errors->has('error'))
                <div class="mb-3 p-3 rounded-lg bg-red-50 border border-red-300 text-red-800 text-sm font-medium">
                    ⚠️ {{ $errors->first('error') }}
                </div>
            @endif

            <div class="overflow-x-auto border-2 border-slate-300 rounded-lg">
                <table class="w-full border-collapse" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                    <thead>
                        <tr class="bg-slate-200 border-b-2 border-slate-500">
                            <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                {{ app()->getLocale() === 'ar' ? 'الرمز' : 'Code' }}</th>
                            <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                {{ app()->getLocale() === 'ar' ? 'البيان' : 'Description' }}</th>
                            <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                {{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                            <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                {{ app()->getLocale() === 'ar' ? 'السعر الافرادي' : 'Unit Price' }}</th>
                            <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                {{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                            @if ($hasInsuranceCoverage)
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                    {{ app()->getLocale() === 'ar' ? 'التغطية' : 'Coverage' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                    {{ app()->getLocale() === 'ar' ? 'المغطى' : 'Covered' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                    {{ app()->getLocale() === 'ar' ? 'المتبقي للمريض' : 'Patient share' }}</th>
                            @endif
                            @if(auth()->user()->can('invoices.edit') || auth()->user()->can('invoices.execute_services'))
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">
                                    {{ app()->getLocale() === 'ar' ? 'التنفيذ' : 'Execution' }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->items as $item)
                            <tr class="border-b border-slate-300 {{ $item->isCompleted() ? 'bg-emerald-50/40' : '' }}">
                                <td class="border border-slate-300 px-2 py-2 text-center text-sm">
                                    {{ $item->service_id ? ($item->service?->code ?? '—') : '—' }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-sm">
                                    @if ($item->service_id)
                                        {{ app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : $item->service?->name ?? '—' }}
                                        @if ($item->description)
                                            <br><span class="text-slate-500 text-xs">{{ $item->description }}</span>
                                        @endif
                                    @else
                                        {{ $item->description ?? (app()->getLocale() === 'ar' ? 'كشفية دخول' : 'Entry fee') }}
                                    @endif
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center text-sm">{{ $item->quantity }}
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center text-sm">@currencyInvoice($item->unit_price)</td>
                                <td class="border border-slate-300 px-2 py-2 text-center text-sm font-medium">
                                    @currencyInvoice($item->total_price)</td>
                                @if ($hasInsuranceCoverage)
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">
                                        @if ($item->insurance_coverage_type)
                                            @if ($item->insurance_coverage_type === 'percentage')
                                                {{ app()->getLocale() === 'ar' ? 'نسبة' : 'Percentage' }}
                                                {{ round((float) $item->insurance_coverage_value, 0) }}%
                                            @else
                                                {{ app()->getLocale() === 'ar' ? 'قيمة ثابتة' : 'Fixed' }}
                                                @currencyInvoice($item->insurance_coverage_value)
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td
                                        class="border border-slate-300 px-2 py-2 text-center text-sm text-emerald-700 font-medium">
                                        @currencyInvoice($item->insurance_covered_amount)</td>
                                    <td
                                        class="border border-slate-300 px-2 py-2 text-center text-sm text-amber-800 font-medium">
                                        @currencyInvoice($item->patient_amount)</td>
                                @endif
                                @if(auth()->user()->can('invoices.edit') || auth()->user()->can('invoices.execute_services'))
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">
                                        <div class="flex flex-col items-center gap-1">
                                            @if ($item->isCompleted())
                                                <span
                                                    class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-1 rounded-full border border-emerald-300">
                                                    ✅ {{ app()->getLocale() === 'ar' ? 'منفذ' : 'Done' }}
                                                </span>
                                                @if ($item->execution_date)
                                                    <span
                                                        class="block text-xs text-slate-500">{{ $item->execution_date->format('Y-m-d') }}</span>
                                                @endif
                                                @if ($item->completedByUser)
                                                    <span
                                                        class="block text-xs text-slate-400">{{ $item->completedByUser->name ?? $item->completedByUser->username }}</span>
                                                @endif
                                            @elseif ($item->service_id)
                                                <button type="button"
                                                    onclick="openExecuteModal({{ $item->id }}, '{{ route('invoices.execute-service', [$invoice, $item]) }}', '{{ addslashes(app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : $item->service?->name ?? '') }}')"
                                                    class="inline-flex items-center gap-1 bg-blue-600  text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-700 shadow-sm">
                                                    ▶ {{ app()->getLocale() === 'ar' ? 'تنفيذ' : 'Execute' }}
                                                </button>
                                            @else
                                                <span class="text-slate-500 text-xs">{{ app()->getLocale() === 'ar' ? 'كشفية دخول' : 'Entry fee' }}</span>
                                            @endif
                                            @if($item->service_id && $invoice->visit_id && (auth()->user()->can('invoices.create')))
                                                <a href="{{ route('invoices.items.treatment-eligibility-print', [$invoice, $item]) }}"
                                                    target="_blank"
                                                    title="{{ app()->getLocale() === 'ar' ? 'طباعة أحقية علاج لهذه الخدمة فقط (بدون إنشاء فاتورة)' : 'Print treatment eligibility for this service only (no invoice created)' }}"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                                                    🖨️ {{ app()->getLocale() === 'ar' ? 'أحقية علاج' : 'Eligibility' }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $hasInsuranceCoverage ? 9 : 6 }}"
                                    class="border border-slate-300 p-4 text-center text-slate-500">
                                    {{ app()->getLocale() === 'ar' ? 'لا توجد بنود' : 'No items' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Execute Service Modal --}}
        <div id="execute-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-1">
                    {{ app()->getLocale() === 'ar' ? '▶ تنفيذ الخدمة' : '▶ Execute Service' }}</h3>
                <p id="execute-modal-service-name" class="text-slate-600 text-sm mb-4"></p>

                <form id="execute-modal-form" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'تاريخ التنفيذ' : 'Execution Date' }} *
                        </label>
                        <input type="date" name="execution_date" value="{{ date('Y-m-d') }}" required
                            class="w-full rounded-lg border-2 border-slate-400 px-3 py-2 text-slate-900 font-medium focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-blue-600  px-4 py-2.5 rounded-lg font-bold hover:bg-blue-700 shadow">
                            ✅ {{ app()->getLocale() === 'ar' ? 'تأكيد التنفيذ' : 'Confirm Execution' }}
                        </button>
                        <button type="button" onclick="closeExecuteModal()"
                            class="px-4 py-2.5 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300">
                            {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openExecuteModal(itemId, actionUrl, serviceName) {
                document.getElementById('execute-modal-form').action = actionUrl;
                document.getElementById('execute-modal-service-name').textContent = serviceName;
                document.getElementById('execute-modal').classList.remove('hidden');
            }

            function closeExecuteModal() {
                document.getElementById('execute-modal').classList.add('hidden');
            }
            document.getElementById('execute-modal').addEventListener('click', function(e) {
                if (e.target === this) closeExecuteModal();
            });
        </script>

        {{-- Recorded Payments Section --}}
        @if ($invoice->payments->isNotEmpty())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/30">
                <h3 class="font-bold text-slate-800 mb-3">
                    {{ app()->getLocale() === 'ar' ? 'سجل المدفوعات والمستندات' : 'Payment & Document History' }}</h3>
                <div class="overflow-x-auto border border-slate-200 rounded-lg bg-white">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-200">
                                <th class="px-3 py-2 text-center font-bold text-slate-700">
                                    {{ app()->getLocale() === 'ar' ? 'رقم السند' : 'Receipt No' }}</th>
                                <th class="px-3 py-2 text-center font-bold text-slate-700">
                                    {{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                <th class="px-3 py-2 text-center font-bold text-slate-700">
                                    {{ app()->getLocale() === 'ar' ? 'الطريقة' : 'Method' }}</th>
                                <th class="px-3 py-2 text-center font-bold text-slate-700">
                                    {{ app()->getLocale() === 'ar' ? 'المستندات' : 'Documents' }}</th>
                                <th class="px-3 py-2 text-center font-bold text-slate-700">
                                    {{ app()->getLocale() === 'ar' ? 'المستلم' : 'Received By' }}</th>
                                <th class="px-3 py-2 text-center font-bold text-slate-700">
                                    {{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->payments as $payment)
                                @php $receipt = $payment->receipt; @endphp
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="px-3 py-2 text-center font-medium">{{ $receipt?->receipt_number ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-center font-bold text-green-700">@currencyInvoice($payment->amount)</td>
                                    <td class="px-3 py-2 text-center">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ in_array($receipt?->payment_method, ['card', 'bank_transfer', 'mixed']) ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                            {{ $receipt?->split_methods_summary ?? $receipt?->payment_method_label ?? ($payment->payment_type ?? '—') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <div class="flex flex-wrap items-center justify-center gap-2">
                                            @if ($receipt)
                                                <a href="{{ route('payment-receipts.print', $receipt) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 px-2 py-1 rounded text-xs font-bold transition-colors">
                                                    🖨️ {{ app()->getLocale() === 'ar' ? 'طباعة ق-1' : 'Print q-1' }}
                                                </a>
                                            @endif
                                            @if ($receipt && $receipt->hasMedia('physical_receipt'))
                                                <a href="{{ $receipt->getFirstMediaUrl('physical_receipt') }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-2 py-1 rounded text-xs font-bold transition-colors">
                                                    📄 {{ app()->getLocale() === 'ar' ? 'المرفق' : 'Doc' }}
                                                </a>
                                            @endif
                                            @if ($receipt && $receipt->hasMedia('collector_screenshot'))
                                                <a href="{{ $receipt->getFirstMediaUrl('collector_screenshot') }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 px-2 py-1 rounded text-xs font-bold transition-colors">
                                                    📸 {{ app()->getLocale() === 'ar' ? 'لقطة' : 'Shot' }}
                                                </a>
                                            @endif
                                            @if (!$receipt || (!$receipt->hasMedia('physical_receipt') && !$receipt->hasMedia('collector_screenshot')))
                                                @if (!$receipt)
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-center text-slate-600">
                                        {{ $payment->receivedByUser->name ?? ($payment->receivedByUser->username ?? '—') }}
                                    </td>
                                    <td class="px-3 py-2 text-center text-slate-500 text-[10px]">
                                        {{ $payment->received_date?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Totals --}}
        <div class="px-6 pb-6">
            <div class="max-w-md ms-auto space-y-2 border-2 border-slate-300 rounded-lg p-4 bg-slate-50">
                <div class="flex justify-between text-sm">
                    <span
                        class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الإجمالي:' : 'Total:' }}</span>
                    <span class="font-bold">@currencyInvoice($invoice->total_amount)</span>
                </div>
                @if ($hasInsuranceCoverage && $totalInsuranceCovered > 0)
                    <div class="flex justify-between text-sm">
                        <span
                            class="font-semibold text-emerald-700">{{ app()->getLocale() === 'ar' ? 'إجمالي المغطى (التأمين):' : 'Insurance covered:' }}</span>
                        <span class="font-bold text-emerald-700">@currencyInvoice($totalInsuranceCovered)</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span
                            class="font-semibold text-amber-800">{{ app()->getLocale() === 'ar' ? 'حصة المريض:' : 'Patient share:' }}</span>
                        <span class="font-bold text-amber-800">@currencyInvoice($totalPatientShare)</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span
                        class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المدفوع:' : 'Paid:' }}</span>
                    <span class="font-bold text-green-700">@currencyInvoice($invoice->paid_amount)</span>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-slate-300">
                    <span
                        class="font-semibold text-slate-700">{{ ($hasInsuranceCoverage || $isInsuranceOrCharity) ? (app()->getLocale() === 'ar' ? 'المتبقي للمريض (حصته):' : 'Remaining (patient share):') : (app()->getLocale() === 'ar' ? 'المتبقي:' : 'Remaining:') }}</span>
                    <span class="font-bold text-slate-900">@currencyInvoice($effectiveRemaining)</span>
                </div>
                @if (($hasInsuranceCoverage || $isInsuranceOrCharity) && $totalRemaining != $effectiveRemaining)
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-slate-600">{{ app()->getLocale() === 'ar' ? 'المتبقي الإجمالي (مديونية — حتى تُدفع مطالبة التأمين):' : 'Total remaining (debt — until claim paid):' }}</span>
                        <span class="font-bold text-slate-800">@currencyInvoice($totalRemaining)</span>
                    </div>
                @endif
                @if (($hasInsuranceCoverage || $isInsuranceOrCharity) && (float) $invoice->paid_amount == 0)
                    <p class="text-xs text-amber-700 mt-2">{{ app()->getLocale() === 'ar' ? 'لم يُدفع شيء — الإجمالي المستحق = حصة المريض كاملة.' : 'Nothing paid — total due = full patient share.' }}</p>
                @endif
            </div>
            @if ($printMedia->isNotEmpty())
                <div class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200">
                    <span
                        class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'مستندات المريض المختارة للطباعة مع الفاتورة:' : 'Patient documents selected to print with invoice:' }}</span>
                    <ul class="mt-2 space-y-1">
                        @foreach ($printMedia as $media)
                            <li>
                                <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener noreferrer"
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm inline-flex items-center gap-1">
                                    {{ $media->file_name ?? ($media->name ?? 'File #' . $media->id) }}
                                    <span
                                        class="text-slate-500 text-xs">({{ strtoupper($media->extension ?? '') }})</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($invoice->notes)
                <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200">
                    <span
                        class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'ملاحظات:' : 'Notes:' }}</span>
                    <p class="text-sm text-slate-800 mt-1">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Signed Documents Section --}}
    <div class="mt-6 bg-white rounded-lg shadow-lg overflow-hidden border-2 border-emerald-200">
        <div class="p-4 bg-emerald-50 border-b border-emerald-200 flex justify-between items-center">
            <h3 class="font-bold text-emerald-900">
                {{ app()->getLocale() === 'ar' ? '📁 المستندات الموقعة (بعد توقيع المريض)' : '📁 Signed Documents (Post-Signature)' }}
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Upload Form --}}
                <div>
                    <h4 class="text-sm font-bold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? 'رفع مستند جديد:' : 'Upload new document:' }}</h4>
                    <form action="{{ route('invoices.upload-signed-document', $invoice) }}" method="POST"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'نوع المستند:' : 'Document Type:' }}</label>
                            <select name="document_type" required
                                class="w-full rounded-lg border-2 border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                                <option value="signed_commitment">
                                    {{ app()->getLocale() === 'ar' ? 'تعهد موقع' : 'Signed Commitment' }}</option>
                                <option value="signed_non_commitment">
                                    {{ app()->getLocale() === 'ar' ? 'إقرار عدم توقيع موقع' : 'Signed Non-commitment' }}
                                </option>
                                <option value="signed_other">
                                    {{ app()->getLocale() === 'ar' ? 'مستند آخر موقع' : 'Other Signed Document' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs  font-bold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'الملف (PDF أو صورة):' : 'File (PDF or Image):' }}</label>
                            <input type="file" name="signed_file" required accept=".pdf,image/*"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                        <button type="submit"
                            class="w-full m-2 bg-emerald-600 text-slate-50 py-2 rounded-lg font-bold hover:bg-emerald-700 shadow transition-colors">
                            {{ app()->getLocale() === 'ar' ? '⬆️ رفع المستند' : '⬆️ Upload Document' }}
                        </button>
                    </form>
                </div>

                {{-- List of Documents --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? 'المستندات المرفوعة حالياً:' : 'Currently uploaded documents:' }}
                    </h4>
                    @php
                        $collections = [
                            'signed_commitment' => ['ar' => 'تعهد موقع', 'en' => 'Signed Commitment'],
                            'signed_non_commitment' => [
                                'ar' => 'إقرار عدم توقيع موقع',
                                'en' => 'Signed Non-commitment',
                            ],
                            'signed_other' => ['ar' => 'مستند آخر موقع', 'en' => 'Other Signed Document'],
                        ];
                    @endphp

                    @php $hasDocs = false; @endphp
                    @foreach ($collections as $col => $labels)
                        @foreach ($invoice->getMedia($col) as $media)
                            @php $hasDocs = true; @endphp
                            <div
                                class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-lg group">
                                <div class="flex items-center gap-3">
                                    <div class="bg-emerald-100 text-emerald-700 p-2 rounded">
                                        @if (str_contains($media->mime_type, 'pdf'))
                                            📄
                                        @else
                                            🖼️
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">
                                            {{ app()->getLocale() === 'ar' ? $labels['ar'] : $labels['en'] }}</p>
                                        <a href="{{ $media->getUrl() }}" target="_blank"
                                            class="text-[10px] text-blue-600 hover:underline truncate max-w-[150px] inline-block">
                                            {{ $media->file_name }}
                                        </a>
                                        <span
                                            class="text-[10px] text-slate-400 block">{{ $media->created_at->format('Y-m-d H:i') }}</span>
                                    </div>
                                </div>
                                <form action="{{ route('invoices.delete-signed-document', [$invoice, $media]) }}"
                                    method="POST"
                                    onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure you want to delete?' }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-600 p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    @endforeach

                    @if (!$hasDocs)
                        <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-lg">
                            <p class="text-sm text-slate-400 italic">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد مستندات موقعة مرفوعة حالياً' : 'No signed documents uploaded yet' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Payment Recording Modal --}}
    <div id="payment-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 my-4">
            <h3 class="text-xl font-bold text-slate-800 mb-4">
                {{ app()->getLocale() === 'ar' ? '💰 تسجيل دفعة ومستندات التحصيل' : '💰 Record Payment & Collection Documents' }}
            </h3>

            <form action="{{ route('payment-receipts.store') }}" method="POST" enctype="multipart/form-data"
                id="payment-form" data-effective-remaining="{{ $effectiveRemaining }}">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                {{-- Item Selection Section --}}
                <div class="mb-5 bg-slate-50 border-2 border-slate-200 rounded-lg p-4">
                    <h4
                        class="text-sm font-bold text-slate-700 mb-3 border-b-2 border-slate-300 pb-2 flex justify-between">
                        <span>📋
                            {{ app()->getLocale() === 'ar' ? 'اختر الخدمات التي يتم دفعها' : 'Select services to pay for' }}</span>
                        <span
                            class="text-xs text-slate-500 font-normal">{{ app()->getLocale() === 'ar' ? '(فقط الخدمات غير المسددة تظهر هنا)' : '(Only unpaid items are listed)' }}</span>
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                        @foreach ($invoice->items as $item)
                            @php
                                // Note: In a real system, we might track per-item paid amount,
                                // but for now we assume if invoice has remaining amount, we can select items.
                                // We'll check if item is not fully covered by existing payments (if we had that tracking)
                            @endphp
                            <label
                                class="flex items-center justify-between p-2 rounded hover:bg-white border border-transparent hover:border-slate-300 cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="item_ids[]" value="{{ $item->id }}"
                                        data-amount="{{ $item->patient_amount }}" checked
                                        onchange="updateCalculatedTotal()"
                                        class="w-5 h-5 rounded text-green-600 border-slate-400 focus:ring-green-500">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">
                                            {{ $item->service?->name_ar ?? $item->service?->name }}</p>
                                        <p class="text-[10px] text-slate-500">Qty: {{ $item->quantity }} @
                                            @currencyInvoice($item->unit_price)</p>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-sm font-bold text-slate-900">@currencyInvoice($item->patient_amount)</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'طرق الدفع (يمكن إضافة أكثر من طريقة — مجموع الأسطر = إجمالي الدفع)' : 'Payment methods (add multiple lines — sum must equal total)' }}
                    </label>
                    <div class="overflow-x-auto rounded-lg border-2 border-slate-200 bg-white">
                        <table class="w-full text-sm" id="split-payment-table">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700">
                                    <th class="p-2 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'الطريقة' : 'Method' }}</th>
                                    <th class="p-2 text-start font-bold w-32">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                    <th class="p-2 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'مرجع' : 'Ref.' }}</th>
                                    <th class="p-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="split-rows-body">
                                <tr data-split-row>
                                    <td class="p-2 align-top">
                                        <select name="split_lines[0][payment_method]" class="split-method w-full rounded border border-slate-300 px-2 py-1.5 font-medium">
                                            <option value="cash">{{ app()->getLocale() === 'ar' ? 'كاش' : 'Cash' }}</option>
                                            <option value="card">{{ app()->getLocale() === 'ar' ? 'شبكة / POS' : 'POS / Card' }}</option>
                                            <option value="bank_transfer">{{ app()->getLocale() === 'ar' ? 'تحويل بنكي' : 'Bank transfer' }}</option>
                                            <option value="cheque">{{ app()->getLocale() === 'ar' ? 'شيك' : 'Cheque' }}</option>
                                            <option value="loyalty_points">{{ app()->getLocale() === 'ar' ? 'نقاط بيع' : 'Loyalty / sale points' }}</option>
                                            @if ($invoice->patient && in_array($invoice->patient->payment_type, ['insurance', 'charity']))
                                                <option value="insurance">{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</option>
                                                <option value="charity">{{ app()->getLocale() === 'ar' ? 'جمعية' : 'Charity' }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="p-2 align-top">
                                        <input type="number" name="split_lines[0][amount]" step="0.01" min="0.01" required
                                            class="split-amount w-full rounded border border-slate-300 px-2 py-1.5 font-bold text-green-800">
                                    </td>
                                    <td class="p-2 align-top">
                                        <input type="text" name="split_lines[0][reference_number]" placeholder="—"
                                            class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs">
                                    </td>
                                    <td class="p-2 align-top text-center">
                                        <button type="button" class="btn-remove-split text-red-600 font-bold text-lg leading-none hidden" title="{{ app()->getLocale() === 'ar' ? 'حذف السطر' : 'Remove' }}">×</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="btn-add-split-row" class="mt-2 text-sm font-bold text-blue-700 hover:text-blue-900">
                        + {{ app()->getLocale() === 'ar' ? 'إضافة طريقة دفع' : 'Add payment method' }}
                    </button>
                    <p id="split-sum-warning" class="mt-2 text-xs text-red-600 hidden"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'إجمالي الدفع (حصة المريض فقط)' : 'Total Payment (patient share only)' }} *
                        </label>
                        @if ($hasInsuranceCoverage || $isInsuranceOrCharity)
                            <p class="text-xs text-amber-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المبلغ المستحق من المريض فقط — لا يدخل مبلغ شركة التأمين/الجمعية هنا.' : 'Amount due from patient only — do not enter insurance/charity portion here.' }}</p>
                            @if ((float) $invoice->paid_amount == 0)
                                <p class="text-xs text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'الفاتورة غير مدفوعة — الإجمالي = المبلغ كامل حصة المريض.' : 'Invoice unpaid — total = full patient share amount.' }}</p>
                            @endif
                        @endif
                        <input type="number" name="amount" id="payment-total-amount" step="0.01"
                            max="{{ $effectiveRemaining }}" value="{{ $effectiveRemaining }}" required readonly
                            class="w-full rounded-lg border-2 border-slate-300 px-3 py-2 text-lg font-bold text-green-700 bg-slate-100 cursor-not-allowed">
                    </div>
                    <div class="hidden md:block"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-rose-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'رقم الترقيم من الوزارة (يُطبع على إيصال التحصيل)' : 'Ministry receipt number (printed on q-1)' }}
                        </label>
                        <input type="text" name="ministry_receipt_number" value="{{ old('ministry_receipt_number') }}"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: 5267859' : 'e.g. 5267859' }}"
                            class="w-full rounded-lg border-2 border-rose-200 px-3 py-2 focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'رقم المرجع / الشيك' : 'Reference / Cheque Number' }}
                        </label>
                        <input type="text" name="reference_number"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }}"
                            class="w-full rounded-lg border-2 border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                        <label class="block text-xs font-bold text-indigo-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? '📁 إرفاق إيصال التحصيل (ق-1)' : '📁 Attach Collection Receipt (q-1)' }}
                        </label>
                        <input type="file" name="physical_receipt" accept="image/*,.pdf"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                    </div>

                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <label class="block text-xs font-bold text-slate-600 mb-2">
                            {{ app()->getLocale() === 'ar' ? '📸 شاشة عمليات المحصل' : '📸 Collector Operation Screenshot' }}
                        </label>
                        <input type="file" name="collector_screenshot" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-slate-600 file:text-white hover:file:bg-slate-700">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'ملاحظات إضافية' : 'Additional Notes' }}
                    </label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-lg border-2 border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" id="submit-payment-btn"
                    style="background-color: #77786c;"
                        class="flex-1 bg-green-600  px-4 py-3 rounded-lg font-bold text-lg hover:bg-green-700 shadow-lg flex items-center justify-center gap-2">
                        ✅ {{ app()->getLocale() === 'ar' ? 'حفظ وإرسال للمحاسب' : 'Save & Send to Accountant' }}
                    </button>
                    <button type="button" onclick="closePaymentModal()"
                        class="px-6 py-3 bg-slate-200 text-slate-700 rounded-lg font-bold hover:bg-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal() {
            document.getElementById('payment-modal').classList.remove('hidden');
            updateCalculatedTotal();
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }
        document.getElementById('payment-modal').addEventListener('click', function(e) {
            if (e.target === this) closePaymentModal();
        });

        function reindexSplitRows() {
            const body = document.getElementById('split-rows-body');
            if (!body) return;
            const rows = body.querySelectorAll('[data-split-row]');
            rows.forEach(function(row, index) {
                row.querySelectorAll('select, input').forEach(function(el) {
                    const n = el.getAttribute('name');
                    if (n && n.indexOf('split_lines[') === 0) {
                        el.setAttribute('name', n.replace(/split_lines\[\d+]/, 'split_lines[' + index + ']'));
                    }
                });
                const rm = row.querySelector('.btn-remove-split');
                if (rm) rm.classList.toggle('hidden', rows.length <= 1);
            });
        }

        function sumSplitAmounts() {
            let s = 0;
            document.querySelectorAll('#split-rows-body .split-amount').forEach(function(inp) {
                s += parseFloat(inp.value || 0) || 0;
            });
            return Math.round(s * 100) / 100;
        }

        function syncSplitAmountsToTotal() {
            const totalInput = document.getElementById('payment-total-amount');
            const rows = document.querySelectorAll('#split-rows-body [data-split-row]');
            if (!totalInput || rows.length !== 1) return;
            const amt = rows[0].querySelector('.split-amount');
            if (amt) amt.value = parseFloat(totalInput.value || 0).toFixed(2);
            validateSplitSum();
        }

        function validateSplitSum() {
            const warn = document.getElementById('split-sum-warning');
            const totalInput = document.getElementById('payment-total-amount');
            if (!warn || !totalInput) return true;
            const target = parseFloat(totalInput.value || 0);
            const sum = sumSplitAmounts();
            if (Math.abs(sum - target) > 0.02) {
                warn.textContent = document.documentElement.lang === 'ar'
                    ? 'مجموع المبالغ في الأسطر (' + sum.toFixed(2) + ') يجب أن يساوي الإجمالي (' + target.toFixed(2) + ').'
                    : 'Line amounts (' + sum.toFixed(2) + ') must equal total (' + target.toFixed(2) + ').';
                warn.classList.remove('hidden');
                return false;
            }
            warn.classList.add('hidden');
            return true;
        }

        function addSplitRow() {
            const body = document.getElementById('split-rows-body');
            const first = body && body.querySelector('[data-split-row]');
            if (!body || !first) return;
            const clone = first.cloneNode(true);
            clone.querySelectorAll('input').forEach(function(i) {
                if (i.classList.contains('split-amount')) i.value = '';
                else i.value = '';
            });
            body.appendChild(clone);
            reindexSplitRows();
            clone.querySelectorAll('.split-amount, .split-method').forEach(function(el) {
                el.addEventListener('input', validateSplitSum);
                el.addEventListener('change', validateSplitSum);
            });
            validateSplitSum();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const addBtn = document.getElementById('btn-add-split-row');
            if (addBtn) addBtn.addEventListener('click', addSplitRow);
            const body = document.getElementById('split-rows-body');
            if (body) {
                body.addEventListener('click', function(e) {
                    if (e.target.classList.contains('btn-remove-split')) {
                        const rows = body.querySelectorAll('[data-split-row]');
                        if (rows.length <= 1) return;
                        e.target.closest('[data-split-row]').remove();
                        reindexSplitRows();
                        syncSplitAmountsToTotal();
                        validateSplitSum();
                    }
                });
                body.querySelectorAll('.split-amount, .split-method').forEach(function(el) {
                    el.addEventListener('input', validateSplitSum);
                    el.addEventListener('change', validateSplitSum);
                });
            }
            const payForm = document.getElementById('payment-form');
            if (payForm) {
                payForm.addEventListener('submit', function(e) {
                    if (!validateSplitSum()) e.preventDefault();
                });
            }
        });

        function updateCalculatedTotal() {
            let total = 0;
            const checkboxes = document.querySelectorAll('input[name="item_ids[]"]:checked');
            checkboxes.forEach(cb => {
                total += parseFloat(cb.dataset.amount || 0);
            });

            const form = document.getElementById('payment-form');
            const maxRemaining = form ? parseFloat(form.dataset.effectiveRemaining || 0) : 0;
            const amountDue = maxRemaining > 0 ? Math.min(total, maxRemaining) : total;

            const totalInput = document.getElementById('payment-total-amount');
            if (totalInput) {
                totalInput.value = amountDue.toFixed(2);
            }

            syncSplitAmountsToTotal();

            const submitBtn = document.getElementById('submit-payment-btn');
            if (submitBtn) {
                if (amountDue <= 0) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        }
    </script>
@endsection
