<?php

namespace App\Http\Services\District;

use App\Http\Repositories\BaseRepository;
use App\Models\District;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;

class DistrictRepository extends BaseRepository implements DistrictRepositoryInterface
{
    public function __construct(District $model)
    {
        parent::__construct($model);
    }

    public function dataList($request): array
    {
        return DataListManager::list(
            request: $request,
            query: District::query()->with('division'),

            searchable: [
                'name',
                'code',
            ],

            filters: [
                'status' => [
                    'column' => 'status'
                ],
                'division_code' => [
                    'column' => 'division_code'
                ],
            ],

            select: [
                'id',
                'code',
                'name',
                'status',
                'division_code',
                'created_at',
            ],
        );
    }
}