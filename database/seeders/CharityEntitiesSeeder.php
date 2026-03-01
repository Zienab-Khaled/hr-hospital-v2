<?php

namespace Database\Seeders;

use App\Models\CharityEntity;
use Illuminate\Database\Seeder;

class CharityEntitiesSeeder extends Seeder
{
    public function run(): void
    {
        $entities = [
            [
                'name' => 'Al-Bir Charity Society - Riyadh',
                'name_ar' => 'جمعية البر الخيرية بالرياض',
                'email' => 'medical@riyadhcharity.org.sa',
                'phone' => '+966 11 402 5555',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'قسم الرعاية الصحية',
                'is_active' => true,
            ],
            [
                'name' => 'King Salman Housing Charity',
                'name_ar' => 'جمعية الملك سلمان للإسكان الخيري',
                'email' => 'health@ksalmanhousing.org',
                'phone' => '+966 11 488 9999',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'إدارة الموافقات الطبية',
                'is_active' => true,
            ],
            [
                'name' => 'Prince Sultan Bin Abdulaziz Charity',
                'name_ar' => 'جمعية الأمير سلطان بن عبدالعزيز الخيرية',
                'email' => 'medical@princesultancharity.org',
                'phone' => '+966 11 465 7777',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'البرامج الصحية',
                'is_active' => true,
            ],
            [
                'name' => 'Al-Bir Charity Society - Jeddah',
                'name_ar' => 'جمعية البر بجدة',
                'email' => 'health@jeddahcharity.org.sa',
                'phone' => '+966 12 651 2222',
                'address' => 'جدة، المملكة العربية السعودية',
                'contact_person' => 'قسم الرعاية الطبية',
                'is_active' => true,
            ],
            [
                'name' => 'Kidney Patients Charity',
                'name_ar' => 'جمعية مرضى الكلى الخيرية',
                'email' => 'approvals@kidneycare.org.sa',
                'phone' => '+966 11 477 3333',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'إدارة الدعم الطبي',
                'is_active' => true,
            ],
            [
                'name' => 'Cancer Patients Care Society',
                'name_ar' => 'جمعية رعاية مرضى السرطان',
                'email' => 'medical@cancercare.org.sa',
                'phone' => '+966 11 499 6666',
                'address' => 'الرياض، المملكة العربية السعودية',
                'contact_person' => 'قسم الموافقات',
                'is_active' => true,
            ],
        ];

        foreach ($entities as $entity) {
            CharityEntity::updateOrCreate(
                ['email' => $entity['email']],
                $entity
            );
        }
    }
}
