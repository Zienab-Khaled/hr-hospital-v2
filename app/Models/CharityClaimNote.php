<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharityClaimNote extends Model
{
    protected $fillable = ['charity_claim_id', 'body', 'created_by'];

    public function charityClaim(): BelongsTo
    {
        return $this->belongsTo(CharityClaim::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
