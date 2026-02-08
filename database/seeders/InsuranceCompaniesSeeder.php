<?php

namespace Database\Seeders;

use App\Models\InsuranceCompany;
use Illuminate\Database\Seeder;

class InsuranceCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Bupa Arabia Insurance',
                'name_ar' => 'شركة بوبا العربية للتأمين',
                'email' => 'approvals@bupa.com.sa',
                'phone' => '+966 11 290 9999',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'إدارة الموافقات الطبية',
                'is_active' => true,
            ],
            [
                'name' => 'Tawuniya Insurance',
                'name_ar' => 'شركة التعاونية للتأمين',
                'email' => 'medical@tawuniya.com.sa',
                'phone' => '+966 11 218 9999',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'قسم الموافقات',
                'is_active' => true,
            ],
            [
                'name' => 'MedGulf Insurance',
                'name_ar' => 'شركة ميدغلف للتأمين',
                'email' => 'claims@medgulf.com.sa',
                'phone' => '+966 11 275 5555',
                'address' => 'جدة، المملكة العربية السعودية',
                'contact_person' => 'إدارة المطالبات',
                'is_active' => true,
            ],
            [
                'name' => 'Saudi Indian Insurance',
                'name_ar' => 'الشركة السعودية الهندية للتأمين',
                'email' => 'medical.approval@saudiarabia.com',
                'phone' => '+966 11 299 8888',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'الموافقات الطبية',
                'is_active' => true,
            ],
            [
                'name' => 'Salama Insurance',
                'name_ar' => 'شركة سلامة للتأمين التعاوني',
                'email' => 'approvals@salama.com.sa',
                'phone' => '+966 11 293 3333',
                'address' => 'الدمام، المملكة العربية السعودية',
                'contact_person' => 'قسم التأمين الطبي',
                'is_active' => true,
            ],
            [
                'name' => 'Allianz Saudi Fransi',
                'name_ar' => 'شركة أليانز السعودي الفرنسي',
                'email' => 'health@allianz.com.sa',
                'phone' => '+966 11 274 4444',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'التأمين الصحي',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            InsuranceCompany::updateOrCreate(
                ['email' => $company['email']],
                $company
            );
        }
    }
}
