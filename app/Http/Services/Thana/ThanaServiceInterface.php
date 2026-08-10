<?php

namespace App\Http\Services\Thana;

interface ThanaServiceInterface
{
    public function getAll();
    public function getById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function getDataTableData($request): array;
    public function getDataList($request): array;
    public function toggleStatus(int $id, int $status): array;
}
