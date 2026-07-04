<?php

use App\Http\Controllers\Api\Tenant\EmployeeController;
use Illuminate\Support\Facades\Route;

/**
 * Employee Management Routes
 * Protected by: employee.management features
 */

Route::group(['middleware' => ['tenant.feature:employee.management']], function() {
    // General employee management
    Route::get('all-employees', [EmployeeController::class, 'allEmployees'])->name('employees.allList');
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.list');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::post('employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.delete');
});
