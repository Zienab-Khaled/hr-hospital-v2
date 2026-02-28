<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegation extends Model
{
    protected $fillable = [
        'delegator_id',
        'delegate_to_id',
        'from_date',
        'to_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
        ];
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegateTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_to_id');
    }

    /**
     * Scope: active delegations that cover the given date (default: today).
     */
    public function scopeActive($query, $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date)->toDateString() : now()->toDateString();
        return $query->where('from_date', '<=', $date)->where('to_date', '>=', $date);
    }
}
