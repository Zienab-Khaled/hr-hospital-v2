<?php

namespace App\Support;

use App\Models\User;

/**
 * قواعد ظهور القوائم واللوحة حسب الدور (كل قسم يرى عمله فقط).
 */
final class RoleNav
{
    public static function isAdministration(?User $user): bool
    {
        return $user !== null && $user->hasAnyRole(['admin', 'manager']);
    }

    /** محاسب / أمين صندوق: الفواتير فقط */
    public static function isInvoicesOnly(?User $user): bool
    {
        if (! $user || self::isAdministration($user)) {
            return false;
        }

        return $user->hasAnyRole(['accountant', 'cashier']);
    }

    /** مكتب الدخول والاستقبال والممرضة: مرضى وزيارات — بدون مطالبات وفواتير وتقارير */
    public static function isAdmissionOnly(?User $user): bool
    {
        if (! $user || self::isAdministration($user) || self::isInvoicesOnly($user)) {
            return false;
        }

        if (! $user->hasAnyRole(['reception', 'employee', 'nurse'])) {
            return false;
        }

        return ! $user->hasAnyRole([
            'insurance_clerk', 'charity_clerk', 'insurance_doctor',
            'debts_head', 'collection', 'patient_follow_up', 'doctor', 'accountant', 'cashier',
        ]);
    }

    /** محاسب: غرفة التحكم + الفواتير */
    public static function canSeeControlRoom(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::isAdministration($user)
            || ($user->hasRole('accountant') && $user->can('invoices.view'));
    }

    /** أمين الصندوق: الخزينة + استلام الإيداع */
    public static function canSeeTreasury(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::isAdministration($user)
            || ($user->hasRole('cashier') && $user->can('invoices.view'));
    }

    /** يرى كل الفواتير بدون تقييد شيفت/قسم (محاسب، أمين صندوق، إدارة) */
    public static function canSeeAllInvoices(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::isAdministration($user)
            || $user->hasAnyRole(['accountant', 'cashier']);
    }

    public static function canSeeFinancialSummary(?User $user): bool
    {
        return self::isAdministration($user);
    }

    public static function canSeeReportsMenu(?User $user): bool
    {
        return self::isAdministration($user);
    }

    public static function canSeeActivityLog(?User $user): bool
    {
        return self::isAdministration($user);
    }

    public static function canSeeClaimsMenu(?User $user): bool
    {
        if (! $user || self::isInvoicesOnly($user) || self::isAdmissionOnly($user)) {
            return false;
        }

        return $user->can('claims.view');
    }

    public static function canSeePatientManagement(?User $user): bool
    {
        if (! $user || self::isInvoicesOnly($user)) {
            return false;
        }

        return self::isAdministration($user)
            || $user->can('patients.view')
            || $user->can('visits.view');
    }

    public static function canSeePaymentsMenu(?User $user): bool
    {
        if (! $user || self::isInvoicesOnly($user) || self::isAdmissionOnly($user)) {
            return false;
        }

        return $user->can('payments.view') || $user->can('payments.approve') || self::isAdministration($user);
    }

    public static function canSeeDelegations(?User $user): bool
    {
        if (! $user || self::isInvoicesOnly($user)) {
            return false;
        }

        return true;
    }

    /**
     * يرى كل الزيارات (وليس زيارات شيفت قسمه فقط):
     * الإدارة + فني متابعة المرضى (وظيفته متابعة المرضى عبر كل الأقسام).
     */
    public static function canSeeAllVisits(?User $user): bool
    {
        return $user !== null
            && ($user->hasAnyRole(['admin', 'manager', 'patient_follow_up']));
    }

    /** تقديم خدمة / إنشاء فاتورة — للجميع ما عدا المحاسب وأمين الصندوق */
    public static function canCreateInvoiceWithServices(?User $user): bool
    {
        if (! $user || ! $user->can('invoices.create')) {
            return false;
        }

        if (self::isInvoicesOnly($user)) {
            return false;
        }

        return true;
    }
}
