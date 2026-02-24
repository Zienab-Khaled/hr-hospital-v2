<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('audit_status')->default('under_review')->index(); // under_review, matched, rejected, ready_for_deposit
            $table->text('rejection_reason')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('audit_status')->default('under_review')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
