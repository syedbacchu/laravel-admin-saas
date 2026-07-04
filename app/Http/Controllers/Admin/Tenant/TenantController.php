<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenantCreateRequest;
use App\Http\Requests\Tenant\TenantUpdateRequest;
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

                    'actions' => function ($item) {
                        $editUrl = route('tenant.edit', $item->id);
                        $backupUrl = route('tenant.backup', $item->id);
                        $backupsUrl = route('tenant.backups', $item->id);

                        $editBtn = '<a href="' . $editUrl . '" class="btn btn-sm btn-info me-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>';

                        $backupBtn = '<button onclick="backupTenant(\'' . $backupUrl . '\', \'' . e($item->company_name) . '\')"
                                        class="btn btn-sm btn-primary me-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Backup
                                    </button>';

                        $listBackupsBtn = '<a href="' . $backupsUrl . '" class="btn btn-sm btn-secondary">
                                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                          </svg>
                                          List
                                        </a>';

                        return '<div class="flex items-center">' . $editBtn . $backupBtn . $listBackupsBtn . '</div>';
                    },
                ],
                rawColumns: ['company', 'owner', 'status','actions']
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

    public function edit(Request $request, $id)
    {
        try {
            $data['pageTitle'] = __('Edit Tenant');
            $data['function_type'] = 'edit';
            $tenant = $this->service->getTenant($id);

            if (!$tenant) {
                return back()->with('error', __('Tenant not found'));
            }

            $data['tenant'] = $tenant;
            $data = array_merge($data, $this->service->tenantCreateData($request)['data']);

            return ResponseService::send([
                'data' => $data,
            ], view: viewss('tenant', 'create'));
        } catch (Throwable $e) {
            return back()->with('error', __('Failed to load tenant: ' . $e->getMessage()));
        }
    }

    public function update(TenantUpdateRequest $request, $id): RedirectResponse
    {
        try {
            $response = $this->service->updateTenant($request, $id);
            return ResponseService::send([
                'response' => $response,
            ], successRoute: 'tenant.list');
        } catch (Throwable $e) {
            return ResponseService::exception($e);
        }
    }

    public function backup(Request $request, $id)
    {
        try {
            $response = $this->service->backupTenantDatabase($id);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return ResponseService::send([
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup failed: ' . $e->getMessage(),
                    'data' => [],
                    'error_message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function backups(Request $request, $id)
    {
        try {
            $response = $this->service->getTenantBackups($id);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            // Get tenant data separately for the view
            $tenant = $this->service->getTenant($id);

            if (!$tenant) {
                return back()->with('error', 'Tenant not found');
            }

            $data['pageTitle'] = __('Backup List for ' . $tenant->company_name);
            $data['tenant'] = $tenant;
            $data['backups'] = $response['data'] ?? [];

            return ResponseService::send([
                'data' => $data,
            ], view: viewss('tenant', 'backups'));
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load backups: ' . $e->getMessage(),
                    'data' => [],
                    'error_message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to load backups: ' . $e->getMessage());
        }
    }

    public function downloadBackup(Request $request, $id, $filename)
    {
        try {
            $response = $this->service->downloadBackup($id, $filename);

            if (!$response['success']) {
                return back()->with('error', $response['message']);
            }

            $data = $response['data'];
            return response()->download($data['path'], $data['filename']);
        } catch (Throwable $e) {
            return back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    public function diagnose(Request $request, $id)
    {
        try {
            $response = $this->service->diagnoseTenantConnection($id);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return back()->with('diagnostics', $response);
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diagnostics failed: ' . $e->getMessage(),
                    'data' => [],
                    'error_message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Diagnostics failed: ' . $e->getMessage());
        }
    }

    public function deleteBackup(Request $request, $id, $filename)
    {
        try {
            $response = $this->service->deleteBackup($id, $filename);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return back()->with('success', $response['message'] ?? 'Backup deleted');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delete failed: ' . $e->getMessage(),
                    'data' => [],
                    'error_message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
