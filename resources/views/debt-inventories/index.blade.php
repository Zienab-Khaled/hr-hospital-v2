@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'حصر ديون' : 'Debt Inventories')
@section('content')

<div x-data="dataTable()" x-init="init()">
    @if(session('success'))<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'حصر ديون' : 'Debt Inventories' }}</h2>
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium"><span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}</span>
                @can('procedures.debt_inventory')
                    <button @click="executeBulkAction('{{ route('debt-inventories.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'حذف الحصر المحدد؟' : 'Delete selected?' }}')" class="px-4 py-2 text-sm font-bold rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md">{{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}</button>
                @endcan
            </div>
        </div>
        @can('procedures.debt_inventory')
            <a href="{{ route('debt-inventories.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg text-sm font-bold hover:from-blue-700 hover:to-blue-800 shadow-lg flex items-center gap-2 transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ app()->getLocale() === 'ar' ? 'إضافة حصر ديون' : 'Add Inventory' }}
            </a>
        @endcan
    </div>

    <x-index-filters :action="route('debt-inventories.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم المريض، المبلغ...' : 'Patient name, amount...'"></x-index-filters>
    
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-700 to-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ __("Patients") }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'إجمالي الدين' : 'Total Debt' }}</th>
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $r)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150" :class="{'bg-blue-50': selectedItems.includes({{ $r->id }})}">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $r->id }}" :checked="selectedItems.includes({{ $r->id }})" @change="toggleItem({{ $r->id }})" class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></td>
                            <td class="px-6 py-4"><span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $r->id }}</span></td>
                            <td class="px-6 py-4 text-slate-600">{{ $r->inventory_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $r->patient?->name }}</td>
                            <td class="px-6 py-4 font-semibold text-red-700">@currency($r->total_debt)</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('debt-inventories.show', $r) }}" class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 shadow-sm transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-slate-500 to-slate-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'لا توجد حصر ديون' : 'No Debt Inventories Found' }}</h3>
                                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'لم يتم العثور على حصر ديون' : 'No debt inventories found' }}</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())<div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">{{ $items->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function dataTable(){return{selectedItems:[],allSelected:false,init(){},toggleSelectAll(){if(this.allSelected){this.selectedItems=[];this.allSelected=false}else{const checkboxes=document.querySelectorAll('tbody input[type="checkbox"]');this.selectedItems=Array.from(checkboxes).map(cb=>parseInt(cb.value)).filter(id=>id>0);this.allSelected=true}},toggleItem(id){const index=this.selectedItems.indexOf(id);if(index>-1){this.selectedItems.splice(index,1)}else{this.selectedItems.push(id)}const totalCheckboxes=document.querySelectorAll('tbody input[type="checkbox"]').length;this.allSelected=this.selectedItems.length===totalCheckboxes&&totalCheckboxes>0},executeBulkAction(action,method,confirmMessage){if(this.selectedItems.length===0){alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');return}if(confirmMessage&&!confirm(confirmMessage))return;const form=document.createElement('form');form.method='POST';form.action=action;const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value='{{ csrf_token() }}';form.appendChild(csrf);if(method!=='POST'){const methodField=document.createElement('input');methodField.type='hidden';methodField.name='_method';methodField.value=method;form.appendChild(methodField)}this.selectedItems.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input)});document.body.appendChild(form);form.submit()}}}
</script>
@endpush
<style>[x-cloak]{display:none !important;}</style>
@endsection
