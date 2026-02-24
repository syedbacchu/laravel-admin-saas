<?php

namespace App\Http\Services\PostComment;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface PostCommentRepositoryInterface extends BaseRepositoryInterface
{
    public function publicCommentList(Request $request, int $postId): array;
    public function adminCommentList(Request $request): array;
    public function createComment(array $data): Model;
}
