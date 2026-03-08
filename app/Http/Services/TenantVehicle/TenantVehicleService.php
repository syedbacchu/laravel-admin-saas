<?php

namespace App\Http\Services\TenantVehicle;

use App\Http\Requests\TenantApi\TenantVehicleCreateRequest;
use App\Http\Services\BaseService;
use App\Http\Services\Tenant\TenantFeatureResolverService;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Throwable;

class TenantVehicleService extends BaseService implements TenantVehicleServiceInterface
{
    protected TenantVehicleRepositoryInterface $tenantVehicleRepository;

    protected TenantFeatureResolverService $tenantFeatureResolverService;

    public function __construct(
        TenantVehicleRepositoryInterface $repository,
        TenantFeatureResolverService $tenantFeatureResolverService
    ) {
        parent::__construct($repository);
        $this->tenantVehicleRepository = $repository;
        $this->tenantFeatureResolverService = $tenantFeatureResolverService;
    }

    public function vehicleList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $limit = $this->resolveVehicleLimit((int) $tenant->id);
        if ($limit <= 0) {
            return $this->sendResponse(false, __('Vehicle management feature is not enabled for your package'), [], 403);
        }

        $data = $this->tenantVehicleRepository->vehicleList($request);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeVehicle(TenantVehicleCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $limit = $this->resolveVehicleLimit((int) $tenant->id);
        if ($limit <= 0) {
            return $this->sendResponse(false, __('Vehicle management feature is not enabled for your package'), [], 403);
        }

        try {
            $data = [
                'registration_no' => (string) $request->registration_no,
                'vehicle_type' => $request->vehicle_type,
                'brand' => $request->brand,
                'model' => $request->model,
                'manufacturing_year' => $request->manufacturing_year,
                'color' => $request->color,
                'notes' => $request->notes,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantVehicleRepository->findVehicle((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Vehicle not found'), [], 404);
                }

                $this->tenantVehicleRepository->update((int) $item->id, $data);
                $item = $this->tenantVehicleRepository->findVehicle((int) $item->id);

                return $this->sendResponse(true, __('Vehicle updated successfully'), $item);
            }

            $currentCount = $this->tenantVehicleRepository->totalVehicle();
            if (!$this->tenantFeatureResolverService->withinLimit((int) $tenant->id, 'vehicle.max_count', $currentCount)) {
                return $this->sendResponse(false, __('Vehicle limit reached for your current package'), [
                    'vehicle_limit' => $limit,
                    'vehicle_used' => $currentCount,
                ], 422);
            }

            if ($currentCount >= $limit) {
                return $this->sendResponse(false, __('Vehicle limit reached for your current package'), [
                    'vehicle_limit' => $limit,
                    'vehicle_used' => $currentCount,
                ], 422);
            }

            $item = $this->tenantVehicleRepository->createVehicle($data);

            return $this->sendResponse(true, __('Vehicle created successfully'), $this->tenantVehicleRepository->findVehicle((int) $item->id));
        } catch (Throwable $e) {
            logStore('TenantVehicleService storeVehicle', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function vehicleDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $limit = $this->resolveVehicleLimit((int) $tenant->id);
        if ($limit <= 0) {
            return $this->sendResponse(false, __('Vehicle management feature is not enabled for your package'), [], 403);
        }

        $item = $this->tenantVehicleRepository->findVehicle($id);
        if (!$item) {
            return $this->sendResponse(false, __('Vehicle not found'), [], 404);
        }

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteVehicle(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $limit = $this->resolveVehicleLimit((int) $tenant->id);
        if ($limit <= 0) {
            return $this->sendResponse(false, __('Vehicle management feature is not enabled for your package'), [], 403);
        }

        $item = $this->tenantVehicleRepository->findVehicle($id);
        if (!$item) {
            return $this->sendResponse(false, __('Vehicle not found'), [], 404);
        }

        $this->tenantVehicleRepository->delete($id);

        return $this->sendResponse(true, __('Vehicle deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function resolveVehicleLimit(int $tenantId): int
    {
        $maxFromNumeric = (int) $this->tenantFeatureResolverService->getValue($tenantId, 'vehicle.max_count', 0);
        if ($maxFromNumeric > 0) {
            return $maxFromNumeric;
        }

        $limits = [
            'vehicle.manage_1_5' => 5,
            'vehicle.manage_5_10' => 10,
            'vehicle.manage_10_20' => 20,
            'vehicle.manage_20_50' => 50,
        ];

        $resolved = 0;
        foreach ($limits as $featureKey => $limit) {
            if ($this->tenantFeatureResolverService->canUse($tenantId, $featureKey)) {
                $resolved = max($resolved, $limit);
            }
        }

        return $resolved;
    }
}
