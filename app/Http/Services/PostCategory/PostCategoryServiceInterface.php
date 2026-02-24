<?php

namespace App\Http\Services\PostCategory;

use App\Http\Requests\Post\PostCategoryCreateRequest;

interface PostCategoryServiceInterface
{
    public function getDataTableData($request): array;
    public function storeOrUpdatePostCategory(PostCategoryCreateRequest $request): array;
    public function deletePostCategory($id): array;
    public function publishPostCategory($id, $status): array;
    public function postCategoryEditData($id): array;
    public function postCategoryCreateData($request): array;
}
