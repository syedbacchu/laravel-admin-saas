<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepository;
use App\Models\Feature;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FeatureRepository extends BaseRepository implements FeatureRepositoryInterface
{
    public function __construct(Feature $model)
    {
        parent::__construct($model);
    }

    public function featureList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Feature::query(),
            searchable: [
                'key',
                'name',
            ],
            filters: [
                'value_type' => [
                    'column' => 'value_type',
                ],
                'is_active' => [
                    'column' => 'is_active',
                ],
            ],
            select: [
                'id',
                'key',
                'name',
                'value_type',
                'is_active',
                'created_at',
            ],
        );
    }

    public function createFeature(array $data): Model
    {
        return $this->create($data);
    }
}
