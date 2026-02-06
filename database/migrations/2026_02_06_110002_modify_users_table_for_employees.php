<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('id');
            $table->foreignId('employee_id')->nullable()->after('username')->constrained()->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['username', 'employee_id', 'last_login_at']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
