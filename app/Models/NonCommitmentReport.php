<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonCommitmentReport extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING_FOLLOW_UP = 'pending_follow_up';

    public const STATUS_PENDING_ACCOUNTANT = 'pending_accountant';

    public const STATUS_PENDING_MANAGER = 'pending_manager';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'patient_id',
        'visit_id',
        'invoice_id',
        'report_date',
        'report_number',
        'reported_at',
        'workflow_status',
        'file_path',
        'notes',
        'created_by',
        'collector_id',
        'follow_up_id',
        'accountant_id',
        'manager_id',
        'follow_up_at',
        'accountant_at',
        'manager_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'reported_at' => 'datetime',
            'follow_up_at' => 'datetime',
            'accountant_at' => 'datetime',
            'manager_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function followUpUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_id');
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function workflowStatusLabel(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this->workflow_status) {
            self::STATUS_PENDING_FOLLOW_UP => $ar ? 'بانتظار متابعة المرضى' : 'Pending patient follow-up',
            self::STATUS_PENDING_ACCOUNTANT => $ar ? 'بانتظار المحاسب' : 'Pending accountant',
            self::STATUS_PENDING_MANAGER => $ar ? 'بانتظار المدير' : 'Pending manager',
            self::STATUS_COMPLETED => $ar ? 'مكتمل' : 'Completed',
            default => $this->workflow_status ?? '—',
        };
    }

    public function canAdvance(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'manager'])) {
            return in_array($this->workflow_status, [
                self::STATUS_PENDING_FOLLOW_UP,
                self::STATUS_PENDING_ACCOUNTANT,
                self::STATUS_PENDING_MANAGER,
            ], true);
        }

        return match ($this->workflow_status) {
            self::STATUS_PENDING_FOLLOW_UP => $user->hasRole('patient_follow_up'),
            self::STATUS_PENDING_ACCOUNTANT => $user->hasRole('accountant'),
            self::STATUS_PENDING_MANAGER => $user->hasAnyRole(['manager', 'admin']),
            default => false,
        };
    }
}
