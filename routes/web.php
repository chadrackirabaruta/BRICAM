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
    SalaryController

};
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// Public Routes
Route::view('/', 'welcome');
require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    // Main dashboard route (only defined once)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile route
    Route::view('/profile', 'profile')->name('profile');
    // Jetstream profile route is usually included:
Route::middleware(['auth'])->group(function () {
    Route::get('/user/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/user/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
     Route::delete('/user/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});


    
    // Chart data routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/chart-data/transport', [DashboardController::class, 'transportChartData'])
             ->name('dashboard.transport.chart');
             
        Route::get('/chart-data/sales', [DashboardController::class, 'salesChartData'])
             ->name('dashboard.sales.chart');
             
        Route::get('/chart-data', [DashboardController::class, 'chartData'])
             ->name('dashboard.chart.data');
    });



Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart.data');


    Route::get('/dashboard/transport-data', [DashboardController::class, 'getTransportChartData']);
Route::get('/dashboard/sales-data', [DashboardController::class, 'getSalesChartData']);
});


    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');

    // ================= EMPLOYEES =================
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    // ================= PRODUCTIONS =================
    Route::resource('productions', ProductionController::class)->except(['show']);
    Route::get('/productions/summary', [ProductionController::class, 'summary'])->name('productions.summary');
    Route::get('/productions/report', [ProductionReportController::class, 'index'])->name('productions.report');

    // ================= TRANSPORT RECORDS =================
    Route::resource('transport-records', TransportRecordController::class);
    Route::get('/transport/create', [TransportRecordController::class, 'create'])->name('transport-records.create');
    Route::post('/transport/store-bulk', [TransportRecordController::class, 'storeBulk'])->name('transport-records.store.bulk');
    Route::get('/transport/available-productions', [TransportRecordController::class, 'availableProductions']);
    Route::get('/transport-records/employee/{employeeId}', [TransportRecordController::class, 'show'])->name('transport-records.show');
    Route::get('/transport/show/{employee}', [TransportRecordController::class, 'show'])->name('transport.show');
    Route::get('/transport-records/summary', [TransportRecordController::class, 'summary'])->name('transport-records.summary');

    // ================= TRANSPORT CATEGORIES =================
    Route::resource('transport-categories', TransportRecordController::class);

    // ================= STOCK TYPES =================
    Route::resource('stock_types', StockTypeController::class)->except(['show', 'destroy']);

    // ================= STOCK REPORTS =================
    Route::get('/reports/stock-summary', [TransportRecordController::class, 'stockSummary'])->name('reports.stock-summary');
    Route::get('/reports/stock-summary/export', [TransportRecordController::class, 'exportStockSummaryCsv'])->name('reports.stock-summary.export');

    // ================= CATEGORY MANAGEMENT =================
    Route::get('/categories', [CategoryManagementController::class, 'index'])->name('categories.index');
    // routes/web.php
Route::prefix('categories')->group(function () {
    // Transport Categories
    Route::post('/transport-categories', [CategoryManagementController::class, 'transportCategoriesStore'])
        ->name('transport-categories.store');
    Route::put('/transport-categories/{transportCategory}', [CategoryManagementController::class, 'transportCategoriesUpdate'])
        ->name('transport-categories.update');
    Route::delete('/transport-categories/{transportCategory}', [CategoryManagementController::class, 'transportCategoriesDestroy'])
        ->name('transport-categories.destroy');
});

    // Employee Type
    Route::post('/employee-types', [CategoryManagementController::class, 'storeEmployeeType'])->name('employee-types.store');
    Route::put('/employee-types/{employeeType}', [CategoryManagementController::class, 'updateEmployeeType'])->name('employee-types.update');
    Route::delete('/employee-types/{employeeType}', [CategoryManagementController::class, 'destroyEmployeeType'])->name('employee-types.destroy');

    // Salary Type
    Route::post('/salary-types', [CategoryManagementController::class, 'storeSalaryType'])->name('salary-types.store');
    Route::put('/salary-types/{salaryType}', [CategoryManagementController::class, 'updateSalaryType'])->name('salary-types.update');
    Route::delete('/salary-types/{salaryType}', [CategoryManagementController::class, 'destroySalaryType'])->name('salary-types.destroy');

    // ================= CUSTOMERS =================
    Route::resource('customers', CustomerController::class);

    // ================= SALES =================
    Route::resource('sales', SalesController::class);
    Route::get('sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');

    // Payments
    Route::post('/sales/{sale}/payments', [SalesController::class, 'addPayment'])->name('sales.payments.store');

    // Email routes for sending receipts
Route::get('/sales/{sale}/email', [SalesController::class, 'showEmailForm'])->name('sales.email.form');
Route::post('/sales/{sale}/email', [SalesController::class, 'sendReceiptEmail'])->name('sales.email.send');

// Optional: One-click email (quick send)
Route::post('/sales/{sale}/quick-email', [SalesController::class, 'quickSendEmail'])->name('sales.email.quick');

// ✅ Define this *only if* you need a generic send with name 'sales.email'
Route::post('/sales/{sale}/email/send', [SalesController::class, 'emailReceipt'])->name('sales.email');

//Receipt pdf
Route::get('/sales/{sale}/download-pdf', [SalesController::class, 'downloadPdf'])->name('sales.pdf');
Route::get('/production-card', [TransportRecordController::class, 'remainingProductionCard'])->name('production.card');
//Sales Report


Route::prefix('sales')->group(function () {
    Route::get('report', [SalesController::class, 'report'])->name('sales.report');
    Route::get('report/pdf', [SalesController::class, 'reportPdf'])->name('sales.report.pdf');
    Route::get('report/csv', [SalesController::class, 'exportCsv'])->name('sales.report.csv');

    // web.php
Route::get('/sales/{id}/pdf', [SalesController::class, 'pdf'])->name('sales.pdf');

});

Route::get('/reports/sales', [SalesController::class, 'report'])->name('sales.report');
Route::get('/reports/sales', [SalesController::class, 'report'])->name('reports.sales');

//salary

Route::get('/payroll/salary', [SalaryController::class, 'Salaries'])->name('salary.All');
Route::get('/salaries/export-csv', [SalaryController::class, 'exportCsv'])->name('salaries.export.csv');


//wages

Route::prefix('payroll')->group(function () {
    // Wages listing
    Route::get('/wages', [SalaryController::class, 'wagesIndex'])->name('payroll.wages.index');

    // Create wage (form submission)
    Route::post('/wages', [SalaryController::class, 'storeWage'])->name('payroll.wages.store');

    // Edit wage form (optional: if needed via AJAX or separate page)
    Route::get('/wages/{wage}/edit', [SalaryController::class, 'editWage'])->name('payroll.wages.edit');

    // Update wage
    Route::put('/wages/{wage}', [SalaryController::class, 'updateWage'])->name('payroll.wages.update');

    // Delete wage
    Route::delete('/wages/{wage}', [SalaryController::class, 'destroyWage'])->name('payroll.wages.destroy');

    // Export wages (optional, if needed)
    Route::get('/wages/export', [SalaryController::class, 'exportWages'])->name('payroll.wages.export');

    // Optional: view single wage details (if used)
    Route::get('/wages/{wage}', [SalaryController::class, 'showWage'])->name('payroll.wages.show');
    Route::post('payroll/wages/bulk-delete', [SalaryController::class, 'bulkDestroy'])->name('payroll.wages.bulkDestroy');
       // Export route
    Route::get('wages/export', [App\Http\Controllers\SalaryController::class, 'export'])
        ->name('wages.export');

});



});
