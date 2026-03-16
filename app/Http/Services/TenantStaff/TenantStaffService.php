<?php

namespace App\Http\Services\TenantStaff;

use App\Enums\UserRole;
use App\Http\Requests\TenantApi\TenantDriverLoginCreateRequest;
use App\Http\Requests\TenantApi\TenantStaffCreateRequest;
use App\Http\Requests\TenantApi\TenantStaffResetPasswordRequest;
use App\Http\Requests\TenantApi\TenantStaffUpdateRequest;
use App\Http\Services\BaseService;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantDriver;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class TenantStaffService extends BaseService implements TenantStaffServiceInterface
{
    protected TenantStaffRepositoryInterface $tenantStaffRepository;

    public function __construct(TenantStaffRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantStaffRepository = $repository;
    }

    public function staffList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;
        if (!$this->isOwnerUser($request, $ownerUserId)) {
            return $this->sendResponse(false, __('Only tenant owner can manage staff'), [], 403);
        }

        $data = $this->tenantStaffRepository->staffList($request, $ownerUserId);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function createStaff(TenantStaffCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;
        if (!$this->isOwnerUser($request, $ownerUserId)) {
            return $this->sendResponse(false, __('Only tenant owner can manage staff'), [], 403);
        }

        if (!$this->isValidApiRole($request->role_id)) {
            return $this->sendResponse(false, __('Invalid role selected for staff'), [], 422);
        }

        $duplicateMessage = $this->tenantScopedDuplicateMessage(
            $ownerUserId,
            $request->email,
            $request->phone,
            $request->username
        );
        if ($duplicateMessage !== null) {
            return $this->sendResponse(false, $duplicateMessage, [], 422);
        }

        try {
            $data = [
                'parent_id' => $ownerUserId,
                'name' => trim((string) $request->name),
                'username' => $this->resolveUsername((string) $request->username, (string) $request->name),
                'email' => $request->email ?: null,
                'phone' => $request->phone ?: null,
                'password' => Hash::make((string) $request->password),
                'role_module' => enum(UserRole::USER_ROLE),
                'user_type' => 'staff',
                'role_id' => $request->role_id ? (int) $request->role_id : null,
                'enable_login' => (int) ($request->enable_login ?? 1),
                'status' => (int) ($request->status ?? 1),
                'added_by' => (int) ($request->user()?->id ?? $ownerUserId),
                'is_email_verified' => !empty($request->email) ? 1 : 0,
                'is_phone_verified' => !empty($request->phone) ? 1 : 0,
                'email_verified_at' => !empty($request->email) ? now() : null,
            ];

            $item = $this->tenantStaffRepository->createTenantUser($data);

            return $this->sendResponse(true, __('Staff created successfully'), $this->tenantStaffRepository->findStaff($ownerUserId, (int) $item->id));
        } catch (Throwable $e) {
            logStore('TenantStaffService createStaff', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function staffDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;
        if (!$this->isOwnerUser($request, $ownerUserId)) {
            return $this->sendResponse(false, __('Only tenant owner can manage staff'), [], 403);
        }

        $item = $this->tenantStaffRepository->findStaff($ownerUserId, $id);
        if (!$item) {
            return $this->sendResponse(false, __('Staff not found'), [], 404);
        }

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function updateStaff(TenantStaffUpdateRequest $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;
        if (!$this->isOwnerUser($request, $ownerUserId)) {
            return $this->sendResponse(false, __('Only tenant owner can manage staff'), [], 403);
        }

        $item = $this->tenantStaffRepository->findStaff($ownerUserId, $id);
        if (!$item) {
            return $this->sendResponse(false, __('Staff not found'), [], 404);
        }

        if ((string) $item->user_type !== 'staff') {
            return $this->sendResponse(false, __('Only staff users are allowed in this endpoint'), [], 422);
        }

        if (!$this->isValidApiRole($request->role_id)) {
            return $this->sendResponse(false, __('Invalid role selected for staff'), [], 422);
        }

        $duplicateMessage = $this->tenantScopedDuplicateMessage(
            $ownerUserId,
            $request->email,
            $request->phone,
            $request->username,
            (int) $item->id
        );
        if ($duplicateMessage !== null) {
            return $this->sendResponse(false, $duplicateMessage, [], 422);
        }

        try {
            $data = [
                'name' => trim((string) $request->name),
                'username' => $this->resolveUsername((string) $request->username, (string) $request->name, (int) $item->id),
                'email' => $request->email ?: null,
                'phone' => $request->phone ?: null,
                'role_id' => $request->role_id ? (int) $request->role_id : null,
                'enable_login' => (int) ($request->enable_login ?? $item->enable_login),
                'status' => (int) ($request->status ?? $item->status),
            ];

            if (!empty($request->password)) {
                $data['password'] = Hash::make((string) $request->password);
            }

            $this->tenantStaffRepository->update((int) $item->id, $data);

            return $this->sendResponse(true, __('Staff updated successfully'), $this->tenantStaffRepository->findStaff($ownerUserId, (int) $item->id));
        } catch (Throwable $e) {
            logStore('TenantStaffService updateStaff', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function deleteStaff(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;
        if (!$this->isOwnerUser($request, $ownerUserId)) {
            return $this->sendResponse(false, __('Only tenant owner can manage staff'), [], 403);
        }

        $item = $this->tenantStaffRepository->findStaff($ownerUserId, $id);
        if (!$item) {
            return $this->sendResponse(false, __('Staff not found'), [], 404);
        }

        if ((string) $item->user_type !== 'staff') {
            return $this->sendResponse(false, __('Only staff users are allowed in this endpoint'), [], 422);
        }

        $this->tenantStaffRepository->delete((int) $item->id);

        return $this->sendResponse(true, __('Staff deleted successfully'));
    }

    public function resetStaffPassword(TenantStaffResetPasswordRequest $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;
        if (!$this->isOwnerUser($request, $ownerUserId)) {
            return $this->sendResponse(false, __('Only tenant owner can manage staff'), [], 403);
        }

        $item = $this->tenantStaffRepository->findStaff($ownerUserId, $id);
        if (!$item) {
            return $this->sendResponse(false, __('Staff not found'), [], 404);
        }

        if ((string) $item->user_type !== 'staff') {
            return $this->sendResponse(false, __('Only staff users are allowed in this endpoint'), [], 422);
        }

        $this->tenantStaffRepository->update((int) $item->id, [
            'password' => Hash::make((string) $request->password),
        ]);

        return $this->sendResponse(true, __('Staff password reset successfully'));
    }

    public function createDriverLogin(TenantDriverLoginCreateRequest $request, int $driverId): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $ownerUserId = (int) $tenant->owner_user_id;
        if (!$this->isOwnerUser($request, $ownerUserId)) {
            return $this->sendResponse(false, __('Only tenant owner can manage driver login'), [], 403);
        }

        if (!$this->isValidApiRole($request->role_id)) {
            return $this->sendResponse(false, __('Invalid role selected for driver login'), [], 422);
        }

        $driver = TenantDriver::query()->find($driverId);
        if (!$driver) {
            return $this->sendResponse(false, __('Driver not found'), [], 404);
        }

        if ((int) $driver->status !== 1) {
            return $this->sendResponse(false, __('Inactive driver cannot login'), [], 422);
        }

        $existing = $this->tenantStaffRepository->findDriverUser($ownerUserId, $driverId);
        if ($existing) {
            return $this->sendResponse(false, __('Login account already exists for this driver'), [], 422);
        }

        $duplicateMessage = $this->tenantScopedDuplicateMessage(
            $ownerUserId,
            $request->email,
            $request->phone,
            $request->username
        );
        if ($duplicateMessage !== null) {
            return $this->sendResponse(false, $duplicateMessage, [], 422);
        }

        try {
            $driverName = trim((string) $driver->name);
            $data = [
                'parent_id' => $ownerUserId,
                'name' => trim((string) ($request->name ?: $driverName)),
                'username' => $this->resolveUsername((string) $request->username, $driverName),
                'email' => $request->email ?: null,
                'phone' => $request->phone ?: null,
                'password' => Hash::make((string) $request->password),
                'role_module' => enum(UserRole::USER_ROLE),
                'user_type' => 'driver',
                'tenant_driver_id' => $driverId,
                'role_id' => $request->role_id ? (int) $request->role_id : null,
                'enable_login' => (int) ($request->enable_login ?? 1),
                'status' => (int) ($request->status ?? 1),
                'added_by' => (int) ($request->user()?->id ?? $ownerUserId),
                'is_email_verified' => !empty($request->email) ? 1 : 0,
                'is_phone_verified' => !empty($request->phone) ? 1 : 0,
                'email_verified_at' => !empty($request->email) ? now() : null,
            ];

            $item = $this->tenantStaffRepository->createTenantUser($data);

            return $this->sendResponse(true, __('Driver login created successfully'), $this->tenantStaffRepository->findStaff($ownerUserId, (int) $item->id));
        } catch (Throwable $e) {
            logStore('TenantStaffService createDriverLogin', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function isOwnerUser(Request $request, int $ownerUserId): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return (int) $user->id === $ownerUserId;
    }

    protected function isValidApiRole($roleId): bool
    {
        if (!$roleId) {
            return true;
        }

        return Role::query()
            ->where('id', (int) $roleId)
            ->where('guard', 'api')
            ->where('status', 1)
            ->exists();
    }

    protected function resolveUsername(string $usernameInput, string $name, ?int $ignoreUserId = null): string
    {
        $usernameInput = strtolower(trim($usernameInput));
        if ($usernameInput !== '') {
            return $this->ensureGlobalUniqueUsername($usernameInput, $ignoreUserId);
        }

        $generated = Helpers::generateUniqueUsername($name !== '' ? $name : 'user', $ignoreUserId);
        if (trim($generated) !== '') {
            return $generated;
        }

        return Helpers::generateUniqueUsername('user', $ignoreUserId);
    }

    protected function tenantScopedDuplicateMessage(
        int $ownerUserId,
        ?string $email,
        ?string $phone,
        ?string $username,
        ?int $ignoreUserId = null
    ): ?string {
        $email = $this->normalizeIdentifier($email, true);
        if ($email !== null && $this->tenantStaffRepository->findTenantUserByIdentifier($ownerUserId, 'email', $email, $ignoreUserId)) {
            return __('Email already exists for this company');
        }

        $phone = $this->normalizeIdentifier($phone);
        if ($phone !== null && $this->tenantStaffRepository->findTenantUserByIdentifier($ownerUserId, 'phone', $phone, $ignoreUserId)) {
            return __('Phone already exists for this company');
        }

        $username = $this->normalizeIdentifier($username, true);
        if ($username !== null && $this->tenantStaffRepository->findTenantUserByIdentifier($ownerUserId, 'username', $username, $ignoreUserId)) {
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
            return Helpers::generateUniqueUsername('user', $ignoreUserId);
        }

        $candidate = $base;
        $counter = 1;

        while ($this->tenantStaffRepository->usernameExists($candidate, $ignoreUserId)) {
            $suffix = (string) $counter;
            $maxLength = max(1, 50 - strlen($suffix));
            $candidate = substr($base, 0, $maxLength) . $suffix;
            $counter++;
        }

        return $candidate;
    }
}
