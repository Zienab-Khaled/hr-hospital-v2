<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Visit extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = ['patient_id', 'department_id', 'visit_date', 'shift_id', 'case_type', 'notes', 'referral_number', 'transferred_department_id', 'registered_by'];

    protected function casts(): array
    {
        return ['visit_date' => 'date'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('charity_approval')
            ->singleFile(); // Only one approval doc per visit
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function transferredDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'transferred_department_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function authorizations(): HasMany
    {
        return $this->hasMany(Authorization::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the charity approval media item for this visit
     */
    public function charityApprovalDocument()
    {
        return $this->getFirstMedia('charity_approval');
    }
}
