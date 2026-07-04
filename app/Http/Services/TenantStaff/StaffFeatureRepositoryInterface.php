<?php

namespace App\Http\Services\TenantStaff;

use App\Http\Repositories\BaseRepositoryInterface;

interface StaffFeatureRepositoryInterface extends BaseRepositoryInterface
{
    public function getStaffFeatures(int $staffId): array;
    public function getStaffAccessibleFeatures(int $staffId): array;
    public function updateStaffFeatures(int $staffId, array $features): array;
    public function deleteStaffFeatures(int $staffId): bool;
}