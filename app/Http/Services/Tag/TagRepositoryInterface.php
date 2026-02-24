<?php

namespace App\Http\Services\Tag;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TagRepositoryInterface extends BaseRepositoryInterface
{
    public function tagList(Request $request): array;
    public function createTag(array $data): Model;
}
