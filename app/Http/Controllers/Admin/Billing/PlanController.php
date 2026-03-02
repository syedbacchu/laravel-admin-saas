<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\PlanCreateRequest;
use App\Http\Services\Billing\PlanServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected PlanServiceInterface $service;

    public function __construct(PlanServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Plan List');
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
                            edit_column(route('plan.edit', $item->id)),
                            delete_column(route('plan.delete', $item->id)),
                        ]),
                ],
                rawColumns: ['status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('plan', 'list'));
    }

    public function create(Request $request)
    {
        $response = $this->service->planCreateData($request);
        $data['pageTitle'] = __('Create Plan');
        $data['function_type'] = 'create';
        $data = array_merge($data, $response['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('plan', 'create'));
    }

    public function store(PlanCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdatePlan($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'plan.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->planEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $request = request();
        $createData = $this->service->planCreateData($request)['data'];

        $data['pageTitle'] = __('Update Plan');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];
        $data = array_merge($data, $createData);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('plan', 'create'));
    }

    public function update(PlanCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdatePlan($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'plan.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deletePlan($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'plan.list');
    }
}
