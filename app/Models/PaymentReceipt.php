<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PaymentReceipt extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'receipt_number',
        'ministry_receipt_number',
        'payment_id',
        'patient_id',
        'amount',
        'patient_cash_amount',
        'total_payment_amount',
        'payment_method',
        'reference_number',
        'collected_by',
        'approved_by',
        'collected_at',
        'approved_at',
        'notes',
        'invoice_snapshot_total',
        'invoice_snapshot_paid',
        'invoice_snapshot_remaining',
        'selected_items',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'patient_cash_amount' => 'decimal:2',
            'total_payment_amount' => 'decimal:2',
            'invoice_snapshot_total' => 'decimal:2',
            'invoice_snapshot_paid' => 'decimal:2',
            'invoice_snapshot_remaining' => 'decimal:2',
            'collected_at' => 'datetime',
            'approved_at' => 'datetime',
            'selected_items' => 'array',
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

    public function splits(): HasMany
    {
        return $this->hasMany(PaymentReceiptSplit::class)->orderBy('sort_order');
    }

    /** @return Collection<int, PaymentReceiptSplit|object{payment_method: string, amount: float|int|string, reference_number: ?string}> */
    public function splitsForDisplay(): Collection
    {
        if ($this->relationLoaded('splits') ? $this->splits->isNotEmpty() : $this->splits()->exists()) {
            return $this->relationLoaded('splits') ? $this->splits : $this->splits()->get();
        }

        return collect([
            (object) [
                'payment_method' => $this->payment_method,
                'amount' => $this->amount,
                'reference_number' => $this->reference_number,
            ],
        ]);
    }

    public static function paymentMethodLabel(?string $method): string
    {
        $labels = [
            'cash' => app()->getLocale() === 'ar' ? 'كاش' : 'Cash',
            'card' => app()->getLocale() === 'ar' ? 'شبكة / POS' : 'POS / Card',
            'bank_transfer' => app()->getLocale() === 'ar' ? 'تحويل بنكي' : 'Bank Transfer',
            'cheque' => app()->getLocale() === 'ar' ? 'شيك' : 'Cheque',
            'loyalty_points' => app()->getLocale() === 'ar' ? 'نقاط بيع' : 'Loyalty / sale points',
            'insurance' => app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance',
            'charity' => app()->getLocale() === 'ar' ? 'جمعية' : 'Charity',
            'mixed' => app()->getLocale() === 'ar' ? 'متعدد' : 'Mixed',
        ];

        return $labels[$method ?? ''] ?? ($method ?? '—');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('physical_receipt')->singleFile();
        $this->addMediaCollection('collector_screenshot')->singleFile();
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        $lines = $this->splitsForDisplay();
        if ($lines->count() > 1) {
            return static::paymentMethodLabel('mixed');
        }
        if ($lines->count() === 1) {
            $m = $lines->first()->payment_method ?? null;

            return static::paymentMethodLabel($m);
        }

        return static::paymentMethodLabel($this->payment_method);
    }

    /** عرض مختصر لجميع طرق الإيصال (للجدول) */
    public function getSplitMethodsSummaryAttribute(): string
    {
        $parts = $this->splitsForDisplay()->map(function ($row) {
            $m = is_object($row) && isset($row->payment_method) ? $row->payment_method : null;
            $a = is_object($row) && isset($row->amount) ? (float) $row->amount : 0;

            return static::paymentMethodLabel($m) . ' ' . number_format($a, 2);
        });

        return $parts->implode(app()->getLocale() === 'ar' ? ' + ' : ' + ');
    }
}
