<?php

namespace App\Http\Services\CustomField;

use App\Http\Requests\CustomField\CustomFieldCreateRequest;
use App\Http\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;

interface CustomFieldServiceInterface extends BaseServiceInterface
{
    public function render(?Model $model = null): string;
    public function storeOrUpdateItem(CustomFieldCreateRequest $request): array;
    public function deleteItem($id): array; // For delete
    public function getByModule(string $module): array;
    public function getModuleData(): array;
    public function autoFields(?string $modelType = null);
    public function fieldsWithValues(?Model $model = null, ?string $modelType = null);
    public function normalizeValue($field, $value);
}
