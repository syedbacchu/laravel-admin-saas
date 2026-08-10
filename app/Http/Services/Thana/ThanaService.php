<?php

namespace App\Http\Services\Thana;

class ThanaService implements ThanaServiceInterface
{
    protected ThanaRepositoryInterface $repository;

    public function __construct(ThanaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        try {
            $data = $this->repository->all();
            return sendResponse(true, 'Thana list retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            logStore('Thana getAll', $e->getMessage());
            return sendResponse(false, 'Failed to retrieve thana list', [], 400);
        }
    }

    public function getById(int $id): array
    {
        try {
            $data = $this->repository->find($id);
            if (!$data) {
                return sendResponse(false, 'Thana not found', [], 404);
            }
            return sendResponse(true, 'Thana retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            logStore('Thana getById', $e->getMessage());
            return sendResponse(false, 'Failed to retrieve thana', [], 400);
        }
    }

    public function create(array $data): array
    {
        try {
            $thana = $this->repository->create($data);
            return sendResponse(true, 'Thana created successfully', $thana, 201);
        } catch (\Exception $e) {
            logStore('Thana create', $e->getMessage());
            return sendResponse(false, 'Failed to create thana', [], 400);
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $result = $this->repository->update($id, $data);
            if (!$result) {
                return sendResponse(false, 'Thana not found or update failed', [], 404);
            }
            return sendResponse(true, 'Thana updated successfully', [], 200);
        } catch (\Exception $e) {
            logStore('Thana update', $e->getMessage());
            return sendResponse(false, 'Failed to update thana', [], 400);
        }
    }

    public function delete(int $id): array
    {
        try {
            $result = $this->repository->delete($id);
            if (!$result) {
                return sendResponse(false, 'Thana not found or delete failed', [], 404);
            }
            return sendResponse(true, 'Thana deleted successfully', [], 200);
        } catch (\Exception $e) {
            logStore('Thana delete', $e->getMessage());
            return sendResponse(false, 'Failed to delete thana', [], 400);
        }
    }

    public function getDataTableData($request): array
    {
        try {
            return $this->repository->dataList($request);
        } catch (\Exception $e) {
            logStore('Thana getDataTableData', $e->getMessage());
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
                return sendResponse(false, 'Thana not found or status update failed', [], 404);
            }
            return sendResponse(true, 'Thana status updated successfully', [], 200);
        } catch (\Exception $e) {
            logStore('Thana toggleStatus', $e->getMessage());
            return sendResponse(false, 'Failed to update thana status', [], 400);
        }
    }
}
