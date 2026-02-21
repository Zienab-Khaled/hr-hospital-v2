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
        'file_number', 'name', 'name_ar', 'identity_type', 'identity_value', 'phone',
        'payment_type', 'insurance_company_id', 'charity_entity_id', 'department_id', 'notes', 'is_active',
        'age', 'gender', 'country_of_origin', 'current_location', 'sponsor_name', 'sponsor_phone',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Identity type options: key => [ar, en] */
    public static function identityTypeOptions(): array
    {
        return [
            'national_id'    => ['ar' => 'هوية', 'en' => 'National ID'],
            'visit_visa'     => ['ar' => 'فيزة زيارة', 'en' => 'Visit Visa'],
            'iqama'          => ['ar' => 'رقم إقامة', 'en' => 'Iqama Number'],
            'passport'       => ['ar' => 'رقم جواز السفر', 'en' => 'Passport Number'],
            'border_number'  => ['ar' => 'رقم الحدود', 'en' => 'Border Number'],
            'visa_number'    => ['ar' => 'رقم التأشيرة', 'en' => 'Visa Number'],
        ];
    }

    public function getIdentityTypeLabelAttribute(): ?string
    {
        $opts = static::identityTypeOptions();
        $key = $this->identity_type;
        if (!$key || !isset($opts[$key])) {
            return null;
        }
        return app()->getLocale() === 'ar' ? $opts[$key]['ar'] : $opts[$key]['en'];
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function charityEntity(): BelongsTo
    {
        return $this->belongsTo(CharityEntity::class, 'charity_entity_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(PatientTransfer::class)->latest('transferred_at');
    }

    /** هل تم تحويل المريض من القسم المحدد (يُستخدم في عرض "تم تحويله" في قائمة القسم) */
    public function wasTransferredFrom(?int $departmentId): bool
    {
        if (! $departmentId) {
            return false;
        }
        return $this->transfers()->where('from_department_id', $departmentId)->exists();
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
     * Get identity number (identity_value)
     */
    public function getIdentityNumberAttribute(): ?string
    {
        return $this->identity_value;
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

        $this->addMediaCollection('charity-approvals')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);
    }
}
