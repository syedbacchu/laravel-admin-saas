<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\Post\BlogCommentController;
use App\Http\Controllers\Api\Post\BlogController;
use App\Http\Controllers\Api\Tenant\AccessController as TenantAccessController;
use App\Http\Controllers\Api\Tenant\AuthController as TenantAuthController;
use App\Http\Controllers\Api\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Api\Tenant\DriverController as TenantDriverController;
use App\Http\Controllers\Api\Tenant\ProfileController as TenantProfileController;
use App\Http\Controllers\Api\Tenant\SubscriptionController as TenantSubscriptionController;
use App\Http\Controllers\Api\Tenant\VehicleController as TenantVehicleController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\User\ProfileController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['api.protection']], function () {

    Route::group(['prefix' => 'test', 'as' => 'apiTest.'], function () {
        Route::get('connection', [TestController::class, 'index'])->name('test');
    });

    Route::group(['prefix' => 'blogs', 'as' => 'apiBlog.'], function () {
        Route::get('/', [BlogController::class, 'index'])->name('list');
        Route::get('{identifier}/comments', [BlogCommentController::class, 'index'])->name('comments');
        Route::post('{identifier}/comments', [BlogCommentController::class, 'store'])->name('commentStore');
        Route::get('{identifier}', [BlogController::class, 'show'])->name('details');
    });

    Route::group(['middleware' => ['auth:api'], 'prefix' => 'user', 'as' => 'apiUser.'], function () {
        Route::get('profile', [ProfileController::class, 'profile'])->name('profile');
    });

    Route::group(['prefix' => 'tenant/{company_username}', 'as' => 'tenantApi.'], function () {
        Route::group(['prefix' => 'auth', 'as' => 'auth.', 'middleware' => ['api.protection', 'tenant.context']], function () {
            Route::post('login', [TenantAuthController::class, 'login'])->name('login');
            Route::post('forgot-password', [TenantAuthController::class, 'forgotPassword'])->name('forgotPassword');
            Route::post('reset-password', [TenantAuthController::class, 'resetPassword'])->name('resetPassword');
        });

        Route::group(['middleware' => [ 'auth:api', 'tenant.context'], 'prefix' => 'account', 'as' => 'account.'], function () {
            Route::get('profile', [TenantProfileController::class, 'profile'])->name('profile');
            Route::post('update-profile', [TenantProfileController::class, 'updateProfile'])->name('updateProfile');
            Route::post('change-password', [TenantProfileController::class, 'changePassword'])->name('changePassword');
            Route::get('subscription-details', [TenantSubscriptionController::class, 'details'])->name('subscriptionDetails');
            Route::get('dashboard', [TenantDashboardController::class, 'index'])
                ->middleware(['tenant.subscription.active'])
                ->name('dashboard');
        });

        Route::group(['middleware' => [ 'auth:api', 'tenant.context', 'tenant.subscription.active']], function () {
            Route::get('feature-check/{feature_key}', [TenantAccessController::class, 'featureCheck'])
                ->middleware('tenant.feature')
                ->name('featureCheck');

            Route::get('vehicles', [TenantVehicleController::class, 'index'])->name('vehicles.list');
            Route::post('vehicles', [TenantVehicleController::class, 'store'])->name('vehicles.store');
            Route::get('vehicles/{id}', [TenantVehicleController::class, 'show'])->name('vehicles.show');
            Route::put('vehicles/{id}', [TenantVehicleController::class, 'update'])->name('vehicles.update');
            Route::delete('vehicles/{id}', [TenantVehicleController::class, 'destroy'])->name('vehicles.delete');

            Route::get('drivers', [TenantDriverController::class, 'index'])->name('drivers.list');
            Route::post('drivers', [TenantDriverController::class, 'store'])->name('drivers.store');
            Route::get('drivers/{id}', [TenantDriverController::class, 'show'])->name('drivers.show');
            Route::put('drivers/{id}', [TenantDriverController::class, 'update'])->name('drivers.update');
            Route::delete('drivers/{id}', [TenantDriverController::class, 'destroy'])->name('drivers.delete');
        });
    });
});
