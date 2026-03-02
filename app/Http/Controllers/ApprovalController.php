<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * Show approval response form (approve/reject)
     */
    public function respond(Request $request, $token)
    {
        $approval = Approval::where('approval_token', $token)->firstOrFail();

        if ($approval->status !== 'pending') {
            return view('approvals.already-responded', compact('approval'));
        }

        $action = $request->get('action'); // 'approve' or 'reject'

        $approval->load([
            'patient',
            'invoice.items.service',
            'insuranceCompany',
            'charityEntity',
        ]);

        return view('approvals.respond', compact('approval', 'action'));
    }

    /**
     * Process approval response (approve/reject)
     */
    public function processResponse(Request $request, $token)
    {
        $approval = Approval::where('approval_token', $token)->firstOrFail();

        if ($approval->status !== 'pending') {
            return redirect()->route('approvals.already-responded', $token)
                ->with('error', 'This approval has already been processed.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'approved_amount' => 'nullable|numeric|min:0',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
            'notes' => 'nullable|string',
        ]);

        $approval->status = $validated['action'] === 'approve' ? 'approved' : 'rejected';
        $approval->approved_at = now();

        if ($validated['action'] === 'approve') {
            $approval->approved_amount = $validated['approved_amount'] ?? $approval->requested_amount;
        } else {
            $approval->rejection_reason = $validated['rejection_reason'];
        }

        $approval->notes = $validated['notes'] ?? $approval->notes;
        $approval->save();

        // Update invoice status based on approval
        if ($approval->status === 'approved') {
            $approval->invoice->update(['status' => 'approved']);
        } else {
            $approval->invoice->update(['status' => 'rejected']);
        }

        // System Notification for Managers and Accountants
        $notifyUsers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['manager', 'admin', 'accountant']);
        })->get();
        if ($notifyUsers->isNotEmpty()) {
            $statusLabel = $approval->status === 'approved' ? (app()->getLocale() === 'ar' ? 'موافقة' : 'Approved') : (app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected');
            $partyName = $approval->insuranceCompany?->name ?? $approval->charityEntity?->name_ar ?? $approval->charityEntity?->name ?? 'External Party';

            Notification::send($notifyUsers, new SystemNotification([
                'title' => (app()->getLocale() === 'ar' ? 'تحديث طلب موافقة: ' : 'Approval update: ') . $statusLabel,
                'message' => (app()->getLocale() === 'ar' ? "تم تحديث طلب الموافقة من " : "Approval request from ") . $partyName . (app()->getLocale() === 'ar' ? " للمريض: " : " for patient: ") . ($approval->patient->name_ar ?? $approval->patient->name),
                'action_url' => route('invoices.show', $approval->invoice_id),
                'type' => $approval->status === 'approved' ? 'success' : 'danger',
            ]));
        }

        return view('approvals.thank-you', compact('approval'));
    }
}
