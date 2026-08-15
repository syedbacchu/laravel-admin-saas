<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DynamicPageListResource;
use App\Http\Resources\DynamicPageResource;
use App\Http\Services\Page\PageServiceInterface;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynamicPageController extends Controller
{
    protected PageServiceInterface $dynamicPageService;

    public function __construct(PageServiceInterface $dynamicPageService)
    {
        $this->dynamicPageService = $dynamicPageService;
    }

    /**
     * Get list of all public dynamic pages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->dynamicPageService->getPublicPagesList($request);

        if (
            isset($response['data']['data']) &&
            is_iterable($response['data']['data'])
        ) {
            $response['data']['data'] = DynamicPageListResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    /**
     * Get public dynamic page data by slug with language support
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $languageCode = $request->header('lang');

        $response = $this->dynamicPageService->getPublicPageBySlug($slug, $languageCode);
//dd($response['data']);
        if (!empty($response['data'])) {
            $response['data'] = new DynamicPageResource($response['data']);
        }

        return ResponseService::send($response);
    }
}
