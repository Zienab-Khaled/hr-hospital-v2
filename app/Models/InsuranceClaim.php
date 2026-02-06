<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends Model
{
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

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
