<?php

namespace App\Http\Services\Tag;

use App\Http\Requests\Post\TagCreateRequest;

interface TagServiceInterface
{
    public function getDataTableData($request): array;
    public function storeOrUpdateTag(TagCreateRequest $request): array;
    public function deleteTag($id): array;
    public function tagEditData($id): array;
    public function tagCreateData($request): array;
}
