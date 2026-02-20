<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function markAsUnderReview(): void
    {
        $this->update(['status' => 'under_review']);
    }

    public function markAsApproved(float $approvedAmount = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_amount' => $approvedAmount ?? $this->invoice->total_amount,
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
        $this->update(['status' => 'paid']);
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
