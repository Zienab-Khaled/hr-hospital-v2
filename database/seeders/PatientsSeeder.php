<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\InsuranceCompany;
use App\Models\CharityEntity;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PatientsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA');
        $insuranceCompanies = InsuranceCompany::pluck('id')->toArray();
        $charityEntities = CharityEntity::pluck('id')->toArray();
        
        // Sample Arabic names
        $arabicNames = [
            'محمد أحمد السعيد', 'فاطمة علي الأحمد', 'عبدالله خالد العتيبي',
            'نورة محمد القحطاني', 'سعد عبدالرحمن المطيري', 'منى سعيد الدوسري',
            'خالد يوسف الشمري', 'ريم عبدالله الزهراني', 'عمر حسن العمري',
            'هدى ماجد السليمان', 'طارق فيصل البكر', 'سارة ناصر الحربي',
            'عبدالعزيز سلطان آل سعود', 'لطيفة راشد المنصور', 'فهد عادل النمر',
            'أمل حمد الجبرين', 'وليد ثامر الرشيد', 'سلمى عماد الخليفة',
            'بدر جمال الفريح', 'شهد راكان العصيمي'
        ];
        
        $englishNames = [
            'Mohammed Ahmed Al-Saeed', 'Fatima Ali Al-Ahmad', 'Abdullah Khalid Al-Otaibi',
            'Noura Mohammed Al-Qahtani', 'Saad Abdulrahman Al-Mutairi', 'Mona Saeed Al-Dosari',
            'Khalid Youssef Al-Shammari', 'Reem Abdullah Al-Zahrani', 'Omar Hassan Al-Omari',
            'Huda Majed Al-Sulaiman', 'Tariq Faisal Al-Bakr', 'Sarah Nasser Al-Harbi',
            'Abdulaziz Sultan Al-Saud', 'Latifa Rashed Al-Mansour', 'Fahad Adel Al-Nimer',
            'Amal Hamad Al-Jebreen', 'Waleed Thamer Al-Rasheed', 'Salma Emad Al-Khalifa',
            'Badr Jamal Al-Freih', 'Shahad Rakan Al-Osaimi'
        ];
        
        for ($i = 0; $i < 20; $i++) {
            $paymentTypes = ['cash', 'insurance', 'charity'];
            $paymentType = $faker->randomElement($paymentTypes);
            
            $insuranceCompanyId = null;
            $charityEntityId = null;
            
            if ($paymentType === 'insurance' && !empty($insuranceCompanies)) {
                $insuranceCompanyId = $faker->randomElement($insuranceCompanies);
            } elseif ($paymentType === 'charity' && !empty($charityEntities)) {
                $charityEntityId = $faker->randomElement($charityEntities);
            }
            
            // Generate unique identifiers
            $hasIdNumber = $faker->boolean(70);
            $identityTypes = ['national_id', 'visit_visa', 'iqama', 'passport', 'border_number', 'visa_number'];
            $identityType = $faker->randomElement($identityTypes);
            $identityValue = $identityType === 'national_id' ? '1' . $faker->numerify('##########')
                : ($identityType === 'iqama' ? '2' . $faker->numerify('##########')
                : $faker->bothify('?######'));
            
            $ar = $arabicNames[$i];
            $parts = preg_split('/\s+/u', $ar, -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) >= 2) {
                $nameArFamily = array_pop($parts);
                $nameArFirst = implode(' ', $parts);
            } else {
                $nameArFirst = $parts[0] ?? $ar;
                $nameArFamily = $parts[0] ?? $ar;
            }
            $nameArFather = null;

            Patient::create([
                'file_number' => 'F-' . date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'name' => $englishNames[$i],
                'name_ar_first' => $nameArFirst,
                'name_ar_father' => $nameArFather,
                'name_ar_family' => $nameArFamily,
                'identity_type' => $identityType,
                'identity_value' => $identityValue,
                'date_of_birth' => $faker->dateTimeBetween('-75 years', '-20 years')->format('Y-m-d'),
                'gender' => $faker->randomElement(['male', 'female']),
                'phone' => '+966' . $faker->numerify('#########'),
                'country_of_origin' => $faker->randomElement(['Saudi Arabia', 'Egypt', 'Jordan', 'Yemen', 'Syria', 'Sudan', 'India', 'Pakistan', 'Bangladesh']),
                'sponsor_name' => $faker->boolean(40) ? $faker->name() : null,
                'sponsor_phone' => $faker->boolean(40) ? '+966' . $faker->numerify('#########') : null,
                'payment_type' => $paymentType,
                'insurance_company_id' => $insuranceCompanyId,
                'charity_entity_id' => $charityEntityId,
                'notes' => $faker->boolean(30) ? $faker->sentence() : null,
            ]);
        }
        
        echo "\n✅ 20 patients created successfully!\n\n";
    }
}
