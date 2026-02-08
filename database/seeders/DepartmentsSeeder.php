<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Emergency', 'name_ar' => 'الطوارئ', 'is_active' => true],
            ['name' => 'Surgery', 'name_ar' => 'الجراحة', 'is_active' => true],
            ['name' => 'Pediatrics', 'name_ar' => 'طب الأطفال', 'is_active' => true],
            ['name' => 'Obstetrics & Gynecology', 'name_ar' => 'النساء والولادة', 'is_active' => true],
            ['name' => 'Internal Medicine', 'name_ar' => 'الباطنية', 'is_active' => true],
            ['name' => 'Cardiology', 'name_ar' => 'القلبية', 'is_active' => true],
            ['name' => 'Orthopedics', 'name_ar' => 'العظام', 'is_active' => true],
            ['name' => 'Neurology', 'name_ar' => 'الأعصاب', 'is_active' => true],
            ['name' => 'Radiology', 'name_ar' => 'الأشعة', 'is_active' => true],
            ['name' => 'Laboratory', 'name_ar' => 'المختبر', 'is_active' => true],
            ['name' => 'Pharmacy', 'name_ar' => 'الصيدلية', 'is_active' => true],
            ['name' => 'Dental', 'name_ar' => 'الأسنان', 'is_active' => true],
            ['name' => 'Dermatology', 'name_ar' => 'الجلدية', 'is_active' => true],
            ['name' => 'ENT', 'name_ar' => 'الأنف والأذن والحنجرة', 'is_active' => true],
            ['name' => 'Ophthalmology', 'name_ar' => 'العيون', 'is_active' => true],
            ['name' => 'Physiotherapy', 'name_ar' => 'العلاج الطبيعي', 'is_active' => true],
            ['name' => 'ICU', 'name_ar' => 'العناية المركزة', 'is_active' => true],
            ['name' => 'Admission', 'name_ar' => 'الدخول', 'is_active' => true],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }
    }
}
