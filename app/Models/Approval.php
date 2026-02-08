<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Approval extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = [
        'approval_number',
        'invoice_id',
        'patient_id',
        'approval_type',
        'insurance_company_id',
        'charity_entity_id',
        'requested_amount',
        'approved_amount',
        'status',
        'rejection_reason',
        'notes',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_token',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($approval) {
            if (empty($approval->approval_number)) {
                $approval->approval_number = 'APR-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
            if (empty($approval->approval_token)) {
                $approval->approval_token = Str::random(64);
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function charityEntity(): BelongsTo
    {
        return $this->belongsTo(CharityEntity::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Register media collections for medical reports and attachments
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('medical-reports')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
            
        $this->addMediaCollection('patient-data')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }
}
