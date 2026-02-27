<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Invoice extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;
    protected $fillable = [
        'patient_id', 'visit_id', 'invoice_number', 'total_amount', 'paid_amount', 'remaining_amount',
        'deposit_amount', 'status', 'invoice_date', 'notes', 'print_media_ids',
        'sent_to_charity_mail_at', 'printed_commitment_at', 'printed_non_commitment_at',
        'payment_type', 'invoice_type', 'audit_status', 'rejection_reason',
        'cashier_otp', 'cashier_id', 'cashier_received_at', 'deposited_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'print_media_ids' => 'array',
            'sent_to_charity_mail_at' => 'datetime',
            'printed_commitment_at' => 'datetime',
            'printed_non_commitment_at' => 'datetime',
            'payment_type' => 'string',
            'invoice_type' => 'string',
            'audit_status' => 'string',
            'cashier_received_at' => 'datetime',
            'deposited_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function insuranceClaims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function charityClaims(): HasMany
    {
        return $this->hasMany(CharityClaim::class);
    }

    public function partySends(): HasMany
    {
        return $this->hasMany(InvoicePartySend::class);
    }


    public function paymentReceipts(): HasManyThrough
    {
        return $this->hasManyThrough(PaymentReceipt::class, Payment::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'ar' => [
                'pending' => 'قيد الانتظار',
                'sent_to_insurance' => 'مرسل لشركة التأمين',
                'sent_to_charity' => 'مرسل للجمعية',
                'approved' => 'معتمد',
                'rejected' => 'مرفوض',
                'paid' => 'مدفوعة',
                'under_review' => 'قيد المراجعة',
                'matched' => 'مكتمل (مطابق)',
                'ready_for_deposit' => 'جاهز للتوريد',
                'deposited' => 'تم التوريد',
            ],
            'en' => [
                'pending' => 'Pending',
                'sent_to_insurance' => 'Sent to insurance',
                'sent_to_charity' => 'Sent to charity',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'paid' => 'Paid',
                'under_review' => 'Under Review',
                'matched' => 'Matched',
                'ready_for_deposit' => 'Ready for Deposit',
                'deposited' => 'Deposited',
            ],
        ];
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        // Priority to audit_status if it's set to a workflow state
        if (in_array($this->audit_status, ['under_review', 'matched', 'ready_for_deposit', 'deposited', 'rejected'])) {
             return $labels[$locale][$this->audit_status] ?? $this->audit_status;
        }

        return $labels[$locale][$this->status] ?? $this->status ?? '—';
    }

    public function getInvoiceTypeLabelAttribute(): string
    {
        $labels = [
            'ar' => [
                'regular' => 'فاتورة عادية',
                'eligibility' => 'أحقية علاج',
            ],
            'en' => [
                'regular' => 'Regular Invoice',
                'eligibility' => 'Treatment Eligibility',
            ],
        ];
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        return $labels[$locale][$this->invoice_type ?? 'regular'] ?? $this->invoice_type ?? '—';
    }

    // Service Tracking Methods
    public function completedItems()
    {
        return $this->items()->where('status', 'completed');
    }

    public function pendingItems()
    {
        return $this->items()->where('status', 'pending');
    }

    public function isFullyCompleted(): bool
    {
        return $this->items()->where('status', '!=', 'completed')->count() === 0;
    }

    public function completionPercentage(): float
    {
        $total = $this->items()->count();
        if ($total === 0) return 0;
        $completed = $this->completedItems()->count();
        return round(($completed / $total) * 100, 1);
    }

    // Charity Claim Helpers
    public function charityClaim()
    {
        return $this->charityClaims()->latest()->first();
    }

    public function hasCharityClaim(): bool
    {
        return $this->charityClaims()->exists();
    }

    public function canCreateCharityClaim(): bool
    {
        return $this->patient && $this->patient->payment_type === 'charity' && !$this->hasCharityClaim();
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signed_commitment')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);

        $this->addMediaCollection('signed_non_commitment')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);

        $this->addMediaCollection('signed_other')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);
    }

    /**
     * Get all media URLs related to this invoice (signed docs, receipts, patient approvals)
     */
    public function getAllRelatedMediaUrls(): array
    {
        $urls = [];

        // 1. Signed documents on this invoice
        foreach (['signed_commitment', 'signed_non_commitment', 'signed_other'] as $collection) {
            foreach ($this->getMedia($collection) as $media) {
                $urls[$media->file_name] = $media->getUrl();
            }
        }

        // 2. Receipts related to this invoice
        foreach ($this->payments as $payment) {
            if ($payment->receipt) {
                $receipt = $payment->receipt;
                $urls["Receipt-{$receipt->receipt_number}"] = route('payment-receipts.print', $receipt);
                foreach (['physical_receipt', 'collector_screenshot'] as $col) {
                    foreach ($receipt->getMedia($col) as $media) {
                        $urls["Attachment-{$media->file_name}"] = $media->getUrl();
                    }
                }
            }
        }

        // 3. Charity approvals from patient profile or visit if charity patient
        if ($this->payment_type === 'charity') {
            if ($this->patient) {
                foreach ($this->patient->getMedia('charity-approvals') as $media) {
                    $urls["CharityApproval-Patient-{$media->file_name}"] = $media->getUrl();
                }
            }
            if ($this->visit) {
                foreach ($this->visit->getMedia('charity_approval') as $media) {
                    $urls["CharityApproval-Visit-{$media->file_name}"] = $media->getUrl();
                }
            }
        }

        return $urls;
    }
}
