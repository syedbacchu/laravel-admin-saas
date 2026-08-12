<?php

namespace App\Http\Services\ComponentField;

use App\Http\Repositories\BaseRepository;
use App\Models\ComponentField;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComponentFieldRepository extends BaseRepository implements ComponentFieldRepositoryInterface
{
    public function __construct(ComponentField $model)
    {
        parent::__construct($model);
    }

    public function fieldList(Request $request, int $componentId): array
    {
        $query = ComponentField::query()
            ->where('component_id', $componentId)
            ->with(['parent:id,name,label', 'children' => function ($query) {
                $query->orderBy('sort_order');
            }]);

        return DataListManager::list(
            request: $request,
            query: $query,
            searchable: [
                'name',
                'label',
                'field_type',
            ],
            filters: [
                'field_type' => [
                    'column' => 'field_type',
                ],
                'is_required' => [
                    'column' => 'is_required',
                ],
                'is_translatable' => [
                    'column' => 'is_translatable',
                ],
            ],
            select: [
                'id',
                'component_id',
                'parent_id',
                'name',
                'label',
                'field_type',
                'is_required',
                'is_translatable',
                'sort_order',
                'config',
                'created_at',
            ],
        );
    }

    public function getComponentFields(int $componentId): Collection
    {
        return ComponentField::where('component_id', $componentId)
            ->with(['parent', 'children'])
            ->orderBy('sort_order')
            ->get();
    }

    public function getParentFields(int $componentId): Collection
    {
        return ComponentField::where('component_id', $componentId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    public function createField(array $data): ComponentField
    {
        return $this->create($data);
    }

    public function updateField(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function updateSortOrder(array $fieldIds): bool
    {
        try {
            DB::beginTransaction();

            foreach ($fieldIds as $index => $fieldId) {
                ComponentField::where('id', $fieldId)->update([
                    'sort_order' => $index + 1
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getRepeatableFields(int $componentId): Collection
    {
        return ComponentField::where('component_id', $componentId)
            ->where('field_type', 'repeatable')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }
}