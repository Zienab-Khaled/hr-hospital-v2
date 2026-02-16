@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تسليم الشيفت' : 'Hand Over Shift')
@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">
                {{ app()->getLocale() === 'ar' ? 'تسليم الشيفت' : 'Hand Over Shift' }}
            </h2>
            <p class="text-slate-600 text-sm mb-6">
                {{ app()->getLocale() === 'ar' ? 'تسجيل تسليم الشيفت للشيفت التالي (الزيارات والفواتير وملاحظاتك تُحفظ ويمكن للشيفت القادم عرضها).' : 'Record handover of your shift to the next (visits, invoices and your notes are saved for the next shift to view).' }}
            </p>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm">
                    @foreach ($errors->all() as $err) <p>{{ $err }}</p> @endforeach
                </div>
            @endif

            <form action="{{ route('shift-handovers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="handover_date" value="{{ date('Y-m-d') }}">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الشيفت المُسلّم' : 'Shift being handed over' }}</label>
                        <select name="shift_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @foreach ($shifts as $s)
                                <option value="{{ $s->id }}" {{ $currentShift && $currentShift->id === $s->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' && $s->name_ar ? $s->name_ar : $s->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($currentShift)
                            <p class="text-xs text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'الافتراضي: الشيفت الحالي' : 'Default: current shift' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات للشيفت القادم (اختياري)' : 'Notes for next shift (optional)' }}</label>
                        <textarea name="notes" rows="4" maxlength="2000" placeholder="{{ app()->getLocale() === 'ar' ? 'متابعات، حالات طارئة، تنبيهات...' : 'Follow-ups, urgent cases, notes...' }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-slate-50 rounded-lg font-semibold hover:bg-red-700">
                        {{ app()->getLocale() === 'ar' ? 'تسليم الشيفت' : 'Hand Over Shift' }}
                    </button>
                    <a href="{{ route('shift-handovers.index') }}" class="px-4 py-2 border border-slate-400 rounded-lg text-slate-700 hover:bg-slate-100">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
