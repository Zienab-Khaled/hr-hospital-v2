@extends('layouts.app')
@section('title', __('Authorizations'))
@section('content')

<div x-data="dataTable()" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Authorizations') }}</h2>
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium"><span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}</span>
                <button @click="executeBulkAction('{{ route('authorizations.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'حذف الموافقات المحددة؟' : 'Delete selected?' }}')" class="px-4 py-2 text-sm font-bold rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md">{{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}</button>
            </div>
        </div>
    </div>

    <x-index-filters :action="route('authorizations.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'رقم المرجع، اسم المريض...' : 'Reference, patient name...'"></x-index-filters>
    
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-700 to-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الرقم المرجعي' : 'Reference' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ __("Patients") }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'تاريخ الإصدار' : 'Issue date' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'تاريخ الانتهاء' : 'Expiry' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($authorizations as $auth)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150" :class="{'bg-blue-50': selectedItems.includes({{ $auth->id }})}">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $auth->id }}" :checked="selectedItems.includes({{ $auth->id }})" @change="toggleItem({{ $auth->id }})" class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></td>
                            <td class="px-6 py-4"><span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $auth->id }}</span></td>
                            <td class="px-6 py-4"><span class="font-mono text-slate-600 bg-amber-50 px-2 py-1 rounded font-semibold">{{ $auth->reference_number ?? '—' }}</span></td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $auth->patient?->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $auth->issue_date?->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $auth->expiry_date?->format('Y-m-d') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                                    {{ $auth->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $auth->status ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('authorizations.show', $auth) }}" class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 shadow-sm transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-slate-500 to-slate-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'لا توجد موافقات' : 'No Authorizations Found' }}</h3>
                                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'لم يتم العثور على موافقات' : 'No authorizations yet' }}</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($authorizations->hasPages())<div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">{{ $authorizations->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function dataTable(){return{selectedItems:[],allSelected:false,init(){},toggleSelectAll(){if(this.allSelected){this.selectedItems=[];this.allSelected=false}else{const checkboxes=document.querySelectorAll('tbody input[type="checkbox"]');this.selectedItems=Array.from(checkboxes).map(cb=>parseInt(cb.value)).filter(id=>id>0);this.allSelected=true}},toggleItem(id){const index=this.selectedItems.indexOf(id);if(index>-1){this.selectedItems.splice(index,1)}else{this.selectedItems.push(id)}const totalCheckboxes=document.querySelectorAll('tbody input[type="checkbox"]').length;this.allSelected=this.selectedItems.length===totalCheckboxes&&totalCheckboxes>0},executeBulkAction(action,method,confirmMessage){if(this.selectedItems.length===0){alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');return}if(confirmMessage&&!confirm(confirmMessage))return;const form=document.createElement('form');form.method='POST';form.action=action;const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value='{{ csrf_token() }}';form.appendChild(csrf);if(method!=='POST'){const methodField=document.createElement('input');methodField.type='hidden';methodField.name='_method';methodField.value=method;form.appendChild(methodField)}this.selectedItems.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input)});document.body.appendChild(form);form.submit()}}}
</script>
@endpush
<style>[x-cloak]{display:none !important;}</style>
@endsection
