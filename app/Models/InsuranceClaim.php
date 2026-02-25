<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InsuranceClaim extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    public function markAsPaid(): void
    {
        DB::transaction(function () {
            $this->update(['status' => 'paid']);

            $invoice = $this->invoice;
            $amount = $this->approved_amount ?: $invoice->remaining_amount;

            // 1. Create Payment record
            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => $invoice->payment_type, // 'insurance'
                'amount' => $amount,
                'received_date' => now(),
                'received_by' => auth()->id() ?? User::role('admin')->first()?->id,
                'status' => 'approved',
                'audit_status' => 'matched',
                'notes' => __('Payment received from insurance claim: :claim', ['claim' => $this->id]),
            ]);

            // 2. Update Invoice balance
            $invoice->increment('paid_amount', $amount);
            $invoice->decrement('remaining_amount', $amount);

            if ($invoice->remaining_amount <= 0) {
                $invoice->update(['status' => 'paid']);
            }
        });
    }

    protected $fillable = [
        'invoice_id', 'insurance_company_id', 'sent_date', 'sent_by', 'status',
        'approved_amount', 'notes', 'company_response_notes',
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

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('arqos_file')
            ->singleFile();
    }
}
