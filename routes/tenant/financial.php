<?php

use App\Http\Controllers\Api\Tenant\DailyOfficeExpenseController;
use App\Http\Controllers\Api\Tenant\SalaryExpenseController;
use Illuminate\Support\Facades\Route;

/**
 * Financial Management Routes
 * Protected by: financial features
 */

Route::group(['middleware' => ['tenant.feature:finance.daily_office_expenses']], function() {
    // Daily office expenses
    Route::get('daily-office-expenses', [DailyOfficeExpenseController::class, 'index'])->name('dailyOfficeExpenses.list');
    Route::post('daily-office-expenses', [DailyOfficeExpenseController::class, 'store'])->name('dailyOfficeExpenses.store');
    Route::get('daily-office-expenses/{id}', [DailyOfficeExpenseController::class, 'show'])->name('dailyOfficeExpenses.show');
    Route::post('daily-office-expenses/{id}', [DailyOfficeExpenseController::class, 'update'])->name('dailyOfficeExpenses.update');
    Route::delete('daily-office-expenses/{id}', [DailyOfficeExpenseController::class, 'destroy'])->name('dailyOfficeExpenses.delete');
});

Route::group(['middleware' => ['tenant.feature:payroll.salary_commission']], function() {
    // Salary expenses
    Route::get('salary-expenses', [SalaryExpenseController::class, 'index'])->name('salaryExpenses.list');
    Route::post('salary-expenses', [SalaryExpenseController::class, 'store'])->name('salaryExpenses.store');
    Route::get('salary-expenses/calculate-payable', [SalaryExpenseController::class, 'calculatePayableAmount'])->name('salaryExpenses.calculatePayable');
    Route::get('salary-expenses/{id}', [SalaryExpenseController::class, 'show'])->name('salaryExpenses.show');
    Route::post('salary-expenses/{id}', [SalaryExpenseController::class, 'update'])->name('salaryExpenses.update');
    Route::delete('salary-expenses/{id}', [SalaryExpenseController::class, 'destroy'])->name('salaryExpenses.delete');
});

