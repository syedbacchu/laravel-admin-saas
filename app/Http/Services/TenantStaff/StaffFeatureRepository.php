<?php

namespace App\Http\Services\TenantStaff;

use App\Http\Repositories\BaseRepository;
use App\Models\StaffFeatureAssignment;
use Illuminate\Support\Facades\DB;

class StaffFeatureRepository extends BaseRepository implements StaffFeatureRepositoryInterface
{
    public function __construct(StaffFeatureAssignment $model)
    {
        parent::__construct($model);
    }

    public function getStaffFeatures(int $staffId): array
    {
        return StaffFeatureAssignment::where('staff_id', $staffId)
            ->pluck('is_accessible', 'feature_key')
            ->toArray();
    }

    public function getStaffAccessibleFeatures(int $staffId): array
    {
        return StaffFeatureAssignment::where('staff_id', $staffId)
            ->where('is_accessible', true)
            ->pluck('feature_key')
            ->toArray();
    }

    public function updateStaffFeatures(int $staffId, array $features): array
    {
        DB::beginTransaction();
        try {
            // Delete existing assignments
            StaffFeatureAssignment::where('staff_id', $staffId)->delete();

            // Create new assignments
            foreach ($features as $featureKey) {
                StaffFeatureAssignment::create([
                    'staff_id' => $staffId,
                    'feature_key' => $featureKey,
                    'is_accessible' => true,
                ]);
            }

            DB::commit();
            return $this->getStaffFeatures($staffId);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteStaffFeatures(int $staffId): bool
    {
        return StaffFeatureAssignment::where('staff_id', $staffId)->delete() > 0;
    }
}