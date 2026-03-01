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
        Schema::table('written_commitments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('visit_id')->constrained()->nullOnDelete();
        });

        Schema::table('non_commitment_reports', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('visit_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('written_commitments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
        });

        Schema::table('non_commitment_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
        });
    }
};
