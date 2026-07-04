<?php

use App\Http\Controllers\Api\Tenant\CustomerController;
use Illuminate\Support\Facades\Route;

/**
 * Customer Management Routes
 * Protected by: customer.management feature
 */

Route::group(['middleware' => ['tenant.feature:customer.management']], function() {
    // Customer CRUD
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.list');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{id}', [CustomerController::class, 'destroy'])->name('customers.delete');
});

Route::group(['middleware' => ['tenant.feature:customer.address_tracking']], function() {
    // Customer address management
    Route::post('customers/{customerId}/addresses', [CustomerController::class, 'addAddress'])->name('customers.addresses.add');
});

