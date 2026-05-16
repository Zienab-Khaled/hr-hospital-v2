@props([
    'name',
    'value' => '',
    'required' => false,
    'withPicker' => true,
])

@php
    $inputId = $attributes->get('id') ?? 'dl_' . preg_replace('/[^a-z0-9_]/', '_', $name);
    $pickerId = $inputId . '_picker';
    $inputClass = $attributes->get('class', 'w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500');
@endphp

<div class="date-latin-field flex gap-1 items-stretch min-w-[10.25rem]">
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $value }}"
        dir="ltr"
        lang="en"
        inputmode="numeric"
        data-western-numerals="1"
        data-date-latin="1"
        placeholder="YYYY-MM-DD"
        pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
        title="YYYY-MM-DD"
        autocomplete="off"
        @if ($required) required @endif
        class="date-latin-input flex-1 min-w-0 {{ $inputClass }}"
    >
    @if ($withPicker)
        <div class="relative shrink-0 w-9">
            <button
                type="button"
                class="date-latin-picker-btn w-full h-full min-h-[2rem] inline-flex items-center justify-center border border-slate-300 rounded bg-slate-50 hover:bg-slate-100 text-slate-600 text-sm"
                title="{{ app()->getLocale() === 'ar' ? 'اختر من التقويم' : 'Open calendar' }}"
                aria-label="{{ app()->getLocale() === 'ar' ? 'اختر تاريخاً' : 'Pick a date' }}"
            >📅</button>
            <input
                type="date"
                id="{{ $pickerId }}"
                lang="en"
                dir="ltr"
                tabindex="-1"
                value="{{ $value }}"
                data-date-latin-picker-for="{{ $inputId }}"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            >
        </div>
    @endif
</div>
