<?php

namespace App\Http\Services\Tag;

use App\Http\Requests\Post\TagCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagService extends BaseService implements TagServiceInterface
{
    protected TagRepositoryInterface $tagRepository;

    public function __construct(TagRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tagRepository = $repository;
    }

    public function storeOrUpdateTag(TagCreateRequest $request): array
    {
        $editId = $request->edit_id;

        $data = [
            'name' => $request->name,
            'slug' => $this->generateUniqueSlug($request->slug ?: $request->name, $editId ? (int) $editId : null),
        ];

        if ($editId) {
            $item = $this->tagRepository->find($editId);
            if (!$item) {
                return $this->sendResponse(false, __('Data not found'));
            }

            $this->tagRepository->update($item->id, $data);
            return $this->sendResponse(true, __('Tag updated successfully'));
        }

        $data['added_by'] = auth()->id() ?? 1;
        $item = $this->tagRepository->createTag($data);
        return $this->sendResponse(true, __('Tag created successfully'), $item);
    }

    public function deleteTag($id): array
    {
        $item = $this->tagRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->tagRepository->delete($id);
        return $this->sendResponse(true, __('Data deleted successfully'));
    }

    public function getDataTableData($request): array
    {
        $data = $this->tagRepository->tagList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function tagEditData($id): array
    {
        $item = $this->tagRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function tagCreateData($request): array
    {
        return $this->sendResponse(true, '', []);
    }

    protected function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'tag';

        if ($ignoreId) {
            $current = Tag::query()->find($ignoreId, ['id', 'slug']);
            if ($current && $current->slug === $base) {
                return $base;
            }
        }

        return make_unique_slug($base, 'tags');
    }
}
