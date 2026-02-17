<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'email',
        'username',
        'password',
        'department_id',
        'job_title',
        'job_title_ar',
        'status',
        'last_login_at',
        'signature',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    /**
     * Get the manager user for electronic signatures on print documents
     * Returns the first active user with 'manager' or 'admin' role
     */
    public static function getManagerForSignature(): ?User
    {
        return static::whereHas('roles', function ($query) {
            $query->whereIn('name', ['manager', 'admin']);
        })
        ->where('status', 'active')
        ->orderBy('id')
        ->first();
    }
}
