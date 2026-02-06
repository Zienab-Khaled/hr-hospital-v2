<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharityClaim extends Model
{
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
}
