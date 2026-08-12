<?php

namespace App\Http\Services\ComponentField;

use App\Http\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComponentFieldService extends BaseService implements ComponentFieldServiceInterface
{
    protected ComponentFieldRepositoryInterface $componentFieldRepository;

    public function __construct(ComponentFieldRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->componentFieldRepository = $repository;
    }

    public function getDataTableData(Request $request, int $componentId): array
    {
        $data = $this->componentFieldRepository->fieldList($request, $componentId);
        return $this->sendResponse(true, __('Data retrieved successfully'), $data);
    }

    public function storeOrUpdateField(Request $request, int $componentId): array
    {
        DB::beginTransaction();

        try {
            $data = [
                'component_id' => $componentId,
                'parent_id' => $request->parent_id ?? null,
                'name' => $request->name,
                'label' => $request->label,
                'field_type' => $request->field_type,
                'is_required' => $request->is_required ?? false,
                'is_translatable' => $request->is_translatable ?? false,
                'sort_order' => $request->sort_order ?? 0,
                'config' => $request->config ?? null,
            ];

            // Handle field-specific configurations
            $config = [];
            switch ($request->field_type) {
                case 'repeatable':
                    $config['min_items'] = $request->min_items ?? 1;
                    $config['max_items'] = $request->max_items ?? null;
                    break;
                case 'number':
                    $config['min'] = $request->min ?? null;
                    $config['max'] = $request->max ?? null;
                    $config['step'] = $request->step ?? 1;
                    break;
                case 'select':
                case 'relation':
                    $config['options'] = $request->options ?? [];
                    break;
                case 'text':
                case 'textarea':
                    $config['max_length'] = $request->max_length ?? null;
                    $config['default'] = $request->default ?? null;
                    break;
                case 'image':
                case 'responsive_image':
                case 'file':
                case 'video':
                    $config['max_size'] = $request->max_size ?? null;
                    $config['allowed_types'] = $request->allowed_types ?? null;
                    break;
            }

            $data['config'] = !empty($config) ? $config : null;

            if ($request->edit_id) {
                $existingField = $this->componentFieldRepository->find($request->edit_id);
                if (!$existingField) {
                    DB::rollBack();
                    return $this->sendResponse(false, __('Field not found'));
                }

                $this->componentFieldRepository->updateField($existingField->id, $data);
                $field = $this->componentFieldRepository->find($existingField->id);
                DB::commit();
                return $this->sendResponse(true, __('Field updated successfully'), $field);
            } else {
                $field = $this->componentFieldRepository->createField($data);
                DB::commit();
                return $this->sendResponse(true, __('Field created successfully'), $field);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse(false, __('Error creating field: ') . $e->getMessage());
        }
    }

    public function deleteField(int $id): array
    {
        $field = $this->componentFieldRepository->find($id);
        if (!$field) {
            return $this->sendResponse(false, __('Field not found'));
        }

        // Check if field has children
        if ($field->children()->count() > 0) {
            return $this->sendResponse(false, __('Cannot delete field that has child fields. Delete child fields first.'));
        }

        $this->componentFieldRepository->delete($id);
        return $this->sendResponse(true, __('Field deleted successfully'));
    }

    public function getFieldsByComponent(int $componentId): Collection
    {
        return $this->componentFieldRepository->getComponentFields($componentId);
    }

    public function getParentFields(int $componentId): Collection
    {
        return $this->componentFieldRepository->getParentFields($componentId);
    }

    public function updateFieldSortOrder(Request $request): array
    {
        $fieldIds = $request->field_ids ?? [];

        if (empty($fieldIds)) {
            return $this->sendResponse(false, __('No field IDs provided'));
        }

        $result = $this->componentFieldRepository->updateSortOrder($fieldIds);

        if ($result) {
            return $this->sendResponse(true, __('Field sort order updated successfully'));
        } else {
            return $this->sendResponse(false, __('Failed to update field sort order'));
        }
    }

    public function getFieldDetail(int $id): ?Model
    {
        return $this->componentFieldRepository->find($id);
    }

    public function getRepeatableFields(int $componentId): Collection
    {
        return $this->componentFieldRepository->getRepeatableFields($componentId);
    }
}
