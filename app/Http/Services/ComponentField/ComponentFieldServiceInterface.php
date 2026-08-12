<?php

namespace App\Http\Services\ComponentField;

use App\Http\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ComponentFieldServiceInterface extends BaseServiceInterface
{
    public function getDataTableData(Request $request, int $componentId): array;
    public function storeOrUpdateField(Request $request, int $componentId): array;
    public function deleteField(int $id): array;
    public function getFieldsByComponent(int $componentId): Collection;
    public function getParentFields(int $componentId): Collection;
    public function updateFieldSortOrder(Request $request): array;
    public function getFieldDetail(int $id): ?Model;
    public function getRepeatableFields(int $componentId): Collection;
}