@extends('layouts.app')
@section('title', __('Upload Official Codes'))
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'رفع الأكواد الرسمية من وزارة الصحة' : 'Upload Official Codes (Ministry of Health)' }}</h2>
    <div class="bg-white rounded-lg shadow p-6 max-w-xl mb-6">
        <p class="text-slate-600 text-sm mb-2">{{ app()->getLocale() === 'ar' ? 'صيغة الملف (جدول مثل Excel): العمود الأول = كود الخدمة، الثاني = اسم الخدمة، الثالث = السعر (ريال)، الرابع = رقم القسم (اختياري، وإلا يُستخدم 1).' : 'File format (table like Excel): column 1 = service code, 2 = service name, 3 = price (SAR), 4 = department id (optional, default 1).' }}</p>
        <p class="text-slate-500 text-xs">{{ app()->getLocale() === 'ar' ? 'يدعم CSV و Excel (.xlsx, .xls). الملفات الكبيرة (مثل 9000 سجل) تتم معالجتها على دفعات وقد تستغرق دقيقة — لا تغلق الصفحة.' : 'Supports CSV and Excel (.xlsx, .xls). Large files (e.g. 9000 records) are processed in batches and may take a minute — do not close the page.' }}</p>
    </div>
    <form action="{{ route('codes.upload.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 max-w-xl" id="codes-upload-form">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملف CSV أو Excel' : 'CSV or Excel file' }}</label>
            <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required class="w-full rounded border border-slate-300 px-3 py-2 @error('file') border-red-500 @enderror">
            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" id="codes-upload-btn">{{ app()->getLocale() === 'ar' ? 'استيراد' : 'Import' }}</button>
    </form>
    <script>
        document.getElementById('codes-upload-form')?.addEventListener('submit', function () {
            var btn = document.getElementById('codes-upload-btn');
            if (btn) { btn.disabled = true; btn.textContent = {{ json_encode(app()->getLocale() === 'ar' ? 'جاري المعالجة...' : 'Processing...') }}; }
        });
    </script>
@endsection
