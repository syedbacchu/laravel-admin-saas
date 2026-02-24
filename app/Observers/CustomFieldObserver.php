<?php

namespace App\Observers;

use App\Http\Services\CustomField\CustomFieldValueService;
use Illuminate\Database\Eloquent\Model;

class CustomFieldObserver
{
    public function saved(Model $model)
    {
        if (!request()->has('custom_fields')) return;

        app(CustomFieldValueService::class)
            ->sync($model, request()->input('custom_fields', []) + request()->file('custom_fields', []));
    }

}
