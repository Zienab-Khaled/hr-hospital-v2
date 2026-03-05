<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * مصدر واحد للأدوار والصلاحيات: يربط كل تاب/قسم في النظام بالصلاحيات المناسبة،
 * ويحدد لكل دور (رول) الصلاحيات التي يراها ويستطيع تنفيذها.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guards = ['web', 'api'];

        // كل الصلاحيات المستخدمة في التطبيق (الكونترولرز والـ sidebar)
        $allPermissionNames = [
            // إدارة المرضى والزيارات
            'patients.view',
            'patients.create',
            'patients.edit',
            'patients.delete',
            // الزيارات
            'visits.view',
            'visits.create',
            'visits.edit',
            'visits.delete',
            // الفواتير
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            // المدفوعات
            'payments.view',
            'payments.create',
            'payments.approve',
            // التقارير
            'reports.view',
            'reports.generate',
            'reports.upload_cluster',
            // الموافقات والمطالبات
            'authorizations.view',
            'claims.view',
            'insurance_reports.view',
            // سجل النشاط
            'activity.view',
            // إدارة النظام
            'departments.manage',
            'services.manage',
            'settings.manage',
            'users.manage',
            'codes.upload',
            'insurance_companies.manage',
            'charity_entities.manage',
            // الإجراءات الإدارية (تقرير اتصال، التزام كتابي، عدم التزام، جرد مديونيات)
            'procedures.contact_report',
            'procedures.written_commitment',
            'procedures.non_commitment',
            'procedures.debt_inventory',
        ];

        foreach ($allPermissionNames as $name) {
            foreach ($guards as $guard) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }

        foreach ($guards as $guard) {
            $allPerms = Permission::where('guard_name', $guard)->pluck('name')->toArray();

            // أدمن: كل الصلاحيات
            $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $admin->syncPermissions($allPerms);

            // مدير: نفس صلاحيات الأدمن (إدارة كاملة + كل التابات + تقارير التأمين)
            $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
            $manager->syncPermissions($allPerms);

            // طبيب التأمين: إضافة مرضى، متابعة مطالبات التأمين (بدون إدارة النظام أو تقارير التأمين)
            $insuranceDoctor = Role::firstOrCreate(['name' => 'insurance_doctor', 'guard_name' => $guard]);
            $insuranceDoctor->syncPermissions([
                'patients.view', 'patients.create', 'patients.edit',
                'visits.view', 'invoices.view', 'invoices.create',
                'claims.view', 'authorizations.view',
            ]);

            // استقبال: إدارة المرضى، الزيارات، إنشاء فواتير، الإجراءات
            $reception = Role::firstOrCreate(['name' => 'reception', 'guard_name' => $guard]);
            $reception->syncPermissions([
                'patients.view', 'patients.create',
                'visits.view', 'visits.create', 'visits.edit',
                'invoices.view', 'invoices.create',
                'procedures.contact_report', 'procedures.written_commitment',
                'procedures.non_commitment', 'procedures.debt_inventory',
            ]);

            // موظف تأمين: مرضى تأمين فقط، فواتير، مطالبات تأمين، تفويضات، شركات تأمين، تقارير تأمين
            $insuranceClerk = Role::firstOrCreate(['name' => 'insurance_clerk', 'guard_name' => $guard]);
            $insuranceClerk->syncPermissions([
                'patients.view', 'visits.view',
                'invoices.view', 'invoices.create',
                'claims.view', 'authorizations.view',
                'insurance_reports.view', 'insurance_companies.manage',
                'procedures.contact_report',
            ]);

            // موظف جمعيات: مرضى، فواتير، مطالبات، إجراءات
            $charityClerk = Role::firstOrCreate(['name' => 'charity_clerk', 'guard_name' => $guard]);
            $charityClerk->syncPermissions([
                'patients.view', 'visits.view',
                'invoices.view', 'invoices.create',
                'claims.view', 'authorizations.view',
                'procedures.contact_report',
            ]);

            // محاسب: فواتير، مدفوعات، تقارير، سجل النشاط
            $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => $guard]);
            $accountant->syncPermissions([
                'patients.view', 'invoices.view', 'invoices.edit',
                'payments.view', 'payments.create', 'payments.approve',
                'reports.view', 'reports.generate', 'reports.upload_cluster',
                'claims.view', 'activity.view',
            ]);

            // طبيب: عرض/تعديل مرضى، عرض فواتير
            $doctor = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => $guard]);
            $doctor->syncPermissions([
                'patients.view', 'patients.edit',
                'visits.view', 'invoices.view',
            ]);

            // ممرضة: عرض المرضى فقط
            $nurse = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => $guard]);
            $nurse->syncPermissions(['patients.view', 'visits.view']);

            // محصل (الاستقبال - الطوارئ/العيادات): مرضى (عرض + إنشاء + تعديل)، زيارات، فواتير، مدفوعات، إجراءات — بدون مديونيات أو مطالبات
            $collection = Role::firstOrCreate(['name' => 'collection', 'guard_name' => $guard]);
            $collection->syncPermissions([
                'patients.view', 'patients.create', 'patients.edit',
                'visits.view', 'visits.create', 'visits.edit',
                'invoices.view', 'invoices.create',
                'payments.view', 'payments.create',
                'procedures.contact_report', 'procedures.written_commitment',
                'procedures.non_commitment',
            ]);

            // أمين صندوق: استلام من المحصل، اعتماد، إغلاق يومي، تقارير
            $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => $guard]);
            $cashier->syncPermissions([
                'patients.view', 'invoices.view',
                'payments.view', 'payments.create', 'payments.approve',
                'reports.view', 'reports.generate',
            ]);

            // رئيس المديونيات: متابعة مديونيات، تقارير، إجراءات
            $debtsHead = Role::firstOrCreate(['name' => 'debts_head', 'guard_name' => $guard]);
            $debtsHead->syncPermissions([
                'patients.view', 'visits.view',
                'invoices.view', 'invoices.edit',
                'payments.view', 'reports.view', 'reports.generate', 'reports.upload_cluster',
                'procedures.contact_report', 'procedures.written_commitment',
                'procedures.non_commitment', 'procedures.debt_inventory',
                'activity.view',
            ]);

            // فني متابعة مرضى (قسم التأمين): مرضى، زيارات، فواتير، مطالبات
            $patientFollowUp = Role::firstOrCreate(['name' => 'patient_follow_up', 'guard_name' => $guard]);
            $patientFollowUp->syncPermissions([
                'patients.view', 'patients.create', 'patients.edit',
                'visits.view', 'visits.create',
                'invoices.view', 'invoices.create',
                'claims.view', 'authorizations.view',
                'procedures.contact_report',
            ]);
        }
    }
}
