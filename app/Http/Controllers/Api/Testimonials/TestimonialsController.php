<?php

namespace App\Http\Controllers\Api\Testimonials;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonialListResource;
use App\Http\Resources\TestimonialDetailsResource;
use App\Http\Services\Testimonial\TestimonialServiceInterface;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialsController extends Controller
{
    protected TestimonialServiceInterface $testimonial;

    public function __construct(TestimonialServiceInterface $testimonial)
    {
        $this->testimonial = $testimonial;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->testimonial->getPublicList($request);

        if (!empty($response['data']['data'])) {
            $response['data']['data'] = TestimonialListResource::collection(
                $response['data']['data']
            );
        }

        return ResponseService::send($response);
    }

    public function show(string $id): JsonResponse
    {
        $response = $this->testimonial->getPublicDetails($id);

        if (!empty($response['data'])) {
            $response['data'] = new TestimonialDetailsResource($response['data']);
        }

        return ResponseService::send($response);
    }
}