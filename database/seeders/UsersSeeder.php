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
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $nurseRole = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web']);
        $receptionRole = Role::firstOrCreate(['name' => 'reception', 'guard_name' => 'web']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $insuranceRole = Role::firstOrCreate(['name' => 'insurance_clerk', 'guard_name' => 'web']);
        $charityRole = Role::firstOrCreate(['name' => 'charity_clerk', 'guard_name' => 'web']);
        
        // Create Permissions (all used in app: sidebar, controllers, gates)
        $permissions = [
            'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
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
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Assign all permissions to admin
        $adminRole->syncPermissions(Permission::all());
        
        // Create Admin User (Login: username + password, not email)
        $admin = User::updateOrCreate(
            ['email' => 'admin@hospital.sa'],
            [
                'username' => 'admin',
                'name' => 'مدير النظام',
                'password' => Hash::make('admin123'),
            ]
        );
        $admin->assignRole($adminRole);
        
        // Create Reception Users
        $reception1 = User::updateOrCreate(
            ['email' => 'reception1@hospital.sa'],
            [
                'username' => 'reception1',
                'name' => 'أحمد محمد - استقبال',
                'password' => Hash::make('password123'),
            ]
        );
        $reception1->assignRole($receptionRole);
        $reception1->givePermissionTo(['patients.view', 'patients.create', 'invoices.create', 'procedures.contact_report']);
        
        $reception2 = User::updateOrCreate(
            ['email' => 'reception2@hospital.sa'],
            [
                'username' => 'reception2',
                'name' => 'فاطمة علي - استقبال',
                'password' => Hash::make('password123'),
            ]
        );
        $reception2->assignRole($receptionRole);
        $reception2->givePermissionTo(['patients.view', 'patients.create', 'invoices.create', 'procedures.contact_report']);
        
        // Create Insurance Clerk
        $insurance = User::updateOrCreate(
            ['email' => 'insurance@hospital.sa'],
            [
                'username' => 'insurance',
                'name' => 'خالد عبدالله - موظف تأمين',
                'password' => Hash::make('password123'),
            ]
        );
        $insurance->assignRole($insuranceRole);
        $insurance->givePermissionTo(['patients.view', 'invoices.view', 'invoices.create', 'procedures.contact_report']);
        
        // Create Charity Clerk
        $charity = User::updateOrCreate(
            ['email' => 'charity@hospital.sa'],
            [
                'username' => 'charity',
                'name' => 'نورة سعيد - موظفة جمعيات',
                'password' => Hash::make('password123'),
            ]
        );
        $charity->assignRole($charityRole);
        $charity->givePermissionTo(['patients.view', 'invoices.view', 'invoices.create', 'procedures.contact_report']);
        
        // Create Accountant
        $accountant = User::updateOrCreate(
            ['email' => 'accountant@hospital.sa'],
            [
                'username' => 'accountant',
                'name' => 'عبدالرحمن حسن - محاسب',
                'password' => Hash::make('password123'),
            ]
        );
        $accountant->assignRole($accountantRole);
        $accountant->givePermissionTo(['invoices.view', 'payments.view', 'payments.create', 'payments.approve', 'reports.view', 'reports.generate']);
        
        // Create Doctor
        $doctor = User::updateOrCreate(
            ['email' => 'doctor@hospital.sa'],
            [
                'username' => 'doctor',
                'name' => 'د. محمد أحمد - طبيب',
                'password' => Hash::make('password123'),
            ]
        );
        $doctor->assignRole($doctorRole);
        $doctor->givePermissionTo(['patients.view', 'patients.edit', 'invoices.view']);
        
        // Create Nurse
        $nurse = User::updateOrCreate(
            ['email' => 'nurse@hospital.sa'],
            [
                'username' => 'nurse',
                'name' => 'مريم خالد - ممرضة',
                'password' => Hash::make('password123'),
            ]
        );
        $nurse->assignRole($nurseRole);
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
