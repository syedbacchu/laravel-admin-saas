<?php

namespace App\Http\Services\PostCategory;

use App\Http\Repositories\BaseRepository;
use App\Models\PostCategory;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PostCategoryRepository extends BaseRepository implements PostCategoryRepositoryInterface
{
    public function __construct(PostCategory $model)
    {
        parent::__construct($model);
    }

    public function postCategoryList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: PostCategory::query()->with('parent:id,name'),
            searchable: [
                'name',
                'slug',
                'meta_title',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
            ],
            select: [
                'id',
                'parent_id',
                'name',
                'slug',
                'serial',
                'status',
                'created_at',
            ],
        );
    }

    public function createPostCategory(array $data): Model
    {
        return $this->create($data);
    }
}
