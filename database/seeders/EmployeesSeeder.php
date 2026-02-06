<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            ['code' => 'RECEPTION', 'job_title' => 'Receptionist', 'job_title_ar' => 'موظف استقبال', 'name' => 'Ahmed Reception', 'name_ar' => 'أحمد الاستقبال'],
            ['code' => 'RECEPTION', 'job_title' => 'Receptionist', 'job_title_ar' => 'موظفة استقبال', 'name' => 'Sara Reception', 'name_ar' => 'سارة الاستقبال'],
            ['code' => 'CASH', 'job_title' => 'Cashier', 'job_title_ar' => 'موظف كاش', 'name' => 'Khalid Cash', 'name_ar' => 'خالد الكاش'],
            ['code' => 'INSURANCE', 'job_title' => 'Insurance Officer', 'job_title_ar' => 'موظف تأمين', 'name' => 'Fatima Insurance', 'name_ar' => 'فاطمة التأمين'],
            ['code' => 'CHARITY', 'job_title' => 'Charity Officer', 'job_title_ar' => 'موظف جمعيات', 'name' => 'Omar Charity', 'name_ar' => 'عمر الجمعيات'],
            ['code' => 'FOLLOWUP', 'job_title' => 'Follow-up Officer', 'job_title_ar' => 'متابعة مرضى', 'name' => 'Nora Followup', 'name_ar' => 'نورة المتابعة'],
            ['code' => 'COLLECTION', 'job_title' => 'Collection Officer', 'job_title_ar' => 'موظف تحصيل', 'name' => 'Abdulrahman Collection', 'name_ar' => 'عبدالرحمن التحصيل'],
            ['code' => 'CASHIER', 'job_title' => 'Treasury Officer', 'job_title_ar' => 'أمين الصندوق', 'name' => 'Mohammed Cashier', 'name_ar' => 'محمد أمين الصندوق'],
            ['code' => 'ACCOUNTING', 'job_title' => 'Accountant', 'job_title_ar' => 'محاسب', 'name' => 'Youssef Accountant', 'name_ar' => 'يوسف المحاسب'],
            ['code' => 'ACCOUNTING', 'job_title' => 'Manager', 'job_title_ar' => 'مدير', 'name' => 'Manager Name', 'name_ar' => 'اسم المدير'],
        ];

        foreach ($employees as $e) {
            $dept = Department::where('code', $e['code'])->first();
            if (!$dept) {
                continue;
            }

            $emp = Employee::firstOrCreate(
                [
                    'department_id' => $dept->id,
                    'name' => $e['name'],
                ],
                [
                    'job_title' => $e['job_title'],
                    'job_title_ar' => $e['job_title_ar'] ?? null,
                    'name_ar' => $e['name_ar'] ?? null,
                    'status' => 'active',
                ]
            );

            // إنشاء يوزر لبعض الموظفين للتجربة (مدير، استقبال، أمين صندوق)
            $username = null;
            $role = null;
            if (str_contains($e['name'], 'Manager')) {
                $username = 'manager';
                $role = 'manager';
            } elseif (str_contains($e['name'], 'Reception') && str_contains($e['name'], 'Ahmed')) {
                $username = 'reception';
                $role = 'employee';
            } elseif (str_contains($e['name'], 'Cashier')) {
                $username = 'cashier';
                $role = 'cashier';
            }

            if ($username && $role && !User::where('username', $username)->exists()) {
                $user = User::create([
                    'username' => $username,
                    'name' => $emp->name,
                    'email' => $username . '@hospital.local',
                    'password' => 'password',
                    'employee_id' => $emp->id,
                ]);
                $user->assignRole($role);
            }
        }
    }
}
