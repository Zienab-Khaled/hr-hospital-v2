@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إنشاء فاتورة جديدة' : 'Create New Invoice')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-slate-800 mb-6">
            {{ app()->getLocale() === 'ar' ? '🧾 إنشاء فاتورة جديدة' : '🧾 Create New Invoice' }}
        </h2>
        
        @if(isset($patient))
            {{-- Patient Information Display --}}
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-300 rounded-lg p-5 mb-6 shadow-sm">
                <h3 class="font-bold text-blue-900 mb-3 text-lg">
                    {{ app()->getLocale() === 'ar' ? '👤 معلومات المريض' : '👤 Patient Information' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-3 rounded shadow-sm">
                        <span class="text-blue-700 font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span>
                        <p class="text-slate-900 font-bold">{{ $patient->name }}</p>
                    </div>
                    <div class="bg-white p-3 rounded shadow-sm">
                        <span class="text-blue-700 font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span>
                        <p class="text-slate-900 font-bold">{{ $patient->file_number }}</p>
                    </div>
                    <div class="bg-white p-3 rounded shadow-sm">
                        <span class="text-blue-700 font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'نوع الدفع:' : 'Payment:' }}</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                            {{ $patient->payment_type === 'cash' ? 'bg-green-500 text-white' : '' }}
                            {{ $patient->payment_type === 'insurance' ? 'bg-blue-500 text-white' : '' }}
                            {{ $patient->payment_type === 'charity' ? 'bg-orange-500 text-white' : '' }}">
                            {{ app()->getLocale() === 'ar' ? 
                                ($patient->payment_type === 'cash' ? 'نقدي' : ($patient->payment_type === 'insurance' ? 'تأمين' : 'جمعية')) : 
                                ucfirst($patient->payment_type) }}
                        </span>
                    </div>
                </div>
                @if($patient->payment_type === 'insurance' && $patient->insuranceCompany)
                    <div class="mt-3 bg-blue-600 text-white p-3 rounded shadow-sm">
                        <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'شركة التأمين:' : 'Insurance:' }}</span>
                        <span class="font-bold">{{ $patient->insuranceCompany->name }}</span>
                    </div>
                @endif
                @if($patient->payment_type === 'charity' && $patient->charityEntity)
                    <div class="mt-3 bg-orange-600 text-white p-3 rounded shadow-sm">
                        <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'الجمعية:' : 'Charity:' }}</span>
                        <span class="font-bold">{{ $patient->charityEntity->name }}</span>
                    </div>
                @endif
            </div>
        @endif
        
        <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form" class="space-y-6">
            @csrf
            
            @if(isset($patient))
                <input type="hidden" name="patient_id" value="{{ $patient->id }}">
            @else
                {{-- Patient Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }} *
                    </label>
                    <select name="patient_id" required class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر المريض --' : '-- Select Patient --' }}</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->file_number }})</option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            {{-- Invoice Date --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'تاريخ الفاتورة' : 'Invoice Date' }} *
                    </label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required 
                           class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            {{-- Services Section --}}
            <div class="border-2 border-blue-300 rounded-lg p-6 bg-gradient-to-br from-blue-50 to-slate-50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-slate-800">
                        {{ app()->getLocale() === 'ar' ? '🏥 الخدمات' : '🏥 Services' }}
                    </h3>
                    <button type="button" onclick="addServiceRow()" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ app()->getLocale() === 'ar' ? 'إضافة خدمة' : 'Add Service' }}
                    </button>
                </div>
                
                <div id="services-container" class="space-y-3"></div>
                
                {{-- Total Section --}}
                <div class="mt-6 pt-6 border-t-2 border-slate-300">
                    <div class="flex justify-between items-center bg-gradient-to-r from-slate-800 to-slate-700 text-white p-4 rounded-lg shadow-lg">
                        <span class="text-xl font-bold">
                            {{ app()->getLocale() === 'ar' ? 'المجموع الإجمالي:' : 'Total Amount:' }}
                        </span>
                        <span id="grand-total" class="text-3xl font-bold">0.00</span>
                    </div>
                </div>
            </div>
            
            {{-- Notes --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}
                </label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-6 border-t-2 border-slate-200">
                <button type="submit" class="bg-blue-600 text-white px-8 py-4 rounded-lg text-lg font-bold hover:bg-blue-700 shadow-lg flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'إنشاء الفاتورة' : 'Create Invoice' }}
                </button>
                <a href="{{ route('invoices.index') }}" class="bg-slate-200 text-slate-700 px-8 py-4 rounded-lg text-lg font-bold hover:bg-slate-300">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Services Data for JavaScript --}}
