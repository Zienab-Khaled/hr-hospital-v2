@extends('layouts.app')
@section('title', __('Upload Reports to Cluster'))
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'رفع التقارير الرسمية للتجمع الصحي' : 'Upload Official Reports to Health Cluster' }}</h2>
    <form action="{{ route('reports.upload-cluster.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 max-w-xl">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الملف (PDF أو Excel)' : 'File (PDF or Excel)' }}</label>
            <input type="file" name="file" accept=".pdf,.xlsx,.xls" required class="w-full rounded border border-slate-300 px-3 py-2 @error('file') border-red-500 @enderror">
            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'رفع' : 'Upload' }}</button>
    </form>
@endsection
