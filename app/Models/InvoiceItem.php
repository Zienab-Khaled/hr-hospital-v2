<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_id', 'service_id', 'quantity', 'unit_price', 'total_price', 'description',
        'insurance_coverage_type', 'insurance_coverage_value',
        'status', 'completed_at', 'completed_by', 'execution_date',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'insurance_coverage_value' => 'decimal:2',
            'completed_at' => 'datetime',
            'execution_date' => 'date',
        ];
    }

    /** Amount covered by insurance for this line (computed from type + value). */
    public function getInsuranceCoveredAmountAttribute(): float
    {
        $total = (float) $this->total_price;
        if (!$this->insurance_coverage_type || $total <= 0) {
            return 0.0;
        }
        $val = (float) $this->insurance_coverage_value;
        if ($this->insurance_coverage_type === 'percentage') {
            $val = min(100, max(0, $val));
            return round($total * $val / 100, 2);
        }
        return round(min($val, $total), 2);
    }

    /** Amount remaining for patient to pay for this line. */
    public function getPatientAmountAttribute(): float
    {
        return round((float) $this->total_price - $this->insurance_covered_amount, 2);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper Methods
    public function canBeCompleted(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsCompleted($executionDate = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->user()?->id,
            'execution_date' => $executionDate ?? today(),
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
