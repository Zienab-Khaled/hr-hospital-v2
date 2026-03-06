<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->change();
            $table->foreignId('department_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable(false)->change();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });
    }
};
