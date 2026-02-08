<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Department;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Laboratory Services
            ['name' => 'Complete Blood Count (CBC)', 'name_ar' => 'تحليل الدم الشامل', 'code' => 'LAB-001', 'default_price' => 150.00, 'department' => 'Laboratory'],
            ['name' => 'Blood Sugar Test', 'name_ar' => 'فحص السكر', 'code' => 'LAB-002', 'default_price' => 80.00, 'department' => 'Laboratory'],
            ['name' => 'Lipid Profile', 'name_ar' => 'فحص الدهون', 'code' => 'LAB-003', 'default_price' => 200.00, 'department' => 'Laboratory'],
            ['name' => 'Liver Function Test', 'name_ar' => 'فحص وظائف الكبد', 'code' => 'LAB-004', 'default_price' => 250.00, 'department' => 'Laboratory'],
            ['name' => 'Kidney Function Test', 'name_ar' => 'فحص وظائف الكلى', 'code' => 'LAB-005', 'default_price' => 250.00, 'department' => 'Laboratory'],
            ['name' => 'Thyroid Function Test', 'name_ar' => 'فحص الغدة الدرقية', 'code' => 'LAB-006', 'default_price' => 300.00, 'department' => 'Laboratory'],
            ['name' => 'Urine Analysis', 'name_ar' => 'تحليل البول', 'code' => 'LAB-007', 'default_price' => 100.00, 'department' => 'Laboratory'],
            
            // Radiology Services
            ['name' => 'X-Ray Chest', 'name_ar' => 'أشعة الصدر', 'code' => 'RAD-001', 'default_price' => 200.00, 'department' => 'Radiology'],
            ['name' => 'CT Scan Brain', 'name_ar' => 'أشعة مقطعية للدماغ', 'code' => 'RAD-002', 'default_price' => 800.00, 'department' => 'Radiology'],
            ['name' => 'MRI Brain', 'name_ar' => 'رنين مغناطيسي للدماغ', 'code' => 'RAD-003', 'default_price' => 1500.00, 'department' => 'Radiology'],
            ['name' => 'Ultrasound Abdomen', 'name_ar' => 'سونار البطن', 'code' => 'RAD-004', 'default_price' => 350.00, 'department' => 'Radiology'],
            ['name' => 'Mammogram', 'name_ar' => 'أشعة الثدي', 'code' => 'RAD-005', 'default_price' => 400.00, 'department' => 'Radiology'],
            ['name' => 'Bone Density Scan', 'name_ar' => 'فحص كثافة العظام', 'code' => 'RAD-006', 'default_price' => 500.00, 'department' => 'Radiology'],
            
            // Consultation Services
            ['name' => 'General Consultation', 'name_ar' => 'استشارة عامة', 'code' => 'CON-001', 'default_price' => 200.00, 'department' => 'Internal Medicine'],
            ['name' => 'Specialist Consultation', 'name_ar' => 'استشارة تخصصية', 'code' => 'CON-002', 'default_price' => 300.00, 'department' => null],
            ['name' => 'Cardiology Consultation', 'name_ar' => 'استشارة قلبية', 'code' => 'CON-003', 'default_price' => 350.00, 'department' => 'Cardiology'],
            ['name' => 'Neurology Consultation', 'name_ar' => 'استشارة أعصاب', 'code' => 'CON-004', 'default_price' => 350.00, 'department' => 'Neurology'],
            ['name' => 'Orthopedic Consultation', 'name_ar' => 'استشارة عظام', 'code' => 'CON-005', 'default_price' => 300.00, 'department' => 'Orthopedics'],
            ['name' => 'Pediatric Consultation', 'name_ar' => 'استشارة أطفال', 'code' => 'CON-006', 'default_price' => 250.00, 'department' => 'Pediatrics'],
            
            // Surgery Services
            ['name' => 'Appendectomy', 'name_ar' => 'استئصال الزائدة', 'code' => 'SUR-001', 'default_price' => 5000.00, 'department' => 'Surgery'],
            ['name' => 'Hernia Repair', 'name_ar' => 'إصلاح الفتق', 'code' => 'SUR-002', 'default_price' => 6000.00, 'department' => 'Surgery'],
            ['name' => 'Gallbladder Removal', 'name_ar' => 'استئصال المرارة', 'code' => 'SUR-003', 'default_price' => 7000.00, 'department' => 'Surgery'],
            ['name' => 'Cesarean Section', 'name_ar' => 'عملية قيصرية', 'code' => 'SUR-004', 'default_price' => 8000.00, 'department' => 'Obstetrics & Gynecology'],
            ['name' => 'Normal Delivery', 'name_ar' => 'ولادة طبيعية', 'code' => 'SUR-005', 'default_price' => 4000.00, 'department' => 'Obstetrics & Gynecology'],
            
            // Physiotherapy Services (Multi-Session)
            ['name' => 'Physiotherapy Session', 'name_ar' => 'جلسة علاج طبيعي', 'code' => 'PHY-001', 'default_price' => 150.00, 'department' => 'Physiotherapy', 
             'is_multi_session' => true, 'session_count' => 10, 'session_wait_time' => 2, 'session_wait_unit' => 'days'],
            ['name' => 'Massage Therapy Session', 'name_ar' => 'جلسة تدليك علاجي', 'code' => 'PHY-002', 'default_price' => 200.00, 'department' => 'Physiotherapy',
             'is_multi_session' => true, 'session_count' => 6, 'session_wait_time' => 3, 'session_wait_unit' => 'days'],
            
            // Dental Services
            ['name' => 'Dental Cleaning', 'name_ar' => 'تنظيف الأسنان', 'code' => 'DEN-001', 'default_price' => 300.00, 'department' => 'Dental'],
            ['name' => 'Tooth Filling', 'name_ar' => 'حشو أسنان', 'code' => 'DEN-002', 'default_price' => 400.00, 'department' => 'Dental'],
            ['name' => 'Tooth Extraction', 'name_ar' => 'خلع الأسنان', 'code' => 'DEN-003', 'default_price' => 350.00, 'department' => 'Dental'],
            ['name' => 'Root Canal Treatment', 'name_ar' => 'علاج جذور', 'code' => 'DEN-004', 'default_price' => 800.00, 'department' => 'Dental'],
            
            // Emergency Services
            ['name' => 'Emergency Consultation', 'name_ar' => 'استشارة طوارئ', 'code' => 'EMR-001', 'default_price' => 500.00, 'department' => 'Emergency'],
            ['name' => 'Emergency Admission', 'name_ar' => 'دخول طوارئ', 'code' => 'EMR-002', 'default_price' => 1000.00, 'department' => 'Emergency'],
            
            // ICU & Admission
            ['name' => 'ICU Per Day', 'name_ar' => 'عناية مركزة يومي', 'code' => 'ICU-001', 'default_price' => 3000.00, 'department' => 'ICU'],
            ['name' => 'Private Room Per Day', 'name_ar' => 'غرفة خاصة يومي', 'code' => 'ADM-001', 'default_price' => 800.00, 'department' => 'Admission'],
            ['name' => 'Shared Room Per Day', 'name_ar' => 'غرفة مشتركة يومي', 'code' => 'ADM-002', 'default_price' => 500.00, 'department' => 'Admission'],
        ];

        foreach ($services as $service) {
            $deptName = $service['department'] ?? null;
            unset($service['department']);
            
            $dept = $deptName ? Department::where('name', $deptName)->first() : null;
            
            Service::updateOrCreate(
                ['code' => $service['code']],
                array_merge($service, [
                    'department_id' => $dept?->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
