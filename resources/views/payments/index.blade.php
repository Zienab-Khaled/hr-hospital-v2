@extends('layouts.app')
@section('title', __('Payments'))
@section('content')

<div x-data="dataTable()" x-init="init()">
    @if(session('success'))<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Payments') }}</h2>
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium"><span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}</span>
                @can('payments.approve')
                    <button @click="executeBulkAction('{{ route('payments.bulk-approve') }}', 'POST', '{{ app()->getLocale() === 'ar' ? 'اعتماد المدفوعات المحددة؟' : 'Approve selected payments?' }}')" class="px-4 py-2 text-sm font-bold rounded-lg bg-green-600 text-white hover:bg-green-700 shadow-md">{{ app()->getLocale() === 'ar' ? 'اعتماد المحدد' : 'Approve Selected' }}</button>
                    <button @click="executeBulkAction('{{ route('payments.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'حذف المدفوعات المحددة؟' : 'Delete selected?' }}')" class="px-4 py-2 text-sm font-bold rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md">{{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}</button>
                @endcan
            </div>
        </div>
    </div>

    <x-index-filters :action="route('payments.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم المريض، المبلغ...' : 'Patient name, amount...'"></x-index-filters>
    
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-700 to-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ __("Patients") }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'تاريخ الاستلام' : 'Received' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'استلم من' : 'Received by' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150" :class="{'bg-blue-50': selectedItems.includes({{ $p->id }})}">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $p->id }}" :checked="selectedItems.includes({{ $p->id }})" @change="toggleItem({{ $p->id }})" class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></td>
                            <td class="px-6 py-4"><span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $p->id }}</span></td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $p->invoice?->patient?->name ?? '—' }}</td>
                            <td class="px-6 py-4 font-semibold text-green-700">@currency($p->amount)</td>
                            <td class="px-6 py-4 text-slate-600">{{ $p->received_date?->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $p->receivedByUser?->username ?? $p->receivedByUser?->name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if($p->approved_at)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">{{ app()->getLocale() === 'ar' ? 'معتمد' : 'Approved' }}</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">{{ app()->getLocale() === 'ar' ? 'قيد الانتظار' : 'Pending' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('payments.approve')
                                        @if(!$p->approved_at)
                                            <form action="{{ route('payments.approve', $p) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    {{ app()->getLocale() === 'ar' ? 'اعتماد' : 'Approve' }}
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-slate-500 to-slate-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'لا توجد مدفوعات' : 'No Payments Found' }}</h3>
                                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'لم يتم العثور على مدفوعات' : 'No payments yet' }}</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())<div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">{{ $payments->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function dataTable(){return{selectedItems:[],allSelected:false,init(){},toggleSelectAll(){if(this.allSelected){this.selectedItems=[];this.allSelected=false}else{const checkboxes=document.querySelectorAll('tbody input[type="checkbox"]');this.selectedItems=Array.from(checkboxes).map(cb=>parseInt(cb.value)).filter(id=>id>0);this.allSelected=true}},toggleItem(id){const index=this.selectedItems.indexOf(id);if(index>-1){this.selectedItems.splice(index,1)}else{this.selectedItems.push(id)}const totalCheckboxes=document.querySelectorAll('tbody input[type="checkbox"]').length;this.allSelected=this.selectedItems.length===totalCheckboxes&&totalCheckboxes>0},executeBulkAction(action,method,confirmMessage){if(this.selectedItems.length===0){alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');return}if(confirmMessage&&!confirm(confirmMessage))return;const form=document.createElement('form');form.method='POST';form.action=action;const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value='{{ csrf_token() }}';form.appendChild(csrf);if(method!=='POST'){const methodField=document.createElement('input');methodField.type='hidden';methodField.name='_method';methodField.value=method;form.appendChild(methodField)}this.selectedItems.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input)});document.body.appendChild(form);form.submit()}}}
</script>
@endpush
<style>[x-cloak]{display:none !important;}</style>
@endsection
