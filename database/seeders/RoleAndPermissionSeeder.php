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

        // موظف (مكتب الدخول / إدخال بيانات): مرضى، خدمات، إجراءات إدارية
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'patients.view', 'patients.create', 'patients.edit', 'patients.print', 'patients.transfer',
            'authorizations.view', 'authorizations.create', 'authorizations.print',
            'attachments.upload', 'attachments.view',
            'procedures.contact_report', 'procedures.written_commitment', 'procedures.non_commitment', 'procedures.debt_inventory', 'procedures.print',
            'invoices.view',
        ]);

        // أمين صندوق: اعتماد الكاش، إغلاق يومي، رفع تقارير
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'patients.view', 'invoices.view', 'payments.view', 'payments.create', 'payments.approve', 'payments.daily_close',
            'attachments.view', 'reports.view', 'reports.export',
        ]);

        // محاسب: مراجعة التحصيل، تقارير مالية، تصدير
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'patients.view', 'invoices.view', 'invoices.print', 'payments.view', 'claims.view',
            'attachments.view', 'reports.view', 'reports.export', 'activity.view',
        ]);

        // التأمين (موظف تأمين)
        $insurance = Role::firstOrCreate(['name' => 'insurance', 'guard_name' => 'web']);
        $insurance->syncPermissions([
            'patients.view', 'invoices.view', 'authorizations.view', 'claims.view', 'claims.create', 'claims.edit', 'claims.notes', 'claims.send',
            'attachments.upload', 'attachments.view',
        ]);

        // التحصيل (يمكن دمجه مع محاسب أو أمين صندوق حسب الهيكل)
        $collection = Role::firstOrCreate(['name' => 'collection', 'guard_name' => 'web']);
        $collection->syncPermissions([
            'patients.view', 'invoices.view', 'payments.view', 'payments.create', 'claims.view',
            'reports.view', 'reports.export',
        ]);
    }
}
