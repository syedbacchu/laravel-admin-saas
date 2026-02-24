<?php

namespace App\Http\Services\CustomField;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CustomFieldValueService
{
    public function sync(Model $model, array $values = [])
    {
        foreach ($values as $fieldId => $value) {
            // CHECKBOX / MULTI VALUE
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $model->customFieldValues()->updateOrCreate(
                [
                    'custom_field_id' => $fieldId,
                ],
                [
                    'value'      => $value,
                    'model_type' => get_class($model),
                    'model_id'   => $model->getKey(),
                ]
            );
        }
    }
}
