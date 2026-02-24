<?php

namespace App\Http\Services\Post;

use App\Http\Repositories\BaseRepository;
use App\Models\Post;
use App\Support\DataListManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function postList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Post::query()->with([
                'author:id,name',
                'categories:id,name,slug',
                'tags:id,name,slug',
            ]),
            searchable: [
                'title',
                'slug',
                'post_type',
                'excerpt',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'post_type' => [
                    'column' => 'post_type',
                ],
            ],
            select: [
                'id',
                'author_id',
                'title',
                'slug',
                'excerpt',
                'content',
                'thumbnail_img',
                'featured_img',
                'post_type',
                'status',
                'visibility',
                'is_comment_allow',
                'published_at',
                'created_at',
            ],
        );
    }

    public function createPost(array $data): Model
    {
        return $this->create($data);
    }

    public function findPublicBlogByIdentifier(string $identifier): ?Post
    {
        return Post::query()
            ->with(['author:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->where('post_type', 'blog')
            ->where('status', 'published')
            ->where(function ($query) use ($identifier) {
                $query->where('slug', $identifier);

                if (is_numeric($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }
            })
            ->first();
    }
}
