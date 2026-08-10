<?php

namespace App\Http\Controllers\Api\Slider;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderListResource;
use App\Http\Resources\SliderDetailsResource;
use App\Http\Services\Slider\SliderServiceInterface;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    protected SliderServiceInterface $slider;

    public function __construct(SliderServiceInterface $slider)
    {
        $this->slider = $slider;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->slider->getPublicList($request);

        if (!empty($response['data']['data'])) {
            $response['data']['data'] = SliderListResource::collection(
                $response['data']['data']
            );
        }

        return ResponseService::send($response);
    }

    public function show(string $identifier): JsonResponse
    {
        $response = $this->slider->getPublicDetails($identifier);

        if (!empty($response['data'])) {
            $response['data'] = new SliderDetailsResource($response['data']);
        }

        return ResponseService::send($response);
    }
}