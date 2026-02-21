<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'payment_id',
        'patient_id',
        'amount',
        'payment_method',
        'reference_number',
        'collected_by',
        'approved_by',
        'collected_at',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'collected_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = 'RCP-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function collectionOrder(): HasOne
    {
        return $this->hasOne(CollectionOrder::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        $labels = [
            'cash' => app()->getLocale() === 'ar' ? 'كاش' : 'Cash',
            'card' => app()->getLocale() === 'ar' ? 'شبكة / POS' : 'POS / Card',
            'bank_transfer' => app()->getLocale() === 'ar' ? 'تحويل بنكي' : 'Bank Transfer',
            'cheque' => app()->getLocale() === 'ar' ? 'شيك' : 'Cheque',
        ];
        return $labels[$this->payment_method] ?? $this->payment_method;
    }
}
