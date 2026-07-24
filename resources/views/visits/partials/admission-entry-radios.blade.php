@php
    $defaultAdmission = $defaultAdmission ?? \App\Models\Visit::ADMISSION_OUTPATIENT_CLINICS;
    $selected = old('admission_entry_source', $defaultAdmission);
@endphp
<div class="mt-3 p-4 rounded-xl border-2 border-indigo-200 bg-indigo-50/60">
    <span class="block text-sm font-black text-indigo-950 mb-2">
        {{ app()->getLocale() === 'ar' ? 'مسار الدخول (مكتب الدخول)' : 'Admission desk route' }}
        <span class="text-rose-600">*</span>
    </span>
    <p class="text-xs text-indigo-800/90 mb-3 leading-relaxed">
        {{ app()->getLocale() === 'ar'
            ? 'حدّد إن كان الدخول من مكتب دخول العيادات الخارجية أو من الطوارئ.'
            : 'Choose whether the patient entered via outpatient clinics admission or emergency.' }}
    </p>
    <div class="flex flex-wrap gap-4">
        <label class="inline-flex items-center gap-2 cursor-pointer rounded-lg bg-white px-3 py-2 border-2 border-teal-200 shadow-sm hover:border-teal-400 transition-colors">
            <input type="radio" name="admission_entry_source" value="{{ \App\Models\Visit::ADMISSION_OUTPATIENT_CLINICS }}"
                {{ $selected === \App\Models\Visit::ADMISSION_OUTPATIENT_CLINICS ? 'checked' : '' }} required
                class="text-teal-600 focus:ring-teal-500">
            <span class="text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'مكتب دخول العيادات الخارجية' : 'Outpatient clinics' }}</span>
        </label>
        <label class="inline-flex items-center gap-2 cursor-pointer rounded-lg bg-white px-3 py-2 border-2 border-rose-200 shadow-sm hover:border-rose-400 transition-colors">
            <input type="radio" name="admission_entry_source" value="{{ \App\Models\Visit::ADMISSION_EMERGENCY }}"
                {{ $selected === \App\Models\Visit::ADMISSION_EMERGENCY ? 'checked' : '' }} required
                class="text-rose-600 focus:ring-rose-500">
            <span class="text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الطوارئ' : 'Emergency' }}</span>
        </label>
    </div>
</div>
