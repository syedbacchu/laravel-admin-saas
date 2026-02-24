<?php

namespace App\Http\Services\CustomField;

use App\Http\Repositories\BaseRepository;
use App\Models\CustomField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CustomFieldRepository extends BaseRepository implements CustomFieldRepositoryInterface
{
    public function __construct(CustomField $model)
    {
        parent::__construct($model);
    }
    public function findData(int $id): Model {
        return $this->find($id);
    }

    public function getByModuleData(string $module): Collection {
        return $this->model
            ->where('module', $module)
            ->orderBy('sort_order')
            ->get();
    }

    public function getValueByModule(string $module, ?string $showIn = null)
    {
        $query = $this->model->where('module', $module)
            ->where('status', true)
            ->orderBy('sort_order');

        if ($showIn) {
            $query->whereJsonContains('show_in', $showIn);
        }

        return $query->get();
    }

    public function saveValues(Model $model, array $values): void
    {
        foreach ($values as $name => $value) {
            $model->setCustomFieldValue($name, $value);
        }
    }

    public function validationRules(string $module): array
    {
        $rules = [];

        $fields = $this->model->where('module', $module)
            ->where('status', true)
            ->get();

        foreach ($fields as $field) {
            $r = [];

            if ($field->is_required) {
                $r[] = 'required';
            }

            if ($field->validation_rules) {
                $r[] = $field->validation_rules;
            }

            if ($r) {
                $rules["custom_fields.{$field->name}"] = implode('|', $r);
            }
        }

        return $rules;
    }

    public function getValueByModuleContext(string $module, string $context = null)
    {
        return $this->model->where('module', $module)
        ->where('status', true)
        ->whereJsonContains('show_in', $context)
        ->orderBy('sort_order')
        ->get();
    }

}
