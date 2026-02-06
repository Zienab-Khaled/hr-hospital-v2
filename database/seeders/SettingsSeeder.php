<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'hospital_name',       'value' => 'Hospital Name',           'group' => 'hospital'],
            ['key' => 'hospital_name_ar',    'value' => 'اسم المستشفى',             'group' => 'hospital'],
            ['key' => 'hospital_phone',      'value' => '',                         'group' => 'hospital'],
            ['key' => 'hospital_address',    'value' => '',                         'group' => 'hospital'],
            ['key' => 'manager_name',        'value' => 'Manager Name',             'group' => 'general'],
            ['key' => 'manager_name_ar',     'value' => 'اسم المدير',              'group' => 'general'],
        ];

        foreach ($defaults as $s) {
            Setting::firstOrCreate(
                ['key' => $s['key']],
                ['value' => $s['value'], 'group' => $s['group']]
            );
        }
    }
}
