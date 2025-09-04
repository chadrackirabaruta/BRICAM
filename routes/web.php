<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    EmployeeController,
    ProductionController,
    ProductionReportController,
    TransportRecordController,
    StockTypeController,
    CategoryManagementController,
    CustomerController,
    SalesController,
    DashboardController,
    SalaryController,
    UserController,
    ProfileController
};

// ================= PUBLIC ROUTES =================
Route::view('/', 'welcome');
require __DIR__ . '/auth.php';

// ================= AUTHENTICATED ROUTES =================
Route::middleware(['auth', 'verified'])->group(function () {

    // ---------------- DASHBOARD ----------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Chart data routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart.data');
        Route::get('/chart-data/transport', [DashboardController::class, 'transportChartData'])->name('dashboard.transport.chart');
        Route::get('/chart-data/sales', [DashboardController::class, 'salesChartData'])->name('dashboard.sales.chart');
        Route::get('/transport-data', [DashboardController::class, 'getTransportChartData'])->name('dashboard.transport.data');
        Route::get('/sales-data', [DashboardController::class, 'getSalesChartData'])->name('dashboard.sales.data');
    });

    // ---------------- PROFILE ----------------
    Route::view('/profile', 'profile')->name('profile');
    Route::prefix('user')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
    Route::resource('users', UserController::class);

    // ---------------- LOGOUT ----------------
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');

    // ---------------- EMPLOYEES ----------------
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    // ---------------- PRODUCTIONS ----------------
     Route::resource('productions', ProductionController::class)->except(['show']);
    Route::get('/productions/summary', [ProductionController::class, 'summary'])->name('productions.summary');
    Route::get('/productions/report', [ProductionReportController::class, 'index'])->name('productions.report');

    // ---------------- TRANSPORT RECORDS ----------------
    Route::resource('transport-records', TransportRecordController::class);
    Route::get('/transport/create', [TransportRecordController::class, 'create'])->name('transport-records.create');
    Route::post('/transport/store-bulk', [TransportRecordController::class, 'storeBulk'])->name('transport-records.store.bulk');
    Route::get('/transport/available-productions', [TransportRecordController::class, 'availableProductions']);
    Route::get('/transport-records/employee/{employeeId}', [TransportRecordController::class, 'show'])->name('transport-records.show');
    Route::get('/transport/show/{employee}', [TransportRecordController::class, 'show'])->name('transport.show');
    Route::get('/transport-records/summary', [TransportRecordController::class, 'summary'])->name('transport-records.summary');

    // ---------------- TRANSPORT CATEGORIES ----------------
    Route::resource('transport-categories', TransportRecordController::class);

    // ---------------- STOCK TYPES ----------------
    Route::resource('stock_types', StockTypeController::class)->except(['show', 'destroy']);

    // ---------------- STOCK REPORTS ----------------
    Route::prefix('reports')->group(function () {
        Route::get('/stock-summary', [TransportRecordController::class, 'stockSummary'])->name('reports.stock-summary');
        Route::get('/stock-summary/export', [TransportRecordController::class, 'exportStockSummaryCsv'])->name('reports.stock-summary.export');
        Route::get('/sales', [SalesController::class, 'report'])->name('reports.sales');
    });

    // ---------------- CATEGORY MANAGEMENT ----------------
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryManagementController::class, 'index'])->name('categories.index');

        // Transport Categories
        Route::post('/transport-categories', [CategoryManagementController::class, 'transportCategoriesStore'])->name('transport-categories.store');
        Route::put('/transport-categories/{transportCategory}', [CategoryManagementController::class, 'transportCategoriesUpdate'])->name('transport-categories.update');
        Route::delete('/transport-categories/{transportCategory}', [CategoryManagementController::class, 'transportCategoriesDestroy'])->name('transport-categories.destroy');
    });

    // Employee Type
    Route::post('/employee-types', [CategoryManagementController::class, 'storeEmployeeType'])->name('employee-types.store');
    Route::put('/employee-types/{employeeType}', [CategoryManagementController::class, 'updateEmployeeType'])->name('employee-types.update');
    Route::delete('/employee-types/{employeeType}', [CategoryManagementController::class, 'destroyEmployeeType'])->name('employee-types.destroy');

    // Salary Type
    Route::post('/salary-types', [CategoryManagementController::class, 'storeSalaryType'])->name('salary-types.store');
    Route::put('/salary-types/{salaryType}', [CategoryManagementController::class, 'updateSalaryType'])->name('salary-types.update');
    Route::delete('/salary-types/{salaryType}', [CategoryManagementController::class, 'destroySalaryType'])->name('salary-types.destroy');

    // ---------------- CUSTOMERS ----------------
    Route::resource('customers', CustomerController::class);

    // ---------------- SALES ----------------
    Route::resource('sales', SalesController::class);
    Route::get('sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');

    // Payments
    Route::post('sales/{sale}/payments', [SalesController::class, 'addPayment'])->name('sales.payments.store');

    // Email receipts
    Route::get('sales/{sale}/email', [SalesController::class, 'showEmailForm'])->name('sales.email.form');
    Route::post('sales/{sale}/email', [SalesController::class, 'sendReceiptEmail'])->name('sales.email.send');
    Route::post('sales/{sale}/quick-email', [SalesController::class, 'quickSendEmail'])->name('sales.email.quick');
    Route::post('sales/{sale}/email/send', [SalesController::class, 'emailReceipt'])->name('sales.email');

    // PDF receipts
    Route::get('sales/{sale}/download-pdf', [SalesController::class, 'downloadPdf'])->name('sales.download.pdf');
    Route::get('sales/{id}/pdf', [SalesController::class, 'pdf'])->name('sales.pdf');

    // Sales Reports
    Route::prefix('sales')->group(function () {
        Route::get('report', [SalesController::class, 'report'])->name('sales.report');
        Route::get('report/pdf', [SalesController::class, 'reportPdf'])->name('sales.report.pdf');
        Route::get('report/csv', [SalesController::class, 'exportCsv'])->name('sales.report.csv');
    });

    // ---------------- SALARY ----------------
    Route::get('/payroll/salary', [SalaryController::class, 'Salaries'])->name('salary.All');
    Route::get('/salaries/export-csv', [SalaryController::class, 'exportCsv'])->name('salaries.export.csv');

    // ---------------- WAGES ----------------
    Route::prefix('payroll')->group(function () {
        Route::get('/wages', [SalaryController::class, 'wagesIndex'])->name('payroll.wages.index');
        Route::post('/wages', [SalaryController::class, 'storeWage'])->name('payroll.wages.store');
        Route::get('/wages/{wage}/edit', [SalaryController::class, 'editWage'])->name('payroll.wages.edit');
        Route::put('/wages/{wage}', [SalaryController::class, 'updateWage'])->name('payroll.wages.update');
        Route::delete('/wages/{wage}', [SalaryController::class, 'destroyWage'])->name('payroll.wages.destroy');
        Route::get('/wages/export', [SalaryController::class, 'exportWages'])->name('payroll.wages.export');
        Route::get('/wages/{wage}', [SalaryController::class, 'showWage'])->name('payroll.wages.show');
        Route::post('/wages/bulk-delete', [SalaryController::class, 'bulkDestroy'])->name('payroll.wages.bulkDestroy');
        Route::get('/wages/export-all', [SalaryController::class, 'export'])->name('wages.export');
    });

    // ---------------- PRODUCTION CARD ----------------
    Route::get('/production-card', [TransportRecordController::class, 'remainingProductionCard'])->name('production.card');
});
