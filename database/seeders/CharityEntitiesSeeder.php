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
                'name' => 'جمعية الحياة لرعاية مرضى السرطان بالقريات',
                'name_ar' => 'جمعية الحياة لرعاية مرضى السرطان بالقريات',
                'email' => 'info@hayat-charity.sa',
                'phone' => null,
                'address' => null,
                'contact_person' => null,
                'is_active' => true,
            ],
            [
                'name' => 'جمعية حياة لرعاية مرضى سرطان الثدي بمنطقة الحدود الشمالية',
                'name_ar' => 'جمعية حياة لرعاية مرضى سرطان الثدي بمنطقة الحدود الشمالية',
                'email' => 'info@hayat-breast-charity.sa',
                'phone' => null,
                'address' => null,
                'contact_person' => null,
                'is_active' => true,
            ],
            [
                'name' => 'جمعية الجوف للخدمات الصحية',
                'name_ar' => 'جمعية الجوف للخدمات الصحية',
                'email' => 'info@jouf-health-charity.sa',
                'phone' => null,
                'address' => null,
                'contact_person' => null,
                'is_active' => true,
            ],
            [
                'name' => 'جمعية سمح',
                'name_ar' => 'جمعية سمح',
                'email' => 'info@samah-charity.sa',
                'phone' => null,
                'address' => null,
                'contact_person' => null,
                'is_active' => true,
            ],
        ];

        foreach ($entities as $entity) {
            CharityEntity::updateOrCreate(
                ['name_ar' => $entity['name_ar']],
                $entity
            );
        }
    }
}
