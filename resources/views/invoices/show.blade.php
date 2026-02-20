@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الفاتورة' : 'Invoice Details')

@section('content')
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
                @can('invoices.edit')
                    <a href="{{ route('invoices.edit', $invoice) }}"
                        class="bg-blue-600  px-4 py-2 rounded-lg font-semibold hover:bg-blue-700">
                        {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                    </a>
                @endcan
            </div>
        </div>

        {{-- الخطوات التالية بعد إنشاء الفاتورة --}}
        <div class="mb-6 p-4 rounded-lg border-2 border-blue-200 bg-blue-50/70">
            <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'الخطوات التالية' : 'Next steps' }}</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoices.print-commitment', $invoice) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 bg-white border-2 border-slate-400 text-slate-800 px-4 py-2 rounded-lg font-semibold hover:bg-slate-100 hover:border-slate-500">
                    {{ app()->getLocale() === 'ar' ? '🖨️ طباعة محضر تعهد' : 'Print commitment form' }}
                </a>
                <a href="{{ route('invoices.print-non-commitment', $invoice) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 bg-white border-2 border-slate-400 text-slate-800 px-4 py-2 rounded-lg font-semibold hover:bg-slate-100 hover:border-slate-500">
                    {{ app()->getLocale() === 'ar' ? '🖨️ طباعة محضر إقرار بعدم التوقيع' : 'Print non-commitment form' }}
                </a>
                @if($invoice->patient?->payment_type === 'charity')
                    <a href="{{ route('invoices.send-to-party', $invoice) }}"
                        class="inline-flex items-center gap-2 bg-emerald-600  px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700">
                        {{ app()->getLocale() === 'ar' ? '✉️ إرسال الفاتورة للجمعية الخيرية' : '✉️ Send invoice to charity' }}
                    </a>
                @endif

                {{-- زرار إشعار الجمعية باكتمال الخدمات — يظهر فقط لمرضى الجمعية بعد تنفيذ كل الخدمات --}}
                @if($invoice->patient?->payment_type === 'charity' && $invoice->isFullyCompleted() && $invoice->patient?->charityEntity?->email)
                    @can('invoices.edit')
                        <form method="POST" action="{{ route('invoices.notify-charity-completed', $invoice) }}"
                              onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل تريد إرسال إيميل للجمعية بأن جميع الخدمات قد نُفِّذت؟' : 'Send completion email to charity?' }}')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 bg-teal-600  px-4 py-2 rounded-lg font-semibold hover:bg-teal-700 shadow-md ring-2 ring-teal-300 animate-pulse">
                                ✉️ {{ app()->getLocale() === 'ar' ? 'إشعار الجمعية باكتمال الخدمات' : 'Notify charity of completion' }}
                            </button>
                        </form>
                    @endcan
                @elseif($invoice->patient?->payment_type === 'charity' && !$invoice->isFullyCompleted())
                    <span class="inline-flex items-center gap-2 bg-amber-50 border-2 border-amber-300 text-amber-800 px-4 py-2 rounded-lg font-semibold text-sm">
                        ⏳ {{ app()->getLocale() === 'ar' ? 'في انتظار تنفيذ جميع الخدمات' : 'Waiting for all services to be executed' }}
                        ({{ $invoice->items->where('status', 'completed')->count() }}/{{ $invoice->items->count() }})
                    </span>
                @endif
            </div>
        </div>


        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            {{-- Invoice header --}}
            <div class="p-6 border-b-2 border-slate-200 bg-slate-50">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice No:' }}</span>
                        <p class="text-lg font-bold text-slate-900">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div>
                        <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</span>
                        <p class="text-lg font-medium text-slate-900">{{ $invoice->invoice_date?->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'الحالة:' : 'Status:' }}</span>
                        <p class="text-lg font-medium text-slate-900">{{ $invoice->status_label }}</p>
                    </div>
                    @if($invoice->visit?->referral_number)
                        <div>
                            <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الإحالة:' : 'Referral No:' }}</span>
                            <p class="text-lg font-medium text-slate-900">{{ $invoice->visit->referral_number }}</p>
                        </div>
                    @endif
                    @if($invoice->visit?->registeredBy)
                        <div>
                            <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'أنشئت بواسطة:' : 'Created by:' }}</span>
                            <p class="text-lg font-medium text-slate-900">{{ $invoice->visit->registeredBy->name ?? $invoice->visit->registeredBy->username ?? '—' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Patient --}}
            @if($invoice->patient)
                <div class="p-6 border-b border-slate-200 bg-blue-50/50">
                    <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'معلومات المريض' : 'Patient' }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                        <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span> {{ $invoice->patient->name }}</p>
                        @if($invoice->patient->name_ar)
                            <p dir="rtl"><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي):' : 'Name (AR):' }}</span> {{ $invoice->patient->name_ar }}</p>
                        @endif
                        <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span> {{ $invoice->patient->file_number }}</p>
                        <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'نوع الدفع:' : 'Payment:' }}</span> {{ $invoice->patient->payment_type ?? '—' }}</p>
                        @if($invoice->patient->payment_type === 'insurance' && $invoice->patient->insuranceCompany)
                            <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'شركة التأمين:' : 'Insurance company:' }}</span> {{ $invoice->patient->insuranceCompany->name }}</p>
                        @endif
                        @if($invoice->patient->payment_type === 'charity' && $invoice->patient->charityEntity)
                            <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الجمعية:' : 'Charity entity:' }}</span> {{ $invoice->patient->charityEntity->name }}</p>
                        @endif
                        @if($invoice->patient->phone)
                            <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الهاتف:' : 'Phone:' }}</span> {{ $invoice->patient->phone }}</p>
                        @endif
                        @if($invoice->patient->identity_value)
                            <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الهوية/الفيزا:' : 'ID/Visa:' }}</span> {{ $invoice->patient->identity_value }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @php
                $hasInsuranceCoverage = $invoice->items->contains(fn ($i) => !empty($i->insurance_coverage_type));
                $totalInsuranceCovered = $invoice->items->sum(fn ($i) => (float) $i->insurance_covered_amount);
                $totalPatientShare = $invoice->items->sum(fn ($i) => (float) $i->patient_amount);
            @endphp
            {{-- Services table (الخدمات المقدمة) --}}
            <div class="p-6">
                <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'الخدمات المقدمة' : 'Provided Services' }}</h3>

                @if(session('success'))
                    <div class="mb-3 p-3 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm font-medium">
                        ✅ {{ session('success') }}
                    </div>
                @endif
                @if($errors->has('error'))
                    <div class="mb-3 p-3 rounded-lg bg-red-50 border border-red-300 text-red-800 text-sm font-medium">
                        ⚠️ {{ $errors->first('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto border-2 border-slate-300 rounded-lg">
                    <table class="w-full border-collapse" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                        <thead>
                            <tr class="bg-slate-200 border-b-2 border-slate-500">
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الرمز' : 'Code' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'البيان' : 'Description' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'السعر الافرادي' : 'Unit Price' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                @if($hasInsuranceCoverage)
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'التغطية' : 'Coverage' }}</th>
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المغطى' : 'Covered' }}</th>
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المتبقي للمريض' : 'Patient share' }}</th>
                                @endif
                                @can('invoices.edit')
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'التنفيذ' : 'Execution' }}</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr class="border-b border-slate-300 {{ $item->isCompleted() ? 'bg-emerald-50/40' : '' }}">
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">{{ $item->service?->code ?? '—' }}</td>
                                    <td class="border border-slate-300 px-2 py-2 text-sm">
                                        {{ app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : ($item->service?->name ?? '—') }}
                                        @if($item->description)
                                            <br><span class="text-slate-500 text-xs">{{ $item->description }}</span>
                                        @endif
                                    </td>
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">{{ $item->quantity }}</td>
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">@currency($item->unit_price)</td>
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm font-medium">@currency($item->total_price)</td>
                                    @if($hasInsuranceCoverage)
                                        <td class="border border-slate-300 px-2 py-2 text-center text-sm">
                                            @if($item->insurance_coverage_type)
                                                @if($item->insurance_coverage_type === 'percentage')
                                                    {{ app()->getLocale() === 'ar' ? 'نسبة' : 'Percentage' }} {{ round((float) $item->insurance_coverage_value, 0) }}%
                                                @else
                                                    {{ app()->getLocale() === 'ar' ? 'قيمة ثابتة' : 'Fixed' }} @currency($item->insurance_coverage_value)
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="border border-slate-300 px-2 py-2 text-center text-sm text-emerald-700 font-medium">@currency($item->insurance_covered_amount)</td>
                                        <td class="border border-slate-300 px-2 py-2 text-center text-sm text-amber-800 font-medium">@currency($item->patient_amount)</td>
                                    @endif
                                    @can('invoices.edit')
                                        <td class="border border-slate-300 px-2 py-2 text-center text-sm">
                                            @if($item->isCompleted())
                                                <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-1 rounded-full border border-emerald-300">
                                                    ✅ {{ app()->getLocale() === 'ar' ? 'منفذ' : 'Done' }}
                                                </span>
                                                @if($item->execution_date)
                                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $item->execution_date->format('Y-m-d') }}</span>
                                                @endif
                                                @if($item->completedByUser)
                                                    <span class="block text-xs text-slate-400">{{ $item->completedByUser->name ?? $item->completedByUser->username }}</span>
                                                @endif
                                            @else
                                                <button type="button"
                                                    onclick="openExecuteModal({{ $item->id }}, '{{ route('invoices.execute-service', [$invoice, $item]) }}', '{{ addslashes(app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : ($item->service?->name ?? '')) }}')"
                                                    class="inline-flex items-center gap-1 bg-blue-600  text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-700 shadow-sm">
                                                    ▶ {{ app()->getLocale() === 'ar' ? 'تنفيذ' : 'Execute' }}
                                                </button>
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $hasInsuranceCoverage ? 9 : 6 }}" class="border border-slate-300 p-4 text-center text-slate-500">
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
                    <h3 class="text-lg font-bold text-slate-800 mb-1">{{ app()->getLocale() === 'ar' ? '▶ تنفيذ الخدمة' : '▶ Execute Service' }}</h3>
                    <p id="execute-modal-service-name" class="text-slate-600 text-sm mb-4"></p>

                    <form id="execute-modal-form" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                {{ app()->getLocale() === 'ar' ? 'تاريخ التنفيذ' : 'Execution Date' }} *
                            </label>
                            <input type="date" name="execution_date"
                                   value="{{ date('Y-m-d') }}"
                                   required
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

            {{-- Totals --}}
            <div class="px-6 pb-6">
                <div class="max-w-md ms-auto space-y-2 border-2 border-slate-300 rounded-lg p-4 bg-slate-50">
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الإجمالي:' : 'Total:' }}</span>
                        <span class="font-bold">@currency($invoice->total_amount)</span>
                    </div>
                    @if($hasInsuranceCoverage && $totalInsuranceCovered > 0)
                        <div class="flex justify-between text-sm">
                            <span class="font-semibold text-emerald-700">{{ app()->getLocale() === 'ar' ? 'إجمالي المغطى (التأمين):' : 'Insurance covered:' }}</span>
                            <span class="font-bold text-emerald-700">@currency($totalInsuranceCovered)</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="font-semibold text-amber-800">{{ app()->getLocale() === 'ar' ? 'حصة المريض:' : 'Patient share:' }}</span>
                            <span class="font-bold text-amber-800">@currency($totalPatientShare)</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المدفوع:' : 'Paid:' }}</span>
                        <span class="font-bold text-green-700">@currency($invoice->paid_amount)</span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-300">
                        <span class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المتبقي:' : 'Remaining:' }}</span>
                        <span class="font-bold text-slate-900">@currency($invoice->remaining_amount)</span>
                    </div>
                </div>
                @if($printMedia->isNotEmpty())
                    <div class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200">
                        <span class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'مستندات المريض المختارة للطباعة مع الفاتورة:' : 'Patient documents selected to print with invoice:' }}</span>
                        <ul class="mt-2 space-y-1">
                            @foreach($printMedia as $media)
                                <li>
                                    <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener noreferrer"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm inline-flex items-center gap-1">
                                        {{ $media->file_name ?? $media->name ?? ('File #' . $media->id) }}
                                        <span class="text-slate-500 text-xs">({{ strtoupper($media->extension ?? '') }})</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @php
                    $medicalReports = $invoice->attachments->where('document_type', 'medical_report');
                @endphp
                @if($medicalReports->isNotEmpty())
                    <div class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'التقرير الطبي المرفق بالفاتورة:' : 'Attached medical report(s):' }}</span>
                        <ul class="mt-2 space-y-1">
                            @foreach($medicalReports as $att)
                                <li>
                                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" rel="noopener noreferrer"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm inline-flex items-center gap-1">
                                        {{ $att->file_name ?? 'File #' . $att->id }}
                                        <span class="text-slate-500 text-xs">({{ $att->mime_type ?: '—' }})</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($invoice->notes)
                    <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200">
                        <span class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'ملاحظات:' : 'Notes:' }}</span>
                        <p class="text-sm text-slate-800 mt-1">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
