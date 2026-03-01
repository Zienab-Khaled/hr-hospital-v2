@extends('layouts.app')
@section('title', __('Authorizations'))
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ __('Authorizations') }}</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الرقم المرجعي' : 'Reference' }}</th>
                    <th class="text-start p-3">{{ __('Patients') }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'تاريخ الإصدار' : 'Issue date' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'تاريخ الانتهاء' : 'Expiry' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment type' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($authorizations as $auth)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ $auth->reference_number ?? '—' }}</td>
                        <td class="p-3">{{ $auth->patient?->name }}</td>
                        <td class="p-3">{{ $auth->issue_date?->format('Y-m-d') }}</td>
                        <td class="p-3">{{ $auth->expiry_date?->format('Y-m-d') }}</td>
                        <td class="p-3">{{ $auth->payment_type ?? '—' }}</td>
                        <td class="p-3">{{ $auth->status ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد موافقات' : 'No authorizations yet' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($authorizations->hasPages())
            <div class="p-3 border-t">{{ $authorizations->links() }}</div>
        @endif
    </div>
@endsection
