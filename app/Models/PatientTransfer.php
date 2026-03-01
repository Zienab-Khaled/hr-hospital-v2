<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientTransfer extends Model
{
    protected $fillable = [
        'patient_id', 'from_department_id', 'to_department_id',
        'transferred_at', 'transferred_by', 'notes',
    ];

    protected function casts(): array
    {
        return ['transferred_at' => 'datetime'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function transferredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
