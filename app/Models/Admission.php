<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admission extends Model
{
    protected $fillable = [
        'patient_id', 'visit_id', 'admission_date', 'discharge_date', 'room', 'daily_cost', 'days', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'discharge_date' => 'date',
            'daily_cost' => 'decimal:2',
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
}
