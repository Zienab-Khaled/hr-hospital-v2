<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * إنشاء مستخدمين للإنتاج: مدير النظام عبير الرويلي + موظفين.
 * الـ username يُشتق من اسم الموظف (إنجليزي): حروف صغيرة، المسافات نقطة.
 * تشغيل: php artisan db:seed --class=ProductionUsersSeeder
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

        $usersConfig = [
            [
                'name' => 'Abeer Al-Ruwaili',
                'name_ar' => 'عبير الرويلي',
                'email' => 'abeer@hospital.sa',
                'role' => 'admin',
                'department' => 'Management',
                'job_title' => 'System Administrator',
                'job_title_ar' => 'مدير النظام',
            ],
            [
                'name' => 'Ahmad Mohammed',
                'name_ar' => 'أحمد محمد - استقبال',
                'email' => 'reception1@hospital.sa',
                'role' => 'reception',
                'department' => 'Reception',
                'job_title' => 'Receptionist',
                'job_title_ar' => 'موظف استقبال',
            ],
            [
                'name' => 'Fatima Ali',
                'name_ar' => 'فاطمة علي - استقبال',
                'email' => 'reception2@hospital.sa',
                'role' => 'reception',
                'department' => 'Reception',
                'job_title' => 'Receptionist',
                'job_title_ar' => 'موظف استقبال',
            ],
            [
                'name' => 'Khalid Abdullah',
                'name_ar' => 'خالد عبدالله - موظف تأمين',
                'email' => 'insurance@hospital.sa',
                'role' => 'insurance_clerk',
                'department' => 'Insurance',
                'job_title' => 'Insurance Clerk',
                'job_title_ar' => 'موظف تأمين',
            ],
            [
                'name' => 'Radi Nasser Al-Kubaidan',
                'name_ar' => 'رضي ناصر الكبيدان',
                'email' => 'insurance.head@hospital.sa',
                'role' => 'manager',
                'department' => 'Insurance',
                'job_title' => 'Head of Insurance Department',
                'job_title_ar' => 'رئيس قسم التأمين',
            ],
            [
                'name' => 'Mohammed Awad',
                'name_ar' => 'محمد عوض - طبيب التأمين',
                'email' => 'insurance.doctor@hospital.sa',
                'role' => 'insurance_doctor',
                'department' => 'Insurance',
                'job_title' => 'Insurance Doctor',
                'job_title_ar' => 'طبيب التأمين',
            ],
            [
                'name' => 'Nora Saeed',
                'name_ar' => 'نورة سعيد - موظفة جمعيات',
                'email' => 'charity@hospital.sa',
                'role' => 'charity_clerk',
                'department' => 'Social Services',
                'job_title' => 'Charity Clerk',
                'job_title_ar' => 'موظف جمعيات',
            ],
            [
                'name' => 'Abdulrahman Hassan',
                'name_ar' => 'عبدالرحمن حسن - محاسب',
                'email' => 'accountant@hospital.sa',
                'role' => 'accountant',
                'department' => 'Finance',
                'job_title' => 'Accountant',
                'job_title_ar' => 'محاسب',
            ],
            [
                'name' => 'Mohammed Ahmed',
                'name_ar' => 'د. محمد أحمد - طبيب',
                'email' => 'doctor@hospital.sa',
                'role' => 'doctor',
                'department' => 'Internal Medicine',
                'job_title' => 'Doctor',
                'job_title_ar' => 'طبيب',
            ],
            [
                'name' => 'Mariam Khalid',
                'name_ar' => 'مريم خالد - ممرضة',
                'email' => 'nurse@hospital.sa',
                'role' => 'nurse',
                'department' => 'Nursing',
                'job_title' => 'Nurse',
                'job_title_ar' => 'ممرضة',
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
