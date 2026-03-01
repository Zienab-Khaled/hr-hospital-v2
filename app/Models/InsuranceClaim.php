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

    /**
     * تسجيل سداد المطالبة من شركة التأمين.
     * المبلغ المغطى من التأمين يبقى في المديونية حتى تُدفع المطالبة؛ عند الدفع نُنشئ دفعة من نوع تأمين ونحدّث الفاتورة.
     */
    public function markAsPaid(): void
    {
        DB::transaction(function () {
            $this->update(['status' => 'paid']);

            $invoice = $this->invoice->load('items');
            // مبلغ المطالبة = المعتمد من الشركة أو مجموع المغطى من بنود الفاتورة
            $amount = $this->approved_amount !== null && (float) $this->approved_amount > 0
                ? (float) $this->approved_amount
                : (float) $invoice->items->sum(fn ($i) => (float) $i->insurance_covered_amount);

            if ($amount <= 0) {
                return;
            }

            $userId = auth()->id() ?? \App\Models\User::role('admin')->first()?->id;

            // 1. إنشاء دفعة في النظام من شركة التأمين
            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => 'insurance',
                'amount' => $amount,
                'received_date' => now(),
                'received_by' => $userId,
                'approved_by' => $userId,
                'approved_at' => now(),
                'status' => 'approved',
                'audit_status' => 'matched',
                'notes' => __('Payment received from insurance claim: :claim', ['claim' => $this->id]),
            ]);

            // 2. تحديث الفاتورة: paid_amount = كل المستلم، remaining_amount = الإجمالي − المستلم
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
