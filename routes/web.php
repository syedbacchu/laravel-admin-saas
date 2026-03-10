<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;



Route::get('/', [AuthController::class, 'showLogin'])->name('login');

