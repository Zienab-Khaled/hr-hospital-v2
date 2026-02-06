<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /** تسجيل كل عملية باسم الموظف (من خلال المستخدم الحالي) */
    public static function log(string $action, ?string $subjectType = null, ?int $subjectId = null, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user?->getKey(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /** اسم الموظف الحالي (ثابت لكل عملية) */
    public static function currentEmployeeName(): ?string
    {
        $user = Auth::user();
        return $user?->employee?->name ?: $user?->name ?: $user?->username;
    }
}
