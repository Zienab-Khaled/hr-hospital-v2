@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إنشاء زيارة' : 'Create Visit')
@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    {{ app()->getLocale() === 'ar' ? '🩺 إنشاء زيارة' : '🩺 Create Visit' }}
                </h2>
                @if ($currentShift)
                    <span class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-800 text-sm font-medium">
                        {{ app()->getLocale() === 'ar' ? 'الشيفت الحالي:' : 'Current shift:' }}
                        {{ app()->getLocale() === 'ar' && $currentShift->name_ar ? $currentShift->name_ar : $currentShift->name }}
                    </span>
                @endif
            </div>

            @if (session('success'))
                <div class="mb-4 p-3 rounded-lg bg-emerald-100 text-emerald-800 text-sm">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm">
                    @foreach ($errors->all() as $err) <p>{{ $err }}</p> @endforeach
                </div>
            @endif

            @if (!$patient)
                {{-- Step 1: اختر مريض (بحث أو إضافة جديد) --}}
                <div class="space-y-4">
                    <p class="text-slate-700">
                        {{ app()->getLocale() === 'ar' ? 'ابحث عن مريض برقم الهوية أو الاسم أو رقم الملف، أو أضف مريضاً جديداً.' : 'Search by identity, name or file number, or add a new patient.' }}
                    </p>
                    <div class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[200px] relative">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'بحث مريض' : 'Search patient' }}</label>
                            <input type="text" id="visit_patient_search"
                            autocomplete="on" placeholder="{{ app()->getLocale() === 'ar' ? 'رقم الهوية / الاسم / رقم الملف...' : 'Identity / name / file number...' }}"
                            style="border-color: black;
                            border-width: 1px;
                            border-radius: 5px;
                            padding: 10px;
                            font-size: 16px;
                            font-weight: 500;
                            color: #333;
                            background-color: #f9f9f9;
                            "
                            class="w-full rounded-lg

                                border-2 px-3 py-2
                                focus:ring-2
                                 focus:ring-red-500
                                  focus:border-red-500">
                            <div id="visit_patient_results" class="hidden absolute left-0 right-0 z-10 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                        @can('patients.create')
                            <a href="{{ route('patients.create', ['redirect_to' => 'visits.create']) }}"
                                class="shrink-0 inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                {{ app()->getLocale() === 'ar' ? 'مريض جديد' : 'New patient' }}
                            </a>
                        @endcan
                    </div>
                </div>
            @else
                {{-- Step 2: بطاقة المريض + تسجيل دخول القسم أو إجراءات --}}
                <div class="border border-slate-200 rounded-lg p-4 mb-6 bg-slate-50">
                    <h3 class="font-semibold text-slate-800 mb-2">{{ app()->getLocale() === 'ar' ? '👤 المريض' : '👤 Patient' }}</h3>
                    <p class="font-medium text-slate-800">{{ $patient->name }} {{ $patient->name_ar ? ' / ' . $patient->name_ar : '' }}</p>
                    <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File no:' }} {{ $patient->file_number }} — {{ app()->getLocale() === 'ar' ? 'الهوية:' : 'ID:' }} {{ $patient->identity_value }}</p>
                    @if ($patient->department)
                        <p class="text-sm text-slate-600 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'القسم الحالي:' : 'Current department:' }}
                            {{ app()->getLocale() === 'ar' && $patient->department->name_ar ? $patient->department->name_ar : $patient->department->name }}
                        </p>
                    @endif
                </div>

                @if (!$visit && $myDepartment)
                    <form action="{{ route('visits.store') }}" method="POST" class="mb-6">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                        <button type="submit" class="w-full py-3 px-4 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل دخول المريض إلى القسم:' : 'Register patient entry to department:' }}
                            {{ app()->getLocale() === 'ar' && $myDepartment->name_ar ? $myDepartment->name_ar : $myDepartment->name }}
                        </button>
                    </form>
                @endif

                @if ($visit || $registered)
                    <div class="space-y-3">
                        <h3 class="font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</h3>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('patients.show', $patient) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-slate-400 bg-slate-100 text-slate-800 font-medium hover:bg-slate-200 text-sm">
                                {{ app()->getLocale() === 'ar' ? 'عرض المريض / تحويل لقسم آخر' : 'View patient / Transfer' }}
                            </a>
                            @php $visitForPrint = $visit ?? $patient->visits()->whereDate('visit_date', today())->latest()->first(); @endphp
                            @if ($visitForPrint)
                                <a href="{{ route('visits.treatment-eligibility-print', $visitForPrint) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-amber-500 bg-amber-50 text-amber-800 font-medium hover:bg-amber-100 text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'طباعة إحقاق علاج' : 'Print treatment eligibility' }}
                                </a>
                            @endif
                            <a href="{{ route('invoices.create', ['patient_id' => $patient->id, 'visit_id' => $visit?->id]) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-blue-600 bg-blue-600 text-white font-medium hover:bg-blue-700 text-sm">
                                {{ app()->getLocale() === 'ar' ? 'تقديم خدمات و إنشاء فاتورة' : 'Add services & create invoice' }}
                            </a>
                        </div>
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-slate-200">
                    <a href="{{ route('visits.create') }}" class="text-slate-600 hover:underline text-sm">
                        {{ app()->getLocale() === 'ar' ? '← إنشاء زيارة لمريض آخر' : '← Create visit for another patient' }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if (!$patient)
    <script>
        (function() {
            var searchUrl = '{{ route('invoices.patients-search') }}';
            var input = document.getElementById('visit_patient_search');
            var results = document.getElementById('visit_patient_results');
            var wrap = input && input.closest('div');
            if (!input || !results) return;

            function showResults(items) {
                results.innerHTML = '';
                results.classList.remove('hidden');
                if (!items.length) { results.innerHTML = '<div class="p-3 text-slate-500 text-sm">' + (document.documentElement.lang === 'ar' ? 'لا توجد نتائج' : 'No results') + '</div>'; return; }
                items.forEach(function(p) {
                    var a = document.createElement('a');
                    a.href = '{{ route('visits.create') }}?patient_id=' + p.id;
                    a.className = 'block px-3 py-2 hover:bg-slate-100 text-slate-800 text-sm border-b border-slate-100 last:border-0';
                    a.textContent = (p.name_ar || p.name) + ' — ' + (p.file_number || '') + ' — ' + (p.identity_value || '');
                    results.appendChild(a);
                });
            }

            var timer;
            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = (input.value || '').trim();
                if (q.length < 1) { results.classList.add('hidden'); return; }
                timer = setTimeout(function() {
                    fetch(searchUrl + '?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(showResults)
                        .catch(function() { results.classList.add('hidden'); });
                }, 200);
            });
            input.addEventListener('blur', function() { setTimeout(function() { results.classList.add('hidden'); }, 150); });
        })();
    </script>
    @endif
@endsection
