<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogDetailsResource;
use App\Http\Resources\BlogListResource;
use App\Http\Services\Post\PostServiceInterface;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected PostServiceInterface $service;

    public function __construct(PostServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->getPublicBlogList($request);
        if (
            isset($response['data']['data']) &&
            is_iterable($response['data']['data'])
        ) {
            $response['data']['data'] = BlogListResource::collection(
                $response['data']['data']
            );
        }
        return ResponseService::send($response);
    }

    public function show(string $identifier): JsonResponse
    {
        $response = $this->service->getPublicBlogDetails($identifier);

        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = BlogDetailsResource::make($response['data']);
        }

        return ResponseService::send($response);
    }
}
