<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantSetting\TenantSettingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected TenantSettingServiceInterface $service;

    public function __construct(TenantSettingServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->settingList($request);

        return ResponseService::send($response);
    }

    public function store(Request $request): JsonResponse
    {
        $response = $this->service->upsertSettings($request);

        return ResponseService::send($response);
    }

    public function destroy(Request $request): JsonResponse
    {
        $response = $this->service->deleteSettings($request);

        return ResponseService::send($response);
    }
}
