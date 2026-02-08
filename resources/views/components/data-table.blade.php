@props([
    'title' => '',
    'addRoute' => null,
    'addLabel' => null,
    'canAdd' => false,
    'searchAction' => '',
    'searchPlaceholder' => '',
    'items',
    'columns' => [],
    'bulkActions' => [],
    'emptyMessage' => '',
])

@php
    $isRtl = app()->getLocale() === 'ar';
    $defaultEmptyMessage = $isRtl ? 'لا توجد بيانات' : 'No data found';
@endphp

<div x-data="dataTable()" x-init="init()">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-slate-800">{{ $title }}</h2>
            
            {{-- Bulk Actions (shown when items are selected) --}}
            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3">
                <span class="text-sm text-slate-600 font-medium">
                    <span x-text="selectedItems.length"></span> {{ $isRtl ? 'محدد' : 'selected' }}
                </span>
                @if(count($bulkActions) > 0)
                    <div class="flex gap-2">
                        @foreach($bulkActions as $action)
                            <button 
                                @click="executeBulkAction('{{ $action['action'] }}', '{{ $action['method'] ?? 'POST' }}', '{{ $action['confirm'] ?? '' }}')"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $action['class'] ?? 'bg-red-600 text-white hover:bg-red-700' }}">
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if($canAdd && $addRoute)
            <a href="{{ $addRoute }}" 
               class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg text-sm font-bold hover:from-blue-700 hover:to-blue-800 shadow-lg flex items-center gap-2 transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $addLabel }}
            </a>
        @endif
    </div>

    {{-- Search and Filters --}}
    @if($searchAction)
        <x-index-filters :action="$searchAction" :searchPlaceholder="$searchPlaceholder">
            {{ $filters ?? '' }}
        </x-index-filters>
    @endif

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
                            {{ $isRtl ? 'المعرف' : 'ID' }}
                        </th>
                        
                        {{-- Dynamic Columns --}}
                        @foreach($columns as $column)
                            <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                                {{ $column['label'] }}
                            </th>
                        @endforeach
                        
                        {{-- Actions Column --}}
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">
                            {{ $isRtl ? 'الإجراءات' : 'Actions' }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150"
                            :class="{'bg-blue-50': selectedItems.includes({{ $item->id }})}">
                            {{-- Checkbox --}}
                            <td class="px-6 py-4">
                                <input 
                                    type="checkbox" 
                                    :checked="selectedItems.includes({{ $item->id }})"
                                    @change="toggleItem({{ $item->id }})"
                                    class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </td>
                            
                            {{-- ID --}}
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">
                                    #{{ $item->id }}
                                </span>
                            </td>
                            
                            {{-- Dynamic Columns --}}
                            @foreach($columns as $column)
                                <td class="px-6 py-4">
                                    @if(isset($column['slot']))
                                        {{ $column['slot']($item) }}
                                    @else
                                        {{ data_get($item, $column['field']) }}
                                    @endif
                                </td>
                            @endforeach
                            
                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                {{ $actions($item) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + 3 }}" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-gradient-to-br from-slate-500 to-slate-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700 mb-2">
                                        {{ $emptyMessage ?: $defaultEmptyMessage }}
                                    </h3>
                                    <p class="text-sm text-slate-500">
                                        {{ $isRtl ? 'لم يتم العثور على أي بيانات' : 'No data found' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($items->hasPages())
            <div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                {{ $items->links() }}
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
                this.selectedItems = Array.from(document.querySelectorAll('tbody input[type="checkbox"]'))
                    .map(checkbox => parseInt(checkbox.closest('tr').querySelector('input[type="checkbox"]').value || 0))
                    .filter(id => id > 0);
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
                alert('{{ $isRtl ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');
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