<script>
    const services = @json($services->map(function($service) {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'name_ar' => $service->name_ar,
            'code' => $service->code,
            'default_price' => $service->default_price,
        ];
    }));
    
    let serviceRowIndex = 0;
    
    function addServiceRow() {
        const container = document.getElementById('services-container');
        const index = serviceRowIndex++;
        const isArabic = '{{ app()->getLocale() }}' === 'ar';
        
        const row = document.createElement('div');
        row.className = 'service-row bg-white p-4 rounded-lg shadow border border-slate-200';
        row.innerHTML = `
            <div class="grid grid-cols-12 gap-3 items-start">
                <div class="col-span-12 md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">${isArabic ? 'الخدمة' : 'Service'}</label>
                    <select name="services[${index}][service_id]" required onchange="updateServicePrice(this, ${index})" 
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">${isArabic ? '-- اختر --' : '-- Select --'}</option>
                        ${services.map(s => `<option value="${s.id}" data-price="${s.default_price}" data-code="${s.code}">${isArabic && s.name_ar ? s.name_ar : s.name} ${s.code ? '(' + s.code + ')' : ''}</option>`).join('')}
                    </select>
                </div>
                <div class="col-span-6 md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">${isArabic ? 'الكمية' : 'Qty'}</label>
                    <input type="number" name="services[${index}][quantity]" value="1" min="1" required 
                           onchange="calculateRowTotal(${index})" 
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-6 md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">${isArabic ? 'السعر' : 'Price'}</label>
                    <input type="number" name="services[${index}][unit_price]" step="0.01" min="0" required 
                           onchange="calculateRowTotal(${index})" 
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-6 md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">${isArabic ? 'المجموع' : 'Total'}</label>
                    <input type="number" name="services[${index}][total_price]" step="0.01" readonly 
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm bg-slate-100 font-bold text-blue-600">
                </div>
                <div class="col-span-12 md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">${isArabic ? 'ملاحظات' : 'Notes'}</label>
                    <input type="text" name="services[${index}][description]" placeholder="${isArabic ? 'اختياري...' : 'Optional...'}" 
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-12 md:col-span-1 flex items-end justify-end md:justify-center">
                    <button type="button" onclick="removeServiceRow(this)" 
                            class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 shadow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(row);
    }
    
    function updateServicePrice(select, index) {
        const option = select.options[select.selectedIndex];
        const price = option.dataset.price || 0;
        const row = select.closest('.service-row');
        const priceInput = row.querySelector(`input[name="services[${index}][unit_price]"]`);
        priceInput.value = price;
        calculateRowTotal(index);
    }
    
    function calculateRowTotal(index) {
        const container = document.getElementById('services-container');
        const rows = container.querySelectorAll('.service-row');
        const row = rows[index];
        
        if (!row) return;
        
        const qty = parseFloat(row.querySelector(`input[name="services[${index}][quantity]"]`).value) || 0;
        const price = parseFloat(row.querySelector(`input[name="services[${index}][unit_price]"]`).value) || 0;
        const total = qty * price;
        
        row.querySelector(`input[name="services[${index}][total_price]"]`).value = total.toFixed(2);
        
        calculateGrandTotal();
    }
    
    function calculateGrandTotal() {
        const container = document.getElementById('services-container');
        const totalInputs = container.querySelectorAll('input[name*="[total_price]"]');
        let grandTotal = 0;
        
        totalInputs.forEach(input => {
            grandTotal += parseFloat(input.value) || 0;
        });
        
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }
    
    function removeServiceRow(button) {
        button.closest('.service-row').remove();
        calculateGrandTotal();
    }
    
    // Add first service row on page load
    document.addEventListener('DOMContentLoaded', function() {
        addServiceRow();
    });
    
    // Form validation
    document.getElementById('invoice-form').addEventListener('submit', function(e) {
        const container = document.getElementById('services-container');
        const rows = container.querySelectorAll('.service-row');
        
        if (rows.length === 0) {
            e.preventDefault();
            alert('{{ app()->getLocale() === 'ar' ? 'يجب إضافة خدمة واحدة على الأقل' : 'Please add at least one service' }}');
            return false;
        }
    });
</script>
@endsection
