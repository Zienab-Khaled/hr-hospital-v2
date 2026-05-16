<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

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

    public function shifts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'shift_user')->withTimestamps();
    }

    /** Delegations I created (I am the delegator). */
    public function delegationsGiven(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Delegation::class, 'delegator_id');
    }

    /** Delegations assigned to me (I am the delegate). */
    public function delegationsReceived(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Delegation::class, 'delegate_to_id');
    }

    /**
     * Whether this user has an active delegation granting them the given permission
     * (i.e. some delegator with that permission delegated to this user for today).
     */
    public function hasPermissionViaDelegation(string $ability): bool
    {
        $today = now()->toDateString();
        $delegations = Delegation::query()
            ->where('delegate_to_id', $this->id)
            ->where('from_date', '<=', $today)
            ->where('to_date', '>=', $today)
            ->with('delegator')
            ->get();
        foreach ($delegations as $d) {
            if ($d->delegator && method_exists($d->delegator, 'hasPermissionTo') && $d->delegator->hasPermissionTo($ability)) {
                return true;
            }
        }
        return false;
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
