@php
    $medicalDepartments = $medicalDepartments ?? ($departments ?? collect());
    $selectedDept = old('department_id', $selectedDepartmentId ?? '');
    $fieldId = $fieldId ?? 'visit_department_id';
@endphp
<div class="mt-3 p-4 rounded-xl border-2 border-emerald-200 bg-emerald-50/60">
    <label for="{{ $fieldId }}" class="block text-sm font-black text-emerald-950 mb-2">
        {{ app()->getLocale() === 'ar' ? 'القسم الطبي للزيارة (التخصص)' : 'Visit medical specialty' }}
        <span class="text-rose-600">*</span>
    </label>
    <select name="department_id" id="{{ $fieldId }}" required
        class="w-full rounded-lg border-2 border-emerald-400 bg-white px-3 py-2.5 text-slate-900 font-bold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <option value="">{{ app()->getLocale() === 'ar' ? '— اختر التخصص (باطنية / مختبر / أورام…)' : '— Select specialty —' }}</option>
        @foreach ($medicalDepartments as $d)
            <option value="{{ $d->id }}" @selected((string) $selectedDept === (string) $d->id)>
                {{ app()->getLocale() === 'ar' && $d->name_ar ? $d->name_ar : $d->name }}
            </option>
        @endforeach
    </select>
    @error('department_id')
        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
    @enderror
</div>
