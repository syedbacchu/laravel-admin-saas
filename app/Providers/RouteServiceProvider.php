<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware(['api', 'api.protection'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware(['web', 'admin.auth', 'permission'])
                ->prefix('admin')
                ->group(base_path('routes/include/admin.php'));

            Route::middleware(['web', 'guest', 'skip.permission', 'no.permission.sync'])
                ->group(base_path('routes/include/auth.php'));
        });
    }

    /**
     * Configure the rate limiters.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Standard API rate limiter - 120 requests per minute per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Heavy operations (list pages, exports, reports) - Higher limit
        RateLimiter::for('api-heavy', function (Request $request) {
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });

        // Light operations (single record fetches) - Standard limit
        RateLimiter::for('api-light', function (Request $request) {
            return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
        });

        // Development environment - Very high limit for debugging
        if (app()->environment('local', 'staging')) {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(600)->by($request->user()?->id ?: $request->ip());
            });
        }
    }
}
