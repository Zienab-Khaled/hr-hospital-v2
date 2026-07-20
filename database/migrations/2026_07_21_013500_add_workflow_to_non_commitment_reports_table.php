<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('non_commitment_reports', function (Blueprint $table) {
            $table->string('report_number')->nullable()->after('invoice_id');
            $table->timestamp('reported_at')->nullable()->after('report_date');
            $table->string('workflow_status')->default('pending_follow_up')->after('reported_at');

            $table->foreignId('collector_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('follow_up_id')->nullable()->after('collector_id')->constrained('users')->nullOnDelete();
            $table->foreignId('accountant_id')->nullable()->after('follow_up_id')->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->after('accountant_id')->constrained('users')->nullOnDelete();

            $table->timestamp('follow_up_at')->nullable()->after('manager_id');
            $table->timestamp('accountant_at')->nullable()->after('follow_up_at');
            $table->timestamp('manager_at')->nullable()->after('accountant_at');
        });
    }

    public function down(): void
    {
        Schema::table('non_commitment_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collector_id');
            $table->dropConstrainedForeignId('follow_up_id');
            $table->dropConstrainedForeignId('accountant_id');
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn([
                'report_number',
                'reported_at',
                'workflow_status',
                'follow_up_at',
                'accountant_at',
                'manager_at',
            ]);
        });
    }
};
