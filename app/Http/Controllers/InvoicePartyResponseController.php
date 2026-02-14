<?php

namespace App\Http\Controllers;

use App\Models\InvoicePartySend;
use Illuminate\Http\Request;

class InvoicePartyResponseController extends Controller
{
    /**
     * Show response form (confirm payment or reject with written approval/reason).
     */
    public function show(Request $request, string $token)
    {
        $partySend = InvoicePartySend::where('token', $token)->firstOrFail();
        $partySend->load(['invoice.patient', 'invoice.items.service']);

        if ($partySend->isResponded()) {
            return view('invoice-party-response.already-responded', compact('partySend'));
        }

        $action = $request->get('action', 'confirm'); // confirm | reject
        if (! in_array($action, ['confirm', 'reject'], true)) {
            $action = 'confirm';
        }

        return view('invoice-party-response.respond', compact('partySend', 'action'));
    }

    /**
     * Process confirm or reject with written approval/reason.
     */
    public function process(Request $request, string $token)
    {
        $partySend = InvoicePartySend::where('token', $token)->firstOrFail();

        if ($partySend->isResponded()) {
            return redirect()->route('invoice-party-response.show', $token)
                ->with('error', __('This response has already been submitted.'));
        }

        $validated = $request->validate([
            'action' => 'required|in:confirm,reject',
            'response_text' => 'required|string|min:3|max:2000',
        ]);

        $partySend->update([
            'response_action' => $validated['action'] === 'confirm' ? 'confirmed' : 'rejected',
            'response_text' => $validated['response_text'],
            'response_at' => now(),
        ]);

        return view('invoice-party-response.thank-you', compact('partySend'));
    }
}
