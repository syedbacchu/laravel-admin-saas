<?php

namespace App\Http\Services;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService implements BaseServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    protected function sendResponse(
        bool $success,
        string $message = "Invalid request",
        $data = [],
        int $status = 200,
        string $errorMessage = ""
    ) {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'status' => $status,
            'error_message' => $errorMessage,
        ];
    }

    public function getAll(array $columns = ['*']): Collection
    {
        return $this->repository->all($columns);
    }

    public function getById(int $id, array $columns = ['*']): Model
    {
        $model = $this->repository->find($id, $columns);
        if (!$model) {
            throw new \Exception("Record with ID {$id} not found");
        }
        return $model;
    }

    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $updated = $this->repository->update($id, $data);
        if (!$updated) {
            throw new \Exception("Failed to update record with ID {$id}");
        }
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        $deleted = $this->repository->delete($id);
        if (!$deleted) {
            throw new \Exception("Failed to delete record with ID {$id}");
        }
        return $deleted;
    }

    // Additional methods mirroring BaseRepository
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns);
    }

    public function where(string $column, $operator = null, $value = null, array $columns = ['*']): Collection
    {
        return $this->repository->where($column, $operator, $value, $columns);
    }

    public function whereFirst(string $column, $operator = null, $value = null, array $columns = ['*']): ?Model
    {
        return $this->repository->whereFirst($column, $operator, $value, $columns);
    }

    public function whereIn(string $column, array $values, array $columns = ['*']): Collection
    {
        return $this->repository->whereIn($column, $values, $columns);
    }

    public function count(): int
    {
        return $this->repository->count();
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function first(array $columns = ['*']): ?Model
    {
        return $this->repository->first($columns);
    }

    public function latest(string $column = 'created_at', array $columns = ['*']): Collection
    {
        return $this->repository->latest($column, $columns);
    }

    public function oldest(string $column = 'created_at', array $columns = ['*']): Collection
    {
        return $this->repository->oldest($column, $columns);
    }

    public function getWhereOrder(
        array $conditions = [],
        array $columns = ['*'],
        string $orderBy = 'id',
        string $direction = 'asc'
    ): Builder {
        return $this->repository->getWhereOrder($conditions, $columns, $orderBy, $direction);
    }

    public function updateWhere(array $conditions, array $data): int
    {
        return $this->repository->updateWhere($conditions, $data);
    }
}
