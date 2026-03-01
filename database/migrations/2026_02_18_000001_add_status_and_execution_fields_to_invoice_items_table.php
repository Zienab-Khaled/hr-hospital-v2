<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('total_price'); // pending, completed, cancelled
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            $table->date('execution_date')->nullable()->after('completed_by');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['status', 'completed_at', 'completed_by', 'execution_date']);
        });
    }
};
