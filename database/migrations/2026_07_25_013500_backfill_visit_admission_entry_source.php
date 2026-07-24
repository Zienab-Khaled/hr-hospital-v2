<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * تعبئة مسار الدخول الفارغ حتى يظهر في قائمة مكتب الدخول.
 * الإحصائيات تبقى على القسم الطبي منفصلًا عن مسار الدخول.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('visits')
            ->whereNull('admission_entry_source')
            ->orWhere('admission_entry_source', '')
            ->update(['admission_entry_source' => 'outpatient_clinics']);
    }

    public function down(): void
    {
        // لا نرجع القيم القديمة المجهولة
    }
};
