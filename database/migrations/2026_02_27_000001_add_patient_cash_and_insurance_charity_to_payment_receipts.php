<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->decimal('patient_cash_amount', 12, 2)->nullable()->after('amount');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payment_receipts MODIFY COLUMN payment_method VARCHAR(50) NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->dropColumn('patient_cash_amount');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payment_receipts MODIFY COLUMN payment_method ENUM('cash','card','bank_transfer','cheque') NOT NULL");
        }
    }
};
