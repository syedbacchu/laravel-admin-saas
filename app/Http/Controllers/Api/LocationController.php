<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\Response\ResponseService;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function divisions(Request $request): JsonResponse
    {
        $data = DataListManager::list(
            request: $request,
            query: Division::active(),
            searchable: ['name', 'code'],
            select: ['id', 'code', 'name', 'status'],
            filters: [
                'status' => ['column' => 'status'],
            ],
        );
        $service = sendResponse(true,__('Data get successfully.'),$data);
        return ResponseService::send($service);

    }

    public function districts(Request $request): JsonResponse
    {
        $data = DataListManager::list(
            request: $request,
            query: District::active()->with('division:id,code,name'),
            searchable: ['name', 'code'],
            select: ['id', 'code', 'name', 'division_code', 'status'],
            filters: [
                'status' => ['column' => 'status'],
                'division_code' => ['column' => 'division_code'],
            ],
        );

        $service = sendResponse(true,__('Data get successfully.'),$data);
        return ResponseService::send($service);

    }

    public function thanas(Request $request): JsonResponse
    {
        $data = DataListManager::list(
            request: $request,
            query: Thana::active()->with('district:id,code,name'),
            searchable: ['name', 'code'],
            select: ['id', 'code', 'name', 'district_code', 'status'],
            filters: [
                'status' => ['column' => 'status'],
                'district_code' => ['column' => 'district_code'],
            ],
        );

        $service = sendResponse(true,__('Data get successfully.'),$data);
        return ResponseService::send($service);
    }
}
