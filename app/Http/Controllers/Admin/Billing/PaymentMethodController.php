<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\PaymentMethodCreateRequest;
use App\Http\Services\Billing\PaymentMethodServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    protected PaymentMethodServiceInterface $service;

    public function __construct(PaymentMethodServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Payment Method List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'status' => fn ($item) =>
                        toggle_column(
                            route('paymentMethod.publish'),
                            (int) $item->id,
                            (int) $item->is_active === 1
                        ),
                    'actions' => fn ($item) =>
                        action_buttons([
                            edit_column(route('paymentMethod.edit', $item->id)),
                            delete_column(route('paymentMethod.delete', $item->id)),
                        ]),
                ],
                rawColumns: ['status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('paymentMethod', 'list'));
    }

    public function create(Request $request)
    {
        $response = $this->service->paymentMethodCreateData($request);
        $data['pageTitle'] = __('Create Payment Method');
        $data['function_type'] = 'create';
        $data = array_merge($data, $response['data']);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('paymentMethod', 'create'));
    }

    public function store(PaymentMethodCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdatePaymentMethod($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'paymentMethod.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->paymentMethodEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $createData = $this->service->paymentMethodCreateData(request())['data'];

        $data['pageTitle'] = __('Update Payment Method');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];
        $data = array_merge($data, $createData);

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('paymentMethod', 'create'));
    }

    public function update(PaymentMethodCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdatePaymentMethod($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'paymentMethod.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deletePaymentMethod($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'paymentMethod.list');
    }

    public function paymentMethodStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->service->publishPaymentMethod($request->id, $request->status);
            return response()->json($response);
        } catch (\Throwable $e) {
            logStore('paymentMethodStatus', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => somethingWrong(),
            ]);
        }
    }
}

