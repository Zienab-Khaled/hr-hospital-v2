@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'محضر اتصال جديد' : 'New Contact Report')
@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-slate-800 mb-6">
            {{ app()->getLocale() === 'ar' ? '📋 محضر اتصال جديد' : '📋 New Contact Report' }}
        </h2>
        
        @if(isset($patient))
            {{-- Patient Information Display --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-3">
                    {{ app()->getLocale() === 'ar' ? 'معلومات المريض' : 'Patient Information' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700 font-medium">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span>
                        <span class="text-slate-900">{{ $patient->name }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span>
                        <span class="text-slate-900">{{ $patient->file_number }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">{{ app()->getLocale() === 'ar' ? 'نوع الدفع:' : 'Payment:' }}</span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $patient->payment_type === 'cash' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $patient->payment_type === 'insurance' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $patient->payment_type === 'charity' ? 'bg-orange-100 text-orange-800' : '' }}">
                            {{ app()->getLocale() === 'ar' ? 
                                ($patient->payment_type === 'cash' ? 'نقدي' : ($patient->payment_type === 'insurance' ? 'تأمين' : 'جمعية')) : 
                                ucfirst($patient->payment_type) }}
                        </span>
                    </div>
                    @if($patient->payment_type === 'insurance' && $patient->insuranceCompany)
                        <div class="md:col-span-3">
                            <span class="text-blue-700 font-medium">{{ app()->getLocale() === 'ar' ? 'شركة التأمين:' : 'Insurance:' }}</span>
                            <span class="text-slate-900 font-semibold">{{ app()->getLocale() === 'ar' ? ($patient->insuranceCompany->name_ar ?? $patient->insuranceCompany->name) : $patient->insuranceCompany->name }}</span>
                        </div>
                    @endif
                    @if($patient->payment_type === 'charity' && $patient->charityEntity)
                        <div class="md:col-span-3">
                            <span class="text-blue-700 font-medium">{{ app()->getLocale() === 'ar' ? 'الجمعية:' : 'Charity:' }}</span>
                            <span class="text-slate-900 font-semibold">{{ $patient->charityEntity->name_ar ?: $patient->charityEntity->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        <form action="{{ route('contact-reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            @if(isset($patient))
                <input type="hidden" name="patient_id" value="{{ $patient->id }}">
            @else
                {{-- Patient Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }} *
                    </label>
                    <select name="patient_id" required class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 @error('patient_id') border-red-500 @enderror focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر المريض --' : '-- Select Patient --' }}</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->file_number }}) - {{ ucfirst($p->payment_type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif
            
            {{-- Contact Date --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'تاريخ الاتصال' : 'Contact Date' }} *
                </label>
                <input type="date" name="contact_date" value="{{ old('contact_date', date('Y-m-d')) }}" required 
                       class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('contact_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            
            {{-- Result/Outcome --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'نتيجة الاتصال' : 'Contact Result' }}
                </label>
                <input type="text" name="result" value="{{ old('result') }}" 
                       placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: تم توجيه المريض للمختبر' : 'Example: Patient directed to lab' }}"
                       class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            {{-- Notes --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}
                </label>
                <textarea name="notes" rows="4" 
                          placeholder="{{ app()->getLocale() === 'ar' ? 'أضف أي ملاحظات إضافية...' : 'Add any additional notes...' }}"
                          class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>
            
            {{-- Document Uploads --}}
            <div class="border-t border-slate-200 pt-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">
                    {{ app()->getLocale() === 'ar' ? '📎 المستندات المرفقة' : '📎 Attached Documents' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'مستندات عامة' : 'General Documents' }}
                        </label>
                        <input type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-slate-500 mt-2">
                            {{ app()->getLocale() === 'ar' ? 'يمكنك رفع عدة ملفات (PDF, JPG, PNG - بحد أقصى 10 ميجا لكل ملف)' : 'You can upload multiple files (PDF, JPG, PNG - max 10MB per file)' }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'بيانات المريض الورقية (مسح ضوئي)' : 'Patient Paper Data (Scanned)' }}
                        </label>
                        <input type="file" name="patient_papers[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                        <p class="text-xs text-slate-500 mt-2">
                            {{ app()->getLocale() === 'ar' ? 'ارفع المستندات الورقية الممسوحة ضوئياً للمريض' : 'Upload scanned paper documents for the patient' }}
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- Referral Section --}}
            <div class="border-t border-slate-200 pt-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">
                    {{ app()->getLocale() === 'ar' ? '👤 تحويل إلى موظف متخصص' : '👤 Refer to Specialist' }}
                </h3>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'تحويل إلى (اختياري)' : 'Refer To (Optional)' }}
                    </label>
                    <select name="referred_to" class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- لا يوجد تحويل --' : '-- No Referral --' }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('referred_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} @if($user->employee) - {{ $user->employee->job_title ?? '' }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-2">
                        {{ app()->getLocale() === 'ar' ? 'اختر موظف متخصص لتحويل المريض إليه (مثال: تأمين، جمعيات، محاسبة)' : 'Select a specialist to refer the patient to (e.g., insurance, charity, accounting)' }}
                    </p>
                </div>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-6 border-t border-slate-200">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'حفظ المحضر' : 'Save Report' }}
                </button>
                <a href="{{ route('contact-reports.index') }}" class="bg-slate-200 text-slate-700 px-8 py-3 rounded-lg font-semibold hover:bg-slate-300">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
