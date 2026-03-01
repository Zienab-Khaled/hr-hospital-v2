<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('charity_entity_id')->constrained('departments')->nullOnDelete();
        });

        Schema::create('patient_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('to_department_id')->constrained('departments')->cascadeOnDelete();
            $table->timestamp('transferred_at');
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_transfers');
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });
    }
};
