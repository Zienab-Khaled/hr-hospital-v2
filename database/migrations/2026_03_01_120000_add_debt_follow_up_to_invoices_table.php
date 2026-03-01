<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('debt_status', 20)->nullable()->after('deposited_at')->comment('null=لم يُبلّغ, notified=تم التبليغ, paid=تم السداد');
            $table->timestamp('debt_notified_at')->nullable()->after('debt_status');
            $table->foreignId('debt_notified_by')->nullable()->after('debt_notified_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['debt_notified_by']);
            $table->dropColumn(['debt_status', 'debt_notified_at', 'debt_notified_by']);
        });
    }
};
