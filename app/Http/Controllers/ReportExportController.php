<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportExportController extends Controller
{
    /**
     * Common data retrieval logic for reports.
     */
    private function getReportData($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $revenueTotal = (float) Payment::whereNotNull('approved_by')
            ->whereBetween('received_date', [$start, $end])
            ->sum('amount');

        $totalInvoiced = (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total_amount');
        $totalDebts = (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('remaining_amount');

        $collectionRate = $totalInvoiced > 0
            ? round(($revenueTotal / $totalInvoiced) * 100, 1)
            : 0;

        // توزيع الإيرادات (Charity, Insurance, Cash)
        $paymentsByType = Payment::whereNotNull('approved_by')
            ->whereBetween('received_date', [$start, $end])
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('patients', 'invoices.patient_id', '=', 'patients.id')
            ->select('patients.payment_type', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('patients.payment_type')
            ->pluck('total', 'payment_type');

        $deptPerformance = \App\Models\Department::all()->map(function($dept) use ($start, $end) {
            $stats = Payment::whereNotNull('approved_by')
                ->whereBetween('received_date', [$start, $end])
                ->whereHas('invoice.visit', function($q) use ($dept) {
                    $q->where('department_id', $dept->id);
                })
                ->select(DB::raw('SUM(amount) as total'), DB::raw('COUNT(DISTINCT invoice_id) as invoices_count'))
                ->first();

            return (object) [
                'name' => $dept->name_ar ?? $dept->name,
                'total' => (float) ($stats->total ?? 0),
                'count' => (int) ($stats->invoices_count ?? 0)
            ];
        })->sortByDesc('total')->values();

        return [
            'start' => $start,
            'end' => $end,
            'revenueTotal' => $revenueTotal,
            'totalInvoiced' => $totalInvoiced,
            'totalDebts' => $totalDebts,
            'collectionRate' => $collectionRate,
            'paymentsByType' => $paymentsByType,
            'deptPerformance' => $deptPerformance,
        ];
    }

    public function exportPdf(Request $request)
    {
        Gate::authorize('reports.view');

        $data = $this->getReportData(
            $request->input('start_date', Carbon::today()->toDateString()),
            $request->input('end_date', Carbon::today()->toDateString())
        );

        $html = view('reports.pdf_template', $data)->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font_size' => 12,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $filename = 'Report_' . $data['start']->format('Y-m-d') . '_to_' . $data['end']->format('Y-m-d') . '.pdf';

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportExcel(Request $request)
    {
        Gate::authorize('reports.view');

        $data = $this->getReportData(
            $request->input('start_date', Carbon::today()->toDateString()),
            $request->input('end_date', Carbon::today()->toDateString())
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if (app()->getLocale() === 'ar') {
            $sheet->setRightToLeft(true);
        }

        // Header
        $sheet->setCellValue('A1', app()->getLocale() === 'ar' ? 'تقرير الإيرادات والتحصيل' : 'Revenue & Collection Report');
        $sheet->setCellValue('A2', (app()->getLocale() === 'ar' ? 'الفترة من: ' : 'From: ') . $data['start']->format('Y-m-d'));
        $sheet->setCellValue('B2', (app()->getLocale() === 'ar' ? 'إلى: ' : 'To: ') . $data['end']->format('Y-m-d'));

        // Summary Metrics
        $sheet->setCellValue('A4', app()->getLocale() === 'ar' ? 'المؤشر' : 'Metric');
        $sheet->setCellValue('B4', app()->getLocale() === 'ar' ? 'القيمة' : 'Value');

        $sheet->setCellValue('A5', app()->getLocale() === 'ar' ? 'إجمالي الإيرادات (المحصلة)' : 'Total Revenue (Collected)');
        $sheet->setCellValue('B5', $data['revenueTotal']);

        $sheet->setCellValue('A6', app()->getLocale() === 'ar' ? 'إجمالي المفوتر' : 'Total Invoiced');
        $sheet->setCellValue('B6', $data['totalInvoiced']);

        $sheet->setCellValue('A7', app()->getLocale() === 'ar' ? 'إجمالي الديون (المتبقي)' : 'Total Debts (Remaining)');
        $sheet->setCellValue('B7', $data['totalDebts']);

        $sheet->setCellValue('A8', app()->getLocale() === 'ar' ? 'نسبة التحصيل' : 'Collection Rate');
        $sheet->setCellValue('B8', $data['collectionRate'] . '%');

        // Department Performance
        $sheet->setCellValue('A10', app()->getLocale() === 'ar' ? 'أداء الأقسام' : 'Department Performance');
        $sheet->setCellValue('A11', app()->getLocale() === 'ar' ? 'القسم' : 'Department');
        $sheet->setCellValue('B11', app()->getLocale() === 'ar' ? 'الإيرادات' : 'Revenue');
        $sheet->setCellValue('C11', app()->getLocale() === 'ar' ? 'عدد الفواتير' : 'Invoice Count');

        $row = 12;
        foreach ($data['deptPerformance'] as $dept) {
            $sheet->setCellValue('A' . $row, $dept->name);
            $sheet->setCellValue('B' . $row, $dept->total);
            $sheet->setCellValue('C' . $row, $dept->count);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Report_' . $data['start']->format('Y-m-d') . '_to_' . $data['end']->format('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $filename .'"');
        $writer->save('php://output');
        exit;
    }
}
