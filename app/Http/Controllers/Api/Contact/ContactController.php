<?php

namespace App\Http\Controllers\Api\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactStoreRequest;
use App\Http\Services\Contact\ContactServiceInterface;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    protected ContactServiceInterface $service;

    public function __construct(ContactServiceInterface $service)
    {
        $this->service = $service;
    }

    public function store(ContactStoreRequest $request): JsonResponse
    {
        $response = $this->service->submitContact($request->validated());
        logStore('contact response', $response);
        return ResponseService::send($response);
    }
}
