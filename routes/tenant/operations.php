<?php

use App\Http\Controllers\Api\Tenant\OfficeController;
use Illuminate\Support\Facades\Route;

/**
 * Operations & Financial Management Routes
 * Protected by: operational and financial features
 */

Route::group(['middleware' => ['tenant.feature:office.management']], function() {
    // Office management
    Route::get('offices', [OfficeController::class, 'index'])->name('offices.list');
    Route::post('offices', [OfficeController::class, 'store'])->name('offices.store');
    Route::get('offices/{id}', [OfficeController::class, 'show'])->name('offices.show');
    Route::post('offices/{id}', [OfficeController::class, 'update'])->name('offices.update');
    Route::delete('offices/{id}', [OfficeController::class, 'destroy'])->name('offices.delete');
});
