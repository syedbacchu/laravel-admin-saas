<?php

namespace App\Http\Controllers\Admin\District;

use App\Http\Controllers\Controller;
use App\Http\Requests\District\DistrictStoreRequest;
use App\Http\Requests\District\DistrictUpdateRequest;
use App\Http\Services\District\DistrictServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Models\Division;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DistrictController extends Controller
{
    protected DistrictServiceInterface $service;

    public function __construct(DistrictServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('District Management');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    $request->merge(['list_size' => 'datatable']);
                    return $this->service
                        ->getDataTableData($request)['data'];
                },
                columns: [
                    'division_code' => fn ($item) =>
                    $item->division?->name ?? 'N/A',

                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                    'status' => fn ($item) =>
                    toggle_column(
                        route('district.status'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('district.edit', $item->id)),
                        delete_column(route('district.destroy', $item->id)),
                    ]),
                ],
                rawColumns: ['actions', 'status']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('district', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create New District');
        $data['function_type'] = 'create';
        $data['divisions'] = Division::active()->get();

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('district', 'create'));
    }

    public function store(DistrictStoreRequest $request): RedirectResponse
    {
        $response = $this->service->create($request->all());
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'district.index');
    }

    public function show(string $id)
    {
        return redirect()->route('district.index');
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
            'pageTitle' => __('Update District'),
            'function_type' => 'update',
            'divisions' => Division::active()->get(),
        ];
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('district', 'create'));
    }

    public function update(DistrictUpdateRequest $request, string $id): RedirectResponse
    {
        $response = $this->service->update($id, $request->all());
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'district.index');
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
            logStore('districtStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $districts = $this->service->getAll();
            return response()->json($districts);
        } catch (\Exception $e) {
            logStore('districtList', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}