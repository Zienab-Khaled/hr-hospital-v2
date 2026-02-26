<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        // Create Roles for both web and api guards
        $guards = ['web', 'api'];
        foreach ($guards as $guard) {
            Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'doctor', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'nurse', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'reception', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'accountant', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'insurance_clerk', 'guard_name' => $guard]);
            Role::firstOrCreate(['name' => 'charity_clerk', 'guard_name' => $guard]);
        }

        // Create Permissions (all used in app: sidebar, controllers, gates)
        $permissions = [
            'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
            'visits.view', 'visits.create', 'visits.edit', 'visits.delete',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
            'payments.view', 'payments.create', 'payments.approve',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'services.manage',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'users.manage',
            'reports.view', 'reports.generate', 'reports.upload_cluster',
            'authorizations.view',
            'claims.view',
            'activity.view',
            'departments.manage',
            'settings.manage',
            'codes.upload',
            'insurance_companies.manage',
            'charity_entities.manage',
            'procedures.contact_report', 'procedures.written_commitment',
            'procedures.non_commitment', 'procedures.debt_inventory',
        ];

        foreach ($permissions as $permission) {
            foreach ($guards as $guard) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
            }
        }

        // Assign all permissions to admin and manager (both guards)
        foreach ($guards as $guard) {
            $allPerms = Permission::where('guard_name', $guard)->get();
            Role::findByName('admin', $guard)->syncPermissions($allPerms);
            Role::findByName('manager', $guard)->syncPermissions($allPerms);
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
        $reception1->givePermissionTo(['patients.view', 'patients.create', 'invoices.create', 'procedures.contact_report']);

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
        $reception2->givePermissionTo(['patients.view', 'patients.create', 'invoices.create', 'procedures.contact_report']);

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
        $insurance->givePermissionTo(['patients.view', 'invoices.view', 'invoices.create', 'procedures.contact_report']);

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
        $charity->givePermissionTo(['patients.view', 'invoices.view', 'invoices.create', 'procedures.contact_report']);

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
        $accountant->givePermissionTo(['invoices.view', 'payments.view', 'payments.create', 'payments.approve', 'reports.view', 'reports.generate']);

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
        $doctor->givePermissionTo(['patients.view', 'patients.edit', 'invoices.view']);

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
        $nurse->givePermissionTo(['patients.view']);

        echo "\n✅ Users created successfully!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Login with USERNAME + PASSWORD (not email):\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Admin:       username: admin       | password: admin123\n";
        echo "Reception:   username: reception1  | password: password123\n";
        echo "Reception:   username: reception2  | password: password123\n";
        echo "Insurance:   username: insurance   | password: password123\n";
        echo "Charity:     username: charity     | password: password123\n";
        echo "Accountant:  username: accountant  | password: password123\n";
        echo "Doctor:      username: doctor      | password: password123\n";
        echo "Nurse:       username: nurse       | password: password123\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}
