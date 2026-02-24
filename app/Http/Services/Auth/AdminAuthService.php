<?php

namespace App\Http\Services\Auth;

use App\Enums\StatusEnum;
use App\Enums\UserRole;
use App\Enums\VerificationCodeTypeEnum;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Services\Mail\MailerInterface;
use App\Http\Services\SMS\SMSManager;
use App\Http\Services\SMS\SMSService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    protected AdminActivityLogger $activityLogger;

    public function __construct(AdminActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    public function authenticate(Request $request): bool
    {
        $loginInput = $request->input('login'); // can be username, email, or phone
        $password = $request->input('password');
        $remember = $request->boolean('remember');
        $key = Str::lower($loginInput) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 4)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'login' => [__("Too many login attempts. Try again in ") . ceil($seconds / 60) . __(" minutes.")],
            ]);
        }
        $query = User::query();
        $query->where(function ($q) use ($loginInput) {
            $q->where('email', $loginInput)
                ->orWhere('username', $loginInput)
                ->orWhere('phone', $loginInput);
        });
        if ($request->auth_type == 'admin') {
            $query->whereIn('role_module', [UserRole::SUPER_ADMIN_ROLE, UserRole::ADMIN_ROLE]);
        } else {
            $query->whereIn('role_module', [UserRole::USER_ROLE]);
        }
        $user = $query->first();

        if (!$user || !Hash::check($password, $user->password)) {
            RateLimiter::hit($key, 60 * 20);

            $this->activityLogger->logFailedLogin($loginInput, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reason' => 'invalid_credentials',
            ]);

            throw ValidationException::withMessages([
                'login' => ['Invalid credentials.'],
            ]);
        }

        RateLimiter::clear($key);
        if ($user->status != enum(StatusEnum::ACTIVE)) {
            throw ValidationException::withMessages([
                'login' => ['Account is not active. Please contact administrator.'],
            ]);
        }

//        if (!$user->email_verified_at) {
//            throw ValidationException::withMessages([
//                'login' => [__('Please verify your email address before logging in.')],
//            ]);
//        }

        Auth::login($user, $remember);

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // ✅ Log success
        $this->activityLogger->log($user, 'admin_login', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember' => $remember,
        ]);

        return true;
    }

    // send forgot password
    public function sendForgotPassword(ForgotPasswordRequest $request) {
        $isResend = isset($request->resend) ? $request->resend : false;
        $input = $request->email;

        $checkUser = User::where('email', $input)
            ->orWhere('phone', $input)
            ->orWhere('username', $input)
            ->first();
        if ($checkUser) {
            $type = match (true) {
                $checkUser && $checkUser->email === $input => enum(VerificationCodeTypeEnum::EMAIL),
                $checkUser && $checkUser->phone === $input => enum(VerificationCodeTypeEnum::PHONE),
                $checkUser && $checkUser->username === $input => enum(VerificationCodeTypeEnum::USERNAME),
                default => null,
            };
            $this->activityLogger->log($checkUser, 'sendForgotPassword', [
                'ip' => $request->ip(),
            ]);
            $request->merge(['user_id' => $checkUser->id,'type' => $type]);
            $createOtp = UserVerifyCodeService::createUserOtpCode($request,$isResend);
            if ($createOtp['success']) {
                $otpData = $createOtp['data'];
                if ($type === enum(VerificationCodeTypeEnum::PHONE)) {
                    $sms = new SMSService(new SMSManager());
                    $sms->sendOtp($input, "Your OTP is ".$otpData->code);
                } else if($type === enum(VerificationCodeTypeEnum::EMAIL)) {
                    $mailer = app(MailerInterface::class);
                    $mailer->send(
                        'emails.forgot_password',
                        ['otp' => $otpData->code],
                        $input,
                        $checkUser->name ?? '',
                        'Your OTP Code'
                    );
                }
            } else {
                return $createOtp;
            }
        }

        return sendResponse(true, __('OTP sent successfully, If your information is correct'));
    }

    public function resetPassword(ResetPasswordRequest $request) {
        $input = $request->password_token;

        $checkUser = User::where('email', $input)
            ->orWhere('phone', $input)
            ->orWhere('username', $input)
            ->first();
        if ($checkUser) {
            $type = match (true) {
                $checkUser && $checkUser->email === $input => enum(VerificationCodeTypeEnum::EMAIL),
                $checkUser && $checkUser->phone === $input => enum(VerificationCodeTypeEnum::PHONE),
                $checkUser && $checkUser->username === $input => enum(VerificationCodeTypeEnum::USERNAME),
                default => null,
            };
            $codeVerify = UserVerifyCodeService::otpCodeVerification($checkUser->id,$request->otp,$type);
            if ($codeVerify['success']) {
                $this->activityLogger->log($checkUser, 'resetPassword', [
                    'ip' => $request->ip(),
                ]);
                $checkUser->update(['password' => Hash::make($request->password)]);
            } else {
                return $codeVerify;
            }
        } else {
            return sendResponse(false, __('Invalid user or otp.'));
        }
        return sendResponse(true, __('Password reset successfully.'));
    }

    public function logout(Request $request): void
    {
        $user = Auth::user();

        if ($user) {
            $sessionDuration = $user->last_login_at
                ? now()->diffInMinutes($user->last_login_at)
                : 0;

            $this->activityLogger->log($user, 'admin_logout', [
                'ip' => $request->ip(),
                'session_duration_minutes' => $sessionDuration,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public static function createUserAccessToken(User $user, string $username): string {
        return $user->createToken($username)->accessToken;
    }
}
