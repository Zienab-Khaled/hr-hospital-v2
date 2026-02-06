<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['code' => 'LAB-001', 'name' => 'Complete Blood Count', 'name_ar' => 'تحليل صورة دم كاملة', 'price' => 50, 'dept' => null],
            ['code' => 'LAB-002', 'name' => 'Blood Sugar', 'name_ar' => 'سكر دم', 'price' => 25, 'dept' => null],
            ['code' => 'LAB-003', 'name' => 'Liver Function', 'name_ar' => 'وظائف كبد', 'price' => 80, 'dept' => null],
            ['code' => 'RAD-001', 'name' => 'Chest X-Ray', 'name_ar' => 'أشعة صدر', 'price' => 100, 'dept' => null],
            ['code' => 'RAD-002', 'name' => 'Ultrasound Abdomen', 'name_ar' => 'أشعة موجات فوق صوتية بطن', 'price' => 150, 'dept' => null],
            ['code' => 'RAD-003', 'name' => 'CT Scan', 'name_ar' => 'أشعة مقطعية', 'price' => 500, 'dept' => null],
            ['code' => 'ADM-001', 'name' => 'General Ward Day', 'name_ar' => 'يوم تنويم جناح عام', 'price' => 300, 'dept' => null],
            ['code' => 'ADM-002', 'name' => 'ICU Day', 'name_ar' => 'يوم عناية مركزة', 'price' => 1500, 'dept' => null],
            ['code' => 'CLN-001', 'name' => 'General Clinic Visit', 'name_ar' => 'كشف عيادة عامة', 'price' => 75, 'dept' => 'RECEPTION'],
            ['code' => 'EMG-001', 'name' => 'Emergency Visit', 'name_ar' => 'كشف طوارئ', 'price' => 120, 'dept' => 'RECEPTION'],
        ];

        foreach ($services as $s) {
            $deptId = null;
            if (!empty($s['dept'])) {
                $dept = Department::where('code', $s['dept'])->first();
                $deptId = $dept?->id;
            }

            Service::firstOrCreate(
                ['code' => $s['code']],
                [
                    'department_id' => $deptId,
                    'name' => $s['name'],
                    'name_ar' => $s['name_ar'] ?? null,
                    'default_price' => $s['price'],
                    'is_active' => true,
                ]
            );
        }
    }
}
