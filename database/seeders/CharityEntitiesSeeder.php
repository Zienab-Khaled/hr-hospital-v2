<?php

namespace Database\Seeders;

use App\Models\CharityEntity;
use Illuminate\Database\Seeder;

class CharityEntitiesSeeder extends Seeder
{
    public function run(): void
    {
        $entities = [
            ['name' => 'Charity Fund A', 'name_ar' => 'صندوق الخير أ', 'contact_person' => 'عمر عبدالله', 'phone' => '0112345690', 'email' => 'info@charity-a.org'],
            ['name' => 'Health Support Society', 'name_ar' => 'جمعية دعم الصحة', 'contact_person' => 'نورة أحمد', 'phone' => '0112345691', 'email' => 'contact@healthsupport.org'],
            ['name' => 'Patient Care Association', 'name_ar' => 'جمعية رعاية المرضى', 'contact_person' => 'عبدالرحمن محمد', 'phone' => '0112345692'],
        ];

        foreach ($entities as $e) {
            CharityEntity::firstOrCreate(
                ['name' => $e['name']],
                [
                    'name_ar' => $e['name_ar'] ?? null,
                    'contact_person' => $e['contact_person'] ?? null,
                    'phone' => $e['phone'] ?? null,
                    'email' => $e['email'] ?? null,
                    'fax' => $e['fax'] ?? null,
                    'address' => $e['address'] ?? null,
                    'notes' => $e['notes'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
