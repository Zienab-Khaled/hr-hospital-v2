<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        // أقسام المرضى / نوع الحالة (الزيارة) — القائمة النهائية فقط
        $medical = [
            ['name' => 'Outpatient Clinics', 'name_ar' => 'العيادات الخارجية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Emergency', 'name_ar' => 'الطوارئ', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Oncology Center', 'name_ar' => 'مركز الأورام', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Heart Center', 'name_ar' => 'مركز القلب', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Radiology', 'name_ar' => 'الأشعة', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Laboratory', 'name_ar' => 'المختبر', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Pharmacy', 'name_ar' => 'الصيدلية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Eye Center', 'name_ar' => 'مركز العيون', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Surgery Ward Men', 'name_ar' => 'تنويم جراحة رجال', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Surgery Ward Women', 'name_ar' => 'تنويم جراحة نساء', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Internal Ward Women', 'name_ar' => 'تنويم باطنية نساء', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Internal Ward Men', 'name_ar' => 'تنويم باطنية رجال', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Cardiac Care', 'name_ar' => 'العناية القلبية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'ICU', 'name_ar' => 'العناية المركزية', 'category' => 'medical', 'is_active' => true],
            ['name' => 'Operations', 'name_ar' => 'العمليات', 'category' => 'medical', 'is_active' => true],
        ];

        foreach ($medical as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }

        // أقسام إدارية (للموظفين والاستقبال والتأمين والمحاسبة...)
        $administrative = [
            ['name' => 'Admission', 'name_ar' => 'الدخول', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Management', 'name_ar' => 'الإدارة التشغيلية', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Reception', 'name_ar' => 'الاستقبال', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Insurance', 'name_ar' => 'التأمين', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Social Services', 'name_ar' => 'الخدمة الاجتماعية', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Finance', 'name_ar' => 'المالية', 'category' => 'administrative', 'is_active' => true],
            ['name' => 'Debts', 'name_ar' => 'المديونيات', 'category' => 'administrative', 'is_active' => true],
        ];

        foreach ($administrative as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }

        // إخفاء أقسام قديمة لم تعد مستخدمة (النساء والولادة وغيرها)
        $obsolete = [
            'Obstetrics & Gynecology',
            'Pediatrics',
            'Surgery',
            'Internal Medicine',
            'Cardiology',
            'Orthopedics',
            'Neurology',
            'Dental',
            'Dermatology',
            'ENT',
            'Ophthalmology',
            'Physiotherapy',
            'Nursing',
        ];
        foreach ($obsolete as $name) {
            Department::where('name', $name)->update(['is_active' => false]);
        }
    }
}
