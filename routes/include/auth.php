<?php
use App\Http\Controllers\Auth\AuthController;



Route::post('login', [AuthController::class, 'login'])->name('loginProcess');
Route::group([ 'as' => 'auth.'], function () {
    Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot.password');
    Route::post('forgot-password', [AuthController::class, 'forgotPasswordProcess'])->name('forgot.password.process');
    Route::get('reset-password', [AuthController::class, 'resetPassword'])->name('forgot.password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPasswordProcess'])->name('forgot.password.resetProcess');
});

