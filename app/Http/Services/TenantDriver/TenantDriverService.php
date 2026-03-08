<?php

namespace App\Http\Services\TenantDriver;

use App\Http\Requests\TenantApi\TenantDriverCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use Illuminate\Http\Request;
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

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeDriver(TenantDriverCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

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

                $this->tenantDriverRepository->update((int) $item->id, $data);
                $item = $this->tenantDriverRepository->findDriver((int) $item->id);

                return $this->sendResponse(true, __('Driver updated successfully'), $item);
            }

            $item = $this->tenantDriverRepository->createDriver($data);

            return $this->sendResponse(true, __('Driver created successfully'), $this->tenantDriverRepository->findDriver((int) $item->id));
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

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }
}
