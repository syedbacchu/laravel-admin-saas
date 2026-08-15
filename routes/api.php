<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Post\BlogCommentController;
use App\Http\Controllers\Api\Post\BlogController;
use App\Http\Controllers\Api\Tenant\AccessController as TenantAccessController;
use App\Http\Controllers\Api\Tenant\AuthController as TenantAuthController;
use App\Http\Controllers\Api\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Api\Tenant\FileController as TenantFileController;
use App\Http\Controllers\Api\Tenant\PayrollAdvanceSalaryController as TenantPayrollAdvanceSalaryController;
use App\Http\Controllers\Api\Tenant\ProfileController as TenantProfileController;
use App\Http\Controllers\Api\Tenant\SettingController as TenantSettingController;
use App\Http\Controllers\Api\Tenant\StaffController as TenantStaffController;
use App\Http\Controllers\Api\Tenant\StaffFeatureController;
use App\Http\Controllers\Api\Tenant\SubscriptionController as TenantSubscriptionController;
use App\Http\Controllers\Api\PricingPlanController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\Testimonials\TestimonialsController;
use App\Http\Controllers\Api\Subscriber\SubscriberController;
use App\Http\Controllers\Api\Contact\ContactController;
use App\Http\Controllers\Api\Slider\SliderController;
use App\Http\Controllers\Api\DynamicPageController;

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

    // Public Dynamic Page API
    Route::group(['prefix' => 'dynamic-page', 'as' => 'apiDynamicPage.'], function () {
        Route::get('/', [DynamicPageController::class, 'index'])->name('list');
        Route::get('{slug}', [DynamicPageController::class, 'show'])->name('show');
    });

    Route::group(['prefix' => 'blogs', 'as' => 'apiBlog.'], function () {
        Route::get('/', [BlogController::class, 'index'])->name('list');
        Route::get('summary', [BlogController::class, 'summary'])->name('summary');
        Route::get('{identifier}/comments', [BlogCommentController::class, 'index'])->name('comments');
        Route::post('{identifier}/comments', [BlogCommentController::class, 'store'])->name('commentStore');
        Route::get('{identifier}', [BlogController::class, 'show'])->name('details');
    });

    Route::group(['prefix' => 'pricing-plan', 'as' => 'apiPricingPlan.'], function () {
        Route::get('/', [PricingPlanController::class, 'index'])->name('list');
        Route::get('{identifier}', [PricingPlanController::class, 'show'])->name('details');
    });

    Route::group(['prefix' => 'location', 'as' => 'apiLocation.'], function () {
        Route::get('divisions', [LocationController::class, 'divisions'])->name('divisions');
        Route::get('districts', [LocationController::class, 'districts'])->name('districts');
        Route::get('thanas', [LocationController::class, 'thanas'])->name('thanas');
    });

    Route::group(['prefix' => 'testimonials', 'as' => 'apiTestimonial.'], function () {
        Route::get('/', [TestimonialsController::class, 'index'])->name('list');
        Route::get('{identifier}', [TestimonialsController::class, 'show'])->name('details');
    });

    Route::group(['prefix' => 'contact', 'as' => 'apiContact.'], function () {
        Route::post('/', [ContactController::class, 'store'])->name('store');
    });

    Route::group(['prefix' => 'subscriber', 'as' => 'apiSubscriber.'], function () {
        Route::post('subscribe', [SubscriberController::class, 'subscribe'])->name('subscribe');
    });

    Route::group(['prefix' => 'sliders', 'as' => 'slider.'], function () {
        Route::get('/', [SliderController::class, 'index'])->name('list');
        Route::get('{identifier}', [SliderController::class, 'show'])->name('details');
    });

    Route::group(['middleware' => ['auth:api'], 'prefix' => 'user', 'as' => 'apiUser.'], function () {
        Route::get('profile', [ProfileController::class, 'profile'])->name('profile');
   });

    Route::group(['prefix' => 'tenant/{company_username}'], function () {
        Route::group(['prefix' => 'auth', 'middleware' => ['api.protection', 'tenant.context']], function () {
            Route::post('login', [TenantAuthController::class, 'login']);
            Route::post('forgot-password', [TenantAuthController::class, 'forgotPassword']);
            Route::post('reset-password', [TenantAuthController::class, 'resetPassword']);
        });

        Route::group(['middleware' => ['tenant.context'], 'prefix' => 'public'], function () {
            Route::get('settings', [TenantSettingController::class, 'index']);
        });

        Route::group(['middleware' => [ 'auth:api', 'tenant.context'], 'prefix' => 'account', 'as' => 'account.'], function () {
            Route::get('profile', [TenantProfileController::class, 'profile'])->name('profile');
            Route::post('update-profile', [TenantProfileController::class, 'updateProfile'])->name('updateProfile');
            Route::post('change-password', [TenantProfileController::class, 'changePassword'])->name('changePassword');
            Route::group(['middleware' => ['tenant.feature:staff.multi_user_access_2,staff.multi_user_access_3,staff.multi_user_access_5,staff.multi_user_access_10']], function() {
                Route::get('staff', [TenantStaffController::class, 'index'])->name('staff.list');
                Route::post('staff', [TenantStaffController::class, 'store'])->name('staff.store');
                Route::get('staff/{id}', [TenantStaffController::class, 'show'])->name('staff.show');
                Route::post('staff/{id}', [TenantStaffController::class, 'update'])->name('staff.update');
                Route::delete('staff/{id}', [TenantStaffController::class, 'destroy'])->name('staff.delete');
                Route::post('staff/{id}/reset-password', [TenantStaffController::class, 'resetPassword'])->name('staff.resetPassword');

                // Staff feature access routes (for owner managing staff)
                Route::get('staff/{staff_id}/features', [StaffFeatureController::class, 'index'])->name('staff.features.index');
                Route::post('staff/{staff_id}/features', [StaffFeatureController::class, 'update'])->name('staff.features.update');
            });

            // Staff accessing their own features (no staff management permission required)
            Route::get('my-features', [StaffFeatureController::class, 'getMyFeatures'])->name('staff.my-features');

            Route::get('subscription-details', [TenantSubscriptionController::class, 'details'])->name('subscriptionDetails');
            Route::get('settings', [TenantSettingController::class, 'index'])->name('settings.list');
            Route::post('settings', [TenantSettingController::class, 'store'])->name('settings.store');
            Route::delete('settings', [TenantSettingController::class, 'destroy'])->name('settings.delete');
            Route::get('dashboard', [TenantDashboardController::class, 'index'])
                ->middleware(['tenant.subscription.active'])
                ->name('dashboard');
            Route::get('dashboard/summary', [TenantDashboardController::class, 'summary'])
                ->middleware(['tenant.subscription.active'])
                ->name('dashboard.summary');
        });

        Route::group(['middleware' => [ 'auth:api', 'tenant.context', 'tenant.subscription.active', 'tenant.api.permission']], function () {
            // Feature check endpoint - requires specific feature
            Route::get('feature-check/{feature_key}', [TenantAccessController::class, 'featureCheck'])
                ->middleware('tenant.feature')
                ->name('featureCheck');

                Route::get('files', [TenantFileController::class, 'index'])->name('files.list');
            Route::post('files/upload', [TenantFileController::class, 'upload'])->name('files.upload');
            Route::post('files/{id}', [TenantFileController::class, 'update'])->name('files.update');
            Route::delete('files/{id}', [TenantFileController::class, 'destroy'])->name('files.delete');
            // Load feature-protected tenant routes from modular files
            foreach (glob(base_path('routes/tenant/*.php')) as $tenantRouteFile) {
                require $tenantRouteFile;
            }
            Route::delete('payroll-advance-salaries/{id}', [TenantPayrollAdvanceSalaryController::class, 'destroy'])->name('payrollAdvanceSalaries.delete');
        });
    });
});
