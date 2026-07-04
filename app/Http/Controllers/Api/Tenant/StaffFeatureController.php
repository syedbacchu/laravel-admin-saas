<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantStaffFeatureUpdateRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantStaff\TenantStaffServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffFeatureController extends Controller
{
    protected TenantStaffServiceInterface $service;

    public function __construct(TenantStaffServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request, string $company_username, int $staff_id): JsonResponse
    {
        $response = $this->service->getStaffFeatures($request, $staff_id);
        return ResponseService::send($response);
    }

    public function update(TenantStaffFeatureUpdateRequest $request, string $company_username, int $staff_id): JsonResponse
    {
        $response = $this->service->updateStaffFeatures($request, $staff_id);
        return ResponseService::send($response);
    }

    public function getMyFeatures(Request $request, string $company_username): JsonResponse
    {
        $currentUser = $request->user();

        // Verify this is a staff user
        if (!$currentUser || (string) $currentUser->user_type !== 'staff') {
            return ResponseService::send($this->sendResponse(false, __('Only staff users can access this endpoint'), [], 403));
        }

        // Staff users can only access their own features
        $response = $this->service->getStaffFeatures($request, (int) $currentUser->id);
        return ResponseService::send($response);
    }

    protected function sendResponse(bool $success, string $message, array $data = [], int $status = 200, string $errorMessage = ''): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'status' => $status,
            'error_message' => $errorMessage,
        ];
    }
}