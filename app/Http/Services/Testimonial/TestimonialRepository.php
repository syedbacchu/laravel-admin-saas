<?php

namespace App\Http\Services\Testimonial;

use App\Http\Repositories\BaseRepository;
use App\Models\Testimonial;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TestimonialRepository extends BaseRepository implements TestimonialRepositoryInterface
{
    public function __construct(Testimonial $model)
    {
        parent::__construct($model);
    }

    public function dataCreate(array $data): Model
    {
        return $this->create($data);
    }

    public function dataList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Testimonial::query(),
            searchable: [
                'name',
                'review_text',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'site_type' => [
                    'column' => 'site_type',
                ],
            ],
            select: [
                'id',
                'name',
                'review_text',
                'review_star',
                'designation',
                'image',
                'sort_order',
                'status',
                'created_by',
                'updated_by',
                'site_type',
            ],
        );
    }
   public function findPublicByIdentifier(string $identifier): ?Testimonial
    {
        return Testimonial::query()
            ->where('status', 1)
            ->where(function ($query) use ($identifier) {
                $query->where('id', $identifier);
            })
            ->first();
    }
}
