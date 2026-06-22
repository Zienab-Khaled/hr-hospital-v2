@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إنشاء زيارة' : 'Create Visit')
@section('content')
    @php
        $inputClass = 'w-full rounded-lg border-2 border-slate-400 bg-white px-3 py-2 text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
        $visitForPrint = isset($patient) && $patient ? ($visit ?? $patient->visits()->whereDate('visit_date', today())->latest()->first()) : null;

        // Define common variables here to be available throughout the view
        $isTransferred = $visitForPrint && $visitForPrint->transferred_department_id;
        $showEligibilitySection = !$isTransferred && ($visitForPrint ?? null) && isset($departments);
        $patientIsInsurance = $patient && $patient->payment_type === 'insurance';
        $patientIsTreatmentEligibility = $patient && $patient->payment_type === 'treatment_eligibility';
    @endphp
    <div class="max-w-6xl mx-auto">
        <div class="rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">
                {{ app()->getLocale() === 'ar' ? '🩺 إنشاء زيارة' : '🩺 Create Visit' }}
                @if ($currentShift ?? null)
                    <span class="text-sm font-normal text-slate-600 ms-2">— {{ app()->getLocale() === 'ar' && $currentShift->name_ar ? $currentShift->name_ar : $currentShift->name }}</span>
                @endif
            </h2>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg border-2 border-red-400 bg-red-50 text-red-800">
                    <p class="font-bold mb-2">{{ app()->getLocale() === 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please fix the following errors:' }}</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!$patient)
                {{-- اختيار مريض — نفس شكل بلوك المريض في إنشاء فاتورة --}}
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg p-5 mb-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <h3 class="font-bold text-blue-900 text-lg">
                            {{ app()->getLocale() === 'ar' ? '👤 اختيار مريض للزيارة' : '👤 Select patient for visit' }}
                        </h3>
                        @can('patients.create')
                            <a href="{{ route('patients.create', ['redirect_to' => 'visits.create']) }}"
                                class="inline-flex items-center gap-1.5 shrink-0 bg-emerald-600 text-slate-50 px-3 py-1.5 rounded-md font-semibold text-xs hover:bg-emerald-700">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>{{ app()->getLocale() === 'ar' ? 'مريض جديد' : 'New patient' }}</span>
                            </a>
                        @endcan
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="flex-1 min-w-[200px] relative" id="visit_search_wrap">
                            <input type="text" id="visit_patient_search" autocomplete="off"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'رقم الهوية / الاسم / رقم الملف...' : 'Identity / name / file number...' }}"
                                class="{{ $inputClass }}">
                            <div id="visit_patient_results" class="hidden absolute left-0 right-0 z-20 mt-1 bg-white border-2 border-slate-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                        <button type="button" id="visit_search_btn" class="bg-blue-600 px-5 text-slate-50 py-3 rounded-lg font-bold text-base hover:bg-blue-700 shadow">
                            {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                        </button>
                    </div>
                    <div id="visit_selected_patient" class="hidden mt-4 p-4 rounded-lg border-2 border-slate-300 bg-white">
                        <p class="text-sm font-semibold text-slate-800" id="visit_selected_name"></p>
                        <p class="text-xs text-slate-600 mt-1" id="visit_selected_meta"></p>
                        <form action="{{ route('visits.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="patient_id" id="visit_selected_patient_id">

                            {{-- Charity Approval Document — Scan or Upload --}}
                            <div id="charity_approval_section" class="hidden mt-3 p-3 bg-amber-50 border border-amber-300 rounded-lg">
                                <label class="block text-sm font-semibold text-amber-900 mb-2">
                                    {{ app()->getLocale() === 'ar' ? '📄 اعتماد الجمعية الخيرية' : '📄 Charity Approval Document' }}
                                </label>

                                {{-- Real file input (receives both camera & file picker) --}}
                                <input type="file" name="charity_approval_document" id="charity_approval_input"
                                       accept="image/*,.pdf" class="hidden" onchange="charityApprovalPreview(this)">

                                {{-- Camera-only input (opens rear camera on mobile) --}}
                                <input type="file" id="charity_approval_camera"
                                       accept="image/*" capture="environment" class="hidden"
                                       onchange="charityApprovalFromCamera(this)">

                                {{-- Action buttons --}}
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <button type="button" onclick="document.getElementById('charity_approval_camera').click()"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-amber-600  text-sm font-semibold hover:bg-amber-700 shadow-sm transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ app()->getLocale() === 'ar' ? 'مسح / تصوير' : 'Scan / Camera' }}
                                    </button>
                                    <button type="button" onclick="document.getElementById('charity_approval_input').click()"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-amber-400 text-amber-800 text-sm font-semibold hover:bg-amber-100 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        {{ app()->getLocale() === 'ar' ? 'رفع ملف' : 'Upload File' }}
                                    </button>
                                    <button type="button" id="charity_approval_remove" onclick="charityApprovalClear()"
                                            class="hidden inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-100 border border-red-300 text-red-700 text-sm font-semibold hover:bg-red-200 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        {{ app()->getLocale() === 'ar' ? 'حذف' : 'Remove' }}
                                    </button>
                                </div>

                                {{-- Preview --}}
                                <div id="charity_approval_preview" class="hidden rounded-lg border border-amber-300 overflow-hidden bg-white">
                                    <img id="charity_approval_img" src="" alt="preview" class="w-full max-h-52 object-contain p-1">
                                    <p id="charity_approval_filename" class="text-xs text-center text-amber-800 py-1 border-t border-amber-200 truncate px-2"></p>
                                </div>

                                <p class="text-xs text-amber-700 mt-2">
                                    {{ app()->getLocale() === 'ar'
                                        ? 'صوّر خطاب الاعتماد أو ارفعه (صورة أو PDF — حد أقصى 5 ميجا)'
                                        : 'Scan or upload the approval letter (image or PDF — max 5MB)' }}
                                </p>
                            </div>
                            <script>
                            function charityApprovalPreview(input) {
                                var file = input.files[0]; if (!file) return;
                                document.getElementById('charity_approval_filename').textContent = file.name;
                                if (file.type.startsWith('image/')) {
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        var img = document.getElementById('charity_approval_img');
                                        img.src = e.target.result; img.classList.remove('hidden');
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    document.getElementById('charity_approval_img').classList.add('hidden');
                                }
                                document.getElementById('charity_approval_preview').classList.remove('hidden');
                                document.getElementById('charity_approval_remove').classList.remove('hidden');
                            }
                            function charityApprovalFromCamera(cam) {
                                var file = cam.files[0]; if (!file) return;
                                var dt = new DataTransfer(); dt.items.add(file);
                                var real = document.getElementById('charity_approval_input');
                                real.files = dt.files;
                                charityApprovalPreview(real);
                            }
                            function charityApprovalClear() {
                                document.getElementById('charity_approval_input').value = '';
                                document.getElementById('charity_approval_camera').value = '';
                                document.getElementById('charity_approval_preview').classList.add('hidden');
                                document.getElementById('charity_approval_remove').classList.add('hidden');
                                document.getElementById('charity_approval_img').src = '';
                            }
                            </script>

                            @include('visits.partials.admission-entry-radios', ['defaultAdmission' => $defaultAdmissionSource ?? \App\Models\Visit::ADMISSION_OUTPATIENT_CLINICS])

                            <button type="submit" id="visit_go_btn" class="mt-3 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 text-slate-50 font-semibold text-sm hover:bg-blue-700 shadow">
                                {{ app()->getLocale() === 'ar' ? 'متابعة ← تسجيل دخول القسم' : 'Continue → Register to department' }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- بطاقة المريض — نفس شكل إنشاء فاتورة --}}
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg p-5 mb-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <h3 class="font-bold text-blue-900 text-lg">{{ app()->getLocale() === 'ar' ? '👤 المريض' : '👤 Patient' }}</h3>
                        <a href="{{ route('patients.show', $patient) }}" target="_blank"
                            class="text-sm font-semibold text-blue-700 hover:text-blue-900 underline underline-offset-2">
                            {{ app()->getLocale() === 'ar' ? 'ملف المريض ↗' : 'Patient profile ↗' }}
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</label>
                            <p class="text-slate-800 font-medium">{{ $patient->name }}</p>
                        </div>
                        <div>
                            <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي):' : 'Name (Arabic):' }}</label>
                            <p class="text-slate-800 font-medium" dir="rtl">{{ $patient->fullArabicName() }}</p>
                        </div>
                        <div>
                            <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</label>
                            <p class="text-slate-800 font-medium">{{ $patient->file_number }}</p>
                        </div>
                        <div>
                            <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الهوية:' : 'Identity:' }}</label>
                            <p class="text-slate-800 font-medium">{{ $patient->identity_value }}</p>
                        </div>
                        <div>
                            <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الميلاد:' : 'Date of birth:' }}</label>
                            <p class="text-slate-800 font-medium" dir="ltr">{{ $patient->date_of_birth?->format('Y-m-d') ?? '—' }}</p>
                        </div>
                        @if ($patient->ageInYears() !== null)
                            <div>
                                <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'العمر:' : 'Age:' }}</label>
                                <p class="text-slate-800 font-medium">{{ $patient->ageInYears() }}</p>
                            </div>
                        @endif
                        @if ($patient->department)
                            <div>
                                <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'القسم الحالي:' : 'Current department:' }}</label>
                                <p class="text-slate-800 font-medium">{{ app()->getLocale() === 'ar' && $patient->department->name_ar ? $patient->department->name_ar : $patient->department->name }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if (isset($activeVisits) && $activeVisits->isNotEmpty() && !$visit)
                    <div class="border-2 border-blue-200 bg-blue-50 rounded-lg p-5 mb-6">
                        <h3 class="font-bold text-blue-900 text-lg mb-3">
                            {{ app()->getLocale() === 'ar' ? 'يوجد زيارات مفتوحة لهذا المريض اليوم:' : 'Patient has active visits today:' }}
                        </h3>
                        <div class="space-y-3 mb-4">
                            @foreach ($activeVisits as $v)
                                <div class="bg-white border border-blue-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
                                    <div class="text-sm">
                                        <span class="font-semibold text-slate-800">
                                            {{ (app()->getLocale() === 'ar' ? ($v->department?->name_ar ?? $v->department?->name) : ($v->department?->name ?? $v->department?->name_ar)) ?? '—' }}
                                        </span>
                                        <span class="text-slate-500 mx-1">—</span>
                                        <span class="text-slate-600">
                                            {{ (app()->getLocale() === 'ar' ? ($v->shift?->name_ar ?? $v->shift?->name) : ($v->shift?->name ?? $v->shift?->name_ar)) ?? '—' }}
                                        </span>
                                        @if ($v->transferred_department_id)
                                            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-1.5 py-0.5 rounded ms-2">
                                                {{ app()->getLocale() === 'ar' ? 'محول' : 'Transferred' }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('visits.create', ['patient_id' => $patient->id, 'visit_id' => $v->id, 'registered' => 1]) }}"
                                       class="bg-blue-600  px-3 py-1.5 rounded text-sm font-semibold hover:bg-blue-700">
                                        {{ app()->getLocale() === 'ar' ? 'فتح الزيارة' : 'Open Visit' }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!$visit && $myDepartment)
                    <form action="{{ route('visits.store') }}" method="POST" class="mb-6">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                        @include('visits.partials.admission-entry-radios', ['defaultAdmission' => $defaultAdmissionSource ?? \App\Models\Visit::ADMISSION_OUTPATIENT_CLINICS])
                        <button type="submit" class="bg-blue-600 px-5 text-slate-50 py-3 rounded-lg font-bold text-base hover:bg-blue-700 shadow">
                            @if (isset($activeVisits) && $activeVisits->isNotEmpty())
                                {{ app()->getLocale() === 'ar' ? 'إنشاء زيارة جديدة (إضافية)' : 'Create New Visit (Additional)' }}
                            @else
                                {{ app()->getLocale() === 'ar' ? 'تسجيل دخول المريض إلى القسم:' : 'Register patient entry to department:' }}
                                {{ app()->getLocale() === 'ar' && $myDepartment->name_ar ? $myDepartment->name_ar : $myDepartment->name }}
                            @endif
                        </button>
                    </form>
                @endif

                @if ($visit || $registered)
                    @php
                        $isTransferred = $visitForPrint && $visitForPrint->transferred_department_id;
                        $transferredDept = $isTransferred ? \App\Models\Department::find($visitForPrint->transferred_department_id) : null;
                    @endphp

                    @if ($isTransferred)
                        <div class="border-2 border-amber-300 bg-amber-50 rounded-lg p-4 mb-6 flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-amber-800 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'تم تحويل الزيارة' : 'Visit Transferred' }}
                                </h3>
                                <p class="text-amber-700 text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'تم تحويل هذه الزيارة إلى قسم:' : 'This visit has been transferred to:' }}
                                    <span class="font-bold">{{ app()->getLocale() === 'ar' && $transferredDept->name_ar ? $transferredDept->name_ar : $transferredDept->name }}</span>
                                </p>
                            </div>
                            <a href="{{ route('visits.index') }}" class="inline-block bg-white border border-amber-300 text-amber-800 px-3 py-1.5 rounded text-sm font-semibold hover:bg-amber-100">
                                {{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}
                            </a>
                        </div>
                    @endif

                    {{-- مسار الدخول + حفظ --}}
                        <div class="border-2 border-slate-300 rounded-lg p-5 mb-6 bg-white shadow-sm">
                            <form action="{{ route('visits.update', $visitForPrint ?? $visit) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="redirect_to_create" value="1">
                                @php
                                    $vAdmission = $visitForPrint ?? $visit;
                                    $admDefault = $vAdmission->admission_entry_source
                                        ?? \App\Models\Visit::inferAdmissionEntryFromDepartment($vAdmission->department);
                                @endphp
                                <input type="hidden" name="case_type" value="{{ old('case_type', $vAdmission->case_type ?? '') }}">
                                @include('visits.partials.admission-entry-radios', ['defaultAdmission' => $admDefault])
                                <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 shadow text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'حفظ التعديلات' : 'Save Changes' }}
                                </button>
                            </form>
                        </div>

                @endif

                    @if ($visitForPrint && isset($departments) && !($visitForPrint->transferred_department_id))
                    {{-- عمودان: طباعة الأحقية | تقديم خدمات --}}
                    <div id="visit_main_actions" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="border-2 border-amber-300 rounded-lg p-5 bg-gradient-to-br from-amber-50 to-white shadow-sm">
                            <h3 class="text-lg font-bold text-amber-900 mb-3">
                                {{ app()->getLocale() === 'ar' ? 'طباعة الأحقية' : 'Print Eligibility' }}
                            </h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-bold text-slate-800 mb-1">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</label>
                                    <select id="eligibility_department_id" class="{{ $inputClass }}">
                                        <option value="">{{ app()->getLocale() === 'ar' ? '— اختر القسم —' : '— Select department —' }}</option>
                                        <option value="none" @selected(old('department_id', $visitForPrint->eligibility_without_department ? 'none' : '') === 'none')>
                                            {{ app()->getLocale() === 'ar' ? 'بدون قسم' : 'No department' }}
                                        </option>
                                        @foreach ($eligibilityDepartments as $d)
                                            <option value="{{ $d->id }}" @selected((string) old('department_id', $visitForPrint->eligibility_print_department_id ?? '') === (string) $d->id)>
                                                {{ app()->getLocale() === 'ar' && $d->name_ar ? $d->name_ar : $d->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label id="eligibility_notes_label" class="block text-sm font-bold text-slate-800 mb-1">
                                        {{ app()->getLocale() === 'ar' ? 'ملاحظة الأحقية' : 'Eligibility note' }}
                                    </label>
                                    <textarea id="eligibility_notes" rows="2" class="{{ $inputClass }} text-sm">{{ old('eligibility_notes', $visitForPrint->eligibility_notes ?? '') }}</textarea>
                                    <p id="eligibility_notes_hint" class="text-xs text-slate-500 mt-1 hidden">
                                        {{ app()->getLocale() === 'ar' ? 'إلزامي عند اختيار «بدون قسم»' : 'Required when «No department» is selected' }}
                                    </p>
                                </div>
                                <form id="eligibility_print_form" method="POST" action="{{ route('visits.treatment-eligibility-print.submit', $visitForPrint) }}" target="_blank" class="pt-1">
                                    @csrf
                                    <button type="button" id="eligibility_print_btn" class="w-full sm:w-auto bg-amber-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-amber-700 shadow text-sm">
                                        {{ app()->getLocale() === 'ar' ? 'طباعة الأحقية' : 'Print eligibility' }}
                                    </button>
                                </form>
                            </div>

                            @if($visitForPrint->printed_eligibility_at)
                                <p class="text-[11px] text-slate-500 mt-3">
                                    {{ app()->getLocale() === 'ar' ? 'آخر أحقية: ' : 'Last eligibility: ' }}{{ $visitForPrint->printed_eligibility_at->format('Y-m-d H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="border-2 border-blue-300 rounded-lg p-5 bg-gradient-to-br from-blue-50 to-white shadow-sm flex flex-col">
                            <h3 class="text-lg font-bold text-blue-900 mb-2">
                                {{ app()->getLocale() === 'ar' ? 'تقديم خدمات وإنشاء فاتورة' : 'Add services & create invoice' }}
                            </h3>
                            <p class="text-sm text-slate-600 flex-1">
                                {{ app()->getLocale() === 'ar' ? 'إضافة الخدمات ثم: فاتورة تفصيلية (إيراد) أو عرض سعر استعلامي (بدون إيراد).' : 'Add services then: detailed invoice (revenue) or price inquiry (no revenue).' }}
                            </p>
                            @if (\App\Support\RoleNav::canCreateInvoiceWithServices(auth()->user()))
                                <a href="{{ route('invoices.create', ['patient_id' => $patient->id, 'visit_id' => $visit?->id]) }}"
                                   class="mt-4 inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-md transition-colors">
                                    {{ app()->getLocale() === 'ar' ? 'متابعة ← تقديم الخدمات' : 'Continue → Add services' }}
                                </a>
                            @else
                                <p class="mt-4 text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا تملك صلاحية إنشاء فاتورة.' : 'You cannot create invoices.' }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- مساحة العمل: خدمات + طباعة --}}
                    <div id="eligibility_workspace" class="border-2 border-slate-300 rounded-lg p-5 mb-6 bg-white shadow-sm">
                        @if ($visitForPrint && isset($entryFeeDepartments) && $entryFeeDepartments->isNotEmpty())
                            <div id="entry_fee_block" class="mb-5 pb-5 border-b border-slate-200">
                                <h4 class="text-sm font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'كشفية دخول القسم' : 'Department entry fee' }}</h4>
                                <form action="{{ route('visits.entry-fee-invoice', $visitForPrint) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'إنشاء فاتورة دخول (كشفية) وطباعة الأحقية؟' : 'Create entry fee invoice and print eligibility?' }}');">
                                    @csrf
                                    <div class="flex flex-wrap items-end gap-3">
                                        <div class="min-w-[180px] flex-1">
                                            <select name="department_id" required class="{{ $inputClass }} text-sm">
                                                <option value="">{{ app()->getLocale() === 'ar' ? '— قسم الكشفية —' : '— Entry department —' }}</option>
                                                @foreach ($entryFeeDepartments as $d)
                                                    <option value="{{ $d->id }}">{{ (app()->getLocale() === 'ar' && $d->name_ar ? $d->name_ar : $d->name) }} — @currency($d->entry_fee ?? 0)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if ($patientIsInsurance)
                                            <select name="insurance_coverage_type" class="{{ $inputClass }} w-32 text-sm">
                                                <option value="">{{ app()->getLocale() === 'ar' ? 'تغطية' : 'Coverage' }}</option>
                                                <option value="percentage">{{ app()->getLocale() === 'ar' ? 'نسبة %' : '%' }}</option>
                                                <option value="fixed">{{ app()->getLocale() === 'ar' ? 'ثابت' : 'Fixed' }}</option>
                                            </select>
                                            <input type="number" name="insurance_coverage_value" min="0" step="0.01" placeholder="0" class="{{ $inputClass }} w-24 text-sm">
                                        @endif
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold hover:bg-emerald-700 text-sm shrink-0">
                                            {{ app()->getLocale() === 'ar' ? 'كشفية + طباعة' : 'Entry fee + print' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-800 mb-2">{{ app()->getLocale() === 'ar' ? 'بحث عن خدمة' : 'Search service' }}</label>
                            <div class="flex flex-wrap gap-2">
                                <input type="text" id="eligibility_service_search" placeholder="{{ app()->getLocale() === 'ar' ? 'اسم الخدمة أو الكود...' : 'Service name or code...' }}"
                                    class="flex-1 min-w-[200px] {{ $inputClass }}">
                                <button type="button" id="eligibility_service_btn" class="bg-blue-600 px-4 text-white py-2 rounded-lg font-bold text-sm hover:bg-blue-700 shadow">
                                    {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                                </button>
                                @if ($visitForPrint->last_eligibility_services)
                                    <button type="button" id="btn_reload_eligibility" class="bg-slate-500 px-3 text-white py-2 rounded-lg font-bold text-sm hover:bg-slate-600">
                                        {{ app()->getLocale() === 'ar' ? 'استعادة آخر خدمات' : 'Reload last' }}
                                    </button>
                                @endif
                            </div>
                            <div id="eligibility_service_results" class="mt-2 hidden border border-slate-300 rounded-lg bg-white max-h-48 overflow-y-auto"></div>
                        </div>

                        <div class="overflow-x-auto border border-slate-300 rounded-lg bg-white mb-4">
                            <table class="w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100 border-b border-slate-300">
                                    <th class="border border-slate-300 px-2 py-2 text-center text-xs font-bold text-slate-800 w-20">{{ app()->getLocale() === 'ar' ? 'الرمز' : 'Code' }}</th>
                                    <th class="border border-slate-300 px-2 py-2 text-center text-xs font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'البيان' : 'Description' }}</th>
                                    <th class="border border-slate-300 px-2 py-2 text-center text-xs font-bold text-slate-800 w-16">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                    <th class="border border-slate-300 px-2 py-2 text-center text-xs font-bold text-slate-800 w-24">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                                    <th class="border border-slate-300 px-2 py-2 text-center text-xs font-bold text-slate-800 w-24">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                    @if ($patientIsInsurance)
                                        <th class="border border-slate-300 px-2 py-2 text-center text-xs font-bold text-slate-800 w-28">{{ app()->getLocale() === 'ar' ? 'التغطية' : 'Coverage' }}</th>
                                        <th class="border border-slate-300 px-2 py-2 text-center text-xs font-bold text-slate-800 w-28">{{ app()->getLocale() === 'ar' ? 'قيمة التغطية' : 'Value' }}</th>
                                    @endif
                                    <th class="border border-slate-300 px-2 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="eligibility_services_tbody"></tbody>
                            <tfoot>
                                <tr class="bg-slate-50 font-bold text-slate-800">
                                    <td colspan="{{ $patientIsInsurance ? 5 : 4 }}" class="border border-slate-300 px-2 py-2 text-end text-sm">{{ app()->getLocale() === 'ar' ? 'المجموع:' : 'Total:' }}</td>
                                    <td class="border border-slate-300 px-2 py-2 text-center" id="eligibility_grand_total">0.00</td>
                                    @if ($patientIsInsurance)
                                        <td colspan="3" class="border border-slate-300"></td>
                                    @else
                                        <td class="border border-slate-300"></td>
                                    @endif
                                </tr>
                                @if ($patientIsInsurance)
                                    <tr class="bg-emerald-50 font-bold text-emerald-900 text-sm">
                                        <td colspan="5" class="border border-slate-300 px-2 py-2 text-end">{{ app()->getLocale() === 'ar' ? 'تحمّل التأمين:' : 'Insurance:' }}</td>
                                        <td class="border border-slate-300 px-2 py-2 text-center" id="eligibility_insurance_total">0.00</td>
                                        <td colspan="3" class="border border-slate-300"></td>
                                    </tr>
                                    <tr class="bg-amber-50 font-bold text-amber-900 text-sm">
                                        <td colspan="5" class="border border-slate-300 px-2 py-2 text-end">{{ app()->getLocale() === 'ar' ? 'تحمّل المريض:' : 'Patient:' }}</td>
                                        <td class="border border-slate-300 px-2 py-2 text-center" id="eligibility_patient_share">0.00</td>
                                        <td colspan="3" class="border border-slate-300"></td>
                                    </tr>
                                @endif
                            </tfoot>
                            </table>
                        </div>

                        <div class="flex flex-wrap gap-3 items-center pt-2 border-t border-slate-200">
                            <form id="revenue_invoice_form" method="POST" action="{{ route('visits.services-revenue-invoice', $visitForPrint) }}" target="_blank">
                                @csrf
                                <button type="button" id="revenue_invoice_btn" class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-emerald-700 shadow text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'فاتورة تفصيلية' : 'Detailed invoice' }}
                                </button>
                            </form>
                            <form id="inquiry_invoice_form" method="POST" action="{{ route('visits.price-inquiry-print.submit', $visitForPrint) }}" target="_blank" class="inline">
                                @csrf
                                <input type="hidden" name="print_title" value="price_quotation">
                                <button type="button" id="inquiry_invoice_btn" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-purple-700 shadow text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'عرض سعر استعلامي' : 'Price inquiry' }}
                                </button>
                            </form>
                            <p class="text-xs text-slate-500 w-full sm:w-auto">
                                {{ app()->getLocale() === 'ar' ? 'تفصيلية: تُنشئ وتطبع وتُسجَّل في الإيرادات — استعلامي: للعرض فقط بدون إيراد' : 'Detailed: create, print & record — Inquiry: display only' }}
                            </p>
                        </div>
                    </div>
                    @endif

                    @php
                        $visitInvoices = ($visitForPrint ?? $visit)?->invoices ?? collect();
                    @endphp
                    @if (($visitForPrint || $visit) && $visitInvoices->isNotEmpty())
                        <div class="border border-slate-300 rounded-lg p-4 mb-6 bg-slate-50">
                            <h3 class="text-base font-bold text-slate-800 mb-3">
                                {{ app()->getLocale() === 'ar' ? 'فواتير هذه الزيارة' : 'Visit invoices' }}
                                <span class="text-sm font-normal text-slate-500">({{ $visitInvoices->count() }})</span>
                            </h3>
                            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                                <table class="w-full text-sm text-right">
                                    <thead>
                                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-800">
                                            <th class="p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice No' }}</th>
                                            <th class="p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                                            <th class="p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</th>
                                            <th class="p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                            <th class="p-2 font-bold">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                                            <th class="p-2 font-bold text-center">{{ app()->getLocale() === 'ar' ? 'فتح' : 'Open' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($visitInvoices as $inv)
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="p-2 font-semibold text-slate-900">{{ $inv->invoice_number }}</td>
                                                <td class="p-2 text-slate-600">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                                                <td class="p-2">
                                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold">{{ $inv->invoice_type_label }}</span>
                                                </td>
                                                <td class="p-2 font-bold text-slate-900">@currencyInvoice($inv->total_amount)</td>
                                                <td class="p-2">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold
                                                        {{ $inv->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                        {{ $inv->status_label }}
                                                    </span>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <a href="{{ route('invoices.show', $inv) }}" target="_blank"
                                                       class="text-blue-600 hover:text-blue-800 font-semibold text-xs">
                                                        {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif


                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('visits.create') }}" class="bg-slate-200 text-slate-700 px-3 py-2 rounded-lg text-sm font-bold hover:bg-slate-300">
                        {{ app()->getLocale() === 'ar' ? '← زيارة لمريض آخر' : '← Another patient' }}
                    </a>
                    <a href="{{ route('visits.index') }}" class="bg-slate-200 text-slate-700 px-3 py-2 rounded-lg text-sm font-bold hover:bg-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'قائمة مكتب الدخول' : 'Admission list' }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if (!$patient)
    <script>
        (function() {
            var searchUrl = '{{ route('visits.create') }}';
            var apiUrl = '{{ route('invoices.patients-search') }}';
            var input = document.getElementById('visit_patient_search');
            var results = document.getElementById('visit_patient_results');
            var selectedBox = document.getElementById('visit_selected_patient');
            var selectedName = document.getElementById('visit_selected_name');
            var selectedMeta = document.getElementById('visit_selected_meta');
            var goBtn = document.getElementById('visit_go_btn');
            var searchBtn = document.getElementById('visit_search_btn');
            if (!input || !results) return;


            function selectPatient(id, name, meta, paymentType) {
                results.classList.add('hidden');
                if (selectedName) selectedName.textContent = name;
                if (selectedMeta) selectedMeta.textContent = meta;
                if (selectedBox) selectedBox.classList.remove('hidden');
                var hiddenInput = document.getElementById('visit_selected_patient_id');
                if (hiddenInput) hiddenInput.value = id;

                // Show/hide charity approval section based on payment type
                var charitySection = document.getElementById('charity_approval_section');
                var charityInput = document.getElementById('charity_approval_input');
                if (charitySection && charityInput) {
                    if (paymentType === 'charity') {
                        charitySection.classList.remove('hidden');
                    } else {
                        charitySection.classList.add('hidden');
                    }
                }
            }
            function showResults(items) {
                results.innerHTML = '';
                results.classList.remove('hidden');
                if (!items.length) { results.innerHTML = '<div class="p-3 text-slate-600 text-sm">' + (document.documentElement.lang === 'ar' ? 'لا توجد نتائج' : 'No results') + '</div>'; return; }
                items.forEach(function(p) {
                    var div = document.createElement('div');
                    div.className = 'block px-3 py-2.5 hover:bg-blue-50 cursor-pointer text-slate-800 font-medium text-sm border-b border-slate-200 last:border-0';
                    div.style.color = '#1e293b';
                    div.textContent = (p.name_ar || p.name) + ' — ' + (p.file_number || '') + ' — ' + (p.identity_value || '');
                    div.dataset.id = p.id;
                    div.dataset.name = (p.name_ar || p.name);
                    div.dataset.meta = (p.file_number || '') + ' — ' + (p.identity_value || '');
                    div.dataset.paymentType = p.payment_type || '';
                    div.addEventListener('mousedown', function(e) { e.preventDefault(); });
                    div.addEventListener('click', function() { selectPatient(this.dataset.id, this.dataset.name, this.dataset.meta, this.dataset.paymentType); });
                    results.appendChild(div);
                });
            }
            function doSearch() {
                var q = (input.value || '').trim();
                if (q.length < 1) { results.classList.add('hidden'); if (selectedBox) selectedBox.classList.add('hidden'); return; }
                fetch(apiUrl + '?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(showResults)
                    .catch(function() { results.classList.add('hidden'); });
            }
            if (searchBtn) searchBtn.addEventListener('click', doSearch);
            var timer;
            input.addEventListener('input', function() {
                if (selectedBox) selectedBox.classList.add('hidden');
                clearTimeout(timer);
                var q = (input.value || '').trim();
                if (q.length < 2) { results.classList.add('hidden'); return; }
                timer = setTimeout(doSearch, 200);
            });
            input.addEventListener('blur', function() { setTimeout(function() { results.classList.add('hidden'); }, 280); });

        })();
    </script>
    @endif

    {{-- أحقية العلاج: بحث خدمات حسب القسم، جدول، مجموع، طباعة --}}
    @if ($patient && $showEligibilitySection)
    <script>
        window.visitPatientIsInsurance = @json($patientIsInsurance);
        window.visitPatientIsTreatmentEligibility = @json($patientIsTreatmentEligibility);
        window.lastEligibilityServices = @json($visitForPrint->last_eligibility_services ?? []);
        window.lastPriceInquiryServices = @json($visitForPrint->last_price_inquiry_services ?? []);
        (function() {
            var deptSelect = document.getElementById('eligibility_department_id');
            var searchInput = document.getElementById('eligibility_service_search');
            var searchBtn = document.getElementById('eligibility_service_btn');
            var resultsDiv = document.getElementById('eligibility_service_results');
            var tbody = document.getElementById('eligibility_services_tbody');
            var grandTotalEl = document.getElementById('eligibility_grand_total');
            var insuranceTotalEl = document.getElementById('eligibility_insurance_total');
            var patientShareEl = document.getElementById('eligibility_patient_share');
            var printForm = document.getElementById('eligibility_print_form');
            var printBtn = document.getElementById('eligibility_print_btn');
            var revenueForm = document.getElementById('revenue_invoice_form');
            var revenueBtn = document.getElementById('revenue_invoice_btn');
            var inquiryForm = document.getElementById('inquiry_invoice_form');
            var inquiryBtn = document.getElementById('inquiry_invoice_btn');
            var searchUrl = '{{ route('visits.eligibility-services-search') }}';
            var rows = [];

            if (!tbody) return;

            function clearResults() {
                if (resultsDiv) { resultsDiv.innerHTML = ''; resultsDiv.classList.add('hidden'); }
            }
            function addRow(service) {
                // Check if service already exists in rows
                var existingIndex = rows.findIndex(function(r) { return r.id == service.id; });

                if (existingIndex !== -1) {
                    // Increment quantity if found
                    rows[existingIndex].qty += 1;
                    rows[existingIndex].total = rows[existingIndex].qty * rows[existingIndex].unit_price;
                } else {
                    var qty = 1;
                    var unitPrice = parseFloat(service.default_price) || 0;
                    var total = qty * unitPrice;
                    var row = {
                        id: service.id,
                        code: service.code || '',
                        name: service.name || '',
                        name_ar: service.name_ar || '',
                        qty: qty,
                        unit_price: unitPrice,
                        total: total,
                        insurance_coverage_type: '',
                        insurance_coverage_value: 0
                    };
                    rows.push(row);
                }
                renderRows();
            }
            function removeRow(index) {
                rows.splice(index, 1);
                renderRows();
            }
            function updateRow(index, field, value) {
                if (!rows[index]) return;
                if (field === 'qty') { rows[index].qty = parseFloat(value) || 0; }
                if (field === 'unit_price') { rows[index].unit_price = parseFloat(value) || 0; }
                if (field === 'insurance_coverage_type') { rows[index].insurance_coverage_type = value; }
                if (field === 'insurance_coverage_value') { rows[index].insurance_coverage_value = parseFloat(value) || 0; }

                rows[index].total = rows[index].qty * rows[index].unit_price;

                // Update specific row total in DOM without re-rendering everything
                var tr = tbody.children[index];
                if (tr) {
                    var totalCell = tr.querySelector('.row-total');
                    if (totalCell) totalCell.textContent = rows[index].total.toFixed(2);
                }
                recalculateTotals();
            }

            function recalculateTotals() {
                var grand = 0;
                var insuranceTotal = 0;
                var isIns = window.visitPatientIsInsurance;

                rows.forEach(function(r) {
                    grand += r.total;
                    if (isIns && r.insurance_coverage_type && r.total > 0) {
                        var val = r.insurance_coverage_value;
                        if (r.insurance_coverage_type === 'percentage') {
                            insuranceTotal += r.total * Math.min(100, Math.max(0, val)) / 100;
                        } else if (r.insurance_coverage_type === 'fixed') {
                            insuranceTotal += Math.min(val, r.total);
                        }
                    }
                });

                if (grandTotalEl) grandTotalEl.textContent = grand.toFixed(2);

                if (window.visitPatientIsTreatmentEligibility && patientShareEl) {
                    patientShareEl.textContent = '0.00';
                } else if (isIns && insuranceTotalEl && patientShareEl) {
                    var patientShare = Math.max(0, grand - insuranceTotal);
                    insuranceTotalEl.textContent = insuranceTotal.toFixed(2);
                    patientShareEl.textContent = patientShare.toFixed(2);
                }
            }

            function renderRows() {
                tbody.innerHTML = '';
                var isIns = window.visitPatientIsInsurance;

                rows.forEach(function(r, i) {
                    var tr = document.createElement('tr');
                    tr.className = 'border-b border-slate-300 hover:bg-slate-50';
                    var nameDisplay = (document.documentElement.lang === 'ar' && r.name_ar) ? r.name_ar : r.name;

                    var insuranceCols = '';
                    if (isIns) {
                        var pctLabel = document.documentElement.lang === 'ar' ? 'نسبة %' : 'Percentage %';
                        var fixedLabel = document.documentElement.lang === 'ar' ? 'قيمة ثابتة' : 'Fixed amount';
                        var selPct = r.insurance_coverage_type === 'percentage' ? 'selected' : '';
                        var selFixed = r.insurance_coverage_type === 'fixed' ? 'selected' : '';

                        insuranceCols =
                            '<td class="border border-slate-400 px-2 py-1">' +
                                '<select class="w-full rounded border border-slate-400 px-1 py-1 text-xs focus:ring-1 focus:ring-blue-500" data-row="' + i + '" data-field="insurance_coverage_type">' +
                                    '<option value="">—</option>' +
                                    '<option value="percentage" ' + selPct + '>' + pctLabel + '</option>' +
                                    '<option value="fixed" ' + selFixed + '>' + fixedLabel + '</option>' +
                                '</select>' +
                            '</td>' +
                            '<td class="border border-slate-400 px-2 py-1">' +
                                '<input type="number" min="0" step="0.01" class="w-full text-center rounded border border-slate-400 px-1 py-1 text-slate-800 text-sm focus:ring-1 focus:ring-blue-500" value="' + (r.insurance_coverage_value || '') + '" placeholder="0" data-row="' + i + '" data-field="insurance_coverage_value">' +
                            '</td>';
                    }

                    tr.innerHTML =
                        '<td class="border border-slate-400 px-2 py-1 text-center text-sm font-medium text-slate-800">' + (r.code || '') + '</td>' +
                        '<td class="border border-slate-400 px-2 py-1 text-sm font-medium text-slate-900">' + (nameDisplay || '') + '</td>' +
                        '<td class="border border-slate-400 px-2 py-1"><input type="number" min="1" step="1" class="w-full text-center rounded border border-slate-400 px-1 py-1 text-slate-800 text-sm focus:ring-2 focus:ring-blue-500" value="' + r.qty + '" data-row="' + i + '" data-field="qty"></td>' +
                        '<td class="border border-slate-400 px-2 py-1"><input type="number" min="0" step="0.01" class="w-full text-center rounded border border-slate-400 px-1 py-1 text-slate-800 text-sm focus:ring-2 focus:ring-blue-500" value="' + r.unit_price + '" data-row="' + i + '" data-field="unit_price"></td>' +
                        '<td class="border border-slate-400 px-2 py-1 text-center text-sm font-bold text-slate-800 bg-slate-100 row-total">' + (r.total.toFixed(2)) + '</td>' +
                        insuranceCols +
                        '<td class="border border-slate-400 px-2 py-1 text-center"><button type="button" class="text-red-600 hover:underline text-sm font-bold" data-remove="' + i + '">' + (document.documentElement.lang === 'ar' ? 'حذف' : 'Remove') + '</button></td>';
                    tbody.appendChild(tr);
                });

                recalculateTotals();

                tbody.querySelectorAll('input[data-field="qty"]').forEach(function(inp) {
                    inp.addEventListener('input', function() { updateRow(parseInt(this.dataset.row, 10), 'qty', this.value); });
                });
                tbody.querySelectorAll('input[data-field="unit_price"]').forEach(function(inp) {
                    inp.addEventListener('input', function() { updateRow(parseInt(this.dataset.row, 10), 'unit_price', this.value); });
                });
                if (isIns) {
                    tbody.querySelectorAll('select[data-field="insurance_coverage_type"]').forEach(function(sel) {
                        sel.addEventListener('change', function() { updateRow(parseInt(this.dataset.row, 10), 'insurance_coverage_type', this.value); });
                    });
                    tbody.querySelectorAll('input[data-field="insurance_coverage_value"]').forEach(function(inp) {
                        inp.addEventListener('input', function() { updateRow(parseInt(this.dataset.row, 10), 'insurance_coverage_value', this.value); });
                    });
                }
                tbody.querySelectorAll('button[data-remove]').forEach(function(btn) {
                    btn.addEventListener('click', function() { removeRow(parseInt(this.dataset.remove, 10)); });
                });
            }
            function doEligibilitySearch() {
                var deptId = deptSelect ? deptSelect.value : '';
                var q = (searchInput && searchInput.value) ? searchInput.value.trim() : '';

                if (q.length < 1) {
                    clearResults();
                    if (resultsDiv) {
                        resultsDiv.innerHTML = '<div class="p-3 text-slate-600 text-sm">' + (document.documentElement.lang === 'ar' ? 'اكتب اسم الخدمة أو الكود للبحث' : 'Type service name or code to search') + '</div>';
                        resultsDiv.classList.remove('hidden');
                    }
                    return;
                }

                var url = searchUrl + '?q=' + encodeURIComponent(q);
                if (deptId && deptId !== 'none') {
                    url += '&department_id=' + encodeURIComponent(deptId);
                }

                fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) {
                        if (!r.ok) throw new Error('search failed');
                        return r.json();
                    })
                    .then(function(data) {
                    if (!resultsDiv) return;
                    var list = Array.isArray(data) ? data : (data.services || data.data || []);
                    resultsDiv.innerHTML = '';
                    resultsDiv.classList.remove('hidden');
                    if (!list.length) { resultsDiv.innerHTML = '<div class="p-3 text-slate-600 text-sm">' + (document.documentElement.lang === 'ar' ? 'لا توجد نتائج' : 'No results') + '</div>'; return; }
                    list.forEach(function(s) {
                        var div = document.createElement('div');
                        div.className = 'px-3 py-2 hover:bg-blue-50 cursor-pointer text-slate-800 border-b border-slate-200 last:border-0 text-sm font-medium';
                        // Fix: use default_price here too for display
                        var priceDisplay = parseFloat(s.default_price) || 0;
                        div.textContent = (s.name_ar || s.name) + ' — ' + (s.code || '') + ' — ' + priceDisplay;

                        div.dataset.id = s.id;
                        div.dataset.code = s.code || '';
                        div.dataset.name = s.name || '';
                        div.dataset.name_ar = s.name_ar || '';
                        // Fix: store default_price in dataset
                        div.dataset.default_price = s.default_price || 0;

                        div.addEventListener('click', function() {
                            addRow({
                                id: this.dataset.id,
                                code: this.dataset.code,
                                name: this.dataset.name,
                                name_ar: this.dataset.name_ar,
                                default_price: this.dataset.default_price
                            });
                            clearResults();
                            if (searchInput) searchInput.value = '';
                        });
                        resultsDiv.appendChild(div);
                    });
                }).catch(function() { resultsDiv.innerHTML = '<div class="p-3 text-red-600 text-sm">' + (document.documentElement.lang === 'ar' ? 'خطأ في البحث' : 'Search error') + '</div>'; resultsDiv.classList.remove('hidden'); });
            }

            function appendServicesToForm(form, fieldMap) {
                var existing = form.querySelectorAll('input[name^="services"]');
                existing.forEach(function(el) { el.remove(); });
                var isAr = document.documentElement.lang === 'ar';
                rows.forEach(function(r, i) {
                    var nameDisplay = (isAr && r.name_ar) ? r.name_ar : r.name;
                    fieldMap.forEach(function(entry) {
                        var inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'services[' + i + '][' + entry.key + ']';
                        inp.value = entry.value(r, nameDisplay);
                        form.appendChild(inp);
                    });
                });
            }

            if (deptSelect) {
                deptSelect.addEventListener('change', function() {
                    clearResults();
                    var q = (searchInput && searchInput.value) ? searchInput.value.trim() : '';
                    if (q.length >= 1) {
                        doEligibilitySearch();
                    }
                });
            }
            if (searchBtn) searchBtn.addEventListener('click', doEligibilitySearch);

            // Live search: trigger automatically while typing (debounced 300ms)
            var _debounceTimer = null;
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(_debounceTimer);
                    _debounceTimer = setTimeout(function() {
                        doEligibilitySearch();
                    }, 300);
                });
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { e.preventDefault(); clearTimeout(_debounceTimer); doEligibilitySearch(); }
                });
            }

            // Handle Detailed Invoice Button (revenue — recorded in system)
            if (revenueBtn && revenueForm) {
                revenueBtn.addEventListener('click', function() {
                    if (rows.length === 0) {
                        alert(document.documentElement.lang === 'ar' ? 'يرجى إضافة خدمات أولاً' : 'Please add services first');
                        return;
                    }
                    appendServicesToForm(revenueForm, [
                        { key: 'service_id', value: function(r) { return r.id || ''; } },
                        { key: 'code', value: function(r) { return r.code || ''; } },
                        { key: 'name', value: function(r, n) { return n; } },
                        { key: 'quantity', value: function(r) { return r.qty || 1; } },
                        { key: 'unit_price', value: function(r) { return r.unit_price || 0; } },
                        { key: 'total', value: function(r) { return r.total.toFixed(2); } },
                        { key: 'insurance_coverage_type', value: function(r) { return r.insurance_coverage_type || ''; } },
                        { key: 'insurance_coverage_value', value: function(r) { return r.insurance_coverage_value || 0; } }
                    ]);
                    revenueForm.submit();
                });
            }

            // Handle Price Inquiry Button (print only — no revenue)
            if (inquiryBtn && inquiryForm) {
                inquiryBtn.addEventListener('click', function() {
                    if (rows.length === 0) {
                        alert(document.documentElement.lang === 'ar' ? 'يرجى إضافة خدمات أولاً' : 'Please add services first');
                        return;
                    }
                    appendServicesToForm(inquiryForm, [
                        { key: 'service_id', value: function(r) { return r.id || ''; } },
                        { key: 'code', value: function(r) { return r.code || ''; } },
                        { key: 'name', value: function(r, n) { return n; } },
                        { key: 'qty', value: function(r) { return r.qty || 1; } },
                        { key: 'unit_price', value: function(r) { return r.unit_price || 0; } },
                        { key: 'total', value: function(r) { return r.total.toFixed(2); } },
                        { key: 'insurance_coverage_type', value: function(r) { return r.insurance_coverage_type || ''; } },
                        { key: 'insurance_coverage_value', value: function(r) { return r.insurance_coverage_value || 0; } }
                    ]);
                    inquiryForm.submit();
                });
            }

            // Handle Print Button
            if (printBtn && printForm) {
                printBtn.addEventListener('click', function() {
                    var notesEl = document.getElementById('eligibility_notes');
                    var deptValue = deptSelect ? deptSelect.value : '';
                    var isAr = document.documentElement.lang === 'ar';

                    if (!deptValue) {
                        alert(isAr ? 'يرجى اختيار القسم أو «بدون قسم».' : 'Please select a department or «No department».');
                        return;
                    }
                    if (deptValue === 'none' && notesEl && !notesEl.value.trim()) {
                        alert(isAr ? 'ملاحظة الأحقية إلزامية عند اختيار «بدون قسم».' : 'Eligibility note is required for «No department».');
                        if (notesEl) notesEl.focus();
                        return;
                    }

                    var existing = printForm.querySelectorAll('input[name^="services"], input[name="department_id"], input[name="eligibility_notes"]');
                    existing.forEach(function(el) { el.remove(); });

                    rows.forEach(function(r, i) {
                        var nameDisplay = (isAr && r.name_ar) ? r.name_ar : r.name;
                        var map = {
                            'service_id': r.id || '',
                            'code': r.code || '',
                            'name': nameDisplay,
                            'quantity': r.qty || 1,
                            'price': r.unit_price || 0,
                            'total': r.total.toFixed(2),
                            'insurance_coverage_type': r.insurance_coverage_type || '',
                            'insurance_coverage_value': r.insurance_coverage_value || 0
                        };
                        for (var k in map) {
                            var inp = document.createElement('input');
                            inp.type = 'hidden';
                            inp.name = 'services[' + i + '][' + k + ']';
                            inp.value = map[k];
                            printForm.appendChild(inp);
                        }
                    });

                    if (deptSelect && deptSelect.value) {
                        var inpDept = document.createElement('input');
                        inpDept.type = 'hidden';
                        inpDept.name = 'department_id';
                        inpDept.value = deptSelect.value;
                        printForm.appendChild(inpDept);
                    }

                    if (notesEl) {
                        var inpNotes = document.createElement('input');
                        inpNotes.type = 'hidden';
                        inpNotes.name = 'eligibility_notes';
                        inpNotes.value = notesEl.value.trim();
                        printForm.appendChild(inpNotes);
                    }

                    printForm.submit();
                });
            }

            // ملاحظات الأحقية: إلزامية عند «بدون قسم»
            (function() {
                var notesEl = document.getElementById('eligibility_notes');
                var notesLabel = document.getElementById('eligibility_notes_label');
                var notesHint = document.getElementById('eligibility_notes_hint');
                var isAr = document.documentElement.lang === 'ar';

                function syncNotesRequired() {
                    if (!deptSelect || !notesEl) return;
                    var withoutDept = deptSelect.value === 'none';
                    notesEl.required = withoutDept;
                    if (notesLabel) {
                        notesLabel.textContent = withoutDept
                            ? (isAr ? 'ملاحظة الأحقية *' : 'Eligibility note *')
                            : (isAr ? 'ملاحظة الأحقية' : 'Eligibility note');
                    }
                    if (notesHint) notesHint.classList.toggle('hidden', !withoutDept);
                }

                if (deptSelect) deptSelect.addEventListener('change', syncNotesRequired);
                syncNotesRequired();
            })();

            // Handle Reload Last Eligibility Services
            var reloadBtn = document.getElementById('btn_reload_eligibility');
            if (reloadBtn) {
                reloadBtn.addEventListener('click', function() {
                    if (rows.length > 0 && !confirm(document.documentElement.lang === 'ar' ? 'هذا سيمسح الخدمات الحالية ويستبدلها بآخر خدمات مطبوعة. هل أنت متأكد؟' : 'This will clear current services and replace them with the last printed services. Are you sure?')) {
                        return;
                    }
                    rows = [];
                    if (window.lastEligibilityServices && Array.isArray(window.lastEligibilityServices)) {
                        window.lastEligibilityServices.forEach(function(s) {
                            rows.push({
                                id: s.service_id || '',
                                code: s.code || '',
                                name: (document.documentElement.lang === 'ar' && s.name_ar) ? s.name_ar : (s.name || ''),
                                name_ar: s.name_ar || '',
                                qty: parseFloat(s.quantity) || parseFloat(s.qty) || 1,
                                unit_price: parseFloat(s.price) || parseFloat(s.unit_price) || 0,
                                total: parseFloat(s.total) || 0,
                                insurance_coverage_type: s.insurance_coverage_type || '',
                                insurance_coverage_value: parseFloat(s.insurance_coverage_value) || 0
                            });
                        });
                    }
                    renderRows();
                });
            }
        })();

    </script>
    @endif
@endsection
