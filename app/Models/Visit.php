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

    public const ADMISSION_OUTPATIENT_CLINICS = 'outpatient_clinics';

    public const ADMISSION_EMERGENCY = 'emergency';

    protected $fillable = [
        'patient_id', 'department_id', 'admission_entry_source', 'visit_date', 'shift_id', 'case_type', 'notes',
        'eligibility_notes', 'eligibility_print_department_id', 'eligibility_without_department',
        'referral_number', 'transferred_department_id', 'registered_by',
        'printed_eligibility_at', 'printed_price_inquiry_at',
        'last_eligibility_services', 'last_price_inquiry_services',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'printed_eligibility_at' => 'datetime',
            'printed_price_inquiry_at' => 'datetime',
            'eligibility_without_department' => 'boolean',
            'last_eligibility_services' => 'array',
            'last_price_inquiry_services' => 'array',
        ];
    }

    public function eligibilityPrintDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'eligibility_print_department_id');
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

    /** تخمين مسار الدخول من اسم/كود القسم (طوارئ vs عيادات) عند الإنشاء التلقائي */
    public static function inferAdmissionEntryFromDepartment(?Department $department): string
    {
        if (! $department) {
            return self::ADMISSION_OUTPATIENT_CLINICS;
        }
        $blob = mb_strtolower(
            trim((string) ($department->name_ar ?? '').' '.(string) ($department->name ?? '').' '.(string) ($department->code ?? ''))
        );
        $needles = ['طوارئ', 'طوارى', 'emergency', 'e.r', ' e.r', 'e.r ', ' er', 'er ', 'ed ', ' aed', 'accident'];
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($blob, $needle)) {
                return self::ADMISSION_EMERGENCY;
            }
        }

        return self::ADMISSION_OUTPATIENT_CLINICS;
    }

    /** قيمة مسار الدخول الصالحة فقط — افتراضي: عيادات خارجية */
    public static function normalizeAdmissionEntrySource(?string $source, ?Department $department = null): string
    {
        if (in_array($source, [self::ADMISSION_OUTPATIENT_CLINICS, self::ADMISSION_EMERGENCY], true)) {
            return $source;
        }

        return self::inferAdmissionEntryFromDepartment($department);
    }

    /** إذا كان مسار الدخول فارغًا نثبّته حتى يظهر في مكتب الدخول */
    public function fillMissingAdmissionEntrySource(?string $preferred = null): void
    {
        if ($this->admission_entry_source) {
            return;
        }

        $this->admission_entry_source = self::normalizeAdmissionEntrySource(
            $preferred,
            $this->relationLoaded('department') ? $this->department : $this->department()->first()
        );
        $this->save();
    }

    public function getAdmissionEntrySourceLabelAttribute(): string
    {
        return match ($this->admission_entry_source) {
            self::ADMISSION_EMERGENCY => app()->getLocale() === 'ar' ? 'الطوارئ' : 'Emergency',
            self::ADMISSION_OUTPATIENT_CLINICS => app()->getLocale() === 'ar' ? 'مكتب دخول العيادات الخارجية' : 'Outpatient clinics admission',
            default => '—',
        };
    }

    public function admissionEntrySourceBadgeClass(): string
    {
        return $this->admission_entry_source === self::ADMISSION_EMERGENCY
            ? 'bg-rose-100 text-rose-800 ring-1 ring-rose-200'
            : 'bg-teal-100 text-teal-800 ring-1 ring-teal-200';
    }

    /**
     * Get the charity approval media item for this visit
     */
    public function charityApprovalDocument()
    {
        return $this->getFirstMedia('charity_approval');
    }
}
