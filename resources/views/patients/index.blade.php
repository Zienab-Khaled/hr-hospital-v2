@extends('layouts.app')
@section('title', $sectionTitle ?? __('Patients'))
@section('content')
    @php
        $addBtnLabel = __('Add Patient');
        if(isset($section)) {
            $labels = [
                'charity' => app()->getLocale() === 'ar' ? 'إضافة مريض جمعية' : 'Add Charity Patient',
                'cash' => app()->getLocale() === 'ar' ? 'إضافة مريض كاش' : 'Add Cash Patient',
                'insurance' => app()->getLocale() === 'ar' ? 'إضافة مريض تأمين' : 'Add Insurance Patient',
            ];
            if(isset($labels[$section])) {
                $addBtnLabel = $labels[$section];
            }
        }
    @endphp
    @if(session('success'))<div class="mb-3 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-slate-800">{{ $sectionTitle ?? __('Patients') }}</h2>
        @can('patients.create')
            <a href="{{ route('patients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $addBtnLabel }}
            </a>
        @endcan
    </div>
    
    {{-- Search and Filter using Global Component --}}
    <x-index-filters :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم، رقم ملف، رقم هوية، هاتف...' : 'Name, file no, ID, phone...'">
        @if(!isset($section) || in_array($section, ['followup', 'collection']))
            <div class="w-36">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}
                </label>
                <select name="payment_type" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    <option value="cash" {{ request('payment_type') === 'cash' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'كاش' : 'Cash' }}</option>
                    <option value="insurance" {{ request('payment_type') === 'insurance' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</option>
                    <option value="charity" {{ request('payment_type') === 'charity' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'جمعية' : 'Charity' }}</option>
                </select>
            </div>
        @endif
    </x-index-filters>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-start px-4 py-3 font-semibold text-slate-700">{{ __('Patients') }}</th>
                        <th class="text-start px-4 py-3 font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File No' }}</th>
                        <th class="text-start px-4 py-3 font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment' }}</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $p)
                        <tr class="border-b border-slate-100 hover:bg-blue-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->file_number }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $p->payment_type === 'cash' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $p->payment_type === 'insurance' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $p->payment_type === 'charity' ? 'bg-orange-100 text-orange-800' : '' }}">
                                    {{ app()->getLocale() === 'ar' ? 
                                        ($p->payment_type === 'cash' ? 'كاش' : ($p->payment_type === 'insurance' ? 'تأمين' : 'جمعية')) : 
                                        ucfirst($p->payment_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="#" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            {{ app()->getLocale() === 'ar' ? 'لا يوجد مرضى' : 'No patients found' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patients->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
@endsection
