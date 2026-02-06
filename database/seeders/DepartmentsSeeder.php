<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'RECEPTION',  'name' => 'Reception Office',     'name_ar' => 'مكتب الدخول (العيادات + الطوارئ)'],
            ['code' => 'CASH',       'name' => 'Cash',                 'name_ar' => 'الكاش'],
            ['code' => 'INSURANCE',  'name' => 'Insurance',            'name_ar' => 'التأمين'],
            ['code' => 'CHARITY',    'name' => 'Charities',            'name_ar' => 'الجمعيات'],
            ['code' => 'FOLLOWUP',   'name' => 'Patient Follow-up',    'name_ar' => 'متابعة المرضى'],
            ['code' => 'COLLECTION', 'name' => 'Collection',           'name_ar' => 'التحصيل'],
            ['code' => 'CASHIER',    'name' => 'Treasury',             'name_ar' => 'أمين الصندوق'],
            ['code' => 'ACCOUNTING', 'name' => 'Accounting',           'name_ar' => 'المحاسبة'],
        ];

        foreach ($departments as $d) {
            Department::firstOrCreate(
                ['code' => $d['code']],
                ['name' => $d['name'], 'name_ar' => $d['name_ar'], 'is_active' => true]
            );
        }
    }
}
