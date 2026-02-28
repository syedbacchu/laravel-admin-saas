<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenantCreateRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\Tenant\TenantServiceInterface;
use App\Support\DataListManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class TenantController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $service
    ) {
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Tenant List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'company' => function ($item) {
                        return '
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900">' . e($item->company_name) . '</span>
                            <small class="text-gray-500">' . e($item->company_username) . '</small>
                        </div>';
                    },

                    'owner' => function ($item) {
                        $contact = $item->owner_email ?: ($item->owner_phone ?: '-');
                        return '
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900">' . e($item->owner_name ?? '-') . '</span>
                            <small class="text-gray-500">' . e($contact) . '</small>
                        </div>';
                    },

                    'status' => function ($item) {
                        $isActive = $item->status === 'active';
                        $class = $isActive ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                        return '<span class="px-2 py-1 text-xs rounded-full ' . $class . '">' . e(ucfirst($item->status)) . '</span>';
                    },

                    'created_at' => fn($item) =>
                        $item->created_at?->diffForHumans(),
                ],
                rawColumns: ['company', 'owner', 'status']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('tenant', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Tenant');
        $data['function_type'] = 'create';
        $data = array_merge($data, $this->service->tenantCreateData($request)['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('tenant', 'create'));
    }

    public function store(TenantCreateRequest $request): RedirectResponse
    {
        try {
            $response = $this->service->storeOrUpdateTenant($request);
            return ResponseService::send([
                'response' => $response,
            ], successRoute: 'tenant.list');
        } catch (Throwable $e) {
            return ResponseService::exception($e);
        }
    }
}
