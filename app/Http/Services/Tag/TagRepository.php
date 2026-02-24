<?php

namespace App\Http\Services\Tag;

use App\Http\Repositories\BaseRepository;
use App\Models\Tag;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    public function tagList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Tag::query(),
            searchable: [
                'name',
                'slug',
            ],
            select: [
                'id',
                'name',
                'slug',
                'created_at',
            ],
        );
    }

    public function createTag(array $data): Model
    {
        return $this->create($data);
    }
}
