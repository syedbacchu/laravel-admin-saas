<?php

namespace App\Http\Services\Slider;

use App\Http\Repositories\BaseRepository;
use App\Models\Slider;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SliderRepository extends BaseRepository implements SliderRepositoryInterface
{
    public function __construct(Slider $model)
    {
        parent::__construct($model);
    }

    public function dataList($request): array
    {
        return DataListManager::list(
            request: $request,
            query: Slider::query(),

            searchable: [
                'title',
                'offer',
            ],

            filters: [
                'published' => [
                    'column' => 'published'
                ],
                'type' => [
                    'column' => 'type'
                ],
            ],

            select: [
                'id',
                'photo',
                'position',
                'title',
                'subtitle',
                'offer',
                'published',
                'link',
                'type',
                'serial',
            ],
        );
    }

    public function createSlider(array $data): Model
    {
        return $this->create($data);
    }


}
