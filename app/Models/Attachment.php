<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'attachable_type', 'attachable_id', 'document_type', 'file_path', 'file_name', 'file_size', 'mime_type', 'is_approved', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean'];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
