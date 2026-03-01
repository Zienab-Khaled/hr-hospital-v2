<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftHandover;
use App\Models\Visit;
use Illuminate\Http\Request;

class ShiftHandoverController extends Controller
{
    /**
     * قائمة تسليمات الشيفتات (آخر التسليمات — للشيفت الجديد لمعرفة ما تم تسليمه)
     */
    public function index(Request $request)
    {
        $this->authorize('invoices.create');
        $handovers = ShiftHandover::with(['shift', 'handedOverBy'])
            ->orderByDesc('handed_over_at')
            ->limit(50)
            ->get();

        return view('shift-handovers.index', compact('handovers'));
    }

    /**
     * نموذج تسليم الشيفت
     */
    public function create(Request $request)
    {
        $this->authorize('invoices.create');
        $currentShift = Shift::currentAt();
        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();

        return view('shift-handovers.create', compact('currentShift', 'shifts'));
    }

    /**
     * حفظ تسليم الشيفت
     */
    public function store(Request $request)
    {
        $this->authorize('invoices.create');
        $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $shiftId = (int) $request->input('shift_id');
        $handedOverAt = now();
        $handoverDate = $request->get('handover_date', $handedOverAt->format('Y-m-d'));

        ShiftHandover::create([
            'shift_id' => $shiftId,
            'handed_over_by' => auth()->id(),
            'handed_over_at' => $handedOverAt,
            'handover_date' => $handoverDate,
            'notes' => $request->input('notes'),
        ]);

        $message = app()->getLocale() === 'ar'
            ? 'تم تسليم الشيفت بنجاح.'
            : 'Shift handed over successfully.';

        return redirect()->route('shift-handovers.index')->with('success', $message);
    }

    /**
     * عرض تفاصيل تسليم واحد (زيارات + فواتير الشيفت)
     */
    public function show(ShiftHandover $handover)
    {
        $this->authorize('invoices.create');
        $handover->load(['shift', 'handedOverBy']);

        $visitIds = Visit::where('shift_id', $handover->shift_id)
            ->whereDate('visit_date', $handover->handover_date)
            ->pluck('id');

        $visits = Visit::where('shift_id', $handover->shift_id)
            ->whereDate('visit_date', $handover->handover_date)
            ->with(['patient', 'department'])
            ->orderBy('visit_date')
            ->get();

        $invoices = \App\Models\Invoice::whereIn('visit_id', $visitIds)
            ->with(['patient'])
            ->orderBy('created_at')
            ->get();

        return view('shift-handovers.show', compact('handover', 'visits', 'invoices'));
    }
}
