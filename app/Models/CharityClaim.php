<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function notes(): HasMany
    {
        return $this->hasMany(CharityClaimNote::class)->latest();
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

    /**
     * تسجيل سداد المطالبة من الجمعية.
     * المبلغ اللي الجمعية تتحمله يبقى في المديونية حتى تُدفع المطالبة؛ عند الدفع نُنشئ دفعة من نوع جمعية ونحدّث الفاتورة.
     */
    public function markAsPaid(): void
    {
        DB::transaction(function () {
            $this->update(['status' => 'paid']);

            $invoice = $this->invoice;
            $amount = (float) ($this->approved_amount ?: $invoice->remaining_amount);
            if ($amount <= 0) {
                return;
            }

            $userId = auth()->id() ?? User::role('admin')->first()?->id;

            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => 'charity',
                'amount' => $amount,
                'received_date' => now(),
                'received_by' => $userId,
                'approved_by' => $userId,
                'approved_at' => now(),
                'status' => 'approved',
                'audit_status' => 'matched',
                'notes' => __('Payment received from charity claim: :claim', ['claim' => $this->id]),
            ]);

            $newPaidAmount = (float) $invoice->paid_amount + $amount;
            $newRemainingAmount = max(0, round((float) $invoice->total_amount - $newPaidAmount, 2));

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 'paid' : 'pending',
                'debt_status' => $newRemainingAmount <= 0 ? 'paid' : $invoice->debt_status,
            ]);
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
