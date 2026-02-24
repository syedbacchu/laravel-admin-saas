<?php

namespace App\Http\Services\Post;

use App\Http\Requests\Post\PostCreateRequest;
use Illuminate\Http\Request;

interface PostServiceInterface
{
    public function getDataTableData($request): array;
    public function storeOrUpdatePost(PostCreateRequest $request): array;
    public function deletePost($id): array;
    public function publishPost($id, $status): array;
    public function postEditData($id): array;
    public function postCreateData($request): array;
    public function getPublicBlogList(Request $request): array;
    public function getPublicBlogDetails(string $identifier): array;
}
