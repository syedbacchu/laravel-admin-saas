<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostCommentCreateRequest;
use App\Http\Resources\BlogCommentResource;
use App\Http\Services\PostComment\PostCommentServiceInterface;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    protected PostCommentServiceInterface $service;

    public function __construct(PostCommentServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request, string $identifier): JsonResponse
    {
        $response = $this->service->getPublicCommentList($request, $identifier);
        if (
            isset($response['data']['data']) &&
            is_iterable($response['data']['data'])
        ) {
            $response['data']['data'] = BlogCommentResource::collection(
                $response['data']['data']
            );
        }

        return ResponseService::send($response);
    }

    public function store(PostCommentCreateRequest $request, string $identifier): JsonResponse
    {
        $response = $this->service->storePublicComment($request, $identifier);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = BlogCommentResource::make($response['data']);
        }

        return ResponseService::send($response);
    }
}
