<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('admission_entry_source', 32)
                ->nullable()
                ->after('department_id')
                ->comment('outpatient_clinics | emergency — مسار مكتب الدخول');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('admission_entry_source');
        });
    }
};
