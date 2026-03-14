<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\PricingPlan\PricingPlanServiceInterface;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingPlanController extends Controller
{
    protected PricingPlanServiceInterface $service;

    public function __construct(PricingPlanServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->getPublicPricingPlanList($request);
        return ResponseService::send($response);
    }

    public function show(Request $request, string $identifier): JsonResponse
    {
        $response = $this->service->getPublicPricingPlanDetails($request, $identifier);
        return ResponseService::send($response);
    }
}
