@extends('layouts.app')
@section('title', $sectionTitle ?? __('Patients'))
@section('content')
    @php
        $addBtnLabel = __('Add Patient');
        if(isset($section)) {
            $labels = [
                'charity' => app()->getLocale() === 'ar' ? 'إضافة مريض جمعية' : 'Add Charity Patient',
                'cash' => app()->getLocale() === 'ar' ? 'إضافة مريض كاش' : 'Add Cash Patient',
                'insurance' => app()->getLocale() === 'ar' ? 'إضافة مريض تأمين' : 'Add Insurance Patient',
            ];
            if(isset($labels[$section])) {
                $addBtnLabel = $labels[$section];
            }
        }
    @endphp
    @php
        $sectionColors = [
            'charity' => ['bg' => 'from-orange-500 to-orange-600', 'badge' => 'bg-orange-100 text-orange-800', 'icon' => '🤝'],
            'cash' => ['bg' => 'from-green-500 to-green-600', 'badge' => 'bg-green-100 text-green-800', 'icon' => '💵'],
            'insurance' => ['bg' => 'from-blue-500 to-blue-600', 'badge' => 'bg-blue-100 text-blue-800', 'icon' => '🏥'],
            'followup' => ['bg' => 'from-purple-500 to-purple-600', 'badge' => 'bg-purple-100 text-purple-800', 'icon' => '👤'],
            'collection' => ['bg' => 'from-red-500 to-red-600', 'badge' => 'bg-red-100 text-red-800', 'icon' => '💰'],
        ];
        $currentSection = $sectionColors[$section ?? 'charity'] ?? $sectionColors['charity'];
    @endphp
    
    @if(session('success'))<div class="mb-3 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    
    {{-- Enhanced Header with Section Color --}}
    <div class="bg-gradient-to-r {{ $currentSection['bg'] }} rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold mb-2 flex items-center gap-3">
                    <span class="text-4xl">{{ $currentSection['icon'] }}</span>
                    {{ $sectionTitle ?? __('Patients') }}
                </h2>
                <p class="text-white/80 text-sm">
                    {{ app()->getLocale() === 'ar' ? 'إدارة وعرض بيانات المرضى' : 'Manage and view patient data' }}
                </p>
            </div>
            @can('patients.create')
                <a href="{{ route('patients.create') }}" class="bg-white text-slate-800 px-6 py-3 rounded-lg text-sm font-bold hover:bg-slate-100 shadow-lg flex items-center gap-2 transition-all transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ $addBtnLabel }}
                </a>
            @endcan
        </div>
    </div>
    
    {{-- Search and Filter using Global Component --}}
    <x-index-filters :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم، رقم ملف، رقم هوية، إقامة، جواز...' : 'Name, file no, ID, Iqama, Passport...'">
        
        {{-- Charity Entity Filter (for charity section) --}}
        @if(isset($section) && $section === 'charity' && $charityEntities->isNotEmpty())
            <div class="w-48">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Charity Entity' }}
                </label>
                <select name="charity_entity_id" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الجمعيات' : 'All Charities' }}</option>
                    @foreach($charityEntities as $entity)
                        <option value="{{ $entity->id }}" {{ request('charity_entity_id') == $entity->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $entity->name_ar ? $entity->name_ar : $entity->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        
        {{-- Insurance Company Filter (for insurance section) --}}
        @if(isset($section) && $section === 'insurance' && $insuranceCompanies->isNotEmpty())
            <div class="w-48">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                </label>
                <select name="insurance_company_id" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الشركات' : 'All Companies' }}</option>
                    @foreach($insuranceCompanies as $company)
                        <option value="{{ $company->id }}" {{ request('insurance_company_id') == $company->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $company->name_ar ? $company->name_ar : $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        
        {{-- Gender Filter (for charity and insurance) --}}
        @if(isset($section) && in_array($section, ['charity', 'insurance', 'cash']))
            <div class="w-32">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }}
                </label>
                <select name="gender" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}</option>
                </select>
            </div>
        @endif
        
        {{-- Payment Type Filter (for followup and collection) --}}
        @if(isset($section) && in_array($section, ['followup', 'collection']))
            <div class="w-36">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                    {{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}
                </label>
                <select name="payment_type" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    <option value="cash" {{ request('payment_type') === 'cash' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'كاش' : 'Cash' }}</option>
                    <option value="insurance" {{ request('payment_type') === 'insurance' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</option>
                    <option value="charity" {{ request('payment_type') === 'charity' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'جمعية' : 'Charity' }}</option>
                </select>
            </div>
        @endif
    </x-index-filters>

    <div x-data="dataTable()" x-init="init()">
        {{-- Bulk Actions Bar --}}
        <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-3 mb-4 p-4 bg-white rounded-lg border border-blue-200 shadow-sm">
            <span class="text-sm text-slate-600 font-medium">
                <span x-text="selectedItems.length"></span> {{ app()->getLocale() === 'ar' ? 'محدد' : 'selected' }}
            </span>
            @can('patients.delete')
                <button @click="executeBulkAction('{{ route('patients.bulk-delete') }}', 'DELETE', '{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف المرضى المحددين؟' : 'Delete selected patients?' }}')"
                    class="px-4 py-2 text-sm font-bold rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md">
                    {{ app()->getLocale() === 'ar' ? 'حذف المحدد' : 'Delete Selected' }}
                </button>
            @endcan
        </div>

        <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r {{ $currentSection['bg'] }} text-white">
                        <tr>
                            <th class="px-6 py-4 w-16">
                                <input type="checkbox" @change="toggleSelectAll()" :checked="allSelected"
                                    class="w-5 h-5 rounded border-2 border-white/30 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                                {{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}
                            </th>
                            <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                                {{ __('Patients') }}
                            </th>
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                            {{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File No' }}
                        </th>
                        @if(isset($section) && $section === 'charity')
                            <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                                {{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Charity Entity' }}
                            </th>
                        @endif
                        @if(isset($section) && $section === 'insurance')
                            <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                                {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                            </th>
                        @endif
                        @if(!isset($section) || in_array($section, ['followup', 'collection']))
                            <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                                {{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment' }}
                            </th>
                        @endif
                        <th class="text-start px-6 py-4 font-bold uppercase tracking-wider text-sm">
                            {{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}
                        </th>
                        <th class="text-center px-6 py-4 font-bold uppercase tracking-wider text-sm">
                            {{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($patients as $p)
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-slate-50 transition-all duration-150"
                            :class="{'bg-blue-50': selectedItems.includes({{ $p->id }})}">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $p->id }}" :checked="selectedItems.includes({{ $p->id }})"
                                    @change="toggleItem({{ $p->id }})"
                                    class="w-5 h-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded text-sm">#{{ $p->id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900">{{ $p->name }}</span>
                                    @if($p->name_ar && $p->name_ar !== $p->name)
                                        <span class="text-xs text-slate-500" dir="rtl">{{ $p->name_ar }}</span>
                                    @endif
                                    @if($p->age || $p->gender)
                                        <span class="text-xs text-slate-400 mt-0.5">
                                            @if($p->age){{ $p->age }} {{ app()->getLocale() === 'ar' ? 'سنة' : 'years' }}@endif
                                            @if($p->age && $p->gender) • @endif
                                            @if($p->gender){{ app()->getLocale() === 'ar' ? ($p->gender === 'male' ? 'ذكر' : 'أنثى') : ucfirst($p->gender) }}@endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded">{{ $p->file_number }}</span>
                            </td>
                            @if(isset($section) && $section === 'charity')
                                <td class="px-6 py-4">
                                    @if($p->charityEntity)
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                            <span class="font-semibold text-orange-700">
                                                {{ app()->getLocale() === 'ar' && $p->charityEntity->name_ar ? $p->charityEntity->name_ar : $p->charityEntity->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                            @endif
                            @if(isset($section) && $section === 'insurance')
                                <td class="px-6 py-4">
                                    @if($p->insuranceCompany)
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                            <span class="font-semibold text-blue-700">
                                                {{ app()->getLocale() === 'ar' && $p->insuranceCompany->name_ar ? $p->insuranceCompany->name_ar : $p->insuranceCompany->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                            @endif
                            @if(!isset($section) || in_array($section, ['followup', 'collection']))
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                        {{ $p->payment_type === 'cash' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $p->payment_type === 'insurance' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $p->payment_type === 'charity' ? 'bg-orange-100 text-orange-800' : '' }}">
                                        {{ app()->getLocale() === 'ar' ? 
                                            ($p->payment_type === 'cash' ? 'كاش' : ($p->payment_type === 'insurance' ? 'تأمين' : 'جمعية')) : 
                                            ucfirst($p->payment_type) }}
                                    </span>
                                </td>
                            @endif
                            <td class="px-6 py-4 text-slate-600">
                                {{ $p->phone ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('patients.show', $p) }}" 
                                       class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 shadow-sm transition-all transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                    </a>
                                    <a href="{{ route('contact-reports.create', ['patient_id' => $p->id]) }}" 
                                       class="inline-flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm transition-all transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ app()->getLocale() === 'ar' ? 'محضر' : 'Report' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ isset($section) && in_array($section, ['charity', 'insurance']) ? 7 : (isset($section) && in_array($section, ['cash']) ? 6 : 7) }}" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-gradient-to-br {{ $currentSection['bg'] }} rounded-full flex items-center justify-center mb-4 shadow-lg">
                                        <span class="text-4xl">{{ $currentSection['icon'] }}</span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700 mb-2">
                                        {{ app()->getLocale() === 'ar' ? 'لا يوجد مرضى' : 'No Patients Found' }}
                                    </h3>
                                    <p class="text-sm text-slate-500">
                                        {{ app()->getLocale() === 'ar' ? 'لم يتم العثور على مرضى في هذا القسم' : 'No patients found in this section' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patients->hasPages())
            <div class="px-6 py-4 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function dataTable(){return{selectedItems:[],allSelected:false,init(){},toggleSelectAll(){if(this.allSelected){this.selectedItems=[];this.allSelected=false}else{const checkboxes=document.querySelectorAll('tbody input[type="checkbox"]');this.selectedItems=Array.from(checkboxes).map(cb=>parseInt(cb.value)).filter(id=>id>0);this.allSelected=true}},toggleItem(id){const index=this.selectedItems.indexOf(id);if(index>-1){this.selectedItems.splice(index,1)}else{this.selectedItems.push(id)}const totalCheckboxes=document.querySelectorAll('tbody input[type="checkbox"]').length;this.allSelected=this.selectedItems.length===totalCheckboxes&&totalCheckboxes>0},executeBulkAction(action,method,confirmMessage){if(this.selectedItems.length===0){alert('{{ app()->getLocale() === 'ar' ? "الرجاء تحديد عنصر واحد على الأقل" : "Please select at least one item" }}');return}if(confirmMessage&&!confirm(confirmMessage))return;const form=document.createElement('form');form.method='POST';form.action=action;const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value='{{ csrf_token() }}';form.appendChild(csrf);if(method!=='POST'){const methodField=document.createElement('input');methodField.type='hidden';methodField.name='_method';methodField.value=method;form.appendChild(methodField)}this.selectedItems.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input)});document.body.appendChild(form);form.submit()}}}
</script>
@endpush

<style>[x-cloak]{display:none !important;}</style>
@endsection
