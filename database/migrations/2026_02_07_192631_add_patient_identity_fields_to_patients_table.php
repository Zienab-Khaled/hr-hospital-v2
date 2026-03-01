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
        Schema::table('patients', function (Blueprint $table) {
            // Identity documents
            $table->string('passport_number')->nullable()->unique()->after('id_number');
            $table->string('iqama_number')->nullable()->unique()->after('passport_number');
            
            // Personal info
            $table->integer('age')->nullable()->after('name_ar');
            $table->enum('gender', ['male', 'female'])->nullable()->after('age');
            $table->string('country_of_origin')->nullable()->after('gender');
            $table->string('current_location')->nullable()->after('country_of_origin');
            $table->string('sponsor_name')->nullable()->after('current_location');
            $table->string('sponsor_phone')->nullable()->after('sponsor_name');
            
            // Add index for faster search
            $table->index(['id_number', 'passport_number', 'iqama_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'passport_number',
                'iqama_number',
                'age',
                'gender',
                'country_of_origin',
                'current_location',
                'sponsor_name',
                'sponsor_phone'
            ]);
        });
    }
};
