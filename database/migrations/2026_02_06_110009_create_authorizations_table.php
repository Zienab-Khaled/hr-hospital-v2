<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->string('service_type'); // lab, radiology, admission, etc.
            $table->string('referring_entity')->nullable();
            $table->string('payment_type'); // insurance, cash, charity
            $table->foreignId('insurance_company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('charity_entity_id')->nullable()->constrained()->nullOnDelete();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('active'); // active, expired, used
            $table->boolean('one_time_use')->default(true);
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorizations');
    }
};
