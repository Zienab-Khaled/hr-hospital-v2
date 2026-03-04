<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * يزرع موظفي الإيرادات — كل موظف له username + باسورد قوي خاص به.
 * القيادات في ProductionUsersSeeder.
 * تشغيل: php artisan db:seed --class=RevenueStaffSeeder
 */
class RevenueStaffSeeder extends Seeder
{
    public function run(): void
    {
        $getDeptId = fn (string $name) => Department::where('name', $name)->orWhere('name_ar', $name)->value('id');

        $receptionId = $getDeptId('Reception');
        $insuranceId = $getDeptId('Insurance');
        $financeId = $getDeptId('Finance');
        $debtsId = $getDeptId('Debts');

        if (! $receptionId || ! $insuranceId || ! $financeId || ! $debtsId) {
            echo "⚠️  Ensure DepartmentsSeeder has run (Reception, Insurance, Finance, Debts).\n";
            return;
        }

        $credentials = [];

        // —— الاستقبال — المحصلين ——
        $collectors = [
            ['username' => 'rev_col_1', 'name_ar' => 'سيف عويد الرويلي', 'password' => 'S@if#Col1!Rev2025'],
            ['username' => 'rev_col_2', 'name_ar' => 'مريم مدالله السرحاني', 'password' => 'M@rym#Col2!Rev2025'],
            ['username' => 'rev_col_3', 'name_ar' => 'خالد عايد العنزي', 'password' => 'Kh@ld#Col3!Rev2025'],
            ['username' => 'rev_col_4', 'name_ar' => 'وليد خالد الدندني', 'password' => 'W@lid#Col4!Rev2025'],
            ['username' => 'rev_col_5', 'name_ar' => 'منصور عبدالعزيز الشمري', 'password' => 'M@nsr#Col5!Rev2025'],
            ['username' => 'rev_col_6', 'name_ar' => 'محمد ثاني الرويلي', 'password' => 'M@hmd#Col6!Rev2025'],
            ['username' => 'rev_col_7', 'name_ar' => 'فهد غضيان الرويلي', 'password' => 'F@hd#Col7!Rev2025'],
            ['username' => 'rev_col_8', 'name_ar' => 'فايز سعيف الرويلي', 'password' => 'F@yz#Col8!Rev2025'],
            ['username' => 'rev_col_9', 'name_ar' => 'عابد عطا الفهد', 'password' => 'A@bd#Col9!Rev2025'],
        ];
        foreach ($collectors as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.col.' . ($i + 1) . '@hospital.sa',
                    'password' => Hash::make($row['password']),
                    'department_id' => $receptionId,
                    'job_title' => 'Collector',
                    'job_title_ar' => 'محصل',
                    'status' => 'active',
                ]
            );
            $user->assignRole('collection');
            $credentials[] = ['name_ar' => $row['name_ar'], 'username' => $row['username'], 'password' => $row['password'], 'role_ar' => 'محصل'];
        }

        // —— التأمين — متابعة مطالبات ——
        $insuranceClaims = [
            ['username' => 'rev_ic_1', 'name_ar' => 'أميرة الرديني الشمري', 'password' => 'A@mra#IC1!Rev2025'],
            ['username' => 'rev_ic_2', 'name_ar' => 'عزيزة الرويلي', 'password' => 'A@zza#IC2!Rev2025'],
        ];
        foreach ($insuranceClaims as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.ic.' . ($i + 1) . '@hospital.sa',
                    'password' => Hash::make($row['password']),
                    'department_id' => $insuranceId,
                    'job_title' => 'Insurance Claims Follow-up',
                    'job_title_ar' => 'متابعة مطالبات التأمين',
                    'status' => 'active',
                ]
            );
            $user->assignRole('insurance_clerk');
            $credentials[] = ['name_ar' => $row['name_ar'], 'username' => $row['username'], 'password' => $row['password'], 'role_ar' => 'متابعة مطالبات التأمين'];
        }

        // —— التأمين — فني متابعة مرضى ——
        $patientFollowUp = [
            ['username' => 'rev_pf_1', 'name_ar' => 'تغريد ممدوح الرويلي', 'password' => 'T@grid#PF1!Rev2025'],
            ['username' => 'rev_pf_2', 'name_ar' => 'حميدة عياء الرويلي', 'password' => 'H@mda#PF2!Rev2025'],
        ];
        foreach ($patientFollowUp as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.pf.' . ($i + 1) . '@hospital.sa',
                    'password' => Hash::make($row['password']),
                    'department_id' => $insuranceId,
                    'job_title' => 'Patient Follow-up Technician',
                    'job_title_ar' => 'فني متابعة مرضى',
                    'status' => 'active',
                ]
            );
            $user->assignRole('patient_follow_up');
            $credentials[] = ['name_ar' => $row['name_ar'], 'username' => $row['username'], 'password' => $row['password'], 'role_ar' => 'فني متابعة مرضى'];
        }

        // —— المحاسب ——
        $accountants = [
            ['username' => 'rev_acc_1', 'name_ar' => 'باسم محمد الخالدي', 'password' => 'B@sm#Acc1!Rev2025'],
            ['username' => 'rev_acc_2', 'name_ar' => 'عبدالله هزاع العتيبي', 'password' => 'Abd@llh#Acc2!Rev2025'],
        ];
        foreach ($accountants as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.acc.' . ($i + 1) . '@hospital.sa',
                    'password' => Hash::make($row['password']),
                    'department_id' => $financeId,
                    'job_title' => 'Accountant',
                    'job_title_ar' => 'محاسب',
                    'status' => 'active',
                ]
            );
            $user->assignRole('accountant');
            $credentials[] = ['name_ar' => $row['name_ar'], 'username' => $row['username'], 'password' => $row['password'], 'role_ar' => 'محاسب'];
        }

        // —— أمين الصندوق ——
        $cashRow = ['username' => 'rev_cash_1', 'name_ar' => 'ناصر علي الرويلي', 'password' => 'N@ser#Cash1!Rev2025'];
        $user = User::updateOrCreate(
            ['username' => $cashRow['username']],
            [
                'name' => $cashRow['name_ar'],
                'name_ar' => $cashRow['name_ar'],
                'email' => 'rev.cashier@hospital.sa',
                'password' => Hash::make($cashRow['password']),
                'department_id' => $financeId,
                'job_title' => 'Treasurer',
                'job_title_ar' => 'أمين الصندوق',
                'status' => 'active',
            ]
        );
        $user->assignRole('cashier');
        $credentials[] = ['name_ar' => $cashRow['name_ar'], 'username' => $cashRow['username'], 'password' => $cashRow['password'], 'role_ar' => 'أمين الصندوق'];

        // —— رئيس المديونيات ——
        $debtsRow = ['username' => 'rev_debts_1', 'name_ar' => 'ماجد خليل الرشيدان', 'password' => 'M@jd#Debts1!Rev2025'];
        $user = User::updateOrCreate(
            ['username' => $debtsRow['username']],
            [
                'name' => $debtsRow['name_ar'],
                'name_ar' => $debtsRow['name_ar'],
                'email' => 'rev.debts@hospital.sa',
                'password' => Hash::make($debtsRow['password']),
                'department_id' => $debtsId,
                'job_title' => 'Head of Debts',
                'job_title_ar' => 'رئيس المديونيات',
                'status' => 'active',
            ]
        );
        $user->assignRole('debts_head');
        $credentials[] = ['name_ar' => $debtsRow['name_ar'], 'username' => $debtsRow['username'], 'password' => $debtsRow['password'], 'role_ar' => 'رئيس المديونيات'];

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
        $out('║         موظفو الإيرادات — بيانات الدخول (باسورد لكل موظف)          ║', 'info');
        $out('╚══════════════════════════════════════════════════════════════════════╝', 'info');
        $out('');
        foreach ($credentials as $row) {
            $out($line);
            $out('  الاسم: ' . $row['name_ar'] . '  |  ' . $row['role_ar']);
            $out('  Username: ' . $row['username'] . '  |  Password: ' . $row['password']);
            $out('');
        }
        $out($line);
        $out('  تسجيل الدخول: Username + Password (ليس الإيميل).', 'warn');
        $out('');
    }
}
