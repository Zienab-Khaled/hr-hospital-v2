@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'التفويضات' : 'Delegations')
@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'التفويضات' : 'Delegations' }}</h2>
    </div>

    
    @if ($canManage)
        {{-- Form: إنشاء تفويض جديد --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ app()->getLocale() === 'ar' ? 'تفويض جديد' : 'New delegation' }}</h3>
            <form action="{{ route('delegations.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المُفوّض إليه' : 'Delegate to' }} <span class="text-red-500">*</span></label>
                    <select name="delegate_to_id" required class="w-full rounded border border-slate-300 px-3 py-2 @error('delegate_to_id') border-red-500 @enderror">
                        <option value="">{{ app()->getLocale() === 'ar' ? '— اختر موظف —' : '— Select user —' }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ old('delegate_to_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} @if($u->job_title_ar) ({{ $u->job_title_ar }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('delegate_to_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'من تاريخ' : 'From date' }} <span class="text-red-500">*</span></label>
                    <input type="date" name="from_date" value="{{ old('from_date', now()->toDateString()) }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('from_date') border-red-500 @enderror">
                    @error('from_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'إلى تاريخ' : 'To date' }} <span class="text-red-500">*</span></label>
                    <input type="date" name="to_date" value="{{ old('to_date', now()->toDateString()) }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('to_date') border-red-500 @enderror">
                    @error('to_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 whitespace-nowrap">{{ app()->getLocale() === 'ar' ? 'إنشاء التفويض' : 'Create delegation' }}</button>
                </div>
                <div class="md:col-span-2 lg:col-span-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات (اختياري)' : 'Notes (optional)' }}</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: إجازة المدير' : 'e.g. Manager on leave' }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('notes') border-red-500 @enderror">
                    @error('notes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </form>
        </div>

        {{-- قائمة التفويضات التي أنشأتها --}}
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <h3 class="px-4 py-3 border-b border-slate-200 font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'تفويضاتي (المُفوّض مني)' : 'Delegations I created' }}</h3>
            @if ($given->isEmpty())
                <p class="p-4 text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'لا توجد تفويضات.' : 'No delegations.' }}</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المُفوّض إليه' : 'Delegate to' }}</th>
                            <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'من' : 'From' }}</th>
                            <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}</th>
                            <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</th>
                            <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($given as $d)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="p-3">{{ $d->delegateTo->name ?? '—' }}</td>
                                <td class="p-3">{{ $d->from_date->format('Y-m-d') }}</td>
                                <td class="p-3">{{ $d->to_date->format('Y-m-d') }}</td>
                                <td class="p-3 text-slate-600">{{ $d->notes ?: '—' }}</td>
                                <td class="p-3">
                                    <form action="{{ route('delegations.destroy', $d) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل تريد إلغاء هذا التفويض؟' : 'Cancel this delegation?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-2">{{ $given->withQueryString()->links() }}</div>
            @endif
        </div>
    @endif

    {{-- التفويضات المُفوّض إليّ فيها --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <h3 class="px-4 py-3 border-b border-slate-200 font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المُفوّض إليّ (أنا مفوّض من)' : 'Delegations to me' }}</h3>
        @if ($received->isEmpty())
            <p class="p-4 text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'لا توجد تفويضات لك.' : 'No delegations assigned to you.' }}</p>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المُفوّض (من)' : 'Delegator' }}</th>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'من تاريخ' : 'From' }}</th>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'إلى تاريخ' : 'To' }}</th>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</th>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($received as $d)
                        @php
                            $today = now()->toDateString();
                            $active = $d->from_date->toDateString() <= $today && $d->to_date->toDateString() >= $today;
                        @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3">{{ $d->delegator->name ?? '—' }}</td>
                            <td class="p-3">{{ $d->from_date->format('Y-m-d') }}</td>
                            <td class="p-3">{{ $d->to_date->format('Y-m-d') }}</td>
                            <td class="p-3 text-slate-600">{{ $d->notes ?: '—' }}</td>
                            <td class="p-3">
                                @if ($active)
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</span>
                                @else
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs">{{ app()->getLocale() === 'ar' ? 'منتهي' : 'Ended' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-2">{{ $received->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
