<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DelegationController extends Controller
{
    public function index(Request $request)
    {
        // التفويضات مفتوحة لجميع الموظفين: الجميع يرى تفويضاته المُعطاة والمُستلمة ويمكنه إنشاء تفويض جديد
        $given = Delegation::with('delegateTo')
            ->where('delegator_id', auth()->id())
            ->orderByDesc('from_date')
            ->orderByDesc('to_date')
            ->paginate(20, ['*'], 'given_page');
        $received = Delegation::with('delegator')
            ->where('delegate_to_id', auth()->id())
            ->orderByDesc('from_date')
            ->orderByDesc('to_date')
            ->paginate(20, ['*'], 'received_page');
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get(['id', 'name', 'name_ar', 'job_title_ar']);
        $canManage = true;

        return view('delegations.index', compact('given', 'received', 'users', 'canManage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'delegate_to_id' => 'required|exists:users,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'notes' => 'nullable|string|max:1000',
        ], [], [
            'delegate_to_id' => app()->getLocale() === 'ar' ? 'المُفوّض إليه' : 'Delegate to',
            'from_date' => app()->getLocale() === 'ar' ? 'من تاريخ' : 'From date',
            'to_date' => app()->getLocale() === 'ar' ? 'إلى تاريخ' : 'To date',
        ]);

        Delegation::create([
            'delegator_id' => auth()->id(),
            'delegate_to_id' => $request->delegate_to_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'notes' => $request->notes,
        ]);

        $message = app()->getLocale() === 'ar' ? 'تم إنشاء التفويض بنجاح.' : 'Delegation created successfully.';
        return redirect()->route('delegations.index')->with('success', $message);
    }

    public function destroy(Delegation $delegation)
    {
        if ($delegation->delegator_id !== auth()->id()) {
            abort(403, app()->getLocale() === 'ar' ? 'يمكن فقط للمُفوّض إلغاء التفويض.' : 'Only the delegator can cancel this delegation.');
        }

        $delegation->delete();
        $message = app()->getLocale() === 'ar' ? 'تم إلغاء التفويض.' : 'Delegation cancelled.';
        return redirect()->route('delegations.index')->with('success', $message);
    }
}
