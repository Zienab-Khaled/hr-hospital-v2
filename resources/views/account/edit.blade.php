@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'حسابي - التوقيع الإلكتروني' : 'My Account - Electronic Signature')
@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'حسابي' : 'My Account' }}</h2>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                {{ app()->getLocale() === 'ar' ? 'حساب المستخدم' : 'User Account' }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'اسم المستخدم' : 'Username' }}</label>
                    <p class="mt-1 font-medium text-slate-800">{{ $user->username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                    <p class="mt-1 font-medium text-slate-800">{{ $user->email ?: '—' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</label>
                    <p class="mt-1 font-medium text-slate-800">{{ $user->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</label>
                    <p class="mt-1 font-medium text-slate-800">{{ $user->department ? (app()->getLocale() === 'ar' && $user->department->name_ar ? $user->department->name_ar : $user->department->name) : '—' }}</p>
                </div>
            </div>

            <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="pt-4 border-t border-slate-200">
                    <x-signature-pad
                        name="signature_data"
                        :current-image="$user->signature"
                        :label="(app()->getLocale() === 'ar' ? 'التوقيع الإلكتروني للموظف' : 'Employee electronic signature')"
                    />
                    <p class="text-xs text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'ارسم في المربع أدناه لاستبدال التوقيع، أو اتركه فارغاً للإبقاء على الحالي.' : 'Draw below to replace signature, or leave empty to keep current.' }}</p>
                    @error('signature_data')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mt-6 flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        {{ app()->getLocale() === 'ar' ? 'حفظ التوقيع' : 'Save signature' }}
                    </button>
                    <a href="{{ url()->previous() }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded hover:bg-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
