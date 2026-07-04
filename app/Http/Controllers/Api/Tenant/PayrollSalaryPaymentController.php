<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantPayrollSalaryPaymentCreateRequest;
use App\Http\Resources\Tenant\TenantPayrollSalaryPaymentResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantPayrollSalaryPayment\TenantPayrollSalaryPaymentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollSalaryPaymentController extends Controller
{
    protected TenantPayrollSalaryPaymentServiceInterface $service;

    public function __construct(TenantPayrollSalaryPaymentServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getPayableAmount(Request $request, string $company_username, int $salary_sheet_id): JsonResponse
    {
        $response = $this->service->getPayableAmount($request, $salary_sheet_id);
        return ResponseService::send($response);
    }

    public function processPayment(TenantPayrollSalaryPaymentCreateRequest $request, string $company_username): JsonResponse
    {
        $response = $this->service->processPayment($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollSalaryPaymentResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function getPaymentHistory(Request $request, string $company_username, int $salary_sheet_id): JsonResponse
    {
        $response = $this->service->getPaymentHistory($request, $salary_sheet_id);
        return ResponseService::send($response);
    }

    public function getEmployeePaymentHistory(Request $request, string $company_username, int $employee_id): JsonResponse
    {
        $response = $this->service->getEmployeePaymentHistory($request, $employee_id);
        return ResponseService::send($response);
    }
}
