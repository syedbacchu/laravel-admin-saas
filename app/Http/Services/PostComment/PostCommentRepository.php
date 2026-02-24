<?php

namespace App\Http\Services\PostComment;

use App\Http\Repositories\BaseRepository;
use App\Models\PostComment;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PostCommentRepository extends BaseRepository implements PostCommentRepositoryInterface
{
    public function __construct(PostComment $model)
    {
        parent::__construct($model);
    }

    public function publicCommentList(Request $request, int $postId): array
    {
        return DataListManager::list(
            request: $request,
            query: PostComment::query()
                ->with(['user:id,name,image', 'replies'])
                ->where('post_id', $postId)
                ->whereNull('parent_id')
                ->where('status', 1)
                ->where('visibility', 1),
            searchable: [
                'comment',
                'name',
                'email',
            ],
            select: [
                'id',
                'post_id',
                'user_id',
                'parent_id',
                'name',
                'email',
                'website',
                'comment',
                'status',
                'visibility',
                'likes_count',
                'created_at',
            ],
            config: [
                'per_page' => 10,
            ]
        );
    }

    public function adminCommentList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: PostComment::query()->with([
                'post:id,title,slug',
                'user:id,name,image',
                'parent:id,comment',
            ]),
            searchable: [
                'comment',
                'name',
                'email',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'post_id' => [
                    'column' => 'post_id',
                ],
            ],
            select: [
                'id',
                'post_id',
                'user_id',
                'parent_id',
                'name',
                'email',
                'website',
                'comment',
                'status',
                'visibility',
                'likes_count',
                'created_at',
            ],
        );
    }

    public function createComment(array $data): Model
    {
        return $this->create($data);
    }
}
