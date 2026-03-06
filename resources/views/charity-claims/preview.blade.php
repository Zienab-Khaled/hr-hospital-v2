@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'معاينة المطالبة قبل الإرسال' : 'Claim Preview Before Send')

@section('content')
    @php
        use App\Models\Setting;
        $hospitalName = Setting::get('hospital_name', '');
        $hospitalNameEn = Setting::get('hospital_name_en', '');
        $healthClusterName = Setting::get('health_cluster_name', '');
        $reportCountryAr = Setting::get('report_header_country_ar', 'المملكة العربية السعودية');
        $reportMinistryAr = Setting::get('report_header_ministry_ar', 'وزارة الصحة');
        $logo = Setting::get('logo');
        $invoice = $charityClaim->invoice;
        $patient = $invoice->patient;
        $charity = $charityClaim->charityEntity;
    @endphp

    {{-- Preview bar: back + print (hidden when printing) --}}
    <div class="print:hidden mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3">
        <p class="text-amber-800 font-semibold text-sm">
            👁️ {{ app()->getLocale() === 'ar' ? 'معاينة قبل الإرسال — هذا هو شكل المستند كما سيظهر للجمعية' : 'Preview before sending — this is how the document will appear to the charity' }}
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('charity-claims.show', $charityClaim) }}"
               class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-300">
                ← {{ app()->getLocale() === 'ar' ? 'العودة للمطالبة' : 'Back to claim' }}
            </a>
            <button type="button" onclick="window.print()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                🖨️ {{ app()->getLocale() === 'ar' ? 'طباعة' : 'Print' }}
            </button>
        </div>
    </div>

    {{-- Document (same as what would be sent to charity) --}}
    <div id="claim-document" class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden border-2 border-slate-200 print:shadow-none print:border print:max-w-none">
        {{-- Official header --}}
        <div class="border-b-2 border-slate-800 bg-slate-50 px-6 py-4">
            <div class="flex flex-wrap items-center gap-4">
                @if($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
                    <div class="shrink-0">
                        <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="h-14 object-contain">
                    </div>
                @endif
                <div class="flex-1 text-center" dir="rtl">
                    <div class="text-sm font-bold text-slate-800">{{ $reportCountryAr }}</div>
                    <div class="text-sm font-bold text-slate-700">{{ $reportMinistryAr }}</div>
                    <div class="text-sm text-slate-600">{{ $healthClusterName }}</div>
                    <div class="text-lg font-bold text-slate-900 mt-1">{{ $hospitalName }}</div>
                    @if($hospitalNameEn)
                        <div class="text-xs text-slate-500">{{ $hospitalNameEn }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-6 py-5 text-slate-800" dir="rtl" style="font-family: 'Cairo', 'Tajawal', sans-serif;">
            <div class="mb-4 text-center">
                <span class="inline-block bg-slate-800 text-white text-xs font-bold px-3 py-1 rounded">
                    {{ app()->getLocale() === 'ar' ? 'مطالبة مالية للجمعية الخيرية' : 'Charity Claim Document' }}
                </span>
            </div>

            <div class="space-y-4 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <p><span class="font-bold text-slate-600">{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</span> {{ now()->translatedFormat('d F Y') }}</p>
                    <p><span class="font-bold text-slate-600">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice No:' }}</span> {{ $invoice->invoice_number ?? '—' }}</p>
                </div>

                <div class="border-r-4 border-blue-600 bg-blue-50/50 p-3 rounded">
                    <p class="font-bold text-slate-800 mb-1">{{ app()->getLocale() === 'ar' ? 'إلى:' : 'To:' }}</p>
                    <p class="text-slate-800">{{ $charity->name_ar ?? $charity->name ?? '—' }}</p>
                    @if($charity->address)
                        <p class="text-slate-600 text-xs mt-1">{{ $charity->address }}</p>
                    @endif
                </div>

                <div>
                    <p class="font-bold text-slate-800 mb-2">{{ app()->getLocale() === 'ar' ? 'الموضوع: مطالبة مالية لعلاج المريض/المرضى المكفولين لديكم' : 'Subject: Financial claim for treatment of sponsored patient(s)' }}</p>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <p class="font-bold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'بيانات المريض' : 'Patient details' }}</p>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <p><span class="text-slate-600">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span> {{ $patient->name_ar ?? $patient->name ?? '—' }}</p>
                        <p><span class="text-slate-600">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span> {{ $patient->file_number ?? '—' }}</p>
                    </div>
                </div>

                <div>
                    <p class="font-bold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'تفاصيل الخدمات والملبغ المطلوب' : 'Services and amount claimed' }}</p>
                    <div class="overflow-x-auto border border-slate-300 rounded-lg">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-700 text-white">
                                    <th class="text-right p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'م' : '#' }}</th>
                                    <th class="text-right p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}</th>
                                    <th class="text-center p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                    <th class="text-left p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'المبلغ (ر.س)' : 'Amount (SAR)' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items ?? [] as $idx => $item)
                                    <tr class="border-b border-slate-200 {{ $loop->even ? 'bg-slate-50' : '' }}">
                                        <td class="p-2">{{ $idx + 1 }}</td>
                                        <td class="p-2">{{ $item->service?->name_ar ?? $item->service?->name ?? '—' }}</td>
                                        <td class="p-2 text-center">{{ $item->quantity }}</td>
                                        <td class="p-2">{{ number_format((float) $item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <p class="text-lg font-bold text-slate-900">
                            {{ app()->getLocale() === 'ar' ? 'الإجمالي المطلوب:' : 'Total claimed:' }}
                            <span class="text-blue-700">{{ number_format((float) $invoice->total_amount, 2) }} {{ app()->getLocale() === 'ar' ? 'ر.س' : 'SAR' }}</span>
                        </p>
                    </div>
                </div>

                @if($charityClaim->notes)
                    <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                        <p class="font-bold text-amber-800 text-sm">{{ app()->getLocale() === 'ar' ? 'ملاحظات:' : 'Notes:' }}</p>
                        <p class="text-slate-800 text-sm mt-1">{{ $charityClaim->notes }}</p>
                    </div>
                @endif

                <p class="text-slate-700 text-sm mt-6">
                    {{ app()->getLocale() === 'ar' ? 'نتطلع لتعاونكم وتنفيذ المبلغ المستحق وفق الضوابط المعتمدة. والله ولي التوفيق.' : 'We look forward to your cooperation and settlement of the amount in accordance with applicable procedures.' }}
                </p>
            </div>
        </div>

        {{-- Footer line --}}
        <div class="border-t-2 border-slate-200 bg-slate-50 px-6 py-3 text-center text-xs text-slate-500" dir="rtl">
            {{ $hospitalName }} — {{ $healthClusterName }}
        </div>
    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            #claim-document, #claim-document * { visibility: visible; }
            #claim-document { position: absolute; left: 0; top: 0; width: 100%; max-width: 100%; box-shadow: none; border: 1px solid #ccc; }
            .print\\:hidden { display: none !important; }
        }
    </style>
@endsection
