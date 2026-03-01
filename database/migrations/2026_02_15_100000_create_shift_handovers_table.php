<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('handed_over_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('handed_over_at');
            $table->date('handover_date'); // تاريخ الشيفت المُسلّم (لربط الزيارات/الفواتير)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};
