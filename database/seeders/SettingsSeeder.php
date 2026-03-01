<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Hospital Information
            ['key' => 'hospital_name', 'value' => 'مستشفى النور الطبي', 'group' => 'hospital'],
            ['key' => 'hospital_name_en', 'value' => 'Al-Nour Medical Hospital', 'group' => 'hospital'],
            ['key' => 'hospital_address', 'value' => 'شارع الملك فهد، الرياض، المملكة العربية السعودية', 'group' => 'hospital'],
            ['key' => 'hospital_phone', 'value' => '+966 11 234 5678', 'group' => 'hospital'],
            ['key' => 'hospital_email', 'value' => 'info@alnourhospital.sa', 'group' => 'hospital'],
            ['key' => 'hospital_website', 'value' => 'www.alnourhospital.sa', 'group' => 'hospital'],
            
            // Manager Information
            ['key' => 'manager_name', 'value' => 'د. محمد أحمد السعيد', 'group' => 'hospital'],
            ['key' => 'manager_name_en', 'value' => 'Dr. Mohammed Ahmed Al-Saeed', 'group' => 'hospital'],
            ['key' => 'manager_title', 'value' => 'المدير العام', 'group' => 'hospital'],
            ['key' => 'manager_title_en', 'value' => 'General Manager', 'group' => 'hospital'],
            
            // Banking Information
            ['key' => 'bank_name', 'value' => 'البنك الأهلي السعودي', 'group' => 'general'],
            ['key' => 'bank_name_en', 'value' => 'Al Ahli Bank', 'group' => 'general'],
            ['key' => 'iban_number', 'value' => 'SA44 2000 0001 2345 6789 1234', 'group' => 'general'],
            ['key' => 'account_number', 'value' => '123456789', 'group' => 'general'],
            ['key' => 'swift_code', 'value' => 'NCBKSAJE', 'group' => 'general'],
            
            // Tax Information
            ['key' => 'tax_number', 'value' => '300123456789003', 'group' => 'general'],
            ['key' => 'commercial_registration', 'value' => '1010123456', 'group' => 'general'],
            
            // Additional Settings
            ['key' => 'currency', 'value' => 'SAR', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'Asia/Riyadh', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'general'],
            ['key' => 'time_format', 'value' => 'H:i', 'group' => 'general'],
            
            // Email Settings
            ['key' => 'default_email_from', 'value' => 'noreply@alnourhospital.sa', 'group' => 'general'],
            ['key' => 'default_email_name', 'value' => 'Al-Nour Hospital System', 'group' => 'general'],
            
            // System Settings
            ['key' => 'invoice_prefix', 'value' => 'INV', 'group' => 'general'],
            ['key' => 'receipt_prefix', 'value' => 'RCP', 'group' => 'general'],
            ['key' => 'approval_prefix', 'value' => 'APR', 'group' => 'general'],
            ['key' => 'commitment_prefix', 'value' => 'COM', 'group' => 'general'],
        ];
        
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
