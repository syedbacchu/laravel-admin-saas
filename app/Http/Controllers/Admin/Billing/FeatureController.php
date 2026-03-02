<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\FeatureCreateRequest;
use App\Http\Services\Billing\FeatureServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    protected FeatureServiceInterface $service;

    public function __construct(FeatureServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Feature List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'status' => fn ($item) => $item->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-warning">Inactive</span>',
                    'actions' => fn ($item) =>
                        action_buttons([
                            edit_column(route('feature.edit', $item->id)),
                            delete_column(route('feature.delete', $item->id)),
                        ]),
                ],
                rawColumns: ['status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('feature', 'list'));
    }

    public function create(Request $request)
    {
        $response = $this->service->featureCreateData($request);
        $data['pageTitle'] = __('Create Feature');
        $data['function_type'] = 'create';
        $data = array_merge($data, $response['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('feature', 'create'));
    }

    public function store(FeatureCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdateFeature($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'feature.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->featureEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $createData = $this->service->featureCreateData(request())['data'];

        $data['pageTitle'] = __('Update Feature');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];
        $data = array_merge($data, $createData);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('feature', 'create'));
    }

    public function update(FeatureCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateFeature($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'feature.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deleteFeature($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'feature.list');
    }
}
