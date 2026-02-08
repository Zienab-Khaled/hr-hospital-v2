<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Patient extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;
    
    protected $fillable = [
        'file_number', 'name', 'name_ar', 'id_number', 'phone',
        'payment_type', 'insurance_company_id', 'charity_entity_id', 'notes', 'is_active',
        'passport_number', 'iqama_number', 'age', 'gender',
        'country_of_origin', 'current_location', 'sponsor_name', 'sponsor_phone',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function charityEntity(): BelongsTo
    {
        return $this->belongsTo(CharityEntity::class, 'charity_entity_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
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

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function contactReports(): HasMany
    {
        return $this->hasMany(ContactReport::class);
    }

    public function writtenCommitments(): HasMany
    {
        return $this->hasMany(WrittenCommitment::class);
    }

    public function nonCommitmentReports(): HasMany
    {
        return $this->hasMany(NonCommitmentReport::class);
    }

    public function debtInventories(): HasMany
    {
        return $this->hasMany(DebtInventory::class);
    }
    
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }
    
    public function paymentReceipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }
    
    /**
     * Get identity number (prioritize ID, then Iqama, then Passport)
     */
    public function getIdentityNumberAttribute(): ?string
    {
        return $this->id_number ?? $this->iqama_number ?? $this->passport_number;
    }
    
    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);
            
        $this->addMediaCollection('medical-reports')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
            
        $this->addMediaCollection('profile-photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg']);
    }
}
