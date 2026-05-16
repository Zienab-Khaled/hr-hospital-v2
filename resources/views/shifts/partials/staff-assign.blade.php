@php
    $selectedIds = old('user_ids', isset($shift) ? $shift->users->pluck('id')->all() : []);
@endphp
<div class="border-t border-slate-100 pt-6">
    <label class="block text-sm font-medium text-slate-700 mb-2">
        {{ app()->getLocale() === 'ar' ? 'موظفو الـ Shift' : 'Shift staff' }}
    </label>
    <p class="text-xs text-slate-500 mb-2">
        {{ app()->getLocale() === 'ar' ? 'اختر الموظفين المرتبطين بهذا الـ Shift (Ctrl+نقرة للاختيار المتعدد).' : 'Select staff assigned to this shift (Ctrl+click for multiple).' }}
    </p>
    <select name="user_ids[]" multiple size="8"
        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
        @foreach ($staffUsers as $u)
            <option value="{{ $u->id }}" {{ in_array($u->id, $selectedIds, false) ? 'selected' : '' }}>
                {{ $u->name }}{{ $u->username ? ' (' . $u->username . ')' : '' }}
            </option>
        @endforeach
    </select>
    @error('user_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    @error('user_ids.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
