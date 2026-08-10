<?php

namespace App\Http\Services\District;

class DistrictService implements DistrictServiceInterface
{
    protected DistrictRepositoryInterface $repository;

    public function __construct(DistrictRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        try {
            $data = $this->repository->all();
            return sendResponse(true, 'District list retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            logStore('District getAll', $e->getMessage());
            return sendResponse(false, 'Failed to retrieve district list', [], 400);
        }
    }

    public function getById(int $id): array
    {
        try {
            $data = $this->repository->find($id);
            if (!$data) {
                return sendResponse(false, 'District not found', [], 404);
            }
            return sendResponse(true, 'District retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            logStore('District getById', $e->getMessage());
            return sendResponse(false, 'Failed to retrieve district', [], 400);
        }
    }

    public function create(array $data): array
    {
        try {
            $district = $this->repository->create($data);
            return sendResponse(true, 'District created successfully', $district, 201);
        } catch (\Exception $e) {
            logStore('District create', $e->getMessage());
            return sendResponse(false, 'Failed to create district', [], 400);
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $result = $this->repository->update($id, $data);
            if (!$result) {
                return sendResponse(false, 'District not found or update failed', [], 404);
            }
            return sendResponse(true, 'District updated successfully', [], 200);
        } catch (\Exception $e) {
            logStore('District update', $e->getMessage());
            return sendResponse(false, 'Failed to update district', [], 400);
        }
    }

    public function delete(int $id): array
    {
        try {
            $result = $this->repository->delete($id);
            if (!$result) {
                return sendResponse(false, 'District not found or delete failed', [], 404);
            }
            return sendResponse(true, 'District deleted successfully', [], 200);
        } catch (\Exception $e) {
            logStore('District delete', $e->getMessage());
            return sendResponse(false, 'Failed to delete district', [], 400);
        }
    }

    public function getDataTableData($request): array
    {
        try {
            return $this->repository->dataList($request);
        } catch (\Exception $e) {
            logStore('District getDataTableData', $e->getMessage());
            return sendResponse(false, 'Failed to get table data', [], 400);
        }
    }

    public function getDataList($request): array
    {
        $data = $this->repository->dataList($request);
        return sendResponse(true, 'Court type retrieved successfully', $data, 200);
    }

    public function toggleStatus(int $id, int $status): array
    {
        try {
            $result = $this->repository->update($id, ['status' => $status]);
            if (!$result) {
                return sendResponse(false, 'District not found or status update failed', [], 404);
            }
            return sendResponse(true, 'District status updated successfully', [], 200);
        } catch (\Exception $e) {
            logStore('District toggleStatus', $e->getMessage());
            return sendResponse(false, 'Failed to update district status', [], 400);
        }
    }
}
