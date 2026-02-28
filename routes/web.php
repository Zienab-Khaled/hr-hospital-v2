<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactReportController;
use App\Http\Controllers\DashboardApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtInventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NonCommitmentReportController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\ShiftHandoverController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\RevenueWorkflowController;
use App\Http\Controllers\CashierWorkflowController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\WrittenCommitmentController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Language switcher
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');

// Public approval routes (no auth required)
Route::get('approvals/{token}', [App\Http\Controllers\ApprovalController::class, 'respond'])->name('approvals.respond');
Route::post('approvals/{token}', [App\Http\Controllers\ApprovalController::class, 'processResponse'])->name('approvals.process');

// Public invoice party response (confirm/reject with written approval - no auth)
Route::get('invoice-party-response/{token}', [App\Http\Controllers\InvoicePartyResponseController::class, 'show'])->name('invoice-party-response.show');
Route::post('invoice-party-response/{token}', [App\Http\Controllers\InvoicePartyResponseController::class, 'process'])->name('invoice-party-response.process');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    // API Token Routes
    Route::post('api/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::post('api/refresh', [App\Http\Controllers\Auth\AuthController::class, 'refresh']);
});

Route::middleware('auth:web,api')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard Dynamic Modals API
    Route::prefix('api/dashboard')->group(function () {
        Route::get('/patients-search', [DashboardApiController::class, 'searchPatients'])->name('api.dashboard.patients-search');
        Route::get('/patient-invoices', [DashboardApiController::class, 'getPatientInvoices'])->name('api.dashboard.patient-invoices');
        Route::get('/get-details', [DashboardApiController::class, 'getRecordDetails'])->name('api.dashboard.get-details');
        Route::post('/patient/store', [DashboardApiController::class, 'storePatient'])->name('api.dashboard.patient.store');
        Route::post('/visit/store', [DashboardApiController::class, 'storeVisit'])->name('api.dashboard.visit.store');
        Route::post('/written-commitment', [DashboardApiController::class, 'storeWrittenCommitment'])->name('api.dashboard.written-commitment.store');
        Route::post('/non-commitment-report', [DashboardApiController::class, 'storeNonCommitmentReport'])->name('api.dashboard.non-commitment-report.store');
        Route::post('/contact-report', [DashboardApiController::class, 'storeContactReport'])->name('api.dashboard.contact-report.store');
        Route::post('/debt-inventory', [DashboardApiController::class, 'storeDebtInventory'])->name('api.dashboard.debt-inventory.store');
    });

    Route::get('patients/search', [PatientController::class, 'search'])->name('patients.search');
    Route::get('patients/check-insurance', [PatientController::class, 'checkInsurance'])->name('patients.check-insurance');
    Route::post('patients/extract-identity-document', [PatientController::class, 'extractIdentityDocument'])->name('patients.extract-identity-document');
    Route::get('patients', [PlaceholderController::class, 'patientsIndex'])->name('patients.index');
    Route::get('patients/charity', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.charity');
    Route::get('patients/cash', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.cash');
    Route::get('patients/insurance', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.insurance');
    Route::get('patients/followup', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.followup');
    Route::get('patients/collection', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.collection');
    Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::post('patients/{patient}/transfer', [PatientController::class, 'transfer'])->name('patients.transfer');
    Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::get('patients/by-department', [PlaceholderController::class, 'patientsDepartmentsList'])->name('patients.departments-list');
    Route::get('patients/by-department/{department}', [PlaceholderController::class, 'patientsByDepartment'])->name('patients.by-department');

    Route::get('visits/create', [VisitController::class, 'create'])->name('visits.create');
    Route::get('visits/create/services-search', [VisitController::class, 'searchServicesForEligibility'])->name('visits.eligibility-services-search');
    Route::resource('visits', VisitController::class)->except(['create']); // visits.index, visits.show, visits.store, visits.edit, visits.update, visits.destroy
    // visits.create is handled manually above because of specific logic

    Route::get('visits/{visit}/treatment-eligibility-print', [VisitController::class, 'treatmentEligibilityPrint'])->name('visits.treatment-eligibility-print');
    Route::post('visits/{visit}/treatment-eligibility-print', [VisitController::class, 'treatmentEligibilityPrintSubmit'])->name('visits.treatment-eligibility-print.submit');

    // Price Inquiry Print (for quotation/estimation only - does NOT record revenue)
    Route::get('visits/{visit}/price-inquiry-print', [VisitController::class, 'priceInquiryPrint'])->name('visits.price-inquiry-print');
    Route::post('visits/{visit}/price-inquiry-print', [VisitController::class, 'priceInquiryPrintSubmit'])->name('visits.price-inquiry-print.submit');

    Route::post('visits/{visit}/transfer', [VisitController::class, 'transfer'])->name('visits.transfer');

    Route::get('shift-handovers', [ShiftHandoverController::class, 'index'])->name('shift-handovers.index');
    Route::get('shift-handovers/create', [ShiftHandoverController::class, 'create'])->name('shift-handovers.create');
    Route::post('shift-handovers', [ShiftHandoverController::class, 'store'])->name('shift-handovers.store');
    Route::get('shift-handovers/{handover}', [ShiftHandoverController::class, 'show'])->name('shift-handovers.show');

    Route::get('contact-reports', [ContactReportController::class, 'index'])->name('contact-reports.index');
    Route::get('contact-reports/create', [ContactReportController::class, 'create'])->name('contact-reports.create');
    Route::post('contact-reports', [ContactReportController::class, 'store'])->name('contact-reports.store');

    Route::get('written-commitments', [WrittenCommitmentController::class, 'index'])->name('written-commitments.index');
    Route::get('written-commitments/create', [WrittenCommitmentController::class, 'create'])->name('written-commitments.create');
    Route::post('written-commitments', [WrittenCommitmentController::class, 'store'])->name('written-commitments.store');
    Route::get('written-commitments/{commitment}/print', [WrittenCommitmentController::class, 'print'])->name('written-commitments.print');

    Route::get('non-commitment-reports', [NonCommitmentReportController::class, 'index'])->name('non-commitment-reports.index');
    Route::get('non-commitment-reports/create', [NonCommitmentReportController::class, 'create'])->name('non-commitment-reports.create');
    Route::post('non-commitment-reports', [NonCommitmentReportController::class, 'store'])->name('non-commitment-reports.store');

    Route::get('debt-inventories', [DebtInventoryController::class, 'index'])->name('debt-inventories.index');
    Route::get('debt-inventories/create', [DebtInventoryController::class, 'create'])->name('debt-inventories.create');
    Route::post('debt-inventories', [DebtInventoryController::class, 'store'])->name('debt-inventories.store');

    Route::get('invoices', [PlaceholderController::class, 'invoicesIndex'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('invoices/create/patients-search', [InvoiceController::class, 'searchPatients'])->name('invoices.patients-search');
    Route::get('invoices/create/services-search', [InvoiceController::class, 'searchServices'])->name('invoices.services-search');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/print-commitment', [InvoiceController::class, 'printCommitmentForm'])->name('invoices.print-commitment');
    Route::get('invoices/{invoice}/print-non-commitment', [InvoiceController::class, 'printNonCommitmentForm'])->name('invoices.print-non-commitment');
    Route::get('invoices/{invoice}/execute-service/{item}', [App\Http\Controllers\InvoiceController::class, 'showExecuteService'])->name('invoices.execute-service.show');
    Route::post('invoices/{invoice}/execute-service/{item}', [App\Http\Controllers\InvoiceController::class, 'executeService'])->name('invoices.execute-service');
    Route::post('invoices/{invoice}/upload-signed-document', [InvoiceController::class, 'uploadSignedDocument'])->name('invoices.upload-signed-document');
    Route::delete('invoices/{invoice}/delete-signed-document/{media}', [InvoiceController::class, 'deleteSignedDocument'])->name('invoices.delete-signed-document');
    Route::post('payment-receipts', [App\Http\Controllers\PaymentReceiptController::class, 'store'])->name('payment-receipts.store');
    Route::get('payment-receipts/{receipt}/print', [App\Http\Controllers\PaymentReceiptController::class, 'print'])->name('payment-receipts.print');

    // Charity Claims Management
    Route::get('charity-claims', [App\Http\Controllers\CharityClaimController::class, 'index'])->name('charity-claims.index');
    Route::get('charity-claims/create', [App\Http\Controllers\CharityClaimController::class, 'create'])->name('charity-claims.create');
    Route::post('charity-claims', [App\Http\Controllers\CharityClaimController::class, 'store'])->name('charity-claims.store');
    Route::get('charity-claims/{charityClaim}', [App\Http\Controllers\CharityClaimController::class, 'show'])->name('charity-claims.show');
    Route::post('charity-claims/{charityClaim}/send', [App\Http\Controllers\CharityClaimController::class, 'send'])->name('charity-claims.send');
    Route::post('charity-claims/{charityClaim}/update-status', [App\Http\Controllers\CharityClaimController::class, 'updateStatus'])->name('charity-claims.update-status');
    Route::post('charity-claims/{charityClaim}/notes', [App\Http\Controllers\CharityClaimController::class, 'addNote'])->name('charity-claims.notes.store');

    // Insurance Claims
    Route::get('insurance-claims/create', [App\Http\Controllers\InsuranceClaimController::class, 'create'])->name('insurance-claims.create');
    Route::post('insurance-claims', [App\Http\Controllers\InsuranceClaimController::class, 'store'])->name('insurance-claims.store');
    Route::get('insurance-claims/patients-search', [App\Http\Controllers\InsuranceClaimController::class, 'searchPatients'])->name('insurance-claims.patients-search');
    Route::get('insurance-claims/get-invoices/{patient}', [App\Http\Controllers\InsuranceClaimController::class, 'getInvoices'])->name('insurance-claims.get-invoices');
    Route::get('insurance-claims/get-items/{invoice}', [App\Http\Controllers\InsuranceClaimController::class, 'getItems'])->name('insurance-claims.get-items');
    Route::get('insurance-claims/{insuranceClaim}', [App\Http\Controllers\InsuranceClaimController::class, 'show'])->name('insurance-claims.show');
    Route::post('insurance-claims/{insuranceClaim}/update-status', [App\Http\Controllers\InsuranceClaimController::class, 'updateStatus'])->name('insurance-claims.update-status');
    Route::get('invoices/{invoice}/send-to-party', [InvoiceController::class, 'sendToParty'])->name('invoices.send-to-party');
    Route::post('invoices/{invoice}/send-to-party', [InvoiceController::class, 'sendToPartySubmit'])->name('invoices.send-to-party.submit');
    Route::post('invoices/{invoice}/send-charity-price-offer', [InvoiceController::class, 'sendCharityPriceOffer'])->name('invoices.send-charity-price-offer');
    Route::post('invoices/{invoice}/notify-charity-completed', [InvoiceController::class, 'notifyCharityCompleted'])->name('invoices.notify-charity-completed');
    Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('authorizations', [PlaceholderController::class, 'authorizationsIndex'])->name('authorizations.index');
    Route::get('payments', [PlaceholderController::class, 'paymentsIndex'])->name('payments.index');
    Route::post('payments/{payment}/approve', [PlaceholderController::class, 'paymentApprove'])->name('payments.approve');
    Route::get('claims', [PlaceholderController::class, 'claimsIndex'])->name('claims.index');
    Route::get('reports', [PlaceholderController::class, 'reportsIndex'])->name('reports.index');
    Route::get('reports/export/pdf', [App\Http\Controllers\ReportExportController::class, 'exportPdf'])->name('reports.export.pdf');
    // Revenue Workflow (Control Room)
    Route::prefix('revenue')->name('revenue.')->group(function () {
        Route::get('/control-room', [RevenueWorkflowController::class, 'controlRoom'])->name('control-room');
        Route::get('/treasury', [RevenueWorkflowController::class, 'treasuryIndex'])->name('treasury.index');
        Route::get('/daily-summary', [RevenueWorkflowController::class, 'dailyRevenueSummary'])->name('daily-summary');
        Route::post('/invoices/{invoice}/match', [RevenueWorkflowController::class, 'match'])->name('invoices.match');
        Route::post('/invoices/{invoice}/reject', [RevenueWorkflowController::class, 'reject'])->name('invoices.reject');
        Route::post('/invoices/{invoice}/ready', [RevenueWorkflowController::class, 'markReadyForDeposit'])->name('invoices.ready');
        Route::post('/invoices/{invoice}/deposited', [RevenueWorkflowController::class, 'markDeposited'])->name('invoices.deposited');
    });
    Route::get('revenue/cashier', [CashierWorkflowController::class, 'index'])->name('cashier.index');
    Route::post('revenue/cashier/{invoice}/receive', [CashierWorkflowController::class, 'receive'])->name('cashier.receive');
    Route::get('reports/export/excel', [App\Http\Controllers\ReportExportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('reports/upload-cluster', [PlaceholderController::class, 'reportsUploadCluster'])->name('reports.upload-cluster');
    Route::post('reports/upload-cluster', [PlaceholderController::class, 'reportsUploadClusterStore'])->name('reports.upload-cluster.store');

    Route::get('departments', [PlaceholderController::class, 'departmentsIndex'])->name('departments.index');
    Route::get('departments/create', [PlaceholderController::class, 'departmentsCreate'])->name('departments.create');
    Route::post('departments', [PlaceholderController::class, 'departmentsStore'])->name('departments.store');
    Route::get('departments/{department}', [PlaceholderController::class, 'departmentsShow'])->name('departments.show');
    Route::get('departments/{department}/edit', [PlaceholderController::class, 'departmentsEdit'])->name('departments.edit');
    Route::put('departments/{department}', [PlaceholderController::class, 'departmentsUpdate'])->name('departments.update');
    Route::delete('departments/{department}', [PlaceholderController::class, 'departmentsDestroy'])->name('departments.destroy');
    Route::get('services', [PlaceholderController::class, 'servicesIndex'])->name('services.index');
    Route::get('services/create', [PlaceholderController::class, 'servicesCreate'])->name('services.create');
    Route::post('services', [PlaceholderController::class, 'servicesStore'])->name('services.store');
    Route::get('services/{service}', [PlaceholderController::class, 'servicesShow'])->name('services.show');
    Route::get('services/{service}/edit', [PlaceholderController::class, 'servicesEdit'])->name('services.edit');
    Route::put('services/{service}', [PlaceholderController::class, 'servicesUpdate'])->name('services.update');
    Route::delete('services/{service}', [PlaceholderController::class, 'servicesDestroy'])->name('services.destroy');
    Route::get('users', [PlaceholderController::class, 'usersIndex'])->name('users.index');
    Route::get('users/create', [PlaceholderController::class, 'usersCreate'])->name('users.create');
    Route::post('users', [PlaceholderController::class, 'usersStore'])->name('users.store');
    Route::get('users/{user}/edit', [PlaceholderController::class, 'usersEdit'])->name('users.edit');
    Route::put('users/{user}', [PlaceholderController::class, 'usersUpdate'])->name('users.update');
    Route::patch('users/{user}', [PlaceholderController::class, 'usersUpdate'])->name('users.update');
    Route::delete('users/{user}', [PlaceholderController::class, 'usersDestroy'])->name('users.destroy');
    Route::get('users/{user}', [PlaceholderController::class, 'usersShow'])->name('users.show');
    Route::get('settings', [PlaceholderController::class, 'settingsIndex'])->name('settings.index');
    Route::post('settings', [PlaceholderController::class, 'settingsUpdate'])->name('settings.update');
    Route::resource('shifts', ShiftController::class);
    Route::get('codes/upload', [PlaceholderController::class, 'codesUpload'])->name('codes.upload');
    Route::post('codes/upload', [PlaceholderController::class, 'codesUploadStore'])->name('codes.upload.store');
    Route::get('activity', [PlaceholderController::class, 'activityIndex'])->name('activity.index');

    Route::get('insurance-companies', [PlaceholderController::class, 'insuranceCompaniesIndex'])->name('insurance-companies.index');
    Route::get('insurance-companies/create', [PlaceholderController::class, 'insuranceCompaniesCreate'])->name('insurance-companies.create');
    Route::post('insurance-companies', [PlaceholderController::class, 'insuranceCompaniesStore'])->name('insurance-companies.store');
    Route::get('insurance-companies/{insurance_company}/edit', [PlaceholderController::class, 'insuranceCompaniesEdit'])->name('insurance-companies.edit');
    Route::put('insurance-companies/{insurance_company}', [PlaceholderController::class, 'insuranceCompaniesUpdate'])->name('insurance-companies.update');
    Route::delete('insurance-companies/{insurance_company}', [PlaceholderController::class, 'insuranceCompaniesDestroy'])->name('insurance-companies.destroy');
    Route::get('insurance-companies/{insurance_company}', [PlaceholderController::class, 'insuranceCompaniesShow'])->name('insurance-companies.show');

    Route::get('charity-entities', [PlaceholderController::class, 'charityEntitiesIndex'])->name('charity-entities.index');
    Route::get('charity-entities/create', [PlaceholderController::class, 'charityEntitiesCreate'])->name('charity-entities.create');
    Route::post('charity-entities', [PlaceholderController::class, 'charityEntitiesStore'])->name('charity-entities.store');
    Route::get('charity-entities/{charity_entity}/edit', [PlaceholderController::class, 'charityEntitiesEdit'])->name('charity-entities.edit');
    Route::put('charity-entities/{charity_entity}', [PlaceholderController::class, 'charityEntitiesUpdate'])->name('charity-entities.update');
    Route::delete('charity-entities/{charity_entity}', [PlaceholderController::class, 'charityEntitiesDestroy'])->name('charity-entities.destroy');
    Route::get('charity-entities/{charity_entity}', [PlaceholderController::class, 'charityEntitiesShow'])->name('charity-entities.show');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});
