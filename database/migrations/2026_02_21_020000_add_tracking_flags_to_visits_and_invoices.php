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
        Schema::table('visits', function (Blueprint $table) {
            $table->timestamp('printed_eligibility_at')->nullable();
            $table->timestamp('printed_price_inquiry_at')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('sent_to_charity_mail_at')->nullable();
            $table->timestamp('printed_commitment_at')->nullable();
            $table->timestamp('printed_non_commitment_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['printed_eligibility_at', 'printed_price_inquiry_at']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['sent_to_charity_mail_at', 'printed_commitment_at', 'printed_non_commitment_at']);
        });
    }
};
