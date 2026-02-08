@extends('layouts.app')
@section('title', __('Services'))
@section('content')

<div x-data="dataTable()" x-init="init()">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Services') }}</h2>
            
            {{-- Bulk Actions --}}
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium">
                    <span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}
                </span>
                @can('services.manage')
                    <div class="flex gap-2">
                        <button 
                            @click="executeBulkAction('{{ route('services.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف الخدمات المحددة؟' : 'Are you sure you want to delete selected services?' }}')"
                            class="px-4 py-2 text-sm font-bold rounded-lg transition-all bg-red-600 text-white hover:bg-red-700 shadow-md">
                            {{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}
                        </button>
                    </div>
                @endcan
            </div>
        </div>

        @can('services.manage')
            <a href="{{ route('services.create') }}" 
               class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg text-sm font-bold hover:from-blue-700 hover:to-blue-800 shadow-lg flex items-center gap-2 transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ app()->getLocale() === 'ar' ? 'إضافة خدمة' : 'Add Service' }}
            </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <x-index-filters 
        :action="route('services.index')"
        :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم الخدمة، الكود...' : 'Service name, code...'">
        <div class="w-40">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}
            </label>
            <select name="department_id" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' && $dept->name_ar ? $dept->name_ar : $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-index-filters>
    
    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-slate-700 to-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4 w-16">
                            <input type="checkbox" @change="toggleSelectAll()" :checked="allSelected"
                                class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($services as $s)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150"
                            :class="{'bg-blue-50': selectedItems.includes({{ $s->id }})}">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $s->id }}" :checked="selectedItems.includes({{ $s->id }})"
                                    @change="toggleItem({{ $s->id }})"
                                    class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $s->id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? ($s->name_ar ?: $s->name) : $s->name }}</div>
                                @if(!$s->is_active)
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-semibold">{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-slate-600 bg-amber-50 px-2 py-1 rounded font-semibold">{{ $s->code }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-green-700">{{ number_format($s->default_price, 2) }}</td>
                            <td class="px-6 py-4">
                                @if($s->department)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        {{ app()->getLocale() === 'ar' ? ($s->department->name_ar ?: $s->department->name) : $s->department->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('services.manage')
                                        <a href="{{ route('services.show', $s) }}" class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 shadow-sm transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('services.edit', $s) }}" class="inline-flex items-center gap-1.5 bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('services.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذه الخدمة؟' : 'Are you sure you want to delete this service?' }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-red-700 shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-gradient-to-br from-slate-500 to-slate-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'لا توجد خدمات' : 'No Services Found' }}</h3>
                                    <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'لم يتم العثور على أي خدمات' : 'No services yet' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($services->hasPages())
            <div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function dataTable() {
    return {
        selectedItems: [],
        allSelected: false,
        init() {},
        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedItems = [];
                this.allSelected = false;
            } else {
                const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                this.selectedItems = Array.from(checkboxes).map(cb => parseInt(cb.value)).filter(id => id > 0);
                this.allSelected = true;
            }
        },
        toggleItem(id) {
            const index = this.selectedItems.indexOf(id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(id);
            }
            const totalCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]').length;
            this.allSelected = this.selectedItems.length === totalCheckboxes && totalCheckboxes > 0;
        },
        executeBulkAction(action, method, confirmMessage) {
            if (this.selectedItems.length === 0) {
                alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');
                return;
            }
            if (confirmMessage && !confirm(confirmMessage)) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            if (method !== 'POST') {
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = method;
                form.appendChild(methodField);
            }
            this.selectedItems.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
@endpush

<style>[x-cloak] { display: none !important; }</style>
@endsection
