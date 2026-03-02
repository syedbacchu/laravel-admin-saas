<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\TenantSubscriptionResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantApi\TenantApiServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected TenantApiServiceInterface $service;

    public function __construct(TenantApiServiceInterface $service)
    {
        $this->service = $service;
    }

    public function details(Request $request): JsonResponse
    {
        $response = $this->service->subscriptionDetails($request);

        if (($response['success'] ?? false) === true && !empty($response['data']['subscription'])) {
            $response['data']['subscription'] = TenantSubscriptionResource::make($response['data']['subscription']);
        }

        return ResponseService::send($response);
    }
}

