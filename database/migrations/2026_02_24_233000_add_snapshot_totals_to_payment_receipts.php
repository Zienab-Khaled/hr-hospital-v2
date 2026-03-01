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
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->decimal('invoice_snapshot_total', 12, 2)->nullable()->after('amount');
            $table->decimal('invoice_snapshot_paid', 12, 2)->nullable()->after('invoice_snapshot_total');
            $table->decimal('invoice_snapshot_remaining', 12, 2)->nullable()->after('invoice_snapshot_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->dropColumn(['invoice_snapshot_total', 'invoice_snapshot_paid', 'invoice_snapshot_remaining']);
        });
    }
};
