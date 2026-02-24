<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\User\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    protected UserServiceInterface $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;
    }

    public function profile(Request $request):JsonResponse
    {
        $data = ProfileResource::make($request->user());
        $response = sendResponse(true,__('Profile'),$data);

        return ResponseService::send($response);
    }
}
