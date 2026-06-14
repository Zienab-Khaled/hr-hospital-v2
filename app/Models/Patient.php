<?php

namespace App\Models;

use Carbon\Carbon;
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
        'file_number', 'name', 'name_ar', 'name_ar_first', 'name_ar_father', 'name_ar_family',
        'identity_type', 'identity_value', 'phone',
        'payment_type', 'insurance_company_id', 'charity_entity_id', 'department_id', 'notes', 'is_active',
        'date_of_birth', 'age', 'gender', 'country_of_origin', 'sponsor_name', 'sponsor_phone',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Patient $patient) {
            $first = trim((string) ($patient->name_ar_first ?? ''));
            $father = trim((string) ($patient->name_ar_father ?? ''));
            $family = trim((string) ($patient->name_ar_family ?? ''));
            if ($first !== '' && $family !== '') {
                $patient->name_ar = trim(implode(' ', array_filter([$first, $father, $family], fn ($s) => $s !== '')));
            }
            if ($patient->date_of_birth) {
                $dob = $patient->date_of_birth instanceof \DateTimeInterface
                    ? Carbon::instance($patient->date_of_birth)
                    : Carbon::parse($patient->date_of_birth);
                $patient->age = $dob->age;
            }
        });
    }

    /** Completed years: from date of birth if set, otherwise stored `age` (legacy). */
    public function ageInYears(): ?int
    {
        if ($this->date_of_birth) {
            $dob = $this->date_of_birth instanceof \DateTimeInterface
                ? Carbon::instance($this->date_of_birth)
                : Carbon::parse($this->date_of_birth);

            return $dob->age;
        }
        $a = $this->attributes['age'] ?? null;

        return $a !== null && $a !== '' ? (int) $a : null;
    }

    /** Full Arabic display: structured parts if present, otherwise legacy `name_ar`, then English `name`. */
    public function fullArabicName(): string
    {
        $first = trim((string) ($this->name_ar_first ?? ''));
        $father = trim((string) ($this->name_ar_father ?? ''));
        $family = trim((string) ($this->name_ar_family ?? ''));
        if ($first !== '' && $family !== '') {
            return trim(implode(' ', array_filter([$first, $father, $family], fn ($s) => $s !== '')));
        }
        $legacy = trim((string) ($this->name_ar ?? ''));

        return $legacy !== '' ? $legacy : trim((string) ($this->name ?? ''));
    }

    /**
     * For edit form: if Arabic parts are empty but legacy `name_ar` exists, suggest splitting (last word = family).
     *
     * @return array{0: string, 1: string, 2: string} [first, father, family] for input defaults
     */
    public function suggestedArabicNamePartsForForm(): array
    {
        $first = trim((string) ($this->name_ar_first ?? ''));
        $family = trim((string) ($this->name_ar_family ?? ''));
        if ($first !== '' || $family !== '') {
            return [$first, trim((string) ($this->name_ar_father ?? '')), $family];
        }
        $full = trim((string) ($this->name_ar ?? ''));
        if ($full === '') {
            return ['', '', ''];
        }
        $parts = preg_split('/\s+/u', $full, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) < 2) {
            return [$full, '', ''];
        }
        $fam = array_pop($parts);
        $given = implode(' ', $parts);

        return [$given, '', $fam];
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

    /** @return array<string, array{ar: string, en: string}> */
    public static function paymentTypeOptions(): array
    {
        return [
            'cash' => ['ar' => 'نقدي', 'en' => 'Cash'],
            'insurance' => ['ar' => 'تأمين', 'en' => 'Insurance'],
            'charity' => ['ar' => 'جمعية خيرية', 'en' => 'Charity'],
            'treatment_eligibility' => ['ar' => 'مؤهل للعلاج (مجانى)', 'en' => 'Treatment Eligible (Free)'],
        ];
    }

    /** @return list<string> */
    public static function paymentTypeKeys(): array
    {
        return array_keys(static::paymentTypeOptions());
    }

    /** مؤهل للعلاج (مجانى) — المريض لا يدفع. */
    public static function isTreatmentEligibility(?string $paymentType): bool
    {
        return ($paymentType ?? '') === 'treatment_eligibility';
    }

    public static function paymentTypeLabel(?string $key, ?string $locale = null): string
    {
        $opts = static::paymentTypeOptions();
        $locale = $locale ?? (app()->getLocale() === 'ar' ? 'ar' : 'en');
        $key = $key ?? 'cash';

        return $opts[$key][$locale] ?? $opts[$key]['ar'] ?? $key;
    }

    public static function paymentTypeValidationRule(): string
    {
        return 'required|in:' . implode(',', static::paymentTypeKeys());
    }

    /** تسمية طريقة الدفع للعرض */
    public function getPaymentTypeLabelAttribute(): string
    {
        return static::paymentTypeLabel($this->payment_type);
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
