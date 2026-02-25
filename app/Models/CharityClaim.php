<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CharityClaim extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_id', 'charity_entity_id', 'sent_date', 'sent_by', 'status',
        'approved_amount', 'notes', 'entity_response_notes',
    ];

    protected function casts(): array
    {
        return [
            'sent_date' => 'date',
            'approved_amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient()
    {
        return $this->hasOneThrough(Patient::class, Invoice::class, 'id', 'id', 'invoice_id', 'patient_id');
    }

    public function charityEntity(): BelongsTo
    {
        return $this->belongsTo(CharityEntity::class, 'charity_entity_id');
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    // Status Helper Methods
    public function canBeSent(): bool
    {
        return $this->status === 'draft';
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_date' => today(),
            'sent_by' => auth()->id(),
        ]);
    }

    public function markAsUnderReview(string $notes = null): void
    {
        $this->update([
            'status' => 'under_review',
            'entity_response_notes' => $notes ?? $this->entity_response_notes,
        ]);
    }

    public function markAsApproved(float $approvedAmount = null, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_amount' => $approvedAmount ?? $this->invoice->total_amount,
            'entity_response_notes' => $notes ?? $this->entity_response_notes,
        ]);
    }

    public function markAsRejected(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'entity_response_notes' => $reason,
        ]);
    }

    public function markAsPaid(): void
    {
        DB::transaction(function () {
            $this->update(['status' => 'paid']);

            $invoice = $this->invoice;
            $amount = $this->approved_amount ?: $invoice->remaining_amount;

            // 1. Create Payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => $invoice->payment_type, // 'charity'
                'amount' => $amount,
                'received_date' => now(),
                'received_by' => auth()->id() ?? User::role('admin')->first()?->id,
                'status' => 'approved', // Claims are usually pre-approved or approved upon payment
                'audit_status' => 'matched',
                'notes' => __('Payment received from charity claim: :claim', ['claim' => $this->id]),
            ]);

            // 2. Update Invoice balance
            $invoice->increment('paid_amount', $amount);
            $invoice->decrement('remaining_amount', $amount);

            if ($invoice->remaining_amount <= 0) {
                $invoice->update(['status' => 'paid']);
            }
        });
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
