<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charity_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('charity_entity_id')->constrained()->cascadeOnDelete();
            $table->date('sent_date')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); // sent, under_review, approved, rejected, paid
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('entity_response_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charity_claims');
    }
};
