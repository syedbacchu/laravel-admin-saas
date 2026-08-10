<?php

namespace App\Http\Controllers\Admin\Thana;

use App\Http\Controllers\Controller;
use App\Http\Requests\Thana\ThanaStoreRequest;
use App\Http\Requests\Thana\ThanaUpdateRequest;
use App\Http\Services\Thana\ThanaServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Models\District;
use App\Models\Thana;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ThanaController extends Controller
{
    protected ThanaServiceInterface $service;

    public function __construct(ThanaServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Thana Management');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    $request->merge(['list_size' => 'datatable']);
                    return $this->service
                        ->getDataTableData($request)['data'];
                },
                columns: [
                    'district_code' => fn ($item) =>
                    $item->district?->name ?? 'N/A',

                    'created_at' => fn ($item) =>
                    $item->created_at?->diffForHumans(),

                    'status' => fn ($item) =>
                    toggle_column(
                        route('thana.status'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('thana.edit', $item->id)),
                        delete_column(route('thana.destroy', $item->id)),
                    ]),
                ],
                rawColumns: ['actions', 'status']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('thana', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create New Thana');
        $data['function_type'] = 'create';
        $data['districts'] = District::active()->get();

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('thana', 'create'));
    }

    public function store(ThanaStoreRequest $request): RedirectResponse
    {
        $response = $this->service->create($request->all());
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'thana.index');
    }

    public function show(string $id)
    {
        return redirect()->route('thana.index');
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
            'pageTitle' => __('Update Thana'),
            'function_type' => 'update',
            'districts' => District::active()->get(),
        ];
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('thana', 'create'));
    }

    public function update(ThanaUpdateRequest $request, string $id): RedirectResponse
    {
        $response = $this->service->update($id, $request->all());
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'thana.index');
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
            logStore('thanaStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $thanas = $this->service->getAll();
            return response()->json($thanas);
        } catch (\Exception $e) {
            logStore('thanaList', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function getByDistrict(Request $request): JsonResponse
    {
        try {
            $districtCode = $request->district_code;
            $thanas = Thana::where('district_code', $districtCode)->active()->get(['id', 'code', 'name']);
            return response()->json(['success' => true, 'data' => $thanas]);
        } catch (\Exception $e) {
            logStore('thanaGetByDistrict', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}