<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'name_ar', 'category', 'code', 'is_active', 'manager_id', 'entry_fee'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'entry_fee' => 'decimal:2'];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }


    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function patientTransfersFrom(): HasMany
    {
        return $this->hasMany(PatientTransfer::class, 'from_department_id');
    }

    /**
     * أقسام عامة لمسار الدخول (عيادات/طوارئ) — ليست تخصصًا طبيًا للزيارة.
     */
    public function isGenericEntryDepartment(): bool
    {
        $blob = mb_strtolower(trim(
            ($this->name_ar ?? '').' '
            .($this->name ?? '').' '
            .($this->code ?? '')
        ));

        foreach (['العيادات الخارجية', 'outpatient', 'طوارئ', 'طوارى', 'emergency'] as $needle) {
            if (str_contains($blob, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    /** أقسام طبية متخصصة (بدون عيادات خارجية / طوارئ كمسار دخول) */
    public function scopeSpecializedMedical($query)
    {
        return $query->where('category', 'medical')->where('is_active', true);
    }
}
