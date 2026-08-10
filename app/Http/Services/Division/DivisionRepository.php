<?php

namespace App\Http\Services\Division;

use App\Http\Repositories\BaseRepository;
use App\Models\Division;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;

class DivisionRepository extends BaseRepository implements DivisionRepositoryInterface
{
    public function __construct(Division $model)
    {
        parent::__construct($model);
    }

    public function dataList($request): array
    {
        return DataListManager::list(
            request: $request,
            query: Division::query(),

            searchable: [
                'name',
                'code',
            ],

            filters: [
                'status' => [
                    'column' => 'status'
                ],
            ],

            select: [
                'id',
                'code',
                'name',
                'status',
                'created_at',
            ],
        );
    }
}