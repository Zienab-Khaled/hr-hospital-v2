@props(['action' => null])

<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-3 mb-4">
    <form method="GET" action="{{ $action ?? url()->current() }}" class="flex flex-wrap gap-2 items-end">
        {{-- Search Field --}}
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
            </label>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="{{ $searchPlaceholder ?? (app()->getLocale() === 'ar' ? 'ابحث...' : 'Search...') }}"
                class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- Custom Filters Slot --}}
        {{ $slot }}

        {{-- Per Page --}}
        <div class="w-20">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'العدد' : 'Show' }}
            </label>
            <select name="per_page" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                @foreach ([10, 15, 25, 50] as $size)
                    <option value="{{ $size }}" {{ request('per_page', 15) == $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium hover:bg-blue-700 shadow-sm inline-flex items-center gap-1 transition-colors whitespace-nowrap">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
            </button>
            
            @if(request()->hasAny(['search', 'per_page', 'identity_type', 'charity_entity_id', 'gender', 'age_from', 'age_to', 'insurance_company_id', 'payment_type']) || count(request()->except(['page'])) > 0)
                <a href="{{ $action ?? url()->current() }}" class="bg-slate-100 text-slate-700 px-3 py-1 rounded text-sm font-medium hover:bg-slate-200 shadow-sm inline-flex items-center gap-1 transition-colors border border-slate-300 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
                </a>
            @endif
        </div>
    </form>
</div>
