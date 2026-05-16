<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shift_user')->withTimestamps();
    }

    /** اسم الفترة بالإنجليزية (Morning / Afternoon / Night) — يُعرض دائماً في الفلاتر حتى لو الواجهة عربية. */
    public function englishPeriodName(): string
    {
        $n = strtolower(trim((string) ($this->name ?? '')));

        if (str_contains($n, 'morning')) {
            return 'Morning';
        }
        if (str_contains($n, 'afternoon')) {
            return 'Afternoon';
        }
        if (str_contains($n, 'night')) {
            return 'Night';
        }

        if (str_contains($n, 'shift 1') || str_contains($n, '12-8') || str_contains($n, '12–8')) {
            return 'Night';
        }
        if (str_contains($n, 'shift 2') || str_contains($n, '8-4') || str_contains($n, '8–4')) {
            return 'Morning';
        }
        if (str_contains($n, 'shift 3') || str_contains($n, '4-12') || str_contains($n, '4–12')) {
            return 'Afternoon';
        }

        return $this->name ?: 'Shift';
    }

    /** مثال: Morning (08:00 – 16:00) */
    public function englishLabelWithTime(): string
    {
        $start = \Carbon\Carbon::parse($this->start_time)->format('H:i');
        $end = \Carbon\Carbon::parse($this->end_time)->format('H:i');

        return $this->englishPeriodName() . ' (' . $start . ' – ' . $end . ')';
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

    /** @alias currentAt() — للتوافق مع الاستدعاءات القديمة */
    public static function getCurrentShift(): ?self
    {
        return static::currentAt();
    }
}
