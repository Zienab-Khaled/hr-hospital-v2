<?php

namespace Database\Seeders;

use App\Models\CharityEntity;
use App\Models\InsuranceCompany;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientsSeeder extends Seeder
{
    public function run(): void
    {
        $bupa = InsuranceCompany::where('name', 'Bupa')->first();
        $tawuniya = InsuranceCompany::where('name', 'Tawuniya')->first();
        $charity1 = CharityEntity::first();

        $patients = [
            ['file' => 'P-2024-001', 'name' => 'Mohammed Ali', 'name_ar' => 'محمد علي', 'payment_type' => 'cash', 'phone' => '0501234567'],
            ['file' => 'P-2024-002', 'name' => 'Fatima Hassan', 'name_ar' => 'فاطمة حسن', 'payment_type' => 'insurance', 'insurance_id' => $bupa?->id, 'id_number' => '1234567890'],
            ['file' => 'P-2024-003', 'name' => 'Khalid Omar', 'name_ar' => 'خالد عمر', 'payment_type' => 'insurance', 'insurance_id' => $tawuniya?->id, 'id_number' => '1234567891'],
            ['file' => 'P-2024-004', 'name' => 'Nora Ahmed', 'name_ar' => 'نورة أحمد', 'payment_type' => 'charity', 'charity_id' => $charity1?->id],
            ['file' => 'P-2024-005', 'name' => 'Abdulrahman Saleh', 'name_ar' => 'عبدالرحمن صالح', 'payment_type' => 'cash', 'phone' => '0501234568'],
            ['file' => 'P-2024-006', 'name' => 'Sara Mohammed', 'name_ar' => 'سارة محمد', 'payment_type' => 'insurance', 'insurance_id' => $bupa?->id],
            ['file' => 'P-2024-007', 'name' => 'Youssef Ibrahim', 'name_ar' => 'يوسف إبراهيم', 'payment_type' => 'charity', 'charity_id' => $charity1?->id],
        ];

        foreach ($patients as $p) {
            Patient::firstOrCreate(
                ['file_number' => $p['file']],
                [
                    'name' => $p['name'],
                    'name_ar' => $p['name_ar'] ?? null,
                    'payment_type' => $p['payment_type'],
                    'insurance_company_id' => $p['insurance_id'] ?? null,
                    'charity_entity_id' => $p['charity_id'] ?? null,
                    'id_number' => $p['id_number'] ?? null,
                    'phone' => $p['phone'] ?? null,
                    'notes' => $p['notes'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
