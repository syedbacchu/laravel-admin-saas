<?php

namespace App\Http\Services\TenantOffice;

use App\Http\Requests\TenantApi\TenantOfficeCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Throwable;

class TenantOfficeService extends BaseService implements TenantOfficeServiceInterface
{
    protected TenantOfficeRepositoryInterface $tenantOfficeRepository;

    public function __construct(TenantOfficeRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantOfficeRepository = $repository;
    }

    public function officeList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantOfficeRepository->officeList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeOffice(TenantOfficeCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $data = [
                'branch_name' => (string) $request->branch_name,
                'opening_balance' => $request->opening_balance,
                'address' => (string) $request->address,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantOfficeRepository->findOffice((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Office not found'), [], 404);
                }

                $this->tenantOfficeRepository->update((int) $item->id, $data);
                $item = $this->tenantOfficeRepository->findOffice((int) $item->id);

                return $this->sendResponse(true, __('Office updated successfully'), $item);
            }

            $item = $this->tenantOfficeRepository->createOffice($data);

            return $this->sendResponse(
                true,
                __('Office created successfully'),
                $this->tenantOfficeRepository->findOffice((int) $item->id)
            );
        } catch (Throwable $e) {
            logStore('TenantOfficeService storeOffice', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function officeDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantOfficeRepository->findOffice($id);
        if (!$item) {
            return $this->sendResponse(false, __('Office not found'), [], 404);
        }

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteOffice(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantOfficeRepository->findOffice($id);
        if (!$item) {
            return $this->sendResponse(false, __('Office not found'), [], 404);
        }

        $this->tenantOfficeRepository->delete($id);

        return $this->sendResponse(true, __('Office deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }
}

