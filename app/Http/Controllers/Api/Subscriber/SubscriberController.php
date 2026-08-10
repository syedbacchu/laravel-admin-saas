<?php

namespace App\Http\Controllers\Api\Subscriber;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriber\SubscriberSubscribeRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\Subscriber\SubscriberServiceInterface;
use Illuminate\Http\JsonResponse;

class SubscriberController extends Controller
{
    protected SubscriberServiceInterface $service;

    public function __construct(SubscriberServiceInterface $service)
    {
        $this->service = $service;
    }

    public function subscribe(SubscriberSubscribeRequest $request): JsonResponse
    {
        $response = $this->service->subscribe($request->validated());
        return response()->json($response, $response['status']);
    }
}
