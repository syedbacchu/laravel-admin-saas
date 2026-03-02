<?php

namespace App\Http\Services\TenantApi;

use App\Enums\VerificationCodeTypeEnum;
use App\Http\Requests\TenantApi\TenantChangePasswordRequest;
use App\Http\Requests\TenantApi\TenantForgotPasswordRequest;
use App\Http\Requests\TenantApi\TenantLoginRequest;
use App\Http\Requests\TenantApi\TenantResetPasswordRequest;
use App\Http\Requests\TenantApi\TenantUpdateProfileRequest;
use App\Http\Services\Auth\UserVerifyCodeService;
use App\Http\Services\BaseService;
use App\Http\Services\Mail\MailerInterface;
use App\Http\Services\SMS\SMSManager;
use App\Http\Services\SMS\SMSService;
use App\Http\Services\Tenant\TenantFeatureResolverService;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class TenantApiService extends BaseService implements TenantApiServiceInterface
{
    protected TenantApiRepositoryInterface $tenantApiRepository;

    protected TenantFeatureResolverService $tenantFeatureResolverService;

    public function __construct(
        TenantApiRepositoryInterface $repository,
        TenantFeatureResolverService $tenantFeatureResolverService
    ) {
        parent::__construct($repository);
        $this->tenantApiRepository = $repository;
        $this->tenantFeatureResolverService = $tenantFeatureResolverService;
    }

    public function login(TenantLoginRequest $request, string $companyUsername): array
    {
        $tenant = $this->tenantApiRepository->findTenantByUsername($companyUsername);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found'), [], 404);
        }

        if ($tenant->status !== 'active') {
            return $this->sendResponse(false, __('Tenant account is not active'), [], 403);
        }

        $request->ensureIsNotRateLimited($companyUsername);
        $throttleKey = $request->throttleKey($companyUsername);

        $user = $this->tenantApiRepository->findTenantUserByLogin($tenant, (string) $request->login);
        if (!$user || !Hash::check((string) $request->password, (string) $user->password)) {
            RateLimiter::hit($throttleKey, 20 * 60);
            return $this->sendResponse(false, __('Invalid credentials'), [], 422);
        }

        if ((int) $user->status !== 1 || (int) $user->enable_login !== 1) {
            return $this->sendResponse(false, __('Account is disabled. Please contact administrator.'), [], 403);
        }

        RateLimiter::clear($throttleKey);

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('tenant_' . $tenant->company_username . '_' . $user->id)->accessToken;
        $activeSubscription = $this->tenantFeatureResolverService->getActiveSubscription((int) $tenant->id);
        $featureMap = $this->tenantFeatureResolverService->getFeatureMap((int) $tenant->id);

        return $this->sendResponse(true, __('Login successful'), [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'tenant' => [
                'id' => $tenant->id,
                'uuid' => $tenant->uuid,
                'company_name' => $tenant->company_name,
                'company_username' => $tenant->company_username,
                'status' => $tenant->status,
            ],
            'package' => [
                'is_active' => $activeSubscription !== null,
                'subscription' => $activeSubscription,
            ],
            'features' => $featureMap,
        ]);
    }

    public function forgotPassword(TenantForgotPasswordRequest $request, string $companyUsername): array
    {
        $tenant = $this->tenantApiRepository->findTenantByUsername($companyUsername);
        if (!$tenant) {
            return $this->sendResponse(true, __('If account exists, OTP sent successfully'));
        }

        $user = $this->tenantApiRepository->findTenantUserByLogin($tenant, (string) $request->login);
        if (!$user) {
            return $this->sendResponse(true, __('If account exists, OTP sent successfully'));
        }

        $type = $this->resolveVerificationType($user, (string) $request->login);
        $deliveryValue = (string) $request->login;

        if ($type === enum(VerificationCodeTypeEnum::USERNAME)) {
            if (!empty($user->email)) {
                $type = enum(VerificationCodeTypeEnum::EMAIL);
                $deliveryValue = (string) $user->email;
            } elseif (!empty($user->phone)) {
                $type = enum(VerificationCodeTypeEnum::PHONE);
                $deliveryValue = (string) $user->phone;
            } else {
                return $this->sendResponse(true, __('If account exists, OTP sent successfully'));
            }
        }

        $otpRequest = new Request([
            'user_id' => $user->id,
            'type' => $type,
            'validity_type' => 'minute',
            'validity' => 20,
        ]);

        $createOtp = UserVerifyCodeService::createUserOtpCode($otpRequest, (bool) $request->resend);
        if (($createOtp['success'] ?? false) !== true) {
            return $createOtp;
        }

        $otpCode = data_get($createOtp, 'data.code');
        if ($otpCode) {
            if ($type === enum(VerificationCodeTypeEnum::PHONE)) {
                $sms = new SMSService(new SMSManager());
                $sms->sendOtp($deliveryValue, "Your OTP is {$otpCode}");
            } elseif ($type === enum(VerificationCodeTypeEnum::EMAIL)) {
                $mailer = app(MailerInterface::class);
                $mailer->send(
                    'emails.forgot_password',
                    ['otp' => $otpCode],
                    $deliveryValue,
                    $user->name ?? '',
                    'Your OTP Code'
                );
            }
        }

        return $this->sendResponse(true, __('If account exists, OTP sent successfully'));
    }

    public function resetPassword(TenantResetPasswordRequest $request, string $companyUsername): array
    {
        $tenant = $this->tenantApiRepository->findTenantByUsername($companyUsername);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found'), [], 404);
        }

        $user = $this->tenantApiRepository->findTenantUserByLogin($tenant, (string) $request->login);
        if (!$user) {
            return $this->sendResponse(false, __('Invalid user or OTP'), [], 400);
        }

        $verified = $this->verifyResetOtp($user, (string) $request->login, (string) $request->otp);
        if (!$verified) {
            return $this->sendResponse(false, __('Invalid OTP code or expired.'), [], 400);
        }

        $user->update([
            'password' => Hash::make((string) $request->password),
        ]);

        return $this->sendResponse(true, __('Password reset successfully'));
    }

    public function profileDetails(Request $request): array
    {
        $user = $request->user();
        $tenant = $this->getRequestTenant($request, $user);

        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found for user'), [], 404);
        }

        return $this->sendResponse(true, __('Profile details'), [
            'user' => $user,
            'tenant' => [
                'id' => $tenant->id,
                'company_name' => $tenant->company_name,
                'company_username' => $tenant->company_username,
                'status' => $tenant->status,
            ],
        ]);
    }

    public function updateProfile(TenantUpdateProfileRequest $request): array
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendResponse(false, __('Unauthenticated'), [], 401);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'image' => $request->image ?: $user->image,
            'address' => $request->address,
            'language' => $request->language ?: $user->language,
        ];

        $user->update($data);

        return $this->sendResponse(true, __('Profile updated successfully'), $user->fresh());
    }

    public function changePassword(TenantChangePasswordRequest $request): array
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendResponse(false, __('Unauthenticated'), [], 401);
        }

        if (!Hash::check((string) $request->current_password, (string) $user->password)) {
            return $this->sendResponse(false, __('Current password is incorrect'), [], 400);
        }

        if ((string) $request->current_password === (string) $request->new_password) {
            return $this->sendResponse(false, __('New password should not be same as current password'), [], 400);
        }

        $user->update([
            'password' => Hash::make((string) $request->new_password),
        ]);

        return $this->sendResponse(true, __('Password changed successfully'));
    }

    public function subscriptionDetails(Request $request): array
    {
        $user = $request->user();
        $tenant = $this->getRequestTenant($request, $user);

        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found for user'), [], 404);
        }

        $activeSubscription = $this->tenantFeatureResolverService->getActiveSubscription((int) $tenant->id);
        $subscription = $activeSubscription ?: Subscription::query()
            ->with(['plan:id,name', 'pricing:id,plan_id,term_months,final_amount,currency'])
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        $featureMap = $this->tenantFeatureResolverService->getFeatureMap((int) $tenant->id);

        $paymentSummary = null;
        if ($subscription) {
            $verifiedPaid = (float) SubscriptionPayment::query()
                ->where('subscription_id', $subscription->id)
                ->where('status', 'verified')
                ->sum('amount');

            $dueAmount = 0;
            $currency = 'BDT';
            if ($subscription->relationLoaded('pricing') && $subscription->pricing) {
                $dueAmount = max(0, (float) $subscription->pricing->final_amount - $verifiedPaid);
                $currency = (string) $subscription->pricing->currency;
            }

            $paymentSummary = [
                'paid_amount' => $verifiedPaid,
                'due_amount' => $dueAmount,
                'currency' => $currency,
            ];
        }

        return $this->sendResponse(true, __('Subscription details'), [
            'package_active' => $activeSubscription !== null,
            'subscription' => $subscription,
            'payment_summary' => $paymentSummary,
            'features' => $featureMap,
        ]);
    }

    public function dashboardData(Request $request): array
    {
        $user = $request->user();
        $tenant = $this->getRequestTenant($request, $user);

        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found for user'), [], 404);
        }

        $activeSubscription = $this->tenantFeatureResolverService->getActiveSubscription((int) $tenant->id);
        $featureMap = $this->tenantFeatureResolverService->getFeatureMap((int) $tenant->id);

        $totalPayments = (int) SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->count();
        $verifiedPayments = (int) SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'verified')
            ->count();
        $pendingPayments = (int) SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->count();
        $totalPaidAmount = (float) SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'verified')
            ->sum('amount');

        return $this->sendResponse(true, __('Dashboard data'), [
            'tenant' => [
                'id' => $tenant->id,
                'company_name' => $tenant->company_name,
                'company_username' => $tenant->company_username,
            ],
            'package' => [
                'is_active' => $activeSubscription !== null,
                'active_subscription' => $activeSubscription,
            ],
            'payments' => [
                'total' => $totalPayments,
                'verified' => $verifiedPayments,
                'pending' => $pendingPayments,
                'total_paid_amount' => $totalPaidAmount,
            ],
            'feature_summary' => [
                'total_features' => count($featureMap),
                'enabled_features' => count(array_filter($featureMap, function ($value) {
                    if (is_bool($value)) {
                        return $value;
                    }
                    if (is_numeric($value)) {
                        return (float) $value > 0;
                    }
                    if (is_array($value)) {
                        return !empty($value);
                    }
                    return !empty($value);
                })),
            ],
        ]);
    }

    protected function getRequestTenant(Request $request, ?User $user): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        if (!$user) {
            return null;
        }

        return $this->tenantApiRepository->findTenantByUser($user);
    }

    protected function resolveVerificationType(User $user, string $login): int
    {
        if ($user->email && $user->email === $login) {
            return enum(VerificationCodeTypeEnum::EMAIL);
        }

        if ($user->phone && $user->phone === $login) {
            return enum(VerificationCodeTypeEnum::PHONE);
        }

        return enum(VerificationCodeTypeEnum::USERNAME);
    }

    protected function verifyResetOtp(User $user, string $login, string $otp): bool
    {
        $types = [];

        $directType = $this->resolveVerificationType($user, $login);
        $types[] = $directType;

        if ($directType === enum(VerificationCodeTypeEnum::USERNAME)) {
            if (!empty($user->email)) {
                $types[] = enum(VerificationCodeTypeEnum::EMAIL);
            }
            if (!empty($user->phone)) {
                $types[] = enum(VerificationCodeTypeEnum::PHONE);
            }
        }

        $types = array_values(array_unique($types));

        foreach ($types as $type) {
            $verified = UserVerifyCodeService::otpCodeVerification($user->id, $otp, $type);
            if (($verified['success'] ?? false) === true) {
                return true;
            }
        }

        return false;
    }
}

