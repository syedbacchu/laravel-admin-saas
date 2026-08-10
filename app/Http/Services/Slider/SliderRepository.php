<?php

namespace App\Http\Services\Slider;

use App\Enums\StatusEnum;
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
                'subtitle',
            ],

            filters: [
                'status' => [
                    'column' => 'status'
                ],
                'type' => [
                    'column' => 'type'
                ],
                'site_type' => [
                    'column' => 'site_type'
                ],
            ],

            select: [
                'id',
                'photo',
                'title',
                'subtitle',
                'description',
                'tagline',
                'status',
                'link',
                'mobile_banner',
                'type',
                'serial',
                'video_link',
                'page',
                'cta_button',
                'stat',
            ],
        );
    }

    public function createSlider(array $data): Model
    {
        return $this->create($data);
    }

    public function findPublicByIdentifier(string $identifier): ?Slider
    {
        return Slider::query()
            ->where('status', enum(StatusEnum::ACTIVE->value))
            ->where(function ($query) use ($identifier) {
                $query->where('id', $identifier);
            })
            ->first();
    }

}
