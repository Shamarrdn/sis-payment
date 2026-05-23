<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentAuthController;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\EmployeeAuthController;

// Employee Auth Routes
Route::get('/login', [EmployeeAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [EmployeeAuthController::class, 'login']);
Route::post('/logout', [EmployeeAuthController::class, 'logout'])->name('logout');

Route::prefix('student')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('student.login');
    Route::post('/login', [StudentAuthController::class, 'login']);
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('student.logout');
    
    Route::middleware('auth:student')->group(function () {
        Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('student.dashboard');
        Route::get('/tuition', [StudentPortalController::class, 'tuition'])->name('student.tuition');
        Route::post('/tuition', [StudentPortalController::class, 'processTuition'])->name('student.tuition.pay');
        Route::get('/checkout/{service}', [StudentPortalController::class, 'checkout'])->name('student.checkout');
        Route::post('/pay/{service}', [StudentPortalController::class, 'pay'])->name('student.pay');
        Route::get('/receipt/{payment}', [StudentPortalController::class, 'receipt'])->name('student.receipt');
        Route::get('/history', [StudentPortalController::class, 'history'])->name('student.history');
        Route::get('/profile', [StudentPortalController::class, 'profile'])->name('student.profile');
        Route::post('/profile', [StudentPortalController::class, 'updateProfile'])->name('student.profile.update');
        Route::post('/profile/document', [StudentPortalController::class, 'uploadDocument'])->name('student.profile.document.upload');
        Route::post('/chat', [\App\Http\Controllers\ChatbotController::class, 'chat'])->name('student.chat');
    });
});

Route::get('/documents/{document}', [StudentPortalController::class, 'viewDocument'])->name('document.view');

use App\Http\Controllers\StudentAffairsController;
use App\Http\Controllers\FinancialAffairsController;
use App\Http\Controllers\AdminController;

