<?php

namespace App\Http\Services\PostCategory;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface PostCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function postCategoryList(Request $request): array;
    public function createPostCategory(array $data): Model;
}
