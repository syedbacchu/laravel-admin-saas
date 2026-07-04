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
use App\Models\TenantDriver;
use App\Models\TenantVehicle;
use App\Models\User;
use App\Support\DataListManager;
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
            RateLimiter::hit($throttleKey, 10 * 60);
            return $this->sendResponse(false, __('Invalid credentials'), [], 422);
        }

        if ((int) $user->status !== 1 || (int) $user->enable_login !== 1) {
            return $this->sendResponse(false, __('Account is disabled. Please contact administrator.'), [], 403);
        }

        $userType = $this->resolveTenantUserType($user, $tenant);
        if ($userType === 'driver') {
            $tenantDriverId = (int) ($user->tenant_driver_id ?? 0);
            $driver = $tenantDriverId > 0 ? TenantDriver::query()->find($tenantDriverId) : null;

            if (!$driver || (int) $driver->status !== 1) {
                return $this->sendResponse(false, __('Driver account is inactive'), [], 403);
            }
        }

        RateLimiter::clear($throttleKey);

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('tenant_' . $tenant->company_username . '_' . $user->id)->accessToken;
        $activeSubscription = $this->tenantFeatureResolverService->getActiveSubscription((int) $tenant->id);

        if ($userType === 'staff') {
            $featureMap = $this->tenantFeatureResolverService->getFeatureMapForStaff((int) $tenant->id, (int) $user->id);
        } else {
            $featureMap = $this->tenantFeatureResolverService->getFeatureMap((int) $tenant->id);
        }

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
            'user_type' => $userType,
            'permissions' => $user->cachedPermissions(),
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
            'validity_type' => 'day',
            'validity' => 1,
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

        $userType = $this->resolveTenantUserType($user, $tenant);
        if ($userType === 'staff') {
            $featureMap = $this->tenantFeatureResolverService->getFeatureMapForStaff((int) $tenant->id, (int) $user->id);
        } else {
            $featureMap = $this->tenantFeatureResolverService->getFeatureMap((int) $tenant->id);
        }

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

        $userType = $this->resolveTenantUserType($user, $tenant);
        if ($userType === 'staff') {
            $featureMap = $this->tenantFeatureResolverService->getFeatureMapForStaff((int) $tenant->id, (int) $user->id);
        } else {
            $featureMap = $this->tenantFeatureResolverService->getFeatureMap((int) $tenant->id);
        }

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

    public function dashboardSummary(Request $request): array
    {
        $user = $request->user();
        $tenant = $this->getRequestTenant($request, $user);

        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found for user'), [], 404);
        }

        // Get financial summary using the report service

        // Create a request for current year's data
        $currentYear = now()->year;
        $reportRequest = new Request(['year' => $currentYear]);
        $reportRequest->attributes->set('tenant', $tenant);

        // Get entity counts
        $entityCounts = $this->getEntityCounts($tenant);

        return $this->sendResponse(true, __('Dashboard summary'), [
            'tenant' => [
                'id' => $tenant->id,
                'company_name' => $tenant->company_name,
                'company_username' => $tenant->company_username,
            ],
            'entity_counts' => $entityCounts,
            'financial_summary' => [],
            'vehicle_alerts' => [],
            'maintenance_alerts' => [],
        ]);
    }

    /**
     * Get entity counts for the dashboard
     */
    protected function getEntityCounts(Tenant $tenant): array
    {
        $vehiclesCount = 0;
        $customersCount = 0;
        $driversCount = 0;
        $suppliersCount = 0;
        $employeesCount = 0;
        $vendorsCount = 0;
        $helpersCount = 0;
        $supervisorsCount = 0;
        $officesCount = 0;
        $tripsCount = 0;

        return [
            'vehicles' => [
                'total' => $vehiclesCount,
                'active' => 0,
            ],
            'customers' => [
                'total' => $customersCount,
                'active' => 0,
            ],
            'drivers' => [
                'total' => $driversCount,
                'active' => 0,
            ],
            'suppliers' => [
                'total' => $suppliersCount,
                'active' => 0,
            ],
            'employees' => [
                'total' => $employeesCount,
                'active' => 0,
            ],
            'vendors' => [
                'total' => $vendorsCount,
                'active' => 0,
            ],
            'helpers' => [
                'total' => $helpersCount,
                'active' => 0,
            ],
            'supervisors' => [
                'total' => $supervisorsCount,
                'active' => 0,
            ],
            'offices' => [
                'total' => $officesCount,
                'active' => 0,
            ],
            'trips' => [
                'total' => $tripsCount,
                'today' => 0,
                'this_month' => 0,
            ],
        ];
    }

    /**
     * Get vehicles with expiry dates coming up soon or already expired
     */
    protected function getVehicleExpiryAlerts(): array
    {
        // Get alert days from tenant settings, default to 30 days
        $alertDays = $this->getVehicleAlertDays();

        $alertDate = now()->addDays($alertDays)->endOfDay();
        $today = now()->startOfDay();

        // Get vehicles where any expiry date is within the alert period or already expired
        $vehicles = TenantVehicle::where('status', 1)
            ->where(function ($query) use ($today, $alertDate) {
                // Registration expiry
                $query->where(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('registration_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('registration_expired_date', [$today, $alertDate])
                                ->orWhere('registration_expired_date', '<', $today);
                        });
                })
                // Tax expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('tax_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('tax_expired_date', [$today, $alertDate])
                                ->orWhere('tax_expired_date', '<', $today);
                        });
                })
                // Road permit expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('road_permit_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('road_permit_expired_date', [$today, $alertDate])
                                ->orWhere('road_permit_expired_date', '<', $today);
                        });
                })
                // Fitness expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('fitness_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('fitness_expired_date', [$today, $alertDate])
                                ->orWhere('fitness_expired_date', '<', $today);
                        });
                })
                // Insurance expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('insurance_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('insurance_expired_date', [$today, $alertDate])
                                ->orWhere('insurance_expired_date', '<', $today);
                        });
                });
            })
            ->get();

        $vehicleAlerts = [];
        foreach ($vehicles as $vehicle) {
            $alerts = $this->getVehicleExpiryDetails($vehicle, $alertDays);
            if (!empty($alerts)) {
                $vehicleAlerts[] = [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_name' => $vehicle->vehicle_name,
                    'registration_no' => $vehicle->registration_no ?? $vehicle->registration_number,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'brand' => $vehicle->brand,
                    'image' => $this->formatImageUrl($vehicle->image),
                    'model' => $vehicle->model,
                    'alerts' => $alerts,
                ];
            }
        }

        // Count expired alerts
        $expiredCount = 0;
        foreach ($vehicleAlerts as $vehicleAlert) {
            foreach ($vehicleAlert['alerts'] as $alert) {
                if ($alert['is_expired']) {
                    $expiredCount++;
                    break;
                }
            }
        }

        return [
            'alert_days' => $alertDays,
            'total_alerts' => count($vehicleAlerts),
            'expired_count' => $expiredCount,
            'vehicles' => $vehicleAlerts,
        ];
    }

    /**
     * Get expiry details for a specific vehicle
     */
    protected function getVehicleExpiryDetails(TenantVehicle $vehicle, int $alertDays): array
    {
        $alerts = [];
        $today = now()->startOfDay();
        $alertDate = now()->addDays($alertDays)->endOfDay();

        $expiryFields = [
            'registration_expired_date' => 'Registration',
            'tax_expired_date' => 'Tax',
            'road_permit_expired_date' => 'Road Permit',
            'fitness_expired_date' => 'Fitness',
            'insurance_expired_date' => 'Insurance',
        ];

        foreach ($expiryFields as $field => $label) {
            if ($vehicle->$field) {
                $expiryDate = \Carbon\Carbon::parse($vehicle->$field);

                // Check if date is within alert period OR already expired
                if ($expiryDate->between($today, $alertDate) || $expiryDate->isPast()) {
                    $daysUntilExpiry = $today->diffInDays($expiryDate, false);
                    $isExpired = $expiryDate->isPast();

                    $alerts[] = [
                        'type' => $label,
                        'expiry_date' => $vehicle->$field,
                        'days_until_expiry' => $daysUntilExpiry,
                        'days_overdue' => $isExpired ? abs($daysUntilExpiry) : 0,
                        'is_expired' => $isExpired,
                        'status' => $isExpired ? 'expired' : ($daysUntilExpiry <= 7 ? 'critical' : 'warning'),
                    ];
                }
            }
        }

        // Sort alerts by status priority (expired first, then critical, then warning)
        usort($alerts, function ($a, $b) {
            $statusPriority = ['expired' => 0, 'critical' => 1, 'warning' => 2];
            $statusA = $statusPriority[$a['status']] ?? 3;
            $statusB = $statusPriority[$b['status']] ?? 3;

            return $statusA - $statusB;
        });

        return $alerts;
    }

    /**
     * Get vehicle alert days from tenant settings
     */
    protected function getVehicleAlertDays(): int
    {
        try {
            $setting = Tenant\TenantSetting::where('slug', 'vehicle_expiry_alert_days')
                ->first();

            if ($setting && is_numeric($setting->value)) {
                return (int) $setting->value;
            }
        } catch (\Exception $e) {
            // Fall back to default if setting retrieval fails
        }

        return 30; // Default to 30 days
    }

    /**
     * Get maintenance services that are due soon or already expired
     */
    protected function getMaintenanceServiceAlerts(): array
    {
        // Get alert days from tenant settings, default to 30 days
        $alertDays = $this->getMaintenanceAlertDays();

        $alertDate = now()->addDays($alertDays)->endOfDay();
        $today = now()->startOfDay();

        // Get maintenance purchases where next service date is within the alert period or already expired
        $maintenancePurchases = \App\Models\TenantMaintenancePurchase::where('status', 1)
            ->whereNotNull('next_service_date')
            ->where(function ($query) use ($today, $alertDate) {
                // Either within the alert period (upcoming) OR already expired
                $query->whereBetween('next_service_date', [$today, $alertDate])
                    ->orWhere('next_service_date', '<', $today);
            })
            ->with(['vehicle', 'supplier', 'office'])
            ->get();

        $maintenanceAlerts = [];
        foreach ($maintenancePurchases as $maintenance) {
            $nextServiceDate = \Carbon\Carbon::parse($maintenance->next_service_date);
            $daysUntilService = $today->diffInDays($nextServiceDate, false);
            $isOverdue = $nextServiceDate->isPast();

            $maintenanceAlerts[] = [
                'maintenance_id' => $maintenance->id,
                'vehicle' => $maintenance->vehicle ? [
                    'id' => $maintenance->vehicle->id,
                    'name' => $maintenance->vehicle->vehicle_name,
                    'image' => $this->formatImageUrl($maintenance->vehicle->image),
                    'registration_no' => $maintenance->vehicle->registration_no ?? $maintenance->vehicle->registration_number,
                ] : null,
                'supplier' => $maintenance->supplier ? [
                    'id' => $maintenance->supplier->id,
                    'name' => $maintenance->supplier->name,
                ] : null,
                'category' => $maintenance->category,
                'service_date' => $maintenance->service_date,
                'next_service_date' => $maintenance->next_service_date,
                'days_until_service' => $daysUntilService,
                'is_overdue' => $isOverdue,
                'days_overdue' => $isOverdue ? abs($daysUntilService) : 0,
                'status' => $isOverdue ? 'overdue' : ($daysUntilService <= 7 ? 'critical' : 'upcoming'),
                'items' => $maintenance->items,
                'service_charge' => (float) ($maintenance->service_charge ?? 0),
            ];
        }

        // Sort by status priority (overdue first, then critical, then upcoming)
        usort($maintenanceAlerts, function ($a, $b) {
            $statusPriority = ['overdue' => 0, 'critical' => 1, 'upcoming' => 2];
            $statusA = $statusPriority[$a['status']] ?? 3;
            $statusB = $statusPriority[$b['status']] ?? 3;

            if ($statusA !== $statusB) {
                return $statusA - $statusB;
            }

            // If same status, sort by days (most urgent first)
            return $a['days_until_service'] - $b['days_until_service'];
        });

        return [
            'alert_days' => $alertDays,
            'total_alerts' => count($maintenanceAlerts),
            'overdue_count' => count(array_filter($maintenanceAlerts, fn($alert) => $alert['is_overdue'])),
            'critical_count' => count(array_filter($maintenanceAlerts, fn($alert) => $alert['status'] === 'critical')),
            'upcoming_count' => count(array_filter($maintenanceAlerts, fn($alert) => $alert['status'] === 'upcoming')),
            'services' => $maintenanceAlerts,
        ];
    }

    /**
     * Get maintenance alert days from tenant settings
     */
    protected function getMaintenanceAlertDays(): int
    {
        try {
            $setting = Tenant\TenantSetting::where('slug', 'maintenance_service_alert_days')
                ->first();

            if ($setting && is_numeric($setting->value)) {
                return (int) $setting->value;
            }
        } catch (\Exception $e) {
            // Fall back to default if setting retrieval fails
        }

        return 30; // Default to 30 days
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

    protected function resolveTenantUserType(User $user, Tenant $tenant): string
    {
        if ((string) $user->user_type !== '') {
            return (string) $user->user_type;
        }

        return (int) $user->id === (int) $tenant->owner_user_id ? 'owner' : 'staff';
    }

    public function vehicleAlertsList(Request $request): array
    {
        $user = $request->user();
        $tenant = $this->getRequestTenant($request, $user);

        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found for user'), [], 404);
        }

        // Get alert days from tenant settings
        $alertDays = $this->getVehicleAlertDays();
        $alertDate = now()->addDays($alertDays)->endOfDay();
        $today = now()->startOfDay();

        // Build query for vehicles with expiry alerts
        $query = TenantVehicle::where('status', 1)
            ->where(function ($q) use ($today, $alertDate) {
                // Registration expiry
                $q->where(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('registration_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('registration_expired_date', [$today, $alertDate])
                                ->orWhere('registration_expired_date', '<', $today);
                        });
                })
                // Tax expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('tax_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('tax_expired_date', [$today, $alertDate])
                                ->orWhere('tax_expired_date', '<', $today);
                        });
                })
                // Road permit expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('road_permit_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('road_permit_expired_date', [$today, $alertDate])
                                ->orWhere('road_permit_expired_date', '<', $today);
                        });
                })
                // Fitness expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('fitness_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('fitness_expired_date', [$today, $alertDate])
                                ->orWhere('fitness_expired_date', '<', $today);
                        });
                })
                // Insurance expiry
                ->orWhere(function ($subQuery) use ($today, $alertDate) {
                    $subQuery->whereNotNull('insurance_expired_date')
                        ->where(function ($dateQuery) use ($today, $alertDate) {
                            $dateQuery->whereBetween('insurance_expired_date', [$today, $alertDate])
                                ->orWhere('insurance_expired_date', '<', $today);
                        });
                });
            });

        $result = DataListManager::list(
            request: $request,
            query: $query,
            searchable: [
                'vehicle_name',
                'registration_no',
                'registration_number',
                'brand',
                'model',
            ],
            filters: [
                'vehicle_type' => ['column' => 'vehicle_type'],
                'status' => ['column' => 'status'],
            ],
            select: [
                'id',
                'vehicle_name',
                'registration_no',
                'registration_number',
                'vehicle_type',
                'brand',
                'model',
                'registration_expired_date',
                'tax_expired_date',
                'road_permit_expired_date',
                'fitness_expired_date',
                'insurance_expired_date',
                'status',
                'image'
            ],
        );

        // Process alerts for each vehicle
        $vehiclesWithAlerts = [];
        foreach ($result['data'] as $vehicle) {
            $alerts = $this->getVehicleExpiryDetails($vehicle, $alertDays);
            if (!empty($alerts)) {
                $vehiclesWithAlerts[] = [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_name' => $vehicle->vehicle_name,
                    'registration_no' => $vehicle->registration_no ?? $vehicle->registration_number,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'image' => $vehicle->image,
                    'alerts' => $alerts,
                    'has_expired' => collect($alerts)->contains('is_expired', true),
                ];
            }
        }

        $result['data'] = $vehiclesWithAlerts;
        $result['alert_days'] = $alertDays;
        $result['total_alerts'] = count($vehiclesWithAlerts);
        $result['expired_count'] = collect($vehiclesWithAlerts)->where('has_expired', true)->count();

        return $this->sendResponse(true, __('Vehicle alerts retrieved successfully'), $result);
    }

    public function maintenanceAlertsList(Request $request): array
    {
        $user = $request->user();
        $tenant = $this->getRequestTenant($request, $user);

        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant not found for user'), [], 404);
        }

        // Get alert days from tenant settings
        $alertDays = $this->getMaintenanceAlertDays();
        $alertDate = now()->addDays($alertDays)->endOfDay();
        $today = now()->startOfDay();

        // Build query for maintenance alerts
        $query = \App\Models\TenantMaintenancePurchase::where('status', 1)
            ->whereNotNull('next_service_date')
            ->where(function ($q) use ($today, $alertDate) {
                // Either within the alert period (upcoming) OR already expired
                $q->whereBetween('next_service_date', [$today, $alertDate])
                    ->orWhere('next_service_date', '<', $today);
            })
            ->with(['vehicle:id,vehicle_name,registration_no,registration_number,image', 'supplier:id,name']);

        $result = DataListManager::list(
            request: $request,
            query: $query,
            searchable: [
                'category',
            ],
            filters: [
                'category' => ['column' => 'category'],
                'vehicle_id' => ['column' => 'vehicle_id'],
                'supplier_id' => ['column' => 'supplier_id'],
            ],
            select: [
                'id',
                'vehicle_id',
                'supplier_id',
                'category',
                'service_date',
                'next_service_date',
                'items',
                'service_charge',
                'total_purchase_amount',
                'status',
            ],
        );

        // Process maintenance alerts
        $maintenanceAlerts = [];
        foreach ($result['data'] as $maintenance) {
            $nextServiceDate = \Carbon\Carbon::parse($maintenance->next_service_date);
            $daysUntilService = $today->diffInDays($nextServiceDate, false);
            $isOverdue = $nextServiceDate->isPast();

            $maintenanceAlerts[] = [
                'maintenance_id' => $maintenance->id,
                'vehicle' => $maintenance->vehicle ? [
                    'id' => $maintenance->vehicle->id,
                    'name' => $maintenance->vehicle->vehicle_name,
                    'image' => $maintenance->vehicle->image,
                    'registration_no' => $maintenance->vehicle->registration_no ?? $maintenance->vehicle->registration_number,
                ] : null,
                'supplier' => $maintenance->supplier ? [
                    'id' => $maintenance->supplier->id,
                    'name' => $maintenance->supplier->name,
                ] : null,
                'category' => $maintenance->category,
                'service_date' => $maintenance->service_date,
                'next_service_date' => $maintenance->next_service_date,
                'days_until_service' => $daysUntilService,
                'is_overdue' => $isOverdue,
                'days_overdue' => $isOverdue ? abs($daysUntilService) : 0,
                'status' => $isOverdue ? 'overdue' : ($daysUntilService <= 7 ? 'critical' : 'upcoming'),
                'items' => $maintenance->items,
                'service_charge' => (float) ($maintenance->service_charge ?? 0),
                'total_purchase_amount' => (float) $maintenance->total_purchase_amount,
            ];
        }

        // Sort by status priority (overdue first, then critical, then upcoming)
        usort($maintenanceAlerts, function ($a, $b) {
            $statusPriority = ['overdue' => 0, 'critical' => 1, 'upcoming' => 2];
            $statusA = $statusPriority[$a['status']] ?? 3;
            $statusB = $statusPriority[$b['status']] ?? 3;

            if ($statusA !== $statusB) {
                return $statusA - $statusB;
            }

            // If same status, sort by days (most urgent first)
            return $a['days_until_service'] - $b['days_until_service'];
        });

        $result['data'] = $maintenanceAlerts;
        $result['alert_days'] = $alertDays;
        $result['total_alerts'] = count($maintenanceAlerts);
        $result['overdue_count'] = count(array_filter($maintenanceAlerts, fn($alert) => $alert['is_overdue']));
        $result['critical_count'] = count(array_filter($maintenanceAlerts, fn($alert) => $alert['status'] === 'critical'));
        $result['upcoming_count'] = count(array_filter($maintenanceAlerts, fn($alert) => $alert['status'] === 'upcoming'));

        return $this->sendResponse(true, __('Maintenance alerts retrieved successfully'), $result);
    }

    /**
     * Format image URL to ensure it includes the full URL path
     */
    protected function formatImageUrl($image): ?string
    {
        if (!$image) {
            return null;
        }

        // If image already starts with http:// or https://, return as is
        if (preg_match('/^https?:\/\//', $image)) {
            return $image;
        }

        // If image starts with /, assume it's a relative path and append to app URL
        if (strpos($image, '/') === 0) {
            return rtrim(config('app.url'), '/') . $image;
        }

        // Otherwise, assume it's a path relative to public/uploads
        return rtrim(config('app.url'), '/') . '/uploads/' . ltrim($image, '/');
    }
}
