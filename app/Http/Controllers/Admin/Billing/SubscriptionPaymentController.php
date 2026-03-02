<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubscriptionPaymentCreateRequest;
use App\Http\Services\Billing\SubscriptionPaymentServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    protected SubscriptionPaymentServiceInterface $service;

    public function __construct(SubscriptionPaymentServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Subscription Payment List');
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
                    'subscription' => function ($item) {
                        return '
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900">#' . e($item->subscription_id) . '</span>
                            <small class="text-gray-500">' . e($item->plan_name ?? 'Plan') . '</small>
                        </div>';
                    },
                    'payment_method' => fn ($item) => e($item->payment_method_name ?? '-'),
                    'status_badge' => function ($item) {
                        $status = strtolower((string) $item->status);
                        $class = $status === 'verified' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning');
                        return '<span class="badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
                    },
                    'actions' => fn ($item) =>
                        action_buttons([
                            edit_column(route('subscriptionPayment.edit', $item->id)),
                            delete_column(route('subscriptionPayment.delete', $item->id)),
                        ]),
                ],
                rawColumns: ['tenant', 'subscription', 'status_badge', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscriptionPayment', 'list'));
    }

    public function create(Request $request)
    {
        $response = $this->service->subscriptionPaymentCreateData($request);
        $data['pageTitle'] = __('Create Subscription Payment');
        $data['function_type'] = 'create';
        $data = array_merge($data, $response['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscriptionPayment', 'create'));
    }

    public function store(SubscriptionPaymentCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdateSubscriptionPayment($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'subscriptionPayment.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->subscriptionPaymentEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $request = request();
        $request->merge([
            'subscription_id' => $response['data']->subscription_id,
            'payment_method_id' => $response['data']->payment_method_id,
        ]);
        $createData = $this->service->subscriptionPaymentCreateData($request)['data'];
        $data['pageTitle'] = __('Update Subscription Payment');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];
        $data = array_merge($data, $createData);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscriptionPayment', 'create'));
    }

    public function update(SubscriptionPaymentCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateSubscriptionPayment($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'subscriptionPayment.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deleteSubscriptionPayment($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'subscriptionPayment.list');
    }

    public function report(Request $request)
    {
        $response = $this->service->paymentReportData($request);
        $data['pageTitle'] = __('Payment Report');
        $data = array_merge($data, $response['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscriptionPayment', 'report'));
    }
}
