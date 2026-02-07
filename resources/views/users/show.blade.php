@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الموظف' : 'Employee Details')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'تفاصيل الموظف' : 'Employee Details' }}</h2>
        <div class="flex gap-2">
            @can('users.manage')
                <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                    {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                </a>
            @endcan
            <a href="{{ route('users.index') }}" class="text-slate-600 hover:text-slate-800 text-sm">{{ app()->getLocale() === 'ar' ? '← العودة' : '← Back' }}</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Employee Information Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                {{ app()->getLocale() === 'ar' ? 'معلومات الموظف' : 'Employee Information' }}
            </h3>
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</label>
                    <p class="text-slate-800">{{ $user->employee?->name ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <p class="text-slate-800">{{ $user->employee?->name_ar ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</label>
                    <p class="text-slate-800">
                        {{ app()->getLocale() === 'ar' && $user->employee?->department?->name_ar ? $user->employee->department->name_ar : ($user->employee?->department?->name ?? '-') }}
                    </p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي (إنجليزي)' : 'Job Title (English)' }}</label>
                    <p class="text-slate-800">{{ $user->employee?->job_title ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي (عربي)' : 'Job Title (Arabic)' }}</label>
                    <p class="text-slate-800">{{ $user->employee?->job_title_ar ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <p>
                        @if($user->employee?->status === 'active')
                            <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                            </span>
                        @else
                            <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded text-sm">
                                {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- User Account Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                {{ app()->getLocale() === 'ar' ? 'حساب المستخدم' : 'User Account' }}
            </h3>
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'اسم المستخدم' : 'Username' }}</label>
                    <p class="text-slate-800">{{ $user->username }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                    <p class="text-slate-800">{{ $user->email ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'الدور' : 'Role' }}</label>
                    <p class="text-slate-800">
                        @foreach($user->roles as $role)
                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                {{ ucfirst($role->name) }}
                            </span>
                        @endforeach
                    </p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'التوقيع الإلكتروني' : 'Electronic signature' }}</label>
                    @if($user->signature)
                        <p class="mt-1"><img src="{{ asset('storage/' . $user->signature) }}" alt="Signature" class="h-14 object-contain border border-slate-200 rounded p-1 bg-white"></p>
                    @else
                        <p class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'غير مرفوع' : 'Not uploaded' }}</p>
                    @endif
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'آخر تسجيل دخول' : 'Last Login' }}</label>
                    <p class="text-slate-800">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : '-' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</label>
                    <p class="text-slate-800">{{ $user->created_at->format('Y-m-d H:i') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'آخر تحديث' : 'Last Updated' }}</label>
                    <p class="text-slate-800">{{ $user->updated_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
