<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Emergency', 'name_ar' => 'الطوارئ', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Surgery', 'name_ar' => 'الجراحة', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Pediatrics', 'name_ar' => 'طب الأطفال', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Obstetrics & Gynecology', 'name_ar' => 'النساء والولادة', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Internal Medicine', 'name_ar' => 'الباطنية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Cardiology', 'name_ar' => 'القلبية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Orthopedics', 'name_ar' => 'العظام', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Neurology', 'name_ar' => 'الأعصاب', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Radiology', 'name_ar' => 'الأشعة', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Laboratory', 'name_ar' => 'المختبر', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Pharmacy', 'name_ar' => 'الصيدلية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Dental', 'name_ar' => 'الأسنان', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Dermatology', 'name_ar' => 'الجلدية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'ENT', 'name_ar' => 'الأنف والأذن والحنجرة', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Ophthalmology', 'name_ar' => 'العيون', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Physiotherapy', 'name_ar' => 'العلاج الطبيعي', 'category' => 'medical', 'is_active' => true],
            ['name' => 'ICU', 'name_ar' => 'العناية المركزة', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Admission', 'name_ar' => 'الدخول', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Management', 'name_ar' => 'الإدارة التشغيلية', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Reception', 'name_ar' => 'الاستقبال', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Insurance', 'name_ar' => 'التأمين', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Social Services', 'name_ar' => 'الخدمة الاجتماعية', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Finance', 'name_ar' => 'المالية', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Debts', 'name_ar' => 'المديونيات', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Nursing', 'name_ar' => 'التمريض', 'category' => 'medical', 'is_active' => true],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }
    }
}
