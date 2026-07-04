<?php

use App\Http\Controllers\Api\Tenant\PayrollAttendanceController;
use App\Http\Controllers\Api\Tenant\PayrollBonusController;
use App\Http\Controllers\Api\Tenant\PayrollAdvanceSalaryController;
use App\Http\Controllers\Api\Tenant\PayrollLoanController;
use App\Http\Controllers\Api\Tenant\PayrollGenerateSalaryController;
use App\Http\Controllers\Api\Tenant\PayrollSalaryPaymentController;
use Illuminate\Support\Facades\Route;

/**
 * HR & Payroll Management Routes
 * Protected by: hr and payroll features
 */

Route::group(['middleware' => ['tenant.feature:hr.attendance_leave']], function() {
    // Attendance management
    Route::get('payroll-attendances', [PayrollAttendanceController::class, 'index'])->name('payrollAttendances.list');
    Route::post('payroll-attendances', [PayrollAttendanceController::class, 'store'])->name('payrollAttendances.store');
    Route::get('payroll-attendances/{id}', [PayrollAttendanceController::class, 'show'])->name('payrollAttendances.show');
    Route::post('payroll-attendances/{id}', [PayrollAttendanceController::class, 'update'])->name('payrollAttendances.update');
    Route::delete('payroll-attendances/{id}', [PayrollAttendanceController::class, 'destroy'])->name('payrollAttendances.delete');
});

Route::group(['middleware' => ['tenant.feature:payroll.salary_commission']], function() {
    // Salary generation and management
    Route::get('generate-salaries', [PayrollGenerateSalaryController::class, 'index'])->name('generateSalaries.list');
    Route::post('generate-salaries', [PayrollGenerateSalaryController::class, 'store'])->name('generateSalaries.store');
    Route::get('generate-salaries/{id}', [PayrollGenerateSalaryController::class, 'show'])->name('generateSalaries.show');
    Route::post('generate-salaries/{id}', [PayrollGenerateSalaryController::class, 'update'])->name('generateSalaries.update');
    Route::delete('generate-salaries/{id}', [PayrollGenerateSalaryController::class, 'destroy'])->name('generateSalaries.delete');
    Route::get('generate-salaries/{id}/salary-sheet', [PayrollGenerateSalaryController::class, 'salarySheet'])->name('generateSalaries.salarySheet');
    Route::get('generate-salaries/{id}/export/pdf', [PayrollGenerateSalaryController::class, 'exportPdf'])->name('generateSalaries.exportPdf');
    Route::get('generate-salaries/{id}/export/excel', [PayrollGenerateSalaryController::class, 'exportExcel'])->name('generateSalaries.exportExcel');

    // Salary payments
    Route::get('salary-payments/{salary_sheet_id}/payable', [PayrollSalaryPaymentController::class, 'getPayableAmount'])->name('salaryPayments.getPayableAmount');
    Route::post('salary-payments/process', [PayrollSalaryPaymentController::class, 'processPayment'])->name('salaryPayments.processPayment');
    Route::get('salary-payments/{salary_sheet_id}/history', [PayrollSalaryPaymentController::class, 'getPaymentHistory'])->name('salaryPayments.getPaymentHistory');
    Route::get('salary-payments/employee/{employee_id}/history', [PayrollSalaryPaymentController::class, 'getEmployeePaymentHistory'])->name('salaryPayments.getEmployeePaymentHistory');

    // Payroll generated salaries
    Route::get('payroll-generated-salaries', [PayrollGenerateSalaryController::class, 'index'])->name('payrollGeneratedSalaries.list');
    Route::post('payroll-generated-salaries', [PayrollGenerateSalaryController::class, 'store'])->name('payrollGeneratedSalaries.store');
    Route::get('payroll-generated-salaries/{id}', [PayrollGenerateSalaryController::class, 'show'])->name('payrollGeneratedSalaries.show');
    Route::post('payroll-generated-salaries/{id}', [PayrollGenerateSalaryController::class, 'update'])->name('payrollGeneratedSalaries.update');
    Route::delete('payroll-generated-salaries/{id}', [PayrollGenerateSalaryController::class, 'destroy'])->name('payrollGeneratedSalaries.delete');
    Route::get('payroll-generated-salaries/{id}/salary-sheet', [PayrollGenerateSalaryController::class, 'salarySheet'])->name('payrollGeneratedSalaries.salarySheet');
});

Route::group(['middleware' => ['tenant.feature:payroll.bonus_management']], function() {
    // Bonus management
    Route::get('payroll-bonuses', [PayrollBonusController::class, 'index'])->name('payrollBonuses.list');
    Route::post('payroll-bonuses', [PayrollBonusController::class, 'store'])->name('payrollBonuses.store');
    Route::get('payroll-bonuses/{id}', [PayrollBonusController::class, 'show'])->name('payrollBonuses.show');
    Route::post('payroll-bonuses/{id}', [PayrollBonusController::class, 'update'])->name('payrollBonuses.update');
    Route::delete('payroll-bonuses/{id}', [PayrollBonusController::class, 'destroy'])->name('payrollBonuses.delete');
});

Route::group(['middleware' => ['tenant.feature:payroll.advance_salary']], function() {
    // Advance salary management
    Route::get('payroll-advance-salaries', [PayrollAdvanceSalaryController::class, 'index'])->name('payrollAdvanceSalaries.list');
    Route::post('payroll-advance-salaries', [PayrollAdvanceSalaryController::class, 'store'])->name('payrollAdvanceSalaries.store');
    Route::get('payroll-advance-salaries/{id}', [PayrollAdvanceSalaryController::class, 'show'])->name('payrollAdvanceSalaries.show');
    Route::post('payroll-advance-salaries/{id}', [PayrollAdvanceSalaryController::class, 'update'])->name('payrollAdvanceSalaries.update');
    Route::delete('payroll-advance-salaries/{id}', [PayrollAdvanceSalaryController::class, 'destroy'])->name('payrollAdvanceSalaries.delete');
});

Route::group(['middleware' => ['tenant.feature:payroll.loan_management']], function() {
    // Employee loan management
    Route::get('payroll-loans', [PayrollLoanController::class, 'index'])->name('payrollLoans.list');
    Route::post('payroll-loans', [PayrollLoanController::class, 'store'])->name('payrollLoans.store');
    Route::get('payroll-loans/{id}', [PayrollLoanController::class, 'show'])->name('payrollLoans.show');
    Route::post('payroll-loans/{id}', [PayrollLoanController::class, 'update'])->name('payrollLoans.update');
    Route::delete('payroll-loans/{id}', [PayrollLoanController::class, 'destroy'])->name('payrollLoans.delete');
    Route::get('payroll-loans/{id}/payment-history', [PayrollLoanController::class, 'paymentHistory'])->name('payrollLoans.paymentHistory');
    Route::get('payroll-loans/employee-history', [PayrollLoanController::class, 'employeeLoanHistory'])->name('payrollLoans.employeeHistory');
});
