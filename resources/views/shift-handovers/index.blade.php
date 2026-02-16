@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تسليمات الشيفتات' : 'Shift Handovers')
@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                {{ app()->getLocale() === 'ar' ? 'تسليمات الشيفتات' : 'Shift Handovers' }}
            </h2>
            <a href="{{ route('shift-handovers.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">
                {{ app()->getLocale() === 'ar' ? 'تسليم الشيفت' : 'Hand Over Shift' }}
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-100 text-emerald-800 text-sm">{{ session('success') }}</div>
        @endif

        <p class="text-slate-600 text-sm mb-4">
            {{ app()->getLocale() === 'ar' ? 'آخر التسليمات — للاطلاع على ما سلّمه الشيفت السابق (زيارات، فواتير، ملاحظات).' : 'Latest handovers — see what the previous shift handed over (visits, invoices, notes).' }}
        </p>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            @forelse ($handovers as $h)
                <div class="border-b border-slate-200 last:border-0 p-4 hover:bg-slate-50">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <a href="{{ route('shift-handovers.show', $h) }}" class="font-semibold text-slate-800 hover:underline">
                                {{ app()->getLocale() === 'ar' && $h->shift->name_ar ? $h->shift->name_ar : $h->shift->name }}
                                — {{ $h->handover_date?->format('Y-m-d') }}
                            </a>
                            <p class="text-sm text-slate-500 mt-0.5">
                                {{ app()->getLocale() === 'ar' ? 'تم التسليم' : 'Handed over' }} {{ $h->handed_over_at?->format('Y-m-d H:i') }}
                                {{ app()->getLocale() === 'ar' ? 'بواسطة' : 'by' }} {{ $h->handedOverBy->name ?? $h->handedOverBy->username ?? '—' }}
                            </p>
                        </div>
                        <div class="flex gap-4 text-sm text-slate-600">
                            <span>{{ app()->getLocale() === 'ar' ? 'زيارات:' : 'Visits:' }} <strong>{{ $h->visits_count }}</strong></span>
                            <span>{{ app()->getLocale() === 'ar' ? 'فواتير:' : 'Invoices:' }} <strong>{{ $h->invoices_count }}</strong></span>
                        </div>
                    </div>
                    @if ($h->notes)
                        <p class="text-sm text-slate-600 mt-2 pl-0">{{ Str::limit($h->notes, 120) }}</p>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد تسليمات حتى الآن.' : 'No handovers yet.' }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
