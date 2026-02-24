<?php

namespace App\Http\Services;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseServiceInterface
{
    public function getAll(array $columns = ['*']): Collection;
    public function getById(int $id, array $columns = ['*']): Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): Model;
    public function delete(int $id): bool;

    // Additional methods
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
    public function where(string $column, $operator = null, $value = null, array $columns = ['*']): Collection;
    public function whereFirst(string $column, $operator = null, $value = null, array $columns = ['*']): ?Model;
    public function whereIn(string $column, array $values, array $columns = ['*']): Collection;
    public function count(): int;
    public function exists(int $id): bool;
    public function first(array $columns = ['*']): ?Model;
    public function latest(string $column = 'created_at', array $columns = ['*']): Collection;
    public function oldest(string $column = 'created_at', array $columns = ['*']): Collection;
    public function getWhereOrder(array $conditions = [], array $columns = ['*'], string $orderBy = 'id',string $direction = 'asc' ): Builder;
    public function updateWhere(array $conditions, array $data): int;
}
