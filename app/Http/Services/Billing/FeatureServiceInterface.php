<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\FeatureCreateRequest;

interface FeatureServiceInterface
{
    public function getDataTableData($request): array;
    public function featureCreateData($request): array;
    public function storeOrUpdateFeature(FeatureCreateRequest $request): array;
    public function deleteFeature($id): array;
    public function featureEditData($id): array;
}
