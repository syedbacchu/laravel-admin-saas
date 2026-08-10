<?php

namespace App\Http\Controllers\Admin\Division;

use App\Http\Controllers\Controller;
use App\Http\Requests\Division\DivisionStoreRequest;
use App\Http\Requests\Division\DivisionUpdateRequest;
use App\Http\Services\Division\DivisionServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DivisionController extends Controller
{
    protected DivisionServiceInterface $service;

    public function __construct(DivisionServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Division Management');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    $request->merge(['list_size' => 'datatable']);
                    return $this->service
                        ->getDataTableData($request)['data'];
                },
                columns: [
                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                    'status' => fn ($item) =>
                    toggle_column(
                        route('division.status'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('division.edit', $item->id)),
                        delete_column(route('division.destroy', $item->id)),
                    ]),
                ],
                rawColumns: ['actions', 'status']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('division', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create New Division');
        $data['function_type'] = 'create';

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('division', 'create'));
    }

    public function store(DivisionStoreRequest $request): RedirectResponse
    {
        $response = $this->service->create($request->all());
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'division.index');
    }

    public function show(string $id)
    {
        return redirect()->route('division.index');
    }

    public function edit(string $id)
    {
        $response = $this->service->getById($id);
        if ($response['success'] == false) {
            return ResponseService::send();
        }
        $item = $response['data'];
        $data = [
            'item' => $item,
            'pageTitle' => __('Update Division'),
            'function_type' => 'update',
        ];
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('division', 'create'));
    }

    public function update(DivisionUpdateRequest $request, string $id): RedirectResponse
    {
        $response = $this->service->update($id, $request->all());
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'division.index');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->delete($id);
        return ResponseService::send([
            'response' => $response,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        try {
            $response = $this->service->toggleStatus($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('divisionStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $divisions = $this->service->getAll();
            return response()->json($divisions);
        } catch (\Exception $e) {
            logStore('divisionList', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}