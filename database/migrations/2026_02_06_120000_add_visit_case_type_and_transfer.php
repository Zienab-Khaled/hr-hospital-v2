<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('case_type')->default('clinics')->after('visit_date'); // clinics, emergency
            $table->foreignId('transferred_department_id')->nullable()->after('notes')->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['transferred_department_id']);
            $table->dropColumn(['case_type', 'transferred_department_id']);
        });
    }
};
