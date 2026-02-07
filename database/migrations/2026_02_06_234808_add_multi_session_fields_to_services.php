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
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_multi_session')->default(false)->after('is_active');
            $table->integer('session_count')->nullable()->after('is_multi_session');
            $table->integer('session_wait_time')->nullable()->after('session_count');
            $table->enum('session_wait_unit', ['days', 'weeks', 'months'])->nullable()->after('session_wait_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['is_multi_session', 'session_count', 'session_wait_time', 'session_wait_unit']);
        });
    }
};
