<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantPayrollLoanCreateRequest;
use App\Http\Resources\Tenant\TenantPayrollLoanResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantPayrollLoan\TenantPayrollLoanServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollLoanController extends Controller
{
    protected TenantPayrollLoanServiceInterface $service;

    public function __construct(TenantPayrollLoanServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->loanList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantPayrollLoanResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantPayrollLoanCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeLoan($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollLoanResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->loanDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollLoanResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantPayrollLoanCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeLoan($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollLoanResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteLoan($request, $id);
        return ResponseService::send($response);
    }

    public function paymentHistory(Request $request, string $company_username, int $id): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (!$tenant) {
            return ResponseService::sendError('Tenant context is missing', [], 422);
        }

        $loan = \App\Models\Tenant\TenantPayrollLoan::with(['payments', 'employee'])
            ->where('id', $id)
            ->first();

        if (!$loan) {
            return ResponseService::sendError('Loan not found', [], 404);
        }

        return ResponseService::sendSuccess('Loan payment history retrieved successfully', [
            'loan' => [
                'id' => $loan->id,
                'loan_amount' => $loan->loan_amount,
                'monthly_deduction' => $loan->monthly_deduction,
                'paid_amount' => $loan->paid_amount,
                'remaining_balance' => $loan->remaining_balance,
                'status' => $loan->status,
                'loan_date' => $loan->loan_date,
            ],
            'payments' => $loan->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date,
                    'salary_month' => $payment->salary_month,
                    'paid_amount' => $payment->paid_amount,
                    'remaining_balance_before' => $payment->remaining_balance_before,
                    'remaining_balance_after' => $payment->remaining_balance_after,
                    'payment_method' => $payment->payment_method,
                    'remarks' => $payment->remarks,
                ];
            }),
        ]);
    }

    public function employeeLoanHistory(Request $request): JsonResponse
    {
        $employeeId = $request->input('employee_id');
        if (!$employeeId) {
            return ResponseService::sendError('Employee ID is required', [], 422);
        }

        $loans = \App\Models\Tenant\TenantPayrollLoan::with(['payments'])
            ->where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->get();

        $loanHistory = $loans->map(function ($loan) {
            return [
                'id' => $loan->id,
                'loan_date' => $loan->loan_date,
                'loan_amount' => $loan->loan_amount,
                'monthly_deduction' => $loan->monthly_deduction,
                'paid_amount' => $loan->paid_amount,
                'remaining_balance' => $loan->remaining_balance,
                'status' => $loan->status,
                'recent_payments' => $loan->payments->take(-5)->map(function ($payment) {
                    return [
                        'payment_date' => $payment->payment_date,
                        'salary_month' => $payment->salary_month,
                        'paid_amount' => $payment->paid_amount,
                        'remaining_balance_after' => $payment->remaining_balance_after,
                    ];
                }),
            ];
        });

        return ResponseService::sendSuccess('Employee loan history retrieved successfully', $loanHistory);
    }
}
