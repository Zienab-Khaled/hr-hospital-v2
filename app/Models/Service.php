<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    protected $fillable = ['department_id', 'name', 'name_ar', 'code', 'default_price', 'is_active', 'is_multi_session', 'session_count', 'session_wait_time', 'session_wait_unit'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_multi_session' => 'boolean',
            'default_price' => 'decimal:2',
            'session_count' => 'integer',
            'session_wait_time' => 'integer',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
