@extends('layouts.app')
@section('title', __('Payments'))
@section('content')
    @if(session('success'))<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ __('Payments') }}</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ __("Patients") }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'تاريخ الاستلام' : 'Received' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'استلم من' : 'Received by' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'اعتماد' : 'Approval' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ $p->invoice?->patient?->name ?? '—' }}</td>
                        <td class="p-3">@currency($p->amount)</td>
                        <td class="p-3">{{ $p->received_date?->format('Y-m-d') }}</td>
                        <td class="p-3">{{ $p->receivedByUser?->username ?? $p->receivedByUser?->name ?? '—' }}</td>
                        <td class="p-3">
                            @if($p->approved_at)
                                {{ app()->getLocale() === 'ar' ? 'معتمد' : 'Approved' }} ({{ $p->approved_at->format('Y-m-d') }})
                            @else
                                <span class="text-amber-600">{{ app()->getLocale() === 'ar' ? 'بانتظار الاعتماد' : 'Pending' }}</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @can('payments.approve')
                                @if(!$p->approved_at)
                                    <form action="{{ route('payments.approve', $p) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'اعتماد' : 'Approve' }}</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مدفوعات' : 'No payments yet' }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($payments->hasPages())<div class="p-3 border-t">{{ $payments->links() }}</div>@endif
    </div>
@endsection
