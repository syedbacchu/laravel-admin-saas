<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use App\Support\SidebarMenu;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Http\Services\CustomField\CustomFieldServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);

        $resolveActorId = static function (): ?int {
            $id = Auth::guard('api')->id() ?? Auth::id();
            return $id ? (int) $id : null;
        };

        $hasColumn = static function (Model $model, string $column): bool {
            static $cache = [];

            $connection = $model->getConnectionName() ?: config('database.default');
            $key = $connection . '.' . $model->getTable() . '.' . $column;

            if (!array_key_exists($key, $cache)) {
                $cache[$key] = Schema::connection($connection)->hasColumn($model->getTable(), $column);
            }

            return $cache[$key];
        };

        Model::creating(function (Model $model) use ($resolveActorId, $hasColumn): void {
            $connection = $model->getConnectionName() ?: config('database.default');
            if ($connection !== 'tenant') {
                return;
            }

            $actorId = $resolveActorId();
            if (!$actorId) {
                return;
            }

            if ($hasColumn($model, 'added_by') && !$model->getAttribute('added_by')) {
                $model->setAttribute('added_by', $actorId);
            }

            if ($hasColumn($model, 'updated_by')) {
                $model->setAttribute('updated_by', $actorId);
            }
        });

        Model::updating(function (Model $model) use ($resolveActorId, $hasColumn): void {
            $connection = $model->getConnectionName() ?: config('database.default');
            if ($connection !== 'tenant') {
                return;
            }

            $actorId = $resolveActorId();
            if (!$actorId) {
                return;
            }

            if ($hasColumn($model, 'updated_by')) {
                $model->setAttribute('updated_by', $actorId);
            }
        });

        View::composer('*', function ($view) {
            $view->with('sidebarMenus', SidebarMenu::get());
        });

        Blade::directive('customFields', function ($expression = null) {
            return "<?php echo resolve(\\App\\Http\\Services\\CustomField\\CustomFieldServiceInterface::class)->render($expression); ?>";
        });

        Schema::defaultStringLength(191);
        RateLimiter::for('forgot-password', function ($request) {
            // The unique identifier (email / phone / username)
            $identifier = $request->input('email') ?? 'unknown';

            // Unique key for rate limit
            $key = 'forgot-password:' . $identifier . ':' . $request->ip();

            return [
                // 3 request per 1 minute, then block for 20 minutes (1200 sec)
                Limit::perMinute(3)->by($key)
                    ->response(function () {
                        return response()->json([
                            'success' => false,
                            'message' => 'Too many attempts. Please try again after 20 minutes.',
                            'status' => 429,
                            'error_message' => 'Rate limited',
                        ], 429);
                    })
                    ->decaySeconds(1200) // block for 20 minutes
            ];
        });
    }
}
