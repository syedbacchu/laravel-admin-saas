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
                            '<a href="' . route('subscription.updatePlan', $item->id) . '" class="btn btn-sm btn-info">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Update Plan
                            </a>',
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

    /**
     * Show update plan features page
     */
    public function updatePlanView(Request $request, string $subscription)
    {
        $response = $this->service->getSubscriptionForUpdatePlan($subscription);

        if (!$response['success']) {
            abort(404, $response['message'] ?? 'Subscription not found');
        }

        $data = array_merge($response['data'], [
            'pageTitle' => __('Update Subscription Features')
        ]);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('subscription', 'update-plan'));

    }

    /**
     * Save updated plan features
     */
    public function updatePlanSave(Request $request, string $subscription)
    {
        $response = $this->service->updateSubscriptionPlanFeatures($request, $subscription);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($response);
        }

        if ($response['success']) {
            return redirect()->route('subscription.list')->with('success', $response['message']);
        }

        return redirect()->back()->with('error', $response['message'] ?? __('Something went wrong'));
    }

    /**
     * Update subscription plan
     */
    public function updateSubscriptionPlan(Request $request, string $subscription)
    {
        $response = $this->service->updateSubscriptionPlan($request, $subscription);
        return response()->json($response);
    }
}
