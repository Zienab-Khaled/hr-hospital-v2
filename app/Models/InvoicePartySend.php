<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InvoicePartySend extends Model
{
    protected $fillable = [
        'invoice_id', 'recipient_type', 'recipient_entity_id', 'recipient_email', 'recipient_name',
        'token', 'sent_at', 'sent_by', 'response_action', 'response_text', 'response_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'response_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function isResponded(): bool
    {
        return ! empty($this->response_action);
    }
}
