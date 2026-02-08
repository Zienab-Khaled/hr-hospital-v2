@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'بحث عن مريض' : 'Patient Search')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-8 mb-6 text-white">
        <h1 class="text-3xl font-bold mb-2">
            {{ app()->getLocale() === 'ar' ? '🔍 بحث عن مريض' : '🔍 Patient Search' }}
        </h1>
        <p class="text-blue-100">
            {{ app()->getLocale() === 'ar' ? 'ابحث باستخدام الرقم القومي، رقم الإقامة، أو جواز السفر' : 'Search using National ID, Iqama Number, or Passport' }}
        </p>
    </div>

    {{-- Search Form --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('patients.search') }}" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'أدخل رقم الهوية / الإقامة / جواز السفر' : 'Enter National ID / Iqama / Passport Number' }}
                </label>
                <input 
                    type="text" 
                    name="identity" 
                    value="{{ request('identity') }}" 
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل الرقم...' : 'Enter number...' }}"
                    class="w-full px-4 py-3 border-2 border-slate-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                    autofocus>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 shadow-lg flex items-center justify-center gap-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
            </button>
        </form>
    </div>

    @if(isset($patient))
        {{-- Patient Found --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'تم العثور على المريض' : 'Patient Found' }}
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</p>
                        <p class="text-lg font-semibold">{{ $patient->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File Number' }}</p>
                        <p class="text-lg font-semibold">{{ $patient->file_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $patient->payment_type === 'cash' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $patient->payment_type === 'insurance' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $patient->payment_type === 'charity' ? 'bg-orange-100 text-orange-800' : '' }}">
                            {{ app()->getLocale() === 'ar' ? 
                                ($patient->payment_type === 'cash' ? 'كاش' : ($patient->payment_type === 'insurance' ? 'تأمين' : 'جمعية')) : 
                                ucfirst($patient->payment_type) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</p>
                        <p class="text-lg font-semibold">{{ $patient->phone ?: '-' }}</p>
                    </div>
                    
                    @if($patient->payment_type === 'insurance' && $patient->insuranceCompany)
                        <div class="md:col-span-2">
                            <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}</p>
                            <p class="text-lg font-semibold text-blue-600">{{ $patient->insuranceCompany->name }}</p>
                        </div>
                    @endif
                    
                    @if($patient->payment_type === 'charity' && $patient->charityEntity)
                        <div class="md:col-span-2">
                            <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'الجمعية الخيرية' : 'Charity Entity' }}</p>
                            <p class="text-lg font-semibold text-orange-600">{{ $patient->charityEntity->name }}</p>
                        </div>
                    @endif
                </div>

                {{-- Visit History --}}
                @if($patient->visits->count() > 0)
                    <div class="border-t pt-4">
                        <h4 class="font-semibold text-slate-800 mb-3">
                            {{ app()->getLocale() === 'ar' ? 'سجل الزيارات السابقة' : 'Visit History' }}
                            ({{ $patient->visits->count() }})
                        </h4>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach($patient->visits->take(5) as $visit)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded">
                                    <span class="text-sm">{{ $visit->visit_date->format('Y-m-d') }}</span>
                                    <span class="text-sm text-slate-600">{{ $visit->notes ?? '-' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('contact-reports.create', ['patient_id' => $patient->id]) }}" 
                       class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 shadow text-center">
                        {{ app()->getLocale() === 'ar' ? '📝 إنشاء محضر اتصال' : '📝 Create Contact Report' }}
                    </a>
                    <a href="{{ route('patients.show', $patient) }}" 
                       class="flex-1 bg-slate-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-700 shadow text-center">
                        {{ app()->getLocale() === 'ar' ? '👁 عرض التفاصيل' : '👁 View Details' }}
                    </a>
                </div>
            </div>
        </div>
    @elseif(request()->has('identity'))
        {{-- Patient Not Found --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-yellow-500 text-white px-6 py-4">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'لم يتم العثور على المريض' : 'Patient Not Found' }}
                </h3>
            </div>
            
            <div class="p-6">
                <p class="text-slate-700 mb-6">
                    {{ app()->getLocale() === 'ar' ? 'هذا المريض غير مسجل في النظام. يمكنك تسجيله كمريض جديد.' : 'This patient is not registered in the system. You can register them as a new patient.' }}
                </p>
                
                <a href="{{ route('patients.create', ['identity' => request('identity')]) }}" 
                   class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 shadow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'تسجيل مريض جديد' : 'Register New Patient' }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
