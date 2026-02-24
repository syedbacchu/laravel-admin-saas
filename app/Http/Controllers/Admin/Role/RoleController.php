<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleCreateRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\Role\RoleServiceInterface;
use App\Models\Permission;
use App\Support\DataListManager;
use App\Support\SyncPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    protected RoleServiceInterface $service;

    public function __construct(RoleServiceInterface $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['pageTitle'] = __('Web Based Role');
        $data['type'] = 'web';
        if ($request->ajax()) {
            $request->merge(['guard' => 'web']);
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                    'status' => fn ($item) =>
                    toggle_column(
                        route('role.status'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('role.edit', $item->id)),
                        delete_column(route('role.destroy', $item->id)),
                    ]),
                ],
                rawColumns: ['actions','status']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('role','list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create New Role');
        $data['function_type'] = 'create';
        $data['type'] = isset($request->type) ? $request->type : 'web';
        $data['permissions'] = $this->service->roleCreateData($data['type'])['data'];;

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('role','create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleCreateRequest $request):RedirectResponse {
        $response = $this->service->storeOrUpdateData($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'role.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->roleEditData($id);
        if ($response['success'] == false) {
            return ResponseService::send();
        }
        $data = $response['data'];
        $data['pageTitle'] = __('Update Role');
        $data['function_type'] = 'update';
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('role','create'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $response = $this->service->deleteData($id);
        return ResponseService::send([
            'response' => $response,
        ]);
    }


    public function roleStatus(Request $request): JsonResponse {
        try {
            $response = $this->service->statusRole($request->id,$request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('roleStatus',$e->getMessage());
            return response()->json(['success'=>false,'message'=>somethingWrong()]);
        }
    }

    public function syncPermission(Request $request){
        $response = SyncPermission::sync($request);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function webPermission(Request $request)
    {
        $data['pageTitle'] = __('Web Permission');
        $data['type'] = 'web';
        if ($request->ajax()) {
            return $this->getPermissionTableDataSet($request,$data['type']);
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('role','permission'));
    }

    public function apiPermission(Request $request)
    {
        $data['pageTitle'] = __('Api Permission');
        $data['type'] = 'api';
        if ($request->ajax()) {
            return $this->getPermissionTableDataSet($request,$data['type']);
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('role','permissionApi'));
    }

    protected function getPermissionTableDataSet(Request $request, $guard)
    {
            $request->merge([
                'guard' => $guard
            ]);
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getPermissionList($request)['data']['data'];
                },
                columns: [
                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                    'status' => fn ($item) =>
                    toggle_column(
                        route('role.permissionStatus'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        delete_column(route('role.deletePermission', $item->id)),
                    ]),
                ],
                rawColumns: ['actions','status']
            );
    }

    public function permissionPublish(Request $request): JsonResponse {
        try {
            $response = $this->service->publishPermission($request->id,$request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('permissionPublish',$e->getMessage());
            return response()->json(['success'=>false,'message'=>somethingWrong()]);
        }
    }

    public function deletePermission($id): RedirectResponse {
        $response = $this->service->permissionDelete($id);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function apiRole(Request $request)
    {
        $data['pageTitle'] = __('Api Based Role');
        $data['type'] = 'api';
        if ($request->ajax()) {
            $request->merge([
                'guard' => 'api'
            ]);
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                    'status' => fn ($item) =>
                    toggle_column(
                        route('role.status'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('role.edit', $item->id)),
                        delete_column(route('role.destroy', $item->id)),
                    ]),
                ],
                rawColumns: ['actions','status']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('role','apiList'));
    }
}
