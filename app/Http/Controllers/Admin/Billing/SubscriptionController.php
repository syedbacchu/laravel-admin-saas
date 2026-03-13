<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubscriptionCreateRequest;
use App\Http\Services\Billing\SubscriptionServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionServiceInterface $service;

    public function __construct(SubscriptionServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Subscription List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'tenant' => function ($item) {
                        return '
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900">' . e($item->company_name) . '</span>
                            <small class="text-gray-500">' . e($item->company_username) . '</small>
                        </div>';
                    },
                    'plan' => function ($item) {
                        $price = number_format((float) $item->final_amount, 2);
                        return '
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900">' . e($item->plan_name) . '</span>
                            <small class="text-gray-500">' . e($item->term_months) . ' month(s) - ' . e($price . ' ' . $item->currency) . '</small>
                        </div>';
                    },
                    'status' => function ($item) {
                        $class = in_array($item->status, ['active', 'trialing'], true) ? 'bg-success' : 'bg-warning';
                        return '<span class="badge ' . $class . '">' . e(ucfirst(str_replace('_', ' ', $item->status))) . '</span>';
                    },
                    'starts_at' => function ($item) {
                        return date('d M Y', strtotime($item->starts_at));
                    },
                    'ends_at' => function ($item) {
                        return date('d M Y', strtotime($item->ends_at));
                    },
                    'actions' => fn ($item) =>
                        action_buttons([
                            edit_column(route('subscription.edit', $item->id)),
                            delete_column(route('subscription.delete', $item->id)),
                        ]),
                ],

                rawColumns: ['tenant', 'plan', 'status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscription', 'list'));
    }

    public function create(Request $request)
    {
        $response = $this->service->subscriptionCreateData($request);
        $data['pageTitle'] = __('Create Subscription');
        $data['function_type'] = 'create';
        $data = array_merge($data, $response['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscription', 'create'));
    }

    public function store(SubscriptionCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdateSubscription($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'subscription.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->subscriptionEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $createData = $this->service->subscriptionCreateData(request())['data'];

        $data['pageTitle'] = __('Update Subscription');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];
        $data = array_merge($data, $createData);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscription', 'create'));
    }

    public function update(SubscriptionCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateSubscription($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'subscription.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deleteSubscription($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'subscription.list');
    }
}
