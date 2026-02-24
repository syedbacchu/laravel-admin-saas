<?php

namespace App\Http\Services\CustomField;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface CustomFieldRepositoryInterface extends BaseRepositoryInterface
{
    public function findData(int $id): Model;
    public function getByModuleData(string $module): Collection;
    public function getValueByModule(string $module, ?string $showIn = null);
    public function getValueByModuleContext(string $module, string $context = null);
    public function saveValues(Model $model, array $values): void;
    public function validationRules(string $module): array;
}
