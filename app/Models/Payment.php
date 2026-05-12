<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_id', 'payment_type', 'amount', 'received_date', 'received_by', 'approved_by', 'approved_at', 'reference_no', 'status', 'notes', 'audit_status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_date' => 'date',
            'approved_at' => 'datetime',
            'audit_status' => 'string',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class);
    }

    /** تسمية طريقة الدفع للعرض (كاش / تأمين / جمعية) */
    public function getPaymentTypeLabelAttribute(): string
    {
        $labels = [
            'ar' => [
                'cash' => 'كاش',
                'card' => 'شبكة',
                'bank_transfer' => 'تحويل بنكي',
                'cheque' => 'شيك',
                'loyalty_points' => 'نقاط بيع',
                'mixed' => 'طرق متعددة',
                'insurance' => 'تأمين',
                'charity' => 'جمعية',
            ],
            'en' => [
                'cash' => 'Cash',
                'card' => 'Card / POS',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
                'loyalty_points' => 'Loyalty points',
                'mixed' => 'Multiple methods',
                'insurance' => 'Insurance',
                'charity' => 'Charity',
            ],
        ];
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $key = $this->payment_type ?? 'cash';
        return $labels[$locale][$key] ?? $labels['ar'][$key] ?? $key;
    }
}
