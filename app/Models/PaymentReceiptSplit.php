<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceiptSplit extends Model
{
    protected $fillable = [
        'payment_receipt_id',
        'payment_method',
        'amount',
        'reference_number',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PaymentReceipt::class, 'payment_receipt_id');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return PaymentReceipt::paymentMethodLabel($this->payment_method);
    }
}
