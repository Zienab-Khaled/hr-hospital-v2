<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('patient_id')->constrained('departments')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->after('visit_date')->constrained('shifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['shift_id']);
        });
    }
};
