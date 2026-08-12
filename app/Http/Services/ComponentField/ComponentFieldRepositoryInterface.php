<?php

namespace App\Http\Services\ComponentField;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\ComponentField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ComponentFieldRepositoryInterface extends BaseRepositoryInterface
{
    public function fieldList(Request $request, int $componentId): array;
    public function getComponentFields(int $componentId): Collection;
    public function getParentFields(int $componentId): Collection;
    public function createField(array $data): ComponentField;
    public function updateField(int $id, array $data): bool;
    public function updateSortOrder(array $fieldIds): bool;
    public function getRepeatableFields(int $componentId): Collection;
}