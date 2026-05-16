<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

final class ShiftStaff
{
    public static function pivotTableReady(): bool
    {
        return Schema::hasTable('shift_user');
    }
}
