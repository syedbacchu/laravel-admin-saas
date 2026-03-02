<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantApi\TenantApiServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected TenantApiServiceInterface $service;

    public function __construct(TenantApiServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->dashboardData($request);
        return ResponseService::send($response);
    }
}

