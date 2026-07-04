<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantPayrollGenerateSalaryCreateRequest;
use App\Http\Resources\Tenant\TenantPayrollGeneratedSalaryResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantPayrollGenerateSalary\TenantPayrollGenerateSalaryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PayrollGenerateSalaryController extends Controller
{
    protected TenantPayrollGenerateSalaryServiceInterface $service;

    public function __construct(TenantPayrollGenerateSalaryServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->generatedSalaryList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantPayrollGeneratedSalaryResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantPayrollGenerateSalaryCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeGeneratedSalary($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollGeneratedSalaryResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->generatedSalaryDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollGeneratedSalaryResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantPayrollGenerateSalaryCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeGeneratedSalary($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollGeneratedSalaryResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteGeneratedSalary($request, $id);
        return ResponseService::send($response);
    }

    public function salarySheet(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->salarySheet($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollGeneratedSalaryResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function exportPdf(Request $request, string $company_username, int $id): Response|BinaryFileResponse
    {
        $response = $this->service->salarySheet($request, $id);
        if (!$response['success'] || !isset($response['data'])) {
            abort(404, 'Salary sheet not found');
        }

        $data = $response['data'];

        // Get company settings for PDF
        $settingService = app(\App\Http\Services\TenantSetting\TenantSettingServiceInterface::class);
        $settingsResponse = $settingService->settingList($request);
        $companySettings = $settingsResponse['data'] ?? [];

        $pdfService = new \App\Http\Services\Pdf\SalarySheetPdfService();
        $pdf = $pdfService->generate($data, $companySettings);

        $filename = 'salary-sheet-' . $data->month . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request, string $company_username, int $id): Response|BinaryFileResponse
    {
        $response = $this->service->salarySheet($request, $id);
        if (!$response['success'] || !isset($response['data'])) {
            abort(404, 'Salary sheet not found');
        }

        $data = $response['data'];

        // Get company settings for Excel
        $settingService = app(\App\Http\Services\TenantSetting\TenantSettingServiceInterface::class);
        $settingsResponse = $settingService->settingList($request);
        $companySettings = $settingsResponse['data'] ?? [];

        $filename = 'salary-sheet-' . $data->month . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new \App\Exports\SalarySheetExport($data, $companySettings),
            $filename
        );
    }
}
