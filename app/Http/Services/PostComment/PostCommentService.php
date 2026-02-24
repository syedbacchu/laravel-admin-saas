<?php

namespace App\Http\Services\PostComment;

use App\Http\Requests\Post\PostCommentCreateRequest;
use App\Http\Requests\Post\PostCommentReplyRequest;
use App\Http\Services\BaseService;
use App\Http\Services\Post\PostRepositoryInterface;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;

class PostCommentService extends BaseService implements PostCommentServiceInterface
{
    protected PostCommentRepositoryInterface $commentRepository;
    protected PostRepositoryInterface $postRepository;

    public function __construct(
        PostCommentRepositoryInterface $repository,
        PostRepositoryInterface $postRepository
    ) {
        parent::__construct($repository);
        $this->commentRepository = $repository;
        $this->postRepository = $postRepository;
    }

    public function getPublicCommentList(Request $request, string $identifier): array
    {
        $post = $this->postRepository->findPublicBlogByIdentifier($identifier);
        if (!$post) {
            return $this->sendResponse(false, __('Blog not found'), [], 404, __('Blog not found'));
        }

        $data = $this->commentRepository->publicCommentList($request, $post->id);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storePublicComment(PostCommentCreateRequest $request, string $identifier): array
    {
        $post = $this->postRepository->findPublicBlogByIdentifier($identifier);
        if (!$post) {
            return $this->sendResponse(false, __('Blog not found'), [], 404, __('Blog not found'));
        }

        if (!(bool) $post->is_comment_allow) {
            return $this->sendResponse(false, __('Comment is disabled for this post'), [], 403, __('Comment is disabled for this post'));
        }

        $parentId = $request->parent_id ? (int) $request->parent_id : null;
        if ($parentId) {
            $parent = PostComment::query()
                ->where('id', $parentId)
                ->where('post_id', $post->id)
                ->first();

            if (!$parent) {
                return $this->sendResponse(false, __('Invalid parent comment'), [], 422, __('Invalid parent comment'));
            }
        }

        $apiUser = auth('api')->user();
        $webUser = auth()->user();
        $user = $apiUser ?: $webUser;

        $data = [
            'post_id' => $post->id,
            'user_id' => $user?->id,
            'name' => $user?->name ?: $request->name,
            'email' => $user?->email ?: $request->email,
            'website' => $request->website,
            'comment' => $request->comment,
            'parent_id' => $parentId,
            'status' => 0,
            'visibility' => 1,
            'ip_address' => $request->ip(),
        ];

        $item = $this->commentRepository->createComment($data);

        return $this->sendResponse(
            true,
            __('Comment submitted successfully. Waiting for approval.'),
            $item->load(['user:id,name,image', 'replies'])
        );
    }

    public function getAdminCommentList(Request $request): array
    {
        $data = $this->commentRepository->adminCommentList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function getCommentForReply(int $id): array
    {
        $item = $this->commentRepository->find($id);
        if (!$item) {
            return $this->sendResponse(false, __('Comment not found'));
        }

        return $this->sendResponse(true, __('Data get successfully.'), $item->load(['post:id,title,slug', 'user:id,name,image']));
    }

    public function adminReplyToComment(PostCommentReplyRequest $request, int $id): array
    {
        $parent = $this->commentRepository->find($id);
        if (!$parent) {
            return $this->sendResponse(false, __('Comment not found'));
        }

        $user = auth()->user();
        $reply = $this->commentRepository->createComment([
            'post_id' => $parent->post_id,
            'user_id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'comment' => $request->comment,
            'parent_id' => $parent->id,
            'status' => 1,
            'visibility' => 1,
            'ip_address' => $request->ip(),
        ]);

        $this->syncCommentsCount($parent->post_id);

        return $this->sendResponse(true, __('Reply submitted successfully'), $reply->load(['user:id,name,image']));
    }

    public function approveComment(int $id): array
    {
        return $this->updateCommentStatus($id, 1);
    }

    public function declineComment(int $id): array
    {
        return $this->updateCommentStatus($id, 2);
    }

    public function deleteComment(int $id): array
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return $this->sendResponse(false, __('Comment not found'));
        }

        $postId = $comment->post_id;

        $this->commentRepository->delete($id);
        $this->syncCommentsCount($postId);

        return $this->sendResponse(true, __('Comment deleted successfully'));
    }

    protected function updateCommentStatus(int $id, int $status): array
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return $this->sendResponse(false, __('Comment not found'));
        }

        $previous = (int) $comment->status;
        $this->commentRepository->update($id, ['status' => $status]);
        if ($previous !== $status) {
            $this->syncCommentsCount($comment->post_id);
        }

        return $this->sendResponse(true, __('Status updated successfully'));
    }

    protected function syncCommentsCount(int $postId): void
    {
        $count = PostComment::query()
            ->where('post_id', $postId)
            ->where('status', 1)
            ->where('visibility', 1)
            ->count();

        Post::query()->whereKey($postId)->update(['comments_count' => $count]);
    }
}
