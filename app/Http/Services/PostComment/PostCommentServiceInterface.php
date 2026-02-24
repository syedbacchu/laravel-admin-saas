<?php

namespace App\Http\Services\PostComment;

use App\Http\Requests\Post\PostCommentCreateRequest;
use App\Http\Requests\Post\PostCommentReplyRequest;
use Illuminate\Http\Request;

interface PostCommentServiceInterface
{
    public function getPublicCommentList(Request $request, string $identifier): array;
    public function storePublicComment(PostCommentCreateRequest $request, string $identifier): array;
    public function getAdminCommentList(Request $request): array;
    public function getCommentForReply(int $id): array;
    public function adminReplyToComment(PostCommentReplyRequest $request, int $id): array;
    public function approveComment(int $id): array;
    public function declineComment(int $id): array;
    public function deleteComment(int $id): array;
}
