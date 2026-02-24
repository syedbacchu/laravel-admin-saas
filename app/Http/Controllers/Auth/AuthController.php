<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Services\Auth\AdminAuthService;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected AdminAuthService $authService;

    public function __construct(AdminAuthService $authService)
    {
        $this->middleware('guest')->except('logout');
        $this->authService = $authService;
    }

    /**
     * Display the admin login form.
     */
    public function showLogin(): View
    {
        $data['pageTitle'] = __('Admin Login');
        if (Auth::user()) {
            return redirect()->route('dashboard');
        }
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('auth', 'login'));
    }

    /**
     * Handle admin login attempt.
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        try {
            try {
                $request->ensureIsNotRateLimited();
                $request->merge(['auth_type' => 'admin']);
                $this->authService->authenticate($request);
                $request->session()->regenerate();
                $user = Auth::user();

                return ResponseService::send([
                    'response' => [
                        'success' => true,
                        'message' => "Welcome back, {$user->name}!",
                        'data' => $user,
                        'status' => 200,
                        'error_message' => "",
                    ],
                ], successRoute: 'dashboard');

            } catch (\Illuminate\Validation\ValidationException $e) {
                return ResponseService::send([
                    'response' => [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'data' => [],
                        'status' => 422,
                        'error_message' => $e->getMessage(),
                    ],
                ]);

            }

        } catch (\Exception $e) {
            logStore('login', $e->getMessage());
            return ResponseService::send([
                'response' => [
                    'success' => false,
                    'message' => __('Something went wrong, please try again later.'),
                    'data' => [],
                    'status' => 500,
                    'error_message' => $e->getMessage(),
                ],
            ]);
        }
    }

    public function forgotPassword()
    {
        $data['pageTitle'] = __('Forgot Password');
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('auth', 'forgot'));
    }

    public function forgotPasswordProcess(ForgotPasswordRequest $request)
    {
        $identifier = $request->email;
        $key = 'forgot-password:' . $identifier . ':' . $request->ip();

        // Check limit
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return ResponseService::send([
                'success' => false,
                'message' => 'Sorry Too many attempts. Please try again after 20 minutes.',
                'status' => 429
            ]);
        }

        // Increase attempt count (expire in 20 min)
        RateLimiter::hit($key, 1200);

        $response = $this->authService->sendForgotPassword($request);
        $response['data']['ptoken'] = encrypt($request->email);

        return ResponseService::send([
            'response' => $response,
        ],null, null,
            ['ptoken' => $response['data']['ptoken']], successRoute: 'auth.forgot.password.reset');
    }

    public function resetPassword(Request $request) {
        if ($request->ptoken) {
            $data['auth_token'] = $request->ptoken;
            $data['pageTitle'] = __('Reset Password');
            return ResponseService::send([
                'data' => $data,
            ], view: viewss('auth', 'reset'));
        } else {
            return redirect()->route('login')->with('dismiss', __('Invalid token! Please try again.'));
        }
    }

    public function resetPasswordProcess(ResetPasswordRequest $request)
    {
        $request->merge(['password_token' => decrypt($request->password_token)]);
        $response = $this->authService->resetPassword($request);

        return ResponseService::send([
            'response' => $response,
        ],successRoute: 'login');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect()->route('login')
            ->with('success', __('You have been logged out successfully.'));
    }

    public function test(Request $request): JsonResponse
    {
        $response = sendResponse(true,__('Api connection success'));
        return ResponseService::send($response);
    }

    public function apiLogin(AdminLoginRequest $request): JsonResponse
    {
        try {
            $request->ensureIsNotRateLimited();
            $request->merge(['auth_type' => 'user']);
            $this->authService->authenticate($request);
            $data['user'] = Auth::user();
            $data['access_token'] = AdminAuthService::createUserAccessToken($data['user'], $data['user']->username);
            return ResponseService::send([
                'response' => [
                    'success' => true,
                    'message' => "Welcome back, {$data['user']->name}!",
                    'data' => $data,
                    'status' => 200,
                    'error_message' => "",
                ]]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseService::send([
                'response' => [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => [],
                    'status' => 422,
                    'error_message' => $e->getMessage(),
                ],
            ]);

        }
    }
}
