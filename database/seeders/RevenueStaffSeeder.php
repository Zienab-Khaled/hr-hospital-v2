<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * يزرع أقسام الإيرادات والموظفين حسب الهيكل المطلوب:
 * الاستقبال (المحصلين)، التأمين (متابعة مطالبات + متابعة مرضى)، المحاسب، أمين الصندوق، المديونيات.
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

        $password = Hash::make('password123');

        // —— الاستقبال — المحصلين (الطوارئ / العيادات) ——
        $collectors = [
            ['username' => 'rev_col_1', 'name_ar' => 'سيف عويد الرويلي'],
            ['username' => 'rev_col_2', 'name_ar' => 'مريم مدالله السرحاني'],
            ['username' => 'rev_col_3', 'name_ar' => 'خالد عايد العنزي'],
            ['username' => 'rev_col_4', 'name_ar' => 'وليد خالد الدندني'],
            ['username' => 'rev_col_5', 'name_ar' => 'منصور عبدالعزيز الشمري'],
            ['username' => 'rev_col_6', 'name_ar' => 'محمد ثاني الرويلي'],
            ['username' => 'rev_col_7', 'name_ar' => 'فهد غضيان الرويلي'],
            ['username' => 'rev_col_8', 'name_ar' => 'فايز سعيف الرويلي'],
            ['username' => 'rev_col_9', 'name_ar' => 'عابد عطا الفهد'],
        ];
        foreach ($collectors as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.col.' . ($i + 1) . '@hospital.sa',
                    'password' => $password,
                    'department_id' => $receptionId,
                    'job_title' => 'Collector',
                    'job_title_ar' => 'محصل',
                    'status' => 'active',
                ]
            );
            $user->assignRole('collection');
        }

        // —— التأمين — متابعة مطالبات التأمين ——
        $insuranceClaims = [
            ['username' => 'rev_ic_1', 'name_ar' => 'أميرة الرديني الشمري'],
            ['username' => 'rev_ic_2', 'name_ar' => 'عزيزة الرويلي'],
        ];
        foreach ($insuranceClaims as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.ic.' . ($i + 1) . '@hospital.sa',
                    'password' => $password,
                    'department_id' => $insuranceId,
                    'job_title' => 'Insurance Claims Follow-up',
                    'job_title_ar' => 'متابعة مطالبات التأمين',
                    'status' => 'active',
                ]
            );
            $user->assignRole('insurance_clerk');
        }

        // —— التأمين — فني متابعة مرضى ——
        $patientFollowUp = [
            ['username' => 'rev_pf_1', 'name_ar' => 'تغريد ممدوح الرويلي'],
            ['username' => 'rev_pf_2', 'name_ar' => 'حميدة عياء الرويلي'],
        ];
        foreach ($patientFollowUp as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.pf.' . ($i + 1) . '@hospital.sa',
                    'password' => $password,
                    'department_id' => $insuranceId,
                    'job_title' => 'Patient Follow-up Technician',
                    'job_title_ar' => 'فني متابعة مرضى',
                    'status' => 'active',
                ]
            );
            $user->assignRole('patient_follow_up');
        }

        // —— المحاسب (المالية) ——
        $accountants = [
            ['username' => 'rev_acc_1', 'name_ar' => 'باسم محمد الخالدي'],
            ['username' => 'rev_acc_2', 'name_ar' => 'عبدالله هزاع العتيبي'],
        ];
        foreach ($accountants as $i => $row) {
            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name_ar'],
                    'name_ar' => $row['name_ar'],
                    'email' => 'rev.acc.' . ($i + 1) . '@hospital.sa',
                    'password' => $password,
                    'department_id' => $financeId,
                    'job_title' => 'Accountant',
                    'job_title_ar' => 'محاسب',
                    'status' => 'active',
                ]
            );
            $user->assignRole('accountant');
        }

        // —— أمين الصندوق ——
        $user = User::updateOrCreate(
            ['username' => 'rev_cash_1'],
            [
                'name' => 'ناصر علي الرويلي',
                'name_ar' => 'ناصر علي الرويلي',
                'email' => 'rev.cashier@hospital.sa',
                'password' => $password,
                'department_id' => $financeId,
                'job_title' => 'Treasurer',
                'job_title_ar' => 'أمين الصندوق',
                'status' => 'active',
            ]
        );
        $user->assignRole('cashier');

        // —— المديونيات — رئيس المديونيات ——
        $user = User::updateOrCreate(
            ['username' => 'rev_debts_1'],
            [
                'name' => 'ماجد خليل الرشيدان',
                'name_ar' => 'ماجد خليل الرشيدان',
                'email' => 'rev.debts@hospital.sa',
                'password' => $password,
                'department_id' => $debtsId,
                'job_title' => 'Head of Debts',
                'job_title_ar' => 'رئيس المديونيات',
                'status' => 'active',
            ]
        );
        $user->assignRole('debts_head');

        echo "✅ Revenue staff seeded: 9 collectors, 2 insurance claims, 2 patient follow-up, 2 accountants, 1 cashier, 1 debts head.\n";
    }
}