Route::middleware(['auth', 'role:student_affairs,super_admin,admin', 'scope'])->prefix('student-affairs')->name('affairs.student.')->group(function () {
    Route::get('/', [StudentAffairsController::class, 'index'])->name('index');
    Route::get('/create', [StudentAffairsController::class, 'create'])->name('create');
    Route::post('/', [StudentAffairsController::class, 'store'])->name('store');
    Route::post('/import', [StudentAffairsController::class, 'import'])->name('import');
    Route::get('/csv-template', [StudentAffairsController::class, 'csvTemplate'])->name('csv-template');
    Route::post('/bulk-action', [StudentAffairsController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/{student}', [StudentAffairsController::class, 'show'])->name('show');
    Route::post('/{student}/status', [StudentAffairsController::class, 'updateStatus'])->name('update-status');
    Route::post('/{student}/notes', [StudentAffairsController::class, 'addNote'])->name('add-note');
    Route::post('/documents/{document}/verify', [StudentAffairsController::class, 'verifyDocument'])->name('verify-document');
    Route::post('/sensitive-requests/{updateRequest}/process', [StudentAffairsController::class, 'processSensitiveRequest'])->name('process-sensitive-request');
    Route::get('/{student}/receipts', [StudentAffairsController::class, 'receipts'])->name('receipts');
    Route::post('/{student}/manual-pay', [StudentAffairsController::class, 'manualPay'])->name('manual-pay');
});

Route::middleware(['auth', 'role:financial_affairs,super_admin,admin', 'scope'])->prefix('financial-affairs')->name('affairs.financial.')->group(function () {
    Route::get('/', [FinancialAffairsController::class, 'index'])->name('index'); // التسوية اليومية
    Route::get('/payments', [FinancialAffairsController::class, 'payments'])->name('payments'); // التقارير مفلترة
});

Route::middleware(['auth', 'role:super_admin,admin,financial_affairs,student_affairs,graduate_affairs', 'scope'])->prefix('admin')->name('admin.')->group(function () {
    // Service Management
    Route::get('/services', [AdminController::class, 'services'])->name('services.index');
    Route::post('/services', [AdminController::class, 'storeService'])->name('services.store');
    Route::put('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
    Route::patch('/services/{service}/toggle', [AdminController::class, 'toggleService'])->name('services.toggle');
});

Route::middleware(['auth', 'role:super_admin,admin', 'scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Employee Management
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees.index');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::put('/employees/{user}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{user}', [AdminController::class, 'deleteEmployee'])->name('employees.delete');
    Route::patch('/employees/{user}/toggle', [AdminController::class, 'toggleEmployee'])->name('employees.toggle');

    // Audit Log
    Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('audit.index');

    // Refund Workflow
    Route::get('/refunds', [AdminController::class, 'refundQueue'])->name('refunds.index');
    Route::post('/refunds/{payment}/approve', [AdminController::class, 'approveRefund'])->name('refunds.approve');
    Route::post('/refunds/{payment}/process', [AdminController::class, 'processRefund'])->name('refunds.process');
    Route::post('/refunds/{payment}/request', [AdminController::class, 'requestRefund'])->name('refunds.request');
    Route::post('/payments/{payment}/cancel', [AdminController::class, 'cancelPayment'])->name('payments.cancel');

    // Pending Review Queue
    Route::get('/review/pending', [AdminController::class, 'pendingQueue'])->name('review.pending');
    Route::post('/review/{payment}/mark-paid', [AdminController::class, 'markPaid'])->name('review.mark-paid');

    // Financial Settings Panel
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

    // Discounts / Scholarships
    Route::post('/settings/discounts', [App\Http\Controllers\SettingsController::class, 'storeDiscount'])->name('discounts.store');
    Route::put('/settings/discounts/{discount}', [App\Http\Controllers\SettingsController::class, 'updateDiscount'])->name('discounts.update');
    Route::delete('/settings/discounts/{discount}', [App\Http\Controllers\SettingsController::class, 'deleteDiscount'])->name('discounts.delete');

    // Employee Permissions
    Route::post('/employees/{user}/permissions', [App\Http\Controllers\SettingsController::class, 'updatePermissions'])->name('employees.permissions');

    // Export
    Route::get('/export/payments', [App\Http\Controllers\ExportController::class, 'payments'])->name('export.payments');
    Route::get('/export/daily-settlement', [App\Http\Controllers\ExportController::class, 'dailySettlement'])->name('export.daily');

    // ─── University Structure ───────────────────────────────────────────
    Route::get('/faculties', [App\Http\Controllers\FacultyController::class, 'index'])->name('faculties.index');
    Route::post('/faculties', [App\Http\Controllers\FacultyController::class, 'store'])->name('faculties.store');
    Route::put('/faculties/{faculty}', [App\Http\Controllers\FacultyController::class, 'update'])->name('faculties.update');
    Route::patch('/faculties/{faculty}/toggle', [App\Http\Controllers\FacultyController::class, 'toggle'])->name('faculties.toggle');

    // Departments (nested under faculty)
    Route::post('/faculties/{faculty}/departments', [App\Http\Controllers\FacultyController::class, 'storeDepartment'])->name('faculties.departments.store');
    Route::put('/faculties/{faculty}/departments/{department}', [App\Http\Controllers\FacultyController::class, 'updateDepartment'])->name('faculties.departments.update');
    Route::patch('/faculties/{faculty}/departments/{department}/toggle', [App\Http\Controllers\FacultyController::class, 'toggleDepartment'])->name('faculties.departments.toggle');

    // ─── Tuition Configuration ─────────────────────────────────────────
    Route::get('/tuition', [App\Http\Controllers\TuitionConfigController::class, 'index'])->name('tuition.index');
    Route::post('/tuition', [App\Http\Controllers\TuitionConfigController::class, 'store'])->name('tuition.store');
    Route::put('/tuition/{tuitionConfig}', [App\Http\Controllers\TuitionConfigController::class, 'update'])->name('tuition.update');
    Route::delete('/tuition/{tuitionConfig}', [App\Http\Controllers\TuitionConfigController::class, 'destroy'])->name('tuition.destroy');

    // ─── Admin Assignments (Faculty Scope) ─────────────────────────────
    Route::post('/employees/{user}/assignment', [App\Http\Controllers\AdminAssignmentController::class, 'update'])->name('employees.assignment');
    Route::delete('/employees/{user}/assignment', [App\Http\Controllers\AdminAssignmentController::class, 'clear'])->name('employees.assignment.clear');

    // ─── Statistics Dashboard ──────────────────────────────────────────
    Route::get('/statistics', [App\Http\Controllers\StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/faculty/{faculty}', [App\Http\Controllers\StatisticsController::class, 'byFaculty'])->name('statistics.faculty');
});
