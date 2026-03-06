@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'الزيارات' : 'Visits')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <h2 class="text-xl font-semibold text-slate-800">
            {{ app()->getLocale() === 'ar' ? 'الزيارات' : 'Visits' }}
            @if (!$isAdmin && $currentShift)
                <span class="text-sm font-normal text-slate-600">— {{ app()->getLocale() === 'ar' ? 'شيفت اليوم في قسمك' : 'Today\'s shift in your department' }}</span>
            @endif
        </h2>
        @can('invoices.create')
            <a href="{{ route('visits.create') }}"
                class="inline-flex text-white items-center gap-2 bg-blue-600 text-slate-50 px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ app()->getLocale() === 'ar' ? 'إضافة زيارة جديدة' : 'Add New Visit' }}
            </a>
        @endcan
    </div>

    @if ($isAdmin)
        <x-index-filters :action="route('visits.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم المريض، رقم الملف، رقم الهوية...' : 'Patient name, file no, identity...'">
            <div class="w-36">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">{{ app()->getLocale() === 'ar' ? 'الشيفت' : 'Shift' }}</label>
                <select name="shift_id" class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach ($shifts as $s)
                        <option value="{{ $s->id }}" {{ request('shift_id') == $s->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $s->name_ar ? $s->name_ar : $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</label>
                <select name="department_id" class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $d->name_ar ? $d->name_ar : $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</label>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}"
                    class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
            </div>
            <div class="w-40">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">{{ app()->getLocale() === 'ar' ? 'الجهة / التأمين' : 'Insurance / Entity' }}</label>
                <select name="insurance_company_id" class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach ($insuranceCompanies as $ic)
                        <option value="{{ $ic->id }}" {{ request('insurance_company_id') == $ic->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $ic->name_ar ? $ic->name_ar : $ic->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">{{ app()->getLocale() === 'ar' ? 'المسجّل' : 'Registered By' }}</label>
                <select name="registered_by" class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach ($registrars as $u)
                        <option value="{{ $u->id }}" {{ request('registered_by') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-index-filters>
    @else
        <x-index-filters :action="route('visits.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم المريض، رقم الملف...' : 'Patient name, file no...'">
        </x-index-filters>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3 text-slate-800">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th class="text-start p-3 text-slate-800">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                    <th class="text-start p-3 text-slate-800">{{ app()->getLocale() === 'ar' ? 'القسم الطبي' : 'Medical Dept' }}</th>
                    @if ($isAdmin)
                        <th class="text-start p-3 text-slate-800">{{ app()->getLocale() === 'ar' ? 'الشيفت' : 'Shift' }}</th>
                    @endif
                    <th class="text-start p-3 text-slate-800">{{ app()->getLocale() === 'ar' ? 'مسجّل بواسطة' : 'Registered by' }}</th>
                    <th class="text-start p-3 w-32 text-slate-800">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visits as $v)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3 text-slate-800">{{ $v->visit_date?->format('Y-m-d') }}</td>
                        <td class="p-3">
                            @if ($v->patient)
                                <a href="{{ route('patients.show', $v->patient) }}" class="text-blue-600 hover:underline font-medium">{{ $v->patient->name }}</a>
                                <span class="text-slate-500 text-xs block">{{ $v->patient->file_number }}</span>
                                <div class="flex gap-1 mt-1">
                                    @if($v->printed_eligibility_at)
                                        <span title="{{ app()->getLocale() === 'ar' ? 'تم طباعة أحقية العلاج: ' . $v->printed_eligibility_at : 'Eligibility printed: ' . $v->printed_eligibility_at }}" class="cursor-help text-xs">📄</span>
                                    @endif
                                    @if($v->printed_price_inquiry_at)
                                        <span title="{{ app()->getLocale() === 'ar' ? 'تم طباعة عرض السعر: ' . $v->printed_price_inquiry_at : 'Price inquiry printed: ' . $v->printed_price_inquiry_at }}" class="cursor-help text-xs">💰</span>
                                    @endif

                                    {{-- Charity/Insurance Communication Indicators --}}
                                    @php
                                        $hasSent = false; $hasConfirmed = false; $hasRejected = false;
                                        foreach($v->invoices as $inv) {
                                            foreach($inv->partySends as $ps) {
                                                $hasSent = true;
                                                if($ps->response_action === 'confirmed') $hasConfirmed = true;
                                                if($ps->response_action === 'rejected') $hasRejected = true;
                                            }
                                        }
                                    @endphp
                                    @if($hasSent)
                                        <span title="{{ app()->getLocale() === 'ar' ? 'تم إرسال إيميل للجمعية/التأمين' : 'Email sent to charity/insurance' }}" class="cursor-help text-xs">📧</span>
                                    @endif
                                    @if($hasConfirmed)
                                        <span title="{{ app()->getLocale() === 'ar' ? 'تمت الموافقة من الطرف الآخر' : 'Response confirmed by party' }}" class="cursor-help text-xs">✅</span>
                                    @endif
                                    @if($hasRejected)
                                        <span title="{{ app()->getLocale() === 'ar' ? 'تم الرفض من الطرف الآخر' : 'Response rejected by party' }}" class="cursor-help text-xs">❌</span>
                                    @endif
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-3 text-slate-800">
                            @if ($v->department)
                                {{ app()->getLocale() === 'ar' && $v->department->name_ar ? $v->department->name_ar : $v->department->name }}
                            @else
                                —
                            @endif
                            @if ($v->transferred_department_id)
                                <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded ms-1 border border-amber-200">
                                    {{ app()->getLocale() === 'ar' ? 'تم التحويل' : 'Transferred' }}
                                </span>
                            @endif
                        </td>
                        @if ($isAdmin)
                            <td class="p-3 text-slate-800">
                                @if ($v->shift)
                                    {{ app()->getLocale() === 'ar' && $v->shift->name_ar ? $v->shift->name_ar : $v->shift->name }}
                                @else
                                    —
                                @endif
                            </td>
                        @endif
                        <td class="p-3 text-slate-600">{{ $v->registeredBy->name ?? $v->registeredBy->username ?? '—' }}</td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                @if ($v->patient)
                                    <a href="{{ route('visits.create', ['patient_id' => $v->patient_id, 'visit_id' => $v->id, 'registered' => 1]) }}"
                                       title="{{ app()->getLocale() === 'ar' ? 'فتح الزيارة' : 'Open Visit' }}"
                                       class="text-purple-600 hover:text-purple-800 p-1 rounded hover:bg-purple-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                    <a href="{{ route('patients.show', $v->patient) }}" title="{{ app()->getLocale() === 'ar' ? 'عرض المريض' : 'View patient' }}" class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @can('invoices.create')
                                        @if (!$v->transferred_department_id)
                                            <a href="{{ route('invoices.create', ['patient_id' => $v->patient->id, 'visit_id' => $v->id]) }}" title="{{ app()->getLocale() === 'ar' ? 'فاتورة' : 'Invoice' }}" class="text-emerald-600 hover:text-emerald-800 p-1 rounded hover:bg-emerald-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                        @endif
                                    @endcan
                                    @can('visits.delete') {{-- Or checks for admin --}}
                                        @if (!$v->transferred_department_id)
                                            <a href="{{ route('visits.edit', $v) }}" title="{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}" class="text-slate-600 hover:text-slate-800 p-1 rounded hover:bg-slate-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <form action="{{ route('visits.destroy', $v) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذه الزيارة؟' : 'Are you sure you want to delete this visit?' }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}" class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="p-8 text-center text-slate-500">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد زيارات' : 'No visits yet' }}
                            @if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager'))
                                <span class="block mt-1 text-sm">{{ app()->getLocale() === 'ar' ? 'لشيفت اليوم في قسمك.' : 'For today\'s shift in your department.' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($visits->hasPages())
            <div class="p-3 border-t border-slate-200">{{ $visits->links() }}</div>
        @endif
    </div>
@endsection
