<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['id_number', 'passport_number', 'iqama_number']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->string('identity_type')->nullable()->after('name_ar');
            $table->string('identity_value')->nullable()->unique()->after('identity_type');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['identity_type', 'identity_value']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->string('id_number')->nullable()->after('name_ar');
            $table->string('passport_number')->nullable()->after('id_number');
            $table->string('iqama_number')->nullable()->after('passport_number');
        });
    }
};
