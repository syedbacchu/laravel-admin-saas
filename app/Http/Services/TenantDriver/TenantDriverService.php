<?php

namespace App\Http\Services\TenantDriver;

use App\Http\Requests\TenantApi\TenantDriverCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\TenantDriver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class TenantDriverService extends BaseService implements TenantDriverServiceInterface
{
    protected TenantDriverRepositoryInterface $tenantDriverRepository;

    public function __construct(TenantDriverRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantDriverRepository = $repository;
    }

    public function driverList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantDriverRepository->driverList($request);
        $this->attachLoginInfoToDriverList($data, (int) $tenant->owner_user_id);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeDriver(TenantDriverCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;

        try {
            $data = [
                'vehicle_id' => $request->vehicle_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'license_no' => $request->license_no,
                'nid_no' => $request->nid_no,
                'joining_date' => $request->joining_date,
                'address' => $request->address,
                'notes' => $request->notes,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantDriverRepository->findDriver((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Driver not found'), [], 404);
                }

                $loginUpdateResponse = $this->syncDriverLoginUpdate($request, $ownerUserId, $item);
                if ($loginUpdateResponse !== null) {
                    return $loginUpdateResponse;
                }

                $this->tenantDriverRepository->update((int) $item->id, $data);
                $item = $this->tenantDriverRepository->findDriver((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Driver not found'), [], 404);
                }

                $loginUser = $this->tenantDriverRepository->findDriverLoginUser($ownerUserId, (int) $item->id);
                $this->attachLoginInfoToDriver($item, $this->mapLoginUser($loginUser));

                return $this->sendResponse(true, __('Driver updated successfully'), $item);
            }

            $item = $this->tenantDriverRepository->createDriver($data);
            $item = $this->tenantDriverRepository->findDriver((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Driver not found'), [], 404);
            }
            $this->attachLoginInfoToDriver($item, null);

            return $this->sendResponse(true, __('Driver created successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantDriverService storeDriver', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function driverDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantDriverRepository->findDriver($id);
        if (!$item) {
            return $this->sendResponse(false, __('Driver not found'), [], 404);
        }

        $loginUser = $this->tenantDriverRepository->findDriverLoginUser((int) $tenant->owner_user_id, $id);
        $this->attachLoginInfoToDriver($item, $this->mapLoginUser($loginUser));

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteDriver(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantDriverRepository->findDriver($id);
        if (!$item) {
            return $this->sendResponse(false, __('Driver not found'), [], 404);
        }

        $this->tenantDriverRepository->delete($id);

        return $this->sendResponse(true, __('Driver deleted successfully'));
    }

    protected function syncDriverLoginUpdate(TenantDriverCreateRequest $request, int $ownerUserId, TenantDriver $driver): ?array
    {
        if (!$this->hasDriverLoginUpdatePayload($request)) {
            return null;
        }

        $loginUser = $this->tenantDriverRepository->findDriverLoginUser($ownerUserId, (int) $driver->id);
        if (!$loginUser) {
            return $this->sendResponse(false, __('Driver login account not found. Create login first'), [], 422);
        }

        $duplicateMessage = $this->tenantScopedDuplicateMessage(
            $ownerUserId,
            $request->login_email,
            $request->login_phone,
            $request->login_username,
            (int) $loginUser->id
        );
        if ($duplicateMessage !== null) {
            return $this->sendResponse(false, $duplicateMessage, [], 422);
        }

        $updateData = [];
        if ($request->exists('login_name')) {
            $name = trim((string) ($request->login_name ?? ''));
            $fallbackName = trim((string) ($request->name ?: $driver->name));
            $updateData['name'] = $name !== '' ? $name : $fallbackName;
        }

        if ($request->exists('login_email')) {
            $email = $request->login_email ?: null;
            $updateData['email'] = $email;
            $updateData['is_email_verified'] = $email ? 1 : 0;
            $updateData['email_verified_at'] = $email ? now() : null;
        }

        if ($request->exists('login_phone')) {
            $phone = $request->login_phone ?: null;
            $updateData['phone'] = $phone;
            $updateData['is_phone_verified'] = $phone ? 1 : 0;
        }

        if ($request->exists('login_username')) {
            $username = trim((string) ($request->login_username ?? ''));
            $updateData['username'] = $username !== ''
                ? $this->ensureGlobalUniqueUsername($username, (int) $loginUser->id)
                : null;
        }

        if (!empty($request->login_password)) {
            $updateData['password'] = Hash::make((string) $request->login_password);
        }

        if ($request->exists('login_enable_login')) {
            $updateData['enable_login'] = (int) $request->login_enable_login;
        }

        if ($request->exists('login_status')) {
            $updateData['status'] = (int) $request->login_status;
        }

        if (!empty($updateData)) {
            $this->tenantDriverRepository->updateDriverLoginUser((int) $loginUser->id, $updateData);
        }

        return null;
    }

    protected function hasDriverLoginUpdatePayload(TenantDriverCreateRequest $request): bool
    {
        $keys = [
            'login_name',
            'login_email',
            'login_phone',
            'login_username',
            'login_password',
            'login_enable_login',
            'login_status',
        ];

        foreach ($keys as $key) {
            if ($request->exists($key)) {
                return true;
            }
        }

        return false;
    }

    protected function attachLoginInfoToDriverList(array &$data, int $ownerUserId): void
    {
        if (!isset($data['data']) || !is_iterable($data['data'])) {
            return;
        }

        $driverIds = [];
        foreach ($data['data'] as $driver) {
            $driverIds[] = (int) $driver->id;
        }

        $driverIds = array_values(array_unique(array_filter($driverIds)));
        if (empty($driverIds)) {
            return;
        }

        $loginMap = $this->tenantDriverRepository->getDriverLoginMap($ownerUserId, $driverIds);
        foreach ($data['data'] as $driver) {
            $loginInfo = $loginMap[(int) $driver->id] ?? null;
            $this->attachLoginInfoToDriver($driver, $loginInfo);
        }
    }

    protected function attachLoginInfoToDriver(TenantDriver $driver, ?array $loginInfo): void
    {
        $driver->setAttribute('has_login_account', $loginInfo !== null);
        $driver->setAttribute('login_enabled', $loginInfo ? ((int) ($loginInfo['enable_login'] ?? 0) === 1) : false);
        $driver->setAttribute('login_account', $loginInfo);
    }

    protected function mapLoginUser(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'user_id' => (int) $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'enable_login' => (int) $user->enable_login,
            'status' => (int) $user->status,
        ];
    }

    protected function tenantScopedDuplicateMessage(
        int $ownerUserId,
        ?string $email,
        ?string $phone,
        ?string $username,
        ?int $ignoreUserId = null
    ): ?string {
        $email = $this->normalizeIdentifier($email, true);
        if ($email !== null && $this->tenantDriverRepository->findTenantUserByIdentifier($ownerUserId, 'email', $email, $ignoreUserId)) {
            return __('Email already exists for this company');
        }

        $phone = $this->normalizeIdentifier($phone);
        if ($phone !== null && $this->tenantDriverRepository->findTenantUserByIdentifier($ownerUserId, 'phone', $phone, $ignoreUserId)) {
            return __('Phone already exists for this company');
        }

        $username = $this->normalizeIdentifier($username, true);
        if ($username !== null && $this->tenantDriverRepository->findTenantUserByIdentifier($ownerUserId, 'username', $username, $ignoreUserId)) {
            return __('Username already exists for this company');
        }

        return null;
    }

    protected function normalizeIdentifier(?string $value, bool $lowercase = false): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return $lowercase ? strtolower($value) : $value;
    }

    protected function ensureGlobalUniqueUsername(string $username, ?int $ignoreUserId = null): string
    {
        $base = strtolower(trim($username));
        if ($base === '') {
            return '';
        }

        $candidate = $base;
        $counter = 1;

        while ($this->tenantDriverRepository->usernameExists($candidate, $ignoreUserId)) {
            $suffix = (string) $counter;
            $maxLength = max(1, 50 - strlen($suffix));
            $candidate = substr($base, 0, $maxLength) . $suffix;
            $counter++;
        }

        return $candidate;
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }
}
