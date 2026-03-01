@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إضافة موظف' : 'Add Employee')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'إضافة موظف' : 'Add Employee' }}</h2>
        <a href="{{ route('users.index') }}" class="text-slate-600 hover:text-slate-800 text-sm">{{ app()->getLocale() === 'ar' ? '← العودة' : '← Back' }}</a>
    </div>

    @if($departments->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
            {{ app()->getLocale() === 'ar' ? 'لا توجد أقسام نشطة. يرجى إضافة قسم أولاً.' : 'No active departments found. Please add a department first.' }}
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 max-w-2xl">
        @csrf
        
        {{-- Employee Information Section --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                {{ app()->getLocale() === 'ar' ? 'معلومات الموظف' : 'Employee Information' }}
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }} <span class="text-red-500">*</span></label>
                    <select name="department_id" required class="w-full rounded border border-slate-300 px-3 py-2 @error('department_id') border-red-500 @enderror" {{ $departments->isEmpty() ? 'disabled' : '' }}>
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر قسم --' : '-- Select Department --' }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' && $dept->name_ar ? $dept->name_ar : $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('name_ar') border-red-500 @enderror">
                    @error('name_ar')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي (إنجليزي)' : 'Job Title (English)' }}</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('job_title') border-red-500 @enderror">
                    @error('job_title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي (عربي)' : 'Job Title (Arabic)' }}</label>
                    <input type="text" name="job_title_ar" value="{{ old('job_title_ar') }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('job_title_ar') border-red-500 @enderror">
                    @error('job_title_ar')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" class="w-full rounded border border-slate-300 px-3 py-2 @error('status') border-red-500 @enderror">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</option>
                    </select>
                    @error('status')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- User Account Section --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                {{ app()->getLocale() === 'ar' ? 'حساب المستخدم' : 'User Account' }}
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم المستخدم' : 'Username' }} <span class="text-red-500">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('username') border-red-500 @enderror">
                    @error('username')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('email') border-red-500 @enderror">
                    @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }} <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required class="w-full rounded border border-slate-300 px-3 py-2 @error('password') border-red-500 @enderror">
                    @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }} <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required class="w-full rounded border border-slate-300 px-3 py-2">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الدور' : 'Role' }} <span class="text-red-500">*</span></label>
                    <select name="role" required class="w-full rounded border border-slate-300 px-3 py-2 @error('role') border-red-500 @enderror">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر دور --' : '-- Select Role --' }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2 pt-4 border-t border-slate-200">
                    <x-signature-pad
                        name="signature_data"
                        :label="(app()->getLocale() === 'ar' ? 'التوقيع الإلكتروني للموظف (اختياري)' : 'Employee electronic signature (optional)')"
                    />
                    @error('signature_data')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" {{ $departments->isEmpty() ? 'disabled' : '' }}>
                {{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}
            </button>
            <a href="{{ route('users.index') }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded hover:bg-slate-300">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
        </div>
    </form>
@endsection
