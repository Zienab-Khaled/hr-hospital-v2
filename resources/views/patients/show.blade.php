@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'عرض مريض' : 'Patient Details')
@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    {{ app()->getLocale() === 'ar' ? '👤 عرض مريض' : '👤 Patient Details' }}
                </h2>
                <div class="flex gap-2">
                    @can('patients.edit')
                        <a href="{{ route('patients.edit', $patient) }}"
                            class="border-2 border-violet-600 bg-violet-600  px-4 py-2 rounded-lg text-sm font-semibold hover:bg-violet-700 transition-colors">
                            {{ app()->getLocale() === 'ar' ? '✏️ تعديل' : '✏️ Edit' }}
                        </a>
                    @endcan
                    <a href="{{ route('patients.search') }}"
                        class="border-2 border-slate-400 bg-slate-100 text-slate-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-200 transition-colors">
                        {{ app()->getLocale() === 'ar' ? '← بحث' : '← Search' }}
                    </a>
                </div>
            </div>

            {{-- Identity --}}
            <div class="border-b border-slate-200 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-slate-700 mb-3">
                    {{ app()->getLocale() === 'ar' ? '📋 وثائق الهوية' : '📋 Identity' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span
                            class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'نوع الهوية' : 'Identity Type' }}</span>
                        <p class="font-medium text-slate-800">{{ $patient->identity_type_label ?? $patient->identity_type }}
                        </p>
                    </div>
                    <div>
                        <span
                            class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'رقم الهوية' : 'Identity Number' }}</span>
                        <p class="font-medium text-slate-800">{{ $patient->identity_value }}</p>
                    </div>
                </div>
            </div>

            {{-- Personal --}}
            <div class="border-b border-slate-200 pb-4 mb-4">
                <h3 class="text-lg font-semibold text-slate-700 mb-3">
                    {{ app()->getLocale() === 'ar' ? '👤 المعلومات الشخصية' : '👤 Personal Information' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span
                            class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</span>
                        <p class="font-medium text-slate-800">{{ $patient->name }}</p>
                    </div>
                    <div>
                        <span
                            class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</span>
                        <p class="font-medium text-slate-800" dir="rtl">{{ $patient->name_ar ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File Number' }}</span>
                        <p class="font-medium text-slate-800">{{ $patient->file_number }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'العمر' : 'Age' }}</span>
                        <p class="font-medium text-slate-800">{{ $patient->age ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }}</span>
                        <p class="font-medium text-slate-800">
                            @if ($patient->gender === 'male')
                                {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}
                            @elseif($patient->gender === 'female')
                                {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</span>
                        <p class="font-medium text-slate-800">{{ $patient->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <span
                            class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}</span>
                        <p class="font-medium text-slate-800">{{ ucfirst($patient->payment_type) }}</p>
                    </div>
                    @if ($patient->insuranceCompany)
                        <div>
                            <span
                                class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}</span>
                            <p class="font-medium text-slate-800">{{ $patient->insuranceCompany->name }}</p>
                        </div>
                    @endif
                    @if ($patient->charityEntity)
                        <div>
                            <span
                                class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'الجمعية الخيرية' : 'Charity' }}</span>
                            <p class="font-medium text-slate-800">{{ $patient->charityEntity->name }}</p>
                        </div>
                    @endif
                </div>
                @if ($patient->notes)
                    <div class="mt-3">
                        <span class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</span>
                        <p class="text-slate-800 mt-1">{{ $patient->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Visits (latest 10) --}}
            @if ($patient->visits->isNotEmpty())
                <div class="border-b border-slate-200 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-3">
                        {{ app()->getLocale() === 'ar' ? '🩺 آخر الزيارات' : '🩺 Recent Visits' }}
                        ({{ $patient->visits->count() }})
                    </h3>
                    <ul class="space-y-1 text-sm text-slate-700">
                        @foreach ($patient->visits as $visit)
                            <li>{{ $visit->visit_date?->format('Y-m-d') ?? $visit->created_at?->format('Y-m-d') }} —
                                {{ $visit->case_type ?? ($visit->notes ?? '—') }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Invoices (latest 10) --}}
            @if ($patient->invoices->isNotEmpty())
                <div class="border-b border-slate-200 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-3">
                        {{ app()->getLocale() === 'ar' ? '💳 آخر الفواتير' : '💳 Recent Invoices' }}
                        ({{ $patient->invoices->count() }})
                    </h3>
                    <ul class="space-y-1 text-sm text-slate-700">
                        @foreach ($patient->invoices as $invoice)
                            <li>#{{ $invoice->invoice_number ?? $invoice->id }} — {{ $invoice->total_amount ?? '—' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Contact Reports --}}
            @if ($patient->contactReports->isNotEmpty())
                <div class="border-b border-slate-200 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-3">
                        {{ app()->getLocale() === 'ar' ? '📞 تقارير الاتصال' : '📞 Contact Reports' }}
                        ({{ $patient->contactReports->count() }})
                    </h3>
                    <ul class="space-y-1 text-sm text-slate-700">
                        @foreach ($patient->contactReports as $report)
                            <li>{{ $report->contact_date?->format('Y-m-d') ?? $report->created_at?->format('Y-m-d') }} —
                                {{ Str::limit($report->notes ?? '—', 60) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Written Commitments --}}
            @if ($patient->writtenCommitments->isNotEmpty())
                <div class="pb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-3">
                        {{ app()->getLocale() === 'ar' ? '📝 الالتزامات المكتوبة' : '📝 Written Commitments' }}
                        ({{ $patient->writtenCommitments->count() }})
                    </h3>
                    <ul class="space-y-1 text-sm text-slate-700">
                        @foreach ($patient->writtenCommitments as $commitment)
                            <li>{{ $commitment->commitment_date?->format('Y-m-d') ?? $commitment->created_at?->format('Y-m-d') }}
                                — {{ $commitment->amount ?? '—' }} — {{ Str::limit($commitment->notes ?? '—', 50) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Documents (media) --}}
            @php $documents = $patient->getMedia('documents'); @endphp
            @if ($documents->isNotEmpty())
                <div class="border-t border-slate-200 pt-4 mt-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-3">
                        {{ app()->getLocale() === 'ar' ? '📎 المستندات المرفقة' : '📎 Attached Documents' }}
                    </h3>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($documents as $media)
                            <li>
                                <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener"
                                    class="text-blue-600 hover:underline text-sm">
                                    {{ $media->file_name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
