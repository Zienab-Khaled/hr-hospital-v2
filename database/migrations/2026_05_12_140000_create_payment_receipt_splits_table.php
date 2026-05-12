<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipt_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_receipt_id')->constrained('payment_receipts')->cascadeOnDelete();
            $table->string('payment_method', 50);
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipt_splits');
    }
};
