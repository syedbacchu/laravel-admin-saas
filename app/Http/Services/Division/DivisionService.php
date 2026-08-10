<?php

namespace App\Http\Services\Division;

class DivisionService implements DivisionServiceInterface
{
    protected DivisionRepositoryInterface $repository;

    public function __construct(DivisionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        try {
            $data = $this->repository->all();
            return sendResponse(true, 'Division list retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            logStore('Division getAll', $e->getMessage());
            return sendResponse(false, 'Failed to retrieve division list', [], 400);
        }
    }

    public function getById(int $id): array
    {
        try {
            $data = $this->repository->find($id);
            if (!$data) {
                return sendResponse(false, 'Division not found', [], 404);
            }
            return sendResponse(true, 'Division retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            logStore('Division getById', $e->getMessage());
            return sendResponse(false, 'Failed to retrieve division', [], 400);
        }
    }

    public function create(array $data): array
    {
        try {
            $division = $this->repository->create($data);
            return sendResponse(true, 'Division created successfully', $division, 201);
        } catch (\Exception $e) {
            logStore('Division create', $e->getMessage());
            return sendResponse(false, 'Failed to create division', [], 400);
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $result = $this->repository->update($id, $data);
            if (!$result) {
                return sendResponse(false, 'Division not found or update failed', [], 404);
            }
            return sendResponse(true, 'Division updated successfully', [], 200);
        } catch (\Exception $e) {
            logStore('Division update', $e->getMessage());
            return sendResponse(false, 'Failed to update division', [], 400);
        }
    }

    public function delete(int $id): array
    {
        try {
            $result = $this->repository->delete($id);
            if (!$result) {
                return sendResponse(false, 'Division not found or delete failed', [], 404);
            }
            return sendResponse(true, 'Division deleted successfully', [], 200);
        } catch (\Exception $e) {
            logStore('Division delete', $e->getMessage());
            return sendResponse(false, 'Failed to delete division', [], 400);
        }
    }

    public function getDataTableData($request): array
    {
        try {
            return $this->repository->dataList($request);
        } catch (\Exception $e) {
            logStore('Division getDataTableData', $e->getMessage());
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
                return sendResponse(false, 'Division not found or status update failed', [], 404);
            }
            return sendResponse(true, 'Division status updated successfully', [], 200);
        } catch (\Exception $e) {
            logStore('Division toggleStatus', $e->getMessage());
            return sendResponse(false, 'Failed to update division status', [], 400);
        }
    }
}
