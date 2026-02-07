<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WrittenCommitment extends Model
{
    use SoftDeletes;
    protected $fillable = ['patient_id', 'visit_id', 'amount', 'commitment_date', 'signed_file_path', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return [
            'commitment_date' => 'date',
            'amount' => 'decimal:2',
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

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
