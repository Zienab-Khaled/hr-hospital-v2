<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * قيادات النظام فقط (مدير تنمية الإيرادات، مسؤولة الجمعيات، رئيس قسم التأمين).
 * باقي الموظفين (المحصلين، التأمين، المحاسب، أمين الصندوق، المديونيات) في RevenueStaffSeeder.
 * الـ username يُشتق من الاسم. تشغيل: php artisan db:seed --class=ProductionUsersSeeder
 */
class ProductionUsersSeeder extends Seeder
{
    /** اشتقاق username من الاسم الإنجليزي: مثال "Abeer Al-Ruwaili" → abeer.alruwaili */
    private static function usernameFromName(string $name): string
    {
        $name = preg_replace('/^\s*(Dr\.?|Mr\.?|Mrs\.?|Ms\.?)\s+/i', '', $name);
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
        $slug = preg_replace('/[\s\-]+/', '.', trim($slug));
        $slug = preg_replace('/\.+/', '.', $slug);
        return $slug ?: 'user';
    }

    public function run(): void
    {
        $getDeptId = fn (string $name) => Department::where('name', $name)->orWhere('name_ar', $name)->value('id');

        // قيادات فقط (الباقي من RevenueStaffSeeder: محصلين، تأمين، محاسب، أمين صندوق، مديونيات)
        $usersConfig = [
            [
                'name' => 'Jasar Mohammed Al-Duwayhi',
                'name_ar' => 'جسار محمد الضويحي',
                'email' => 'jasar@hospital.sa',
                'role' => 'manager',
                'department' => 'Management',
                'job_title' => 'Revenue Development Manager',
                'job_title_ar' => 'مدير تنمية الإيرادات / مدير النظام',
            ],
            [
                'name' => 'Abeer Al-Ruwaili',
                'name_ar' => 'عبير الرويلي',
                'email' => 'abeer@hospital.sa',
                'role' => 'admin',
                'department' => 'Social Services',
                'job_title' => 'Charity Officer',
                'job_title_ar' => 'مسؤولة الجمعيات',
            ],
            [
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
        $usedUsernames = [];

        foreach ($usersConfig as $config) {
            $baseUsername = self::usernameFromName($config['name']);
            $username = $baseUsername;
            $n = 1;
            while (in_array($username, $usedUsernames, true) || User::where('username', $username)->exists()) {
                $username = $baseUsername . '.' . (++$n);
            }
            $usedUsernames[] = $username;

            $password = Str::password(14, true, true, true, false);
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
