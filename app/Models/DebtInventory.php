<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebtInventory extends Model
{
    use SoftDeletes;
    protected $fillable = ['patient_id', 'inventory_date', 'total_debt', 'details', 'created_by'];

    protected function casts(): array
    {
        return [
            'inventory_date' => 'date',
            'total_debt' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
