@extends('layouts.app')
@section('title', __('Claims'))
@section('content')

<h2 class="text-2xl font-bold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'مطالبات التأمين والجمعيات' : 'Insurance & Charity Claims' }}</h2>

{{-- Insurance Claims --}}
<div x-data="dataTable('insurance')" x-init="init()" class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'مطالبات التأمين' : 'Insurance Claims' }}</h3>
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-2">
                <span class="text-sm text-slate-600 font-medium"><span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}</span>
                <button @click="executeBulkAction('{{ route('insurance-claims.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'حذف المحدد؟' : 'Delete?' }}')" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-red-600 text-white hover:bg-red-700">{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}</button>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-blue-700 to-blue-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ __("Patients") }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الشركة' : 'Company' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($insuranceClaims as $c)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all" :class="{'bg-blue-50': selectedItems.includes({{ $c->id }})}">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $c->id }}" :checked="selectedItems.includes({{ $c->id }})" @change="toggleItem({{ $c->id }})" class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></td>
                            <td class="px-6 py-4"><span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $c->id }}</span></td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $c->invoice?->patient?->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->insuranceCompany?->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->sent_date?->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-semibold text-green-700">@currency($c->approved_amount ?? 0)</td>
                            <td class="px-6 py-4"><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">{{ $c->status ?? '—' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مطالبات تأمين' : 'No insurance claims' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($insuranceClaims->hasPages())<div class="px-6 py-3 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">{{ $insuranceClaims->links() }}</div>@endif
    </div>
</div>

{{-- Charity Claims --}}
<div x-data="dataTable('charity')" x-init="init()">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'مطالبات الجمعيات الخيرية' : 'Charity Claims' }}</h3>
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-2">
                <span class="text-sm text-slate-600 font-medium"><span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}</span>
                <button @click="executeBulkAction('{{ route('charity-claims.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'حذف المحدد؟' : 'Delete?' }}')" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-red-600 text-white hover:bg-red-700">{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}</button>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-orange-700 to-orange-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="w-5 h-5 rounded border-2 border-white/30 text-orange-600 focus:ring-2 focus:ring-orange-500 cursor-pointer"></th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ __("Patients") }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Entity' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($charityClaims as $c)
                        <tr class="hover:bg-gradient-to-r hover:from-orange-50 hover:to-slate-50 transition-all" :class="{'bg-orange-50': selectedItems.includes({{ $c->id }})}">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $c->id }}" :checked="selectedItems.includes({{ $c->id }})" @change="toggleItem({{ $c->id }})" class="w-5 h-5 rounded border-2 border-slate-300 text-orange-600 focus:ring-2 focus:ring-orange-500 cursor-pointer"></td>
                            <td class="px-6 py-4"><span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $c->id }}</span></td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $c->invoice?->patient?->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->charityEntity?->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->sent_date?->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-semibold text-green-700">@currency($c->approved_amount ?? 0)</td>
                            <td class="px-6 py-4"><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">{{ $c->status ?? '—' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مطالبات جمعيات' : 'No charity claims' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($charityClaims->hasPages())<div class="px-6 py-3 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">{{ $charityClaims->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function dataTable(type){return{selectedItems:[],allSelected:false,type:type,init(){},toggleSelectAll(){if(this.allSelected){this.selectedItems=[];this.allSelected=false}else{const checkboxes=document.querySelectorAll('tbody input[type="checkbox"]');this.selectedItems=Array.from(checkboxes).map(cb=>parseInt(cb.value)).filter(id=>id>0);this.allSelected=true}},toggleItem(id){const index=this.selectedItems.indexOf(id);if(index>-1){this.selectedItems.splice(index,1)}else{this.selectedItems.push(id)}const totalCheckboxes=document.querySelectorAll('tbody input[type="checkbox"]').length;this.allSelected=this.selectedItems.length===totalCheckboxes&&totalCheckboxes>0},executeBulkAction(action,method,confirmMessage){if(this.selectedItems.length===0){alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');return}if(confirmMessage&&!confirm(confirmMessage))return;const form=document.createElement('form');form.method='POST';form.action=action;const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value='{{ csrf_token() }}';form.appendChild(csrf);if(method!=='POST'){const methodField=document.createElement('input');methodField.type='hidden';methodField.name='_method';methodField.value=method;form.appendChild(methodField)}this.selectedItems.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input)});document.body.appendChild(form);form.submit()}}}
</script>
@endpush
<style>[x-cloak]{display:none !important;}</style>
@endsection
