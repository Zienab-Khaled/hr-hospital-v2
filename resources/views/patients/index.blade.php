@extends('layouts.app')
@section('title', $sectionTitle ?? __('Patients'))
@section('content')
    @php
        $addBtnLabel = __('Add Patient');
        if (isset($section)) {
            $labels = [
                'charity' => app()->getLocale() === 'ar' ? 'إضافة مريض جمعية' : 'Add Charity Patient',
                'cash' => app()->getLocale() === 'ar' ? 'إضافة مريض كاش' : 'Add Cash Patient',
                'insurance' => app()->getLocale() === 'ar' ? 'إضافة مريض تأمين' : 'Add Insurance Patient',
            ];
            if (isset($labels[$section])) {
                $addBtnLabel = $labels[$section];
            }
        }
    @endphp
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ $sectionTitle ?? __('Patients') }}</h2>
        @can('patients.create')
            <a href="{{ route('patients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+
                {{ $addBtnLabel }}</a>
        @endcan
    </div>

    {{-- Search and Filter using Global Component --}}
    <x-index-filters :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم، رقم ملف، رقم هوية، هاتف...' : 'Name, file no, identity, phone...'">
        {{-- Identity Type Filter (all sections) --}}
        <div class="w-40">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'نوع الهوية' : 'Identity Type' }}
            </label>
            <select name="identity_type"
                class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                @foreach (\App\Models\Patient::identityTypeOptions() as $key => $labels)
                    <option value="{{ $key }}" {{ request('identity_type') === $key ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? $labels['ar'] : $labels['en'] }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Charity Section Filters --}}
        @if (isset($section) && $section === 'charity')
            <div class="w-44">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Charity Entity' }}
                </label>
                <select name="charity_entity_id"
                    class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach (\App\Models\CharityEntity::orderBy('name')->get() as $charity)
                        <option value="{{ $charity->id }}"
                            {{ request('charity_entity_id') == $charity->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $charity->name_ar ? $charity->name_ar : $charity->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-28">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }}
                </label>
                <select name="gender"
                    class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}</option>
                </select>
            </div>

            <div class="w-24">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'العمر من' : 'Age From' }}
                </label>
                <input type="number" name="age_from" value="{{ request('age_from') }}" min="0" max="150"
                    placeholder="{{ app()->getLocale() === 'ar' ? '0' : '0' }}"
                    class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="w-24">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'إلى' : 'Age To' }}
                </label>
                <input type="number" name="age_to" value="{{ request('age_to') }}" min="0" max="150"
                    placeholder="{{ app()->getLocale() === 'ar' ? '150' : '150' }}"
                    class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        @endif

        {{-- Insurance Section Filters --}}
        @if (isset($section) && $section === 'insurance')
            <div class="w-44">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                </label>
                <select name="insurance_company_id"
                    class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach (\App\Models\InsuranceCompany::orderBy('name')->get() as $insurance)
                        <option value="{{ $insurance->id }}"
                            {{ request('insurance_company_id') == $insurance->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $insurance->name_ar ? $insurance->name_ar : $insurance->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Payment Type Filter (for followup/collection sections) --}}
        @if (!isset($section) || in_array($section, ['followup', 'collection']))
            <div class="w-36">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}
                </label>
                <select name="payment_type"
                    class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    <option value="cash" {{ request('payment_type') === 'cash' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'كاش' : 'Cash' }}</option>
                    <option value="insurance" {{ request('payment_type') === 'insurance' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</option>
                    <option value="charity" {{ request('payment_type') === 'charity' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'جمعية' : 'Charity' }}</option>
                </select>
            </div>
        @endif
    </x-index-filters>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-center p-3">{{ app()->getLocale() === 'ar' ? 'الرقم' : 'ID' }}</th>
                    <th class="text-center p-3">{{ app()->getLocale() === 'ar' ? 'اسم المريض' : 'Patient Name' }}</th>
                    <th class="text-center p-3">{{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File No' }}</th>
                    <th class="text-center p-3">{{ app()->getLocale() === 'ar' ? 'نوع الهوية' : 'Identity Type' }}</th>
                    <th class="text-center p-3">{{ app()->getLocale() === 'ar' ? 'رقم الهوية' : 'Identity No' }}</th>
                    <th class="text-center p-3">
                        @if (isset($section) && $section === 'charity')
                            {{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Charity Entity' }}
                        @elseif(isset($section) && $section === 'insurance')
                            {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                        @else
                            {{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}
                        @endif
                    </th>
                    <th class="text-center p-3">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $p)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3 text-center text-slate-600">{{ $p->id }}</td>
                        <td class="p-3 text-center font-medium text-slate-800">{{ $p->name }}</td>
                        <td class="p-3 text-center text-slate-600">{{ $p->file_number }}</td>
                        <td class="p-3 text-center text-slate-600">{{ $p->identity_type_label ?? '-' }}</td>
                        <td class="p-3 text-center text-slate-600">{{ $p->identity_value ?? '-' }}</td>
                        <td class="p-3 text-center">
                            @if (isset($section) && $section === 'charity')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    {{ app()->getLocale() === 'ar' && $p->charityEntity?->name_ar
                                        ? $p->charityEntity->name_ar
                                        : $p->charityEntity?->name ?? '-' }}
                                </span>
                            @elseif(isset($section) && $section === 'insurance')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ app()->getLocale() === 'ar' && $p->insuranceCompany?->name_ar
                                        ? $p->insuranceCompany->name_ar
                                        : $p->insuranceCompany?->name ?? '-' }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $p->payment_type === 'cash' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $p->payment_type === 'insurance' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $p->payment_type === 'charity' ? 'bg-orange-100 text-orange-800' : '' }}">
                                    {{ app()->getLocale() === 'ar'
                                        ? ($p->payment_type === 'cash'
                                            ? 'كاش'
                                            : ($p->payment_type === 'insurance'
                                                ? 'تأمين'
                                                : 'جمعية'))
                                        : ucfirst($p->payment_type) }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex gap-2 justify-center">
                                @can('patients.view')
                                    <a href="{{ route('patients.show', $p) }}" class="text-blue-600 hover:text-blue-800"
                                        title="{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                @endcan
                                @can('patients.edit')
                                    <a href="{{ route('patients.edit', $p) }}" class="text-green-600 hover:text-green-800"
                                        title="{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endcan
                                @can('patients.delete')
                                    <form action="{{ route('patients.destroy', $p) }}" method="POST" class="inline"
                                        onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا المريض؟' : 'Are you sure you want to delete this patient?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800"
                                            title="{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-500">
                            {{ app()->getLocale() === 'ar' ? 'لا يوجد مرضى' : 'No patients' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($patients->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
@endsection
