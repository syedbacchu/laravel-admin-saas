<?php
namespace App\Http\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->get();
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->find($id);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->select($columns)->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->find($id);
        if ($record) {
            return $record->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $record = $this->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    // Additional common methods for enterprise usage
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->select($columns)->paginate($perPage);
    }

    public function where(string $column, $operator = null, $value = null, array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->where($column, $operator, $value)->get();
    }

    public function whereFirst(string $column, $operator = null, $value = null, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->where($column, $operator, $value)->first();
    }

    public function whereIn(string $column, array $values, array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->whereIn($column, $values)->get();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    public function first(array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->first();
    }

    public function latest(string $column = 'created_at', array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->latest($column)->get();
    }

    public function oldest(string $column = 'created_at', array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->oldest($column)->get();
    }

    public function getWhereOrder(
        array $conditions = [],
        array $columns = ['*'],
        string $orderBy = 'id',
        string $direction = 'asc'
    ): Builder {
        $query = $this->model->select($columns);

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Example: ['status' => ['!=', 1]]
                $query->where($column, $value[0], $value[1]);
            } else {
                // Example: ['status' => 1]
                $query->where($column, $value);
            }
        }

        return $query->orderBy($orderBy, $direction);
    }

    public function updateWhere(array $conditions, array $data): int
    {
        $query = $this->model;

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Example: ['status' => ['!=', 1]]
                $query = $query->where($column, $value[0], $value[1]);
            } else {
                $query = $query->where($column, $value);
            }
        }

        return $query->update($data);
    }
}
