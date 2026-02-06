<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceCompany extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'contact_person', 'phone', 'email', 'fax', 'address', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'insurance_company_id');
    }

    public function insuranceClaims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class, 'insurance_company_id');
    }
}
