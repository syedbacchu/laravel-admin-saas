<?php

namespace App\Http\Services\Audit;

use App\Http\Services\BaseService;

class AuditService extends BaseService implements AuditServiceInterface
{

    protected AuditRepositoryInterface $auditRepository;

    public function __construct(AuditRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->auditRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->auditRepository->getDataTableQuery($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function deleteData($id): mixed
    {
        $item = $this->auditRepository->find($id);
        if ($item) {
            $this->delete($item->id);
            return $this->sendResponse(true,__('Audit log deleted successfully'));
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }

    public function detailsData($id): array
    {
        $item = $this->auditRepository->find($id);
        if ($item) {
            return $this->sendResponse(true,__('Data get successfully'),$item);
        } else {
            return $this->sendResponse(false,__('Data not found'));
        }
    }

}
