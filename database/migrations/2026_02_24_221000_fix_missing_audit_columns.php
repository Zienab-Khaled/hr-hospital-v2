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
            if (!Schema::hasColumn('invoices', 'audit_status')) {
                $table->string('audit_status')->default('under_review')->index();
            }
            if (!Schema::hasColumn('invoices', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'audit_status')) {
                $table->string('audit_status')->default('under_review')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['audit_status', 'rejection_reason']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('audit_status');
        });
    }
};
