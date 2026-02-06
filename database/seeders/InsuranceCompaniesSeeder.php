<?php

namespace Database\Seeders;

use App\Models\InsuranceCompany;
use Illuminate\Database\Seeder;

class InsuranceCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Bupa', 'name_ar' => 'بوبا', 'contact_person' => 'أحمد محمد', 'phone' => '0112345678', 'email' => 'contact@bupa.com.sa'],
            ['name' => 'Tawuniya', 'name_ar' => 'التأمينية', 'contact_person' => 'خالد علي', 'phone' => '0112345679', 'email' => 'info@tawuniya.com.sa'],
            ['name' => 'MedGulf', 'name_ar' => 'مدغلف', 'contact_person' => 'سارة أحمد', 'phone' => '0112345680', 'email' => 'support@medgulf.com'],
            ['name' => 'AXA Cooperative', 'name_ar' => 'أكسا التعاونية', 'contact_person' => 'محمد سالم', 'phone' => '0112345681'],
            ['name' => 'Allianz', 'name_ar' => 'أليانز', 'contact_person' => 'فاطمة حسن', 'phone' => '0112345682', 'email' => 'health@allianz.com.sa'],
        ];

        foreach ($companies as $c) {
            InsuranceCompany::firstOrCreate(
                ['name' => $c['name']],
                [
                    'name_ar' => $c['name_ar'] ?? null,
                    'contact_person' => $c['contact_person'] ?? null,
                    'phone' => $c['phone'] ?? null,
                    'email' => $c['email'] ?? null,
                    'fax' => $c['fax'] ?? null,
                    'address' => $c['address'] ?? null,
                    'notes' => $c['notes'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
