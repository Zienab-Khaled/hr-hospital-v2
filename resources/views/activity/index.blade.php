@extends('layouts.app')
@section('title', __('Activity Log'))
@section('content')

<div x-data="dataTable()" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Activity Log') }}</h2>
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium"><span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}</span>
                <button @click="executeBulkAction('{{ route('activity.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'حذف السجلات المحددة؟' : 'Delete selected logs?' }}')" class="px-4 py-2 text-sm font-bold rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md">{{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}</button>
            </div>
        </div>
    </div>
    
    <p class="text-sm text-slate-600 mb-4">{{ app()->getLocale() === 'ar' ? 'جميع الإجراءات التي تمت في النظام (إنشاء، تعديل، حذف، رفع ملفات، إلخ).' : 'All actions performed in the system (create, update, delete, uploads, etc.).' }}</p>

    <x-index-filters :action="route('activity.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'الموظف، الإجراء، الوصف...' : 'User, action, description...'"></x-index-filters>

    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-700 to-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16"><input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'التاريخ والوقت' : 'Date & Time' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'User' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الإجراء' : 'Action' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150" :class="{'bg-blue-50': selectedItems.includes({{ $log->id }})}">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $log->id }}" :checked="selectedItems.includes({{ $log->id }})" @change="toggleItem({{ $log->id }})" class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"></td>
                            <td class="px-6 py-4"><span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $log->id }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                {{ $log->created_at->format('Y-m-d') }}<br>
                                <span class="text-xs">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $log->user?->employee?->name ?? $log->user?->name ?? $log->user?->username ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 rounded text-xs font-bold
                                    @if(str_contains($log->action, 'created')) bg-green-100 text-green-800
                                    @elseif(str_contains($log->action, 'updated') || str_contains($log->action, 'uploaded')) bg-blue-100 text-blue-800
                                    @elseif(str_contains($log->action, 'deleted')) bg-red-100 text-red-800
                                    @else bg-slate-100 text-slate-800
                                    @endif">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ Str::limit($log->description, 60) }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-slate-500 to-slate-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No Activity Logs' }}</h3>
                                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'لم يتم العثور على سجلات نشاط' : 'No activity logs yet' }}</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())<div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">{{ $logs->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function dataTable(){return{selectedItems:[],allSelected:false,init(){},toggleSelectAll(){if(this.allSelected){this.selectedItems=[];this.allSelected=false}else{const checkboxes=document.querySelectorAll('tbody input[type="checkbox"]');this.selectedItems=Array.from(checkboxes).map(cb=>parseInt(cb.value)).filter(id=>id>0);this.allSelected=true}},toggleItem(id){const index=this.selectedItems.indexOf(id);if(index>-1){this.selectedItems.splice(index,1)}else{this.selectedItems.push(id)}const totalCheckboxes=document.querySelectorAll('tbody input[type="checkbox"]').length;this.allSelected=this.selectedItems.length===totalCheckboxes&&totalCheckboxes>0},executeBulkAction(action,method,confirmMessage){if(this.selectedItems.length===0){alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');return}if(confirmMessage&&!confirm(confirmMessage))return;const form=document.createElement('form');form.method='POST';form.action=action;const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value='{{ csrf_token() }}';form.appendChild(csrf);if(method!=='POST'){const methodField=document.createElement('input');methodField.type='hidden';methodField.name='_method';methodField.value=method;form.appendChild(methodField)}this.selectedItems.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input)});document.body.appendChild(form);form.submit()}}}
</script>
@endpush
<style>[x-cloak]{display:none !important;}</style>
@endsection
