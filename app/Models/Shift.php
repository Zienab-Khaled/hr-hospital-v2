<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = ['name', 'name_ar', 'start_time', 'end_time', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Get the shift that would be "current" at a given time (e.g. now).
     * Assumes shifts don't overlap and cover 24h (e.g. 00:00-08:00, 08:00-16:00, 16:00-24:00).
     */
    public static function currentAt(?string $time = null): ?self
    {
        $t = $time ? \Carbon\Carbon::parse($time)->format('H:i:s') : now()->format('H:i:s');
        $shifts = static::where('is_active', true)->orderBy('sort_order')->get();
        foreach ($shifts as $shift) {
            $start = \Carbon\Carbon::parse($shift->start_time)->format('H:i:s');
            $end = \Carbon\Carbon::parse($shift->end_time)->format('H:i:s');
            if ($start <= $end) {
                if ($t >= $start && $t < $end) return $shift;
            } else {
                if ($t >= $start || $t < $end) return $shift;
            }
        }
        return $shifts->first();
    }
}
