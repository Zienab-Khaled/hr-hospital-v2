<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // مرضى وزيارات
            'patients.view', 'patients.create', 'patients.edit', 'patients.print', 'patients.transfer',
            'visits.view', 'visits.create', 'visits.edit', 'visits.delete', 'visits.print',
            // فواتير ومدفوعات
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.print',
            'payments.view', 'payments.create', 'payments.edit', 'payments.approve', 'payments.daily_close',
            // موافقات وإحالات
            'authorizations.view', 'authorizations.create', 'authorizations.edit', 'authorizations.print',
            // مرفقات
            'attachments.upload', 'attachments.view',
            // إجراءات إدارية
            'procedures.contact_report', 'procedures.written_commitment', 'procedures.non_commitment', 'procedures.debt_inventory', 'procedures.print',
            // مطالبات تأمين/جمعيات
            'claims.view', 'claims.create', 'claims.edit', 'claims.notes', 'claims.send',
            // إدارة (سوبر أدمن فقط)
            'departments.manage', 'services.manage', 'settings.manage', 'users.manage', 'codes.upload',
            'insurance_companies.manage', 'charity_entities.manage',
            // تقارير
            'reports.view', 'reports.export', 'reports.upload_cluster', 'activity.view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $allPermissions = Permission::pluck('name')->toArray();

        // Super Admin: كل الصلاحيات (بما فيها إدارة النظام)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($allPermissions);

        // مدير = الأدمن: يرى نفس الشاشة (إدارة النظام + كل العمل اليومي)
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'patients.view', 'patients.create', 'patients.edit', 'patients.print', 'patients.transfer',
            'visits.view', 'visits.create', 'visits.edit', 'visits.delete', 'visits.print',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.print',
            'authorizations.view', 'authorizations.create', 'authorizations.edit', 'authorizations.print',
            'payments.view', 'payments.create', 'payments.edit', 'payments.approve', 'payments.daily_close',
            'claims.view', 'claims.create', 'claims.edit', 'claims.notes', 'claims.send',
            'attachments.upload', 'attachments.view',
            'procedures.contact_report', 'procedures.written_commitment', 'procedures.non_commitment', 'procedures.debt_inventory', 'procedures.print',
            'reports.view', 'reports.export', 'reports.upload_cluster', 'activity.view',
            'departments.manage', 'services.manage', 'settings.manage', 'users.manage', 'codes.upload',
            'insurance_companies.manage', 'charity_entities.manage',
        ]);

        // موظف (مكتب الدخول): مرضى وزيارات فقط
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'patients.view', 'patients.create', 'patients.edit', 'patients.print', 'patients.transfer',
            'visits.view', 'visits.create', 'visits.edit', 'visits.print',
            'attachments.upload', 'attachments.view',
            'procedures.contact_report', 'procedures.written_commitment', 'procedures.non_commitment', 'procedures.print',
        ]);

        // أمين صندوق: الفواتير فقط
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'invoices.view',
        ]);

        // محاسب: عرض وتعديل الفواتير فقط
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'invoices.view', 'invoices.edit', 'invoices.print',
        ]);

        // التأمين (موظف تأمين)
        $insurance = Role::firstOrCreate(['name' => 'insurance', 'guard_name' => 'web']);
        $insurance->syncPermissions([
            'patients.view', 'invoices.view', 'authorizations.view', 'claims.view', 'claims.create', 'claims.edit', 'claims.notes', 'claims.send',
            'visits.view', // Can view visits
            'attachments.upload', 'attachments.view',
        ]);

        // التحصيل: مرضى، زيارات، فواتير، مدفوعات (بدون مطالبات أو تقارير إدارية)
        $collection = Role::firstOrCreate(['name' => 'collection', 'guard_name' => 'web']);
        $collection->syncPermissions([
            'patients.view', 'patients.create', 'patients.edit',
            'visits.view', 'visits.create', 'visits.edit',
            'invoices.view', 'invoices.create',
            'payments.view', 'payments.create',
            'procedures.contact_report', 'procedures.written_commitment', 'procedures.non_commitment',
        ]);
    }
}
