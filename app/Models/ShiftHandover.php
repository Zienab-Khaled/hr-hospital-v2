<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftHandover extends Model
{
    protected $fillable = ['shift_id', 'handed_over_by', 'handed_over_at', 'handover_date', 'notes'];

    protected function casts(): array
    {
        return [
            'handed_over_at' => 'datetime',
            'handover_date' => 'date',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    /** عدد الزيارات في هذا الشيفت لنفس اليوم */
    public function getVisitsCountAttribute(): int
    {
        return Visit::where('shift_id', $this->shift_id)
            ->whereDate('visit_date', $this->handover_date)
            ->count();
    }

    /** عدد الفواتير المرتبطة بزيارات هذا الشيفت في نفس اليوم */
    public function getInvoicesCountAttribute(): int
    {
        $visitIds = Visit::where('shift_id', $this->shift_id)
            ->whereDate('visit_date', $this->handover_date)
            ->pluck('id');
        return Invoice::whereIn('visit_id', $visitIds)->count();
    }
}
