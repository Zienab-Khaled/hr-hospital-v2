<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * قيادات النظام فقط (مدير تنمية الإيرادات، مسؤولة الجمعيات، رئيس قسم التأمين).
 * باقي الموظفين (المحصلين، التأمين، المحاسب، أمين الصندوق، المديونيات) في RevenueStaffSeeder.
 * تشغيل: php artisan db:seed --class=ProductionUsersSeeder
 */
class ProductionUsersSeeder extends Seeder
{
    public function run(): void
    {
        $getDeptId = fn (string $name) => Department::where('name', $name)->orWhere('name_ar', $name)->value('id');

        // قيادات — username + باسورد قوي خاص بكل مستخدم
        $usersConfig = [
            [
                'username' => 'jasar.alduwayhi',
                'password' => 'J@sar#Rev2025!Duwayhi',
                'name' => 'Jasar Mohammed Al-Duwayhi',
                'name_ar' => 'جسار محمد الضويحي',
                'email' => 'jasar@hospital.sa',
                'role' => 'manager',
                'department' => 'Management',
                'job_title' => 'Revenue Development Manager',
                'job_title_ar' => 'مدير تنمية الإيرادات / مدير النظام',
            ],
            [
                'username' => 'abeer.alruwaili',
                'password' => 'Ab33r!Ruw#2025$Revenue',
                'name' => 'Abber Suliman Alrwaily',
                'name_ar' => 'عبير الرويلي',
                'email' => 'abeer@hospital.sa',
                'role' => 'admin',
                'department' => 'Social Services',
                'job_title' => 'Charity Officer',
                'job_title_ar' => 'مسؤولة الجمعيات',
            ],
            [
                'username' => 'radi.alkubaidan',
                'password' => 'R@di#Ins2025!Kubaidan',
                'name' => 'Radi Al-Kubaidan',
                'name_ar' => 'د. رضي الكبيدان',
                'email' => 'radi.insurance@hospital.sa',
                'role' => 'manager',
                'department' => 'Insurance',
                'job_title' => 'Head of Insurance Department',
                'job_title_ar' => 'رئيس قسم التأمين',
            ],
        ];

        $credentials = [];

        foreach ($usersConfig as $config) {
            $username = $config['username'];
            $password = $config['password'];
            $deptId = $getDeptId($config['department']);

            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $config['name'],
                    'name_ar' => $config['name_ar'],
                    'email' => $config['email'],
                    'password' => Hash::make($password),
                    'department_id' => $deptId,
                    'job_title' => $config['job_title'],
                    'job_title_ar' => $config['job_title_ar'],
                    'status' => 'active',
                ]
            );
            $user->syncRoles([$config['role']]);

            $credentials[] = [
                'name_ar' => $config['name_ar'],
                'username' => $username,
                'password' => $password,
                'role' => $config['role'],
            ];
        }

        $this->printCredentials($credentials);
    }

    private function printCredentials(array $credentials): void
    {
        $out = function (string $msg, string $type = 'line') {
            if ($this->command) {
                $this->command->{$type}($msg);
            } else {
                echo $msg . "\n";
            }
        };

        $line = str_repeat('─', 72);
        $out('');
        $out('╔══════════════════════════════════════════════════════════════════════╗', 'info');
        $out('║           بيانات الدخول للموظفين (احفظها في مكان آمن)               ║', 'info');
        $out('╚══════════════════════════════════════════════════════════════════════╝', 'info');
        $out('');

        foreach ($credentials as $row) {
            $out($line);
            $out('  الاسم (عربي): ' . $row['name_ar']);
            $out('  الدور:       ' . $row['role']);
            $out('  Username:    ' . $row['username']);
            $out('  Password:    ' . $row['password']);
            $out('');
        }

        $out($line);
        $out('');
        $out('جدول سريع للنسخ (Username | Password):', 'info');
        $out($line);
        foreach ($credentials as $row) {
            $out('  ' . $row['username'] . ' | ' . $row['password']);
        }
        $out($line);
        $out('  تسجيل الدخول بالموقع يكون بـ Username + Password (ليس الإيميل).', 'warn');
        $out('  احفظ هذه البيانات في مكان آمن ثم لا تخزنها في ملف على السيرفر.', 'warn');
        $out('');
    }
}
