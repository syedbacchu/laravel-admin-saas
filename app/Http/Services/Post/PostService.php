<?php

namespace App\Http\Services\Post;

use App\Http\Requests\Post\PostCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostService extends BaseService implements PostServiceInterface
{
    protected PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->postRepository = $repository;
    }

    public function storeOrUpdatePost(PostCreateRequest $request): array
    {
        $editId = $request->edit_id;

        $data = [
            'title' => $request->title,
            'slug' => $this->generateUniqueSlug($request->slug ?: $request->title, $editId ? (int) $editId : null),
            'excerpt' => $request->excerpt,
            'content' => $request->content ?? '',
            'post_type' => $request->post_type ?: 'blog',
            'thumbnail_img' => $request->thumbnail_img,
            'featured_img' => $request->featured_img,
            'visibility' => $request->visibility ?? 1,
            'is_comment_allow' => $request->is_comment_allow ?? 1,
            'is_featured' => $request->is_featured ?? 0,
            'featured_order' => $request->featured_order ?? 0,
            'status' => $request->status ?? 'draft',
            'published_at' => $request->published_at ?: null,
            'serial' => $request->serial ?? 0,
            'event_date' => $request->event_date ?: null,
            'event_end_date' => $request->event_end_date ?: null,
            'venue' => $request->venue,
            'video_url' => $request->video_url,
            'photos' => $request->photos,
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
        ];

        if ($editId) {
            $item = $this->postRepository->find($editId);
            if (!$item) {
                return $this->sendResponse(false, __('Data not found'));
            }

            $this->postRepository->update($item->id, $data);
            $item = $this->postRepository->find($item->id);
            $message = __('Post updated successfully');
        } else {
            $data['author_id'] = auth()->id() ?? 1;
            $item = $this->postRepository->createPost($data);
            $message = __('Post created successfully');
        }

        $item->categories()->sync($request->input('category_ids', []));
        $item->tags()->sync($request->input('tag_ids', []));

        return $this->sendResponse(true, $message, $item->fresh(['categories', 'tags']));
    }

    public function deletePost($id): array
    {
        $item = $this->postRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $item->categories()->sync([]);
        $item->tags()->sync([]);
        $this->postRepository->delete($id);

        return $this->sendResponse(true, __('Data deleted successfully'));
    }

    public function publishPost($id, $status): array
    {
        $item = $this->postRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $newStatus = (int) $status === 1 ? 'published' : 'draft';
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'published' && !$item->published_at) {
            $updateData['published_at'] = now();
        }

        $this->postRepository->update($id, $updateData);
        return $this->sendResponse(true, __('Status updated successfully'));
    }

    public function getDataTableData($request): array
    {
        $data = $this->postRepository->postList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function postEditData($id): array
    {
        $item = $this->postRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item->load(['categories:id', 'tags:id']));
    }

    public function postCreateData($request): array
    {
        $categories = PostCategory::query()->where('status', 1)->orderBy('name')->get(['id', 'name']);
        $tags = Tag::query()->orderBy('name')->get(['id', 'name']);

        return $this->sendResponse(true, '', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function getPublicBlogList(Request $request): array
    {
        $request->merge(['post_type' => $request->post_type ?: 'blog', 'status' => $request->status ?? 'published']);
        $data = $this->postRepository->postList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function getPublicBlogDetails(string $identifier): array
    {
        $item = $this->postRepository->findPublicBlogByIdentifier($identifier);

        if (!$item) {
            return $this->sendResponse(false, __('Blog not found'), [], 404, __('Blog not found'));
        }

        Post::query()->whereKey($item->id)->increment('total_hit');
        $item->refresh();

        return $this->sendResponse(true, __('Blog details'), $item);
    }

    protected function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'post';

        if ($ignoreId) {
            $current = Post::query()->find($ignoreId, ['id', 'slug']);
            if ($current && $current->slug === $base) {
                return $base;
            }
        }

        return make_unique_slug($base, 'posts');
    }
}
