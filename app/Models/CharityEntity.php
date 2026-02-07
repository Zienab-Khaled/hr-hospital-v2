<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharityEntity extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'name_ar', 'contact_person', 'phone', 'email', 'fax', 'address', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'charity_entity_id');
    }

    public function charityClaims(): HasMany
    {
        return $this->hasMany(CharityClaim::class, 'charity_entity_id');
    }
}
