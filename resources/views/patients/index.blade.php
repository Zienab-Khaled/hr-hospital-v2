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
    @if(session('success'))<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ $sectionTitle ?? __('Patients') }}</h2>
        @can('patients.create')
            <a href="{{ route('patients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 shadow flex items-center gap-1">
                <span class="text-lg font-bold">+</span> {{ $addBtnLabel }}
            </a>
        @endcan
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ __('Patients') }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File No' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment' }}</th>
                    <th class="text-start p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $p)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ $p->name }}</td>
                        <td class="p-3">{{ $p->file_number }}</td>
                        <td class="p-3">{{ $p->payment_type }}</td>
                        <td class="p-3"><a href="#" class="text-blue-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا يوجد مرضى' : 'No patients yet' }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($patients->hasPages())
            <div class="p-3 border-t">{{ $patients->links() }}</div>
        @endif
    </div>
@endsection
