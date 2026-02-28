<?php

namespace App\Http\Services\Tenant;

use App\Http\Requests\Tenant\TenantCreateRequest;
use App\Http\Services\BaseService;

class TenantService extends BaseService implements TenantServiceInterface
{
    protected TenantRepositoryInterface $tenantRepository;

    protected TenantProvisionServiceInterface $tenantProvisionService;

    public function __construct(
        TenantRepositoryInterface $repository,
        TenantProvisionServiceInterface $tenantProvisionService
    ) {
        parent::__construct($repository);
        $this->tenantRepository = $repository;
        $this->tenantProvisionService = $tenantProvisionService;
    }

    public function getDataTableData($request): array
    {
        $data = $this->tenantRepository->tenantList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeOrUpdateTenant(TenantCreateRequest $request): array
    {
        if ($request->edit_id) {
            return $this->sendResponse(false, __('Tenant update is not implemented yet'));
        }

        return $this->tenantProvisionService->provision($request->validated());
    }

    public function tenantCreateData($request): array
    {
        return $this->sendResponse(true, '', []);
    }
}
