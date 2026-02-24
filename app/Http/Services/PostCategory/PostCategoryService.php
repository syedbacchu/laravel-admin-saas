<?php

namespace App\Http\Services\PostCategory;

use App\Enums\StatusEnum;
use App\Http\Requests\Post\PostCategoryCreateRequest;
use App\Http\Services\BaseService;
use App\Models\PostCategory;
use Illuminate\Support\Str;

class PostCategoryService extends BaseService implements PostCategoryServiceInterface
{
    protected PostCategoryRepositoryInterface $postCategoryRepository;

    public function __construct(PostCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->postCategoryRepository = $repository;
    }

    public function storeOrUpdatePostCategory(PostCategoryCreateRequest $request): array
    {
        $editId = $request->edit_id;

        if ($editId && (int) $request->parent_id === (int) $editId) {
            return $this->sendResponse(false, __('Category cannot be parent of itself'));
        }

        $data = [
            'parent_id' => $request->parent_id ?: null,
            'name' => $request->name,
            'slug' => $this->generateUniqueSlug($request->slug ?: $request->name, $editId ? (int) $editId : null),
            'image' => $request->image ?: null,
            'meta_title' => $request->meta_title ?: null,
            'meta_description' => $request->meta_description ?: null,
            'meta_keywords' => $request->meta_keywords ?: null,
            'serial' => $request->serial ?? 0,
            'status' => $request->status ?? StatusEnum::ACTIVE,
        ];

        if ($editId) {
            $item = $this->postCategoryRepository->find($editId);
            if (!$item) {
                return $this->sendResponse(false, __('Data not found'));
            }

            $this->postCategoryRepository->update($item->id, $data);
            return $this->sendResponse(true, __('Post category updated successfully'));
        }

        $data['added_by'] = auth()->id() ?? 1;
        $item = $this->postCategoryRepository->createPostCategory($data);

        return $this->sendResponse(true, __('Post category created successfully'), $item);
    }

    public function deletePostCategory($id): array
    {
        $item = $this->postCategoryRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->postCategoryRepository->delete($id);
        return $this->sendResponse(true, __('Data deleted successfully'));
    }

    public function publishPostCategory($id, $status): array
    {
        $item = $this->postCategoryRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->postCategoryRepository->update($id, ['status' => $status]);
        return $this->sendResponse(true, __('Status updated successfully'));
    }

    public function getDataTableData($request): array
    {
        $data = $this->postCategoryRepository->postCategoryList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function postCategoryEditData($id): array
    {
        $item = $this->postCategoryRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function postCategoryCreateData($request): array
    {
        $id = $request->id ?? null;
        $parents = PostCategory::query()
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return $this->sendResponse(true, '', [
            'parents' => $parents,
        ]);
    }

    protected function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'post-category';

        if ($ignoreId) {
            $current = PostCategory::query()->find($ignoreId, ['id', 'slug']);
            if ($current && $current->slug === $base) {
                return $base;
            }
        }

        return make_unique_slug($base, 'post_categories');
    }
}
