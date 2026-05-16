<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use App\Helpers\ActivityLogger;
use App\Support\ShiftStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('settings.manage');
        $shifts = Shift::orderBy('sort_order')->get();
        return view('shifts.index', compact('shifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('settings.manage');
        $staffUsers = User::query()->where('status', 'active')->orderBy('name')->get();
        $staffPivotReady = ShiftStaff::pivotTableReady();

        return view('shifts.create', compact('staffUsers', 'staffPivotReady'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('settings.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if (!isset($validated['name_ar'])) {
            $validated['name_ar'] = $validated['name'];
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $userIds = $validated['user_ids'] ?? [];
        unset($validated['user_ids']);

        // Auto-assign sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Shift::max('sort_order') + 1;
        }

        $shift = Shift::create($validated);
        $staffWarning = $this->syncShiftStaff($shift, $userIds);

        ActivityLogger::log('shift_created', Shift::class, $shift->id, __('Shift created') . ': ' . $shift->name, null, $shift->toArray());

        $redirect = redirect()->route('shifts.index')->with('success', __('Shift created successfully.'));
        if ($staffWarning) {
            $redirect->with('warning', $staffWarning);
        }

        return $redirect;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        Gate::authorize('settings.manage');
        $staffPivotReady = ShiftStaff::pivotTableReady();
        if ($staffPivotReady) {
            $shift->load('users');
        } else {
            $shift->setRelation('users', collect());
        }
        $staffUsers = User::query()->where('status', 'active')->orderBy('name')->get();

        return view('shifts.edit', compact('shift', 'staffUsers', 'staffPivotReady'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        Gate::authorize('settings.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if (!isset($validated['name_ar'])) {
            $validated['name_ar'] = $validated['name'];
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $userIds = $validated['user_ids'] ?? [];
        unset($validated['user_ids']);

        $old = $shift->toArray();
        $shift->update($validated);
        $staffWarning = $this->syncShiftStaff($shift, $userIds);

        ActivityLogger::log('shift_updated', Shift::class, $shift->id, __('Shift updated') . ': ' . $shift->name, $old, $shift->toArray());

        $redirect = redirect()->route('shifts.index')->with('success', __('Shift updated successfully.'));
        if ($staffWarning) {
            $redirect->with('warning', $staffWarning);
        }

        return $redirect;
    }

    /** @return string|null رسالة تحذير إن لم يُحفظ ربط الموظفين */
    private function syncShiftStaff(Shift $shift, array $userIds): ?string
    {
        if (! ShiftStaff::pivotTableReady()) {
            if ($userIds !== []) {
                return app()->getLocale() === 'ar'
                    ? 'تم حفظ الـ Shift، لكن ربط الموظفين يتطلب تشغيل التحديث على السيرفر: php artisan migrate'
                    : 'Shift saved, but staff assignment requires running: php artisan migrate';
            }

            return null;
        }

        $shift->users()->sync($userIds);

        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        Gate::authorize('settings.manage');

        // Check if shift is in use (optional but recommended)
        if ($shift->visits()->exists()) {
            return back()->with('error', __('Cannot delete shift because it has linked visits.'));
        }

        $old = $shift->toArray();
        $name = $shift->name;
        $id = $shift->id;

        $shift->delete();

        ActivityLogger::log('shift_deleted', Shift::class, $id, __('Shift deleted') . ': ' . $name, $old, null);

        return redirect()->route('shifts.index')->with('success', __('Shift deleted successfully.'));
    }
}
