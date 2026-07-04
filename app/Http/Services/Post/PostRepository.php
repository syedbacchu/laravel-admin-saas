<?php

namespace App\Http\Services\Post;

use App\Http\Repositories\BaseRepository;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function postList(Request $request): array
    {
        $query = Post::query()->with([
            'author:id,name',
            'categories:id,name,slug',
            'tags:id,name,slug',
        ]);

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($categoryQuery) use ($request) {
                $categoryQuery->where('post_categories.id', $request->category_id);
            });
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($tagQuery) use ($request) {
                $tagQuery->where('tags.id', $request->tag_id);
            });
        }

        return DataListManager::list(
            request: $request,
            query: $query,
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
                'site_type',
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

    public function getSimilarPublicBlogs(Post $post, int $limit = 6): Collection
    {
        $categoryIds = $post->categories->pluck('id')->all();

        $query = Post::query()
            ->with(['author:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->where('post_type', 'blog')
            ->where('status', 'published')
            ->whereKeyNot($post->id);

        if (!empty($categoryIds)) {
            $query->whereHas('categories', function ($categoryQuery) use ($categoryIds) {
                $categoryQuery->whereIn('post_categories.id', $categoryIds);
            })->withCount([
                'categories as shared_categories_count' => function ($categoryQuery) use ($categoryIds) {
                    $categoryQuery->whereIn('post_categories.id', $categoryIds);
                },
            ])->orderByDesc('shared_categories_count');
        }

        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getLatestPublicBlogs(int $limit = 6): Collection
    {
        return Post::query()
            ->with(['author:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->where('post_type', 'blog')
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getPublicBlogCategoriesWithCount(): Collection
    {
        return PostCategory::query()
            ->where('status', 1)
            ->withCount([
                'posts as blogs_count' => function ($query) {
                    $query->where('post_type', 'blog')
                        ->where('status', 'published');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'status']);
    }
}
