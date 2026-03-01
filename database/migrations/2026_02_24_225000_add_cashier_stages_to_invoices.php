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
            if (!Schema::hasColumn('invoices', 'cashier_otp')) {
                $table->string('cashier_otp')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('invoices', 'cashier_id')) {
                $table->foreignId('cashier_id')->nullable()->constrained('users')->after('cashier_otp');
            }
            if (!Schema::hasColumn('invoices', 'cashier_received_at')) {
                $table->timestamp('cashier_received_at')->nullable()->after('cashier_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropColumn(['cashier_otp', 'cashier_id', 'cashier_received_at']);
        });
    }
};
