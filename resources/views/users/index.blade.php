@extends('layouts.app')
@section('title', __('Users'))
@section('content')

<div x-data="dataTable()" x-init="init()">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الموظفين والصلاحيات' : 'Employees & Permissions' }}</h2>
            
            {{-- Bulk Actions (shown when items are selected) --}}
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium">
                    <span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}
                </span>
                @can('users.manage')
                    <div class="flex gap-2">
                        <button 
                            @click="executeBulkAction('{{ route('users.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف الموظفين المحددين؟' : 'Are you sure you want to delete selected employees?' }}')"
                            class="px-4 py-2 text-sm font-bold rounded-lg transition-all bg-red-600 text-white hover:bg-red-700 shadow-md">
                            {{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}
                        </button>
                    </div>
                @endcan
            </div>
        </div>

        @can('users.manage')
            <a href="{{ route('users.create') }}" 
               class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg text-sm font-bold hover:from-blue-700 hover:to-blue-800 shadow-lg flex items-center gap-2 transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ app()->getLocale() === 'ar' ? 'إضافة موظف' : 'Add Employee' }}
            </a>
        @endcan
    </div>

    {{-- Search and Filters --}}
    <x-index-filters 
        :action="route('users.index')"
        :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم المستخدم، البريد، الاسم...' : 'Username, email, name...'">
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
                        {{-- Select All Checkbox --}}
                        <th class="px-6 py-4 w-16">
                            <input 
                                type="checkbox" 
                                @change="toggleSelectAll()"
                                :checked="allSelected"
                                class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        </th>
                        
                        {{-- ID Column --}}
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                            {{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}
                        </th>
                        
                        {{-- Other Columns --}}
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ __('Username') }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الدور' : 'Role' }}</th>
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150"
                            :class="{'bg-blue-50': selectedItems.includes({{ $u->id }})}">
                            {{-- Checkbox --}}
                            <td class="px-6 py-4">
                                <input 
                                    type="checkbox" 
                                    value="{{ $u->id }}"
                                    :checked="selectedItems.includes({{ $u->id }})"
                                    @change="toggleItem({{ $u->id }})"
                                    class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </td>
                            
                            {{-- ID --}}
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">
                                    #{{ $u->id }}
                                </span>
                            </td>
                            
                            {{-- Username --}}
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $u->username }}</td>
                            
                            {{-- Employee --}}
                            <td class="px-6 py-4 text-slate-600">{{ $u->employee?->name ?? '-' }}</td>
                            
                            {{-- Department --}}
                            <td class="px-6 py-4">
                                @if($u->employee?->department)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        {{ app()->getLocale() === 'ar' && $u->employee->department->name_ar ? $u->employee->department->name_ar : $u->employee->department->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            
                            {{-- Role --}}
                            <td class="px-6 py-4">
                                @if($u->getRoleNames()->count() > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        {{ $u->getRoleNames()->join(', ') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            
                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @can('users.manage')
                                        <a href="{{ route('users.show', $u) }}" 
                                           class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 shadow-sm transition-all"
                                           title="{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('users.edit', $u) }}" 
                                           class="inline-flex items-center gap-1.5 bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm transition-all"
                                           title="{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا الموظف؟' : 'Are you sure you want to delete this employee?' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1.5 bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-red-700 shadow-sm transition-all"
                                                    title="{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700 mb-2">
                                        {{ app()->getLocale() === 'ar' ? 'لا يوجد مستخدمون' : 'No Users Found' }}
                                    </h3>
                                    <p class="text-sm text-slate-500">
                                        {{ app()->getLocale() === 'ar' ? 'لم يتم العثور على أي مستخدمين' : 'No users found' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                {{ $users->links() }}
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
        
        init() {
            // Initialize
        },
        
        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedItems = [];
                this.allSelected = false;
            } else {
                // Select all items on current page
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
            
            // Update select all checkbox state
            const totalCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]').length;
            this.allSelected = this.selectedItems.length === totalCheckboxes && totalCheckboxes > 0;
        },
        
        executeBulkAction(action, method, confirmMessage) {
            if (this.selectedItems.length === 0) {
                alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');
                return;
            }
            
            if (confirmMessage && !confirm(confirmMessage)) {
                return;
            }
            
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action;
            
            // Add CSRF token
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            // Add method if not POST
            if (method !== 'POST') {
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = method;
                form.appendChild(methodField);
            }
            
            // Add selected IDs
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

<style>
[x-cloak] { display: none !important; }
</style>

@endsection
