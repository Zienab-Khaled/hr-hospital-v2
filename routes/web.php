<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtInventoryController;
use App\Http\Controllers\NonCommitmentReportController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\WrittenCommitmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('patients', [PlaceholderController::class, 'patientsIndex'])->name('patients.index');
    Route::get('patients/charity', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.charity');
    Route::get('patients/cash', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.cash');
    Route::get('patients/insurance', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.insurance');
    Route::get('patients/followup', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.followup');
    Route::get('patients/collection', [PlaceholderController::class, 'patientsBySection'])->name('patients.section.collection');
    Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('patients', [PatientController::class, 'store'])->name('patients.store');

    Route::get('contact-reports', [ContactReportController::class, 'index'])->name('contact-reports.index');
    Route::get('contact-reports/create', [ContactReportController::class, 'create'])->name('contact-reports.create');
    Route::post('contact-reports', [ContactReportController::class, 'store'])->name('contact-reports.store');

    Route::get('written-commitments', [WrittenCommitmentController::class, 'index'])->name('written-commitments.index');
    Route::get('written-commitments/create', [WrittenCommitmentController::class, 'create'])->name('written-commitments.create');
    Route::post('written-commitments', [WrittenCommitmentController::class, 'store'])->name('written-commitments.store');

    Route::get('non-commitment-reports', [NonCommitmentReportController::class, 'index'])->name('non-commitment-reports.index');
    Route::get('non-commitment-reports/create', [NonCommitmentReportController::class, 'create'])->name('non-commitment-reports.create');
    Route::post('non-commitment-reports', [NonCommitmentReportController::class, 'store'])->name('non-commitment-reports.store');

    Route::get('debt-inventories', [DebtInventoryController::class, 'index'])->name('debt-inventories.index');
    Route::get('debt-inventories/create', [DebtInventoryController::class, 'create'])->name('debt-inventories.create');
    Route::post('debt-inventories', [DebtInventoryController::class, 'store'])->name('debt-inventories.store');

    Route::get('invoices', [PlaceholderController::class, 'invoicesIndex'])->name('invoices.index');
    Route::get('authorizations', [PlaceholderController::class, 'authorizationsIndex'])->name('authorizations.index');
    Route::get('payments', [PlaceholderController::class, 'paymentsIndex'])->name('payments.index');
    Route::post('payments/{payment}/approve', [PlaceholderController::class, 'paymentApprove'])->name('payments.approve');
    Route::get('claims', [PlaceholderController::class, 'claimsIndex'])->name('claims.index');
    Route::get('reports', [PlaceholderController::class, 'reportsIndex'])->name('reports.index');
    Route::get('reports/upload-cluster', [PlaceholderController::class, 'reportsUploadCluster'])->name('reports.upload-cluster');
    Route::post('reports/upload-cluster', [PlaceholderController::class, 'reportsUploadClusterStore'])->name('reports.upload-cluster.store');

    Route::get('departments', [PlaceholderController::class, 'departmentsIndex'])->name('departments.index');
    Route::get('departments/create', [PlaceholderController::class, 'departmentsCreate'])->name('departments.create');
    Route::post('departments', [PlaceholderController::class, 'departmentsStore'])->name('departments.store');
    Route::get('services', [PlaceholderController::class, 'servicesIndex'])->name('services.index');
    Route::get('services/create', [PlaceholderController::class, 'servicesCreate'])->name('services.create');
    Route::post('services', [PlaceholderController::class, 'servicesStore'])->name('services.store');
    Route::get('users', [PlaceholderController::class, 'usersIndex'])->name('users.index');
    Route::get('settings', [PlaceholderController::class, 'settingsIndex'])->name('settings.index');
    Route::post('settings', [PlaceholderController::class, 'settingsUpdate'])->name('settings.update');
    Route::get('codes/upload', [PlaceholderController::class, 'codesUpload'])->name('codes.upload');
    Route::post('codes/upload', [PlaceholderController::class, 'codesUploadStore'])->name('codes.upload.store');
});
