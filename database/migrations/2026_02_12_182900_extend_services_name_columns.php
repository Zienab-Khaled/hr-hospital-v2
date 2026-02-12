<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE services MODIFY name VARCHAR(1000) NOT NULL, MODIFY name_ar VARCHAR(1000) NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE services MODIFY name VARCHAR(255) NOT NULL, MODIFY name_ar VARCHAR(255) NULL');
        }
    }
};
