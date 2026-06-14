<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->text('eligibility_notes')->nullable()->after('notes');
            $table->foreignId('eligibility_print_department_id')->nullable()->after('eligibility_notes')->constrained('departments')->nullOnDelete();
            $table->boolean('eligibility_without_department')->default(false)->after('eligibility_print_department_id');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eligibility_print_department_id');
            $table->dropColumn(['eligibility_notes', 'eligibility_without_department']);
        });
    }
};
