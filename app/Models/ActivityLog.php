<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'description', 'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** تسمية الإجراء للعرض (عربي عند لغة الواجهة عربية) */
    public function actionLabel(): string
    {
        $action = (string) ($this->action ?? '');
        if (app()->getLocale() !== 'ar' || $action === '') {
            return $action !== '' ? $action : '—';
        }

        $map = [
            'Invoice Created' => 'إنشاء فاتورة',
            'Invoice Updated' => 'تعديل فاتورة',
            'Invoice Deleted' => 'حذف فاتورة',
            'Invoice Sent' => 'إرسال فاتورة',
            'Invoice Auto-Created' => 'إنشاء فاتورة تلقائي',
            'Print Invoice' => 'طباعة فاتورة',
            'Print Commitment' => 'طباعة محضر تعهد',
            'Print Non-Commitment' => 'طباعة محضر عدم التوقيع',
            'Print Out-of-Coverage' => 'طباعة إقرار خدمة خارج التغطية',
            'Print Eligibility' => 'طباعة أحقية العلاج',
            'Print Price Inquiry' => 'طباعة عرض سعر استعلامي',
            'Print Price Offer' => 'طباعة عرض السعر',
            'Payment Recorded' => 'تسجيل دفعة',
            'Service Executed' => 'تنفيذ خدمة',
            'Patient Created' => 'إنشاء مريض',
            'Patient Updated' => 'تعديل مريض',
            'Patient Deleted' => 'حذف مريض',
            'Visit Created' => 'إنشاء زيارة',
            'Visit Updated' => 'تعديل زيارة',
            'Visit Deleted' => 'حذف زيارة',
            'Visit Transferred' => 'نقل زيارة',
            'Entry Fee Invoice' => 'فاتورة كشفية دخول',
            'Charity Claim Created' => 'إنشاء مطالبة جمعية',
            'Charity Claim Sent' => 'إرسال مطالبة جمعية',
            'Charity Claim Status Updated' => 'تحديث حالة مطالبة جمعية',
            'Charity Claim Note Added' => 'إضافة ملاحظة مطالبة جمعية',
            'Charity Notified' => 'إشعار الجمعية',
            'Charity Payment Reminder Sent' => 'تم تذكير الجمعية بالسداد',
            'Insurance Claim Created' => 'إنشاء مطالبة تأمين',
            'Insurance Claim Status Updated' => 'تحديث حالة مطالبة تأمين',
            'Signed Document Uploaded' => 'رفع مستند موقّع',
            'Signed Document Deleted' => 'حذف مستند موقّع',
            'Debt Marked Paid' => 'سداد مديونية',
            'shift_created' => 'إنشاء مناوبة',
            'shift_updated' => 'تعديل مناوبة',
            'shift_deleted' => 'حذف مناوبة',
            'department_created' => 'إنشاء قسم',
            'service_created' => 'إنشاء خدمة',
            'service_updated' => 'تعديل خدمة',
            'service_deleted' => 'حذف خدمة',
            'user_created' => 'إنشاء مستخدم',
            'user_updated' => 'تعديل مستخدم',
            'user_deleted' => 'حذف مستخدم',
            'settings_updated' => 'تحديث الإعدادات',
            'codes_uploaded' => 'رفع أكواد رسمية',
            'cluster_report_uploaded' => 'رفع تقرير للتجمع',
            'insurance_company_created' => 'إنشاء شركة تأمين',
            'insurance_company_updated' => 'تعديل شركة تأمين',
            'insurance_company_deleted' => 'حذف شركة تأمين',
            'charity_entity_created' => 'إنشاء جمعية خيرية',
            'charity_entity_updated' => 'تعديل جمعية خيرية',
            'charity_entity_deleted' => 'حذف جمعية خيرية',
        ];

        return $map[$action] ?? $action;
    }

    /** الوصف للعرض (عربي عند لغة الواجهة عربية) — يشمل السجلات القديمة الإنجليزية */
    public function descriptionLabel(): string
    {
        $desc = trim((string) ($this->description ?? ''));
        if ($desc === '') {
            return '—';
        }
        if (app()->getLocale() !== 'ar') {
            return $desc;
        }

        // أنماط أوصاف ديناميكية شائعة
        $patterns = [
            '/^Invoice created with (\d+) items?$/i' => 'تم إنشاء فاتورة بعدد $1 بند',
            '/^Non-commitment form printed for invoice:\s*(.+)$/i' => 'تم طباعة محضر عدم التوقيع للفاتورة: $1',
            '/^تم طباعة إقرار خدمة خارج التغطية للفاتورة:\s*(.+)$/u' => 'تم طباعة إقرار خدمة خارج التغطية للفاتورة: $1',
            '/^Commitment form printed for invoice:\s*(.+)$/i' => 'تم طباعة محضر التعهد للفاتورة: $1',
            '/^Detailed invoice printed:\s*(.+)$/i' => 'تم طباعة الفاتورة التفصيلية: $1',
            '/^Invoice printed as q-1 receipt:\s*(.+)$/i' => 'تم طباعة الفاتورة (نموذج ق-1): $1',
            '/^Invoice printed \(q-1 layout\):\s*(.+)$/i' => 'تم طباعة الفاتورة (نموذج ق-1): $1',
            '/^Payment of (.+) recorded \((.+)\)\. services:\s*(.*)$/i' => 'تم تسجيل دفعة بمبلغ $1 ($2). الخدمات: $3',
            '/^Payment of (.+) recorded \((.+)\)$/i' => 'تم تسجيل دفعة بمبلغ $1 ($2)',
            '/^Invoice sent to (.+)$/i' => 'تم إرسال الفاتورة إلى $1',
            '/^Charity price offer email sent to (.+)$/i' => 'تم إرسال عرض السعر للجمعية إلى $1',
            '/^Charity completion email sent to (.+)$/i' => 'تم إرسال إيميل اكتمال الخدمات إلى $1',
            '/^Payment reminder sent to (.+)$/i' => 'تم إرسال تذكير السداد إلى $1',
            '/^Service marked as executed on (.+)$/i' => 'تم تنفيذ الخدمة بتاريخ $1',
            '/^Signed document uploaded to collection:\s*(.+)$/i' => 'تم رفع مستند موقّع إلى المجموعة: $1',
            '/^Signed document deleted:\s*(.+)$/i' => 'تم حذف المستند الموقّع: $1',
            '/^Invoice created automatically from Eligibility Print for patient:\s*(.+)$/i' => 'تم إنشاء فاتورة تلقائياً من طباعة الأحقية للمريض: $1',
            '/^Treatment eligibility form printed for patient:\s*(.+)$/i' => 'تم طباعة نموذج أحقية العلاج للمريض: $1',
            '/^Price inquiry form printed for patient:\s*(.+)$/i' => 'تم طباعة عرض السعر الاستعلامي للمريض: $1',
            '/^Department entry fee invoice created for visit:\s*(.+)$/i' => 'تم إنشاء فاتورة كشفية دخول للزيارة: $1',
            '/^Detailed invoice from visit services$/i' => 'فاتورة تفصيلية من خدمات الزيارة',
            '/^Patient registered$/i' => 'تم تسجيل المريض',
            '/^Patient details updated$/i' => 'تم تحديث بيانات المريض',
            '/^Patient deleted$/i' => 'تم حذف المريض',
            '/^Visit auto-created on patient registration$/i' => 'تم إنشاء زيارة تلقائياً عند تسجيل المريض',
            '/^Patient registered to department$/i' => 'تم تسجيل المريض في القسم',
            '/^Visit details updated$/i' => 'تم تحديث بيانات الزيارة',
            '/^Visit transferred to department$/i' => 'تم نقل الزيارة إلى قسم آخر',
            '/^Visit record deleted$/i' => 'تم حذف سجل الزيارة',
            '/^Invoice details updated$/i' => 'تم تحديث تفاصيل الفاتورة',
            '/^Invoice deleted$/i' => 'تم حذف الفاتورة',
            '/^Claim created for invoice$/i' => 'تم إنشاء مطالبة للفاتورة',
            '/^Claim sent to charity entity$/i' => 'تم إرسال المطالبة للجمعية',
            '/^Status changed$/i' => 'تم تغيير الحالة',
            '/^Note added$/i' => 'تمت إضافة ملاحظة',
            '/^Upload report to cluster$/i' => 'رفع تقرير إلى التجمع',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $translated = preg_replace($pattern, $replacement, $desc);
            if ($translated !== null && $translated !== $desc) {
                return $translated;
            }
        }

        // أوصاف ثابتة شائعة
        $exact = [
            'Invoice created with 1 items' => 'تم إنشاء فاتورة بعدد 1 بند',
            'Patient registered' => 'تم تسجيل المريض',
            'Patient details updated' => 'تم تحديث بيانات المريض',
            'Patient deleted' => 'تم حذف المريض',
            'Invoice details updated' => 'تم تحديث تفاصيل الفاتورة',
            'Invoice deleted' => 'تم حذف الفاتورة',
            'Detailed invoice from visit services' => 'فاتورة تفصيلية من خدمات الزيارة',
            'Claim created for invoice' => 'تم إنشاء مطالبة للفاتورة',
            'Status changed' => 'تم تغيير الحالة',
            'Note added' => 'تمت إضافة ملاحظة',
        ];

        return $exact[$desc] ?? $desc;
    }
}
