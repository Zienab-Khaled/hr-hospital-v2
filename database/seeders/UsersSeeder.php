<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // الأدوار والصلاحيات تُعرّف في RolesAndPermissionsSeeder (يُستدعى قبله في DatabaseSeeder).
        // هنا فقط التأكد من وجود الأدوار لـ web و api.
        $guards = ['web', 'api'];
        $roleNames = ['admin', 'manager', 'doctor', 'nurse', 'reception', 'accountant', 'insurance_clerk', 'charity_clerk', 'insurance_doctor'];
        foreach ($guards as $guard) {
            foreach ($roleNames as $name) {
                Role::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }

        // Helper to get department ID by name (English or Arabic)
        $getDeptId = function ($name) {
            return Department::where('name', $name)->orWhere('name_ar', $name)->value('id');
        };

        // Create Admin User (Login: username + password, not email)
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@hospital.sa',
                'name' => 'مدير النظام',
                'password' => Hash::make('admin123'),
                'department_id' => $getDeptId('Management'), // الإدارة التشغيلية
                'job_title' => 'System Administrator',
                'job_title_ar' => 'مدير النظام',
            ]
        );
        $admin->assignRole('admin');

        // Create Reception Users
        $reception1 = User::updateOrCreate(
            ['username' => 'reception1'],
            [
                'email' => 'reception1@hospital.sa',
                'name' => 'أحمد محمد - استقبال',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Reception'), // الاستقبال
                'job_title' => 'Receptionist',
                'job_title_ar' => 'موظف استقبال',
            ]
        );
        $reception1->assignRole('reception');

        $reception2 = User::updateOrCreate(
            ['username' => 'reception2'],
            [
                'email' => 'reception2@hospital.sa',
                'name' => 'فاطمة علي - استقبال',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Reception'),
                'job_title' => 'Receptionist',
                'job_title_ar' => 'موظف استقبال',
            ]
        );
        $reception2->assignRole('reception');

        // Create Insurance Clerk
        $insurance = User::updateOrCreate(
            ['username' => 'insurance'],
            [
                'email' => 'insurance@hospital.sa',
                'name' => 'خالد عبدالله - موظف تأمين',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Insurance'), // التأمين
                'job_title' => 'Insurance Clerk',
                'job_title_ar' => 'موظف تأمين',
            ]
        );
        $insurance->assignRole('insurance_clerk');

        // Head of Insurance Department — رئيس قسم التأمين
        $insuranceHead = User::updateOrCreate(
            ['username' => 'insurance_head'],
            [
                'email' => 'insurance.head@hospital.sa',
                'name' => 'رضي ناصر الكبيدان',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Insurance'),
                'job_title' => 'Head of Insurance Department',
                'job_title_ar' => 'رئيس قسم التأمين',
            ]
        );
        $insuranceHead->assignRole('manager');

        // Insurance Doctor — طبيب التأمين
        $insuranceDoctor = User::updateOrCreate(
            ['username' => 'insurance_doctor'],
            [
                'email' => 'insurance.doctor@hospital.sa',
                'name' => 'محمد عوض',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Insurance'),
                'job_title' => 'Insurance Doctor',
                'job_title_ar' => 'طبيب التأمين',
            ]
        );
        $insuranceDoctor->assignRole('insurance_doctor');

        // Create Charity Clerk
        $charity = User::updateOrCreate(
            ['username' => 'charity'],
            [
                'email' => 'charity@hospital.sa',
                'name' => 'نورة سعيد - موظفة جمعيات',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Social Services'), // الخدمة الاجتماعية
                'job_title' => 'Charity Clerk',
                'job_title_ar' => 'موظف جمعيات',
            ]
        );
        $charity->assignRole('charity_clerk');

        // Create Accountant
        $accountant = User::updateOrCreate(
            ['username' => 'accountant'],
            [
                'email' => 'accountant@hospital.sa',
                'name' => 'عبدالرحمن حسن - محاسب',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Finance'), // المالية
                'job_title' => 'Accountant',
                'job_title_ar' => 'محاسب',
            ]
        );
        $accountant->assignRole('accountant');

        // Create Doctor
        $doctor = User::updateOrCreate(
            ['username' => 'doctor'],
            [
                'email' => 'doctor@hospital.sa',
                'name' => 'د. محمد أحمد - طبيب',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Internal Medicine'), // الباطنة
                'job_title' => 'Doctor',
                'job_title_ar' => 'طبيب',
            ]
        );
        $doctor->assignRole('doctor');

        // Create Nurse
        $nurse = User::updateOrCreate(
            ['username' => 'nurse'],
            [
                'email' => 'nurse@hospital.sa',
                'name' => 'مريم خالد - ممرضة',
                'password' => Hash::make('password123'),
                'department_id' => $getDeptId('Nursing'), // التمريض
                'job_title' => 'Nurse',
                'job_title_ar' => 'ممرضة',
            ]
        );
        $nurse->assignRole('nurse');

        echo "\n✅ Users created successfully!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Login with USERNAME + PASSWORD (not email):\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Admin:       username: admin       | password: admin123\n";
        echo "Reception:   username: reception1  | password: password123\n";
        echo "Reception:   username: reception2  | password: password123\n";
        echo "Insurance:     username: insurance        | password: password123\n";
        echo "Insur. Head:   username: insurance_head  | password: password123  (رئيس قسم التأمين)\n";
        echo "Insur. Dr:     username: insurance_doctor| password: password123  (طبيب التأمين)\n";
        echo "Charity:       username: charity         | password: password123\n";
        echo "Accountant:    username: accountant      | password: password123\n";
        echo "Doctor:        username: doctor          | password: password123\n";
        echo "Nurse:         username: nurse           | password: password123\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}
