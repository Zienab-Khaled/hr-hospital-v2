<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('name_ar_first', 255)->nullable()->after('name_ar');
            $table->string('name_ar_father', 255)->nullable()->after('name_ar_first');
            $table->string('name_ar_family', 255)->nullable()->after('name_ar_father');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['name_ar_first', 'name_ar_father', 'name_ar_family']);
        });
    }
};
