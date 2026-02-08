@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'شركات التأمين' : 'Insurance Companies')
@section('content')

<div x-data="dataTable()" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'شركات التأمين' : 'Insurance Companies' }}</h2>
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium">
                    <span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}
                </span>
                @can('insurance_companies.manage')
                    <button @click="executeBulkAction('{{ route('insurance-companies.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف الشركات المحددة؟' : 'Delete selected companies?' }}')"
                        class="px-4 py-2 text-sm font-bold rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md">
                        {{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}
                    </button>
                @endcan
            </div>
        </div>
        @can('insurance_companies.manage')
            <a href="{{ route('insurance-companies.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg text-sm font-bold hover:from-blue-700 hover:to-blue-800 shadow-lg flex items-center gap-2 transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ app()->getLocale() === 'ar' ? 'إضافة شركة تأمين' : 'Add Insurance Company' }}
            </a>
        @endcan
    </div>

    <x-index-filters :action="route('insurance-companies.index')"
        :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم الشركة، جهة الاتصال، الهاتف...' : 'Company name, contact, phone...'">
    </x-index-filters>
    
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-700 to-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'جهة الاتصال' : 'Contact' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المرضى' : 'Patients' }}</th>
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($companies as $c)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150" :class="{'bg-blue-50': selectedItems.includes({{ $c->id }})}">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $c->id }}" :checked="selectedItems.includes({{ $c->id }})" @change="toggleItem({{ $c->id }})" class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></td>
                            <td class="px-6 py-4"><span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $c->id }}</span></td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? ($c->name_ar ?: $c->name) : $c->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->contact_person ?: '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->phone ?: '-' }}</td>
                            <td class="px-6 py-4"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">{{ $c->patients_count }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('insurance_companies.manage')
                                        <a href="{{ route('insurance-companies.show', $c) }}" class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 shadow-sm transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('insurance-companies.edit', $c) }}" class="inline-flex items-center gap-1.5 bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('insurance-companies.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد؟' : 'Are you sure?' }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-red-700 shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($companies->hasPages())
            <div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">{{ $companies->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function dataTable() {
    return {selectedItems:[],allSelected:false,init(){},toggleSelectAll(){if(this.allSelected){this.selectedItems=[];this.allSelected=false}else{const checkboxes=document.querySelectorAll('tbody input[type="checkbox"]');this.selectedItems=Array.from(checkboxes).map(cb=>parseInt(cb.value)).filter(id=>id>0);this.allSelected=true}},toggleItem(id){const index=this.selectedItems.indexOf(id);if(index>-1){this.selectedItems.splice(index,1)}else{this.selectedItems.push(id)}const totalCheckboxes=document.querySelectorAll('tbody input[type="checkbox"]').length;this.allSelected=this.selectedItems.length===totalCheckboxes&&totalCheckboxes>0},executeBulkAction(action,method,confirmMessage){if(this.selectedItems.length===0){alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');return}if(confirmMessage&&!confirm(confirmMessage))return;const form=document.createElement('form');form.method='POST';form.action=action;const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value='{{ csrf_token() }}';form.appendChild(csrf);if(method!=='POST'){const methodField=document.createElement('input');methodField.type='hidden';methodField.name='_method';methodField.value=method;form.appendChild(methodField)}this.selectedItems.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input)});document.body.appendChild(form);form.submit()}}
}
</script>
@endpush
<style>[x-cloak]{display:none !important;}</style>
@endsection
