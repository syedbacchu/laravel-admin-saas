<?php

namespace App\Http\Services\Post;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    public function postList(Request $request): array;
    public function createPost(array $data): Model;
    public function findPublicBlogByIdentifier(string $identifier): ?Post;
}
