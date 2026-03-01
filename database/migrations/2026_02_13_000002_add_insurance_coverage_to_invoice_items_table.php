<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('insurance_coverage_type', 20)->nullable()->after('description')->comment('percentage or fixed');
            $table->decimal('insurance_coverage_value', 12, 2)->nullable()->after('insurance_coverage_type')->comment('percentage 0-100 or fixed amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['insurance_coverage_type', 'insurance_coverage_value']);
        });
    }
};
