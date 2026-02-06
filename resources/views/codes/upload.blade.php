@extends('layouts.app')
@section('title', __('Upload Official Codes'))
@section('content')
    @if(session('success'))<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'رفع الأكواد الرسمية من وزارة الصحة' : 'Upload Official Codes (Ministry of Health)' }}</h2>
    <div class="bg-white rounded-lg shadow p-6 max-w-xl mb-6">
        <p class="text-slate-600 text-sm mb-2">{{ app()->getLocale() === 'ar' ? 'صيغة CSV: عمود 1 = كود الخدمة، عمود 2 = اسم الخدمة، عمود 3 = السعر (ريال)، عمود 4 = رقم القسم (اختياري).' : 'CSV format: column 1 = service code, 2 = name, 3 = price (SAR), 4 = department id (optional).' }}</p>
    </div>
    <form action="{{ route('codes.upload.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 max-w-xl">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملف CSV أو Excel' : 'CSV or Excel file' }}</label>
            <input type="file" name="file" accept=".csv,.txt" required class="w-full rounded border border-slate-300 px-3 py-2 @error('file') border-red-500 @enderror">
            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'استيراد' : 'Import' }}</button>
    </form>
@endsection
