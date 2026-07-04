<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantFileUploadRequest;
use App\Http\Requests\TenantApi\TenantFileUpdateRequest;
use App\Http\Resources\Tenant\TenantFileResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantFile\TenantFileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected TenantFileServiceInterface $service;

    public function __construct(TenantFileServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->fileList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = $this->resolveResourceArrayCollection($response['data']['data'], $request);
        }

        return ResponseService::send($response);
    }

    public function upload(TenantFileUploadRequest $request): JsonResponse
    {
        $response = $this->service->uploadFile($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            if (is_iterable($response['data'])) {
                $response['data'] = $this->resolveResourceArrayCollection($response['data'], $request);
            } else {
                $response['data'] = TenantFileResource::make($response['data'])->resolve($request);
            }
        }

        return ResponseService::send($response);
    }

    public function update(TenantFileUpdateRequest $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->updateFileMeta($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantFileResource::make($response['data'])->resolve($request);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteFile($request, $id);
        return ResponseService::send($response);
    }

    protected function resolveResourceArrayCollection(iterable $items, Request $request): array
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = TenantFileResource::make($item)->resolve($request);
        }

        return $data;
    }
}
