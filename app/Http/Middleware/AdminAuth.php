<?php

namespace App\Http\Middleware;

use App\Enums\StatusEnum;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminAuth
 *
 * Middleware to ensure that only authenticated and active admin users
 * can access the admin panel. Handles various user status conditions
 * such as pending, suspended, or temporarily deactivated.
 */
class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $this->logoutAndRedirect(
                $request,
                __('Please login to access the admin panel.')
            );
        }

        if ($user->role_module === UserRole::SUPER_ADMIN_ROLE) {
            return $next($request);
        }

        $statusMessages = [
            StatusEnum::PENDING->value => __('Your account is not active yet. Please contact your administrator.'),
            StatusEnum::SUSPENDED->value => __('Your account has been suspended. Please contact your administrator.'),
            StatusEnum::TEMPORARY_DEACTIVE->value => __('Your account has been temporarily deactivated. Please contact your administrator.'),
        ];


        // ⚠️ If user status matches any restricted state, logout and redirect
        if (isset($statusMessages[$user->status])) {
            return $this->logoutAndRedirect($request, $statusMessages[$user->status]);
        }

        // ✅ Proceed if everything is fine
        return $next($request);
    }

    /**
     * Logout the user, invalidate session, and redirect to login with a message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function logoutAndRedirect(Request $request, string $message): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('dismiss', $message);
    }
}
