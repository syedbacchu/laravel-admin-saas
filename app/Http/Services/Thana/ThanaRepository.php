<?php

namespace App\Http\Services\Thana;

use App\Http\Repositories\BaseRepository;
use App\Models\Thana;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;

class ThanaRepository extends BaseRepository implements ThanaRepositoryInterface
{
    public function __construct(Thana $model)
    {
        parent::__construct($model);
    }

    public function dataList($request): array
    {
        return DataListManager::list(
            request: $request,
            query: Thana::query()->with('district.division'),

            searchable: [
                'name',
                'code',
            ],

            filters: [
                'status' => [
                    'column' => 'status'
                ],
                'district_code' => [
                    'column' => 'district_code'
                ],
            ],

            select: [
                'id',
                'code',
                'name',
                'status',
                'district_code',
                'created_at',
            ],
        );
    }
}