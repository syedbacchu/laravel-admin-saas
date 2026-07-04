<?php

namespace App\Http\Services\TenantDailyOfficeExpense;

use App\Http\Requests\TenantApi\TenantDailyOfficeExpenseCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\TenantDailyOfficeExpense;
use App\Models\TenantOffice;
use Illuminate\Http\Request;
use Throwable;

class TenantDailyOfficeExpenseService extends BaseService implements TenantDailyOfficeExpenseServiceInterface
{
    protected TenantDailyOfficeExpenseRepositoryInterface $tenantDailyOfficeExpenseRepository;

    public function __construct(TenantDailyOfficeExpenseRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantDailyOfficeExpenseRepository = $repository;
    }

    public function dailyOfficeExpenseList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantDailyOfficeExpenseRepository->dailyOfficeExpenseList($request);
        $this->attachOfficeToExpenseList($data);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeDailyOfficeExpense(TenantDailyOfficeExpenseCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $data = [
                'date' => $request->date,
                'paid_to' => (string) $request->paid_to,
                'category' => (string) $request->category,
                'office_id' => (int) $request->office_id,
                'amount' => $request->amount,
                'remarks' => (string) $request->remarks,
                'attachment' => $request->attachment,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantDailyOfficeExpenseRepository->findDailyOfficeExpense((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Daily office expense not found'), [], 404);
                }

                $this->tenantDailyOfficeExpenseRepository->update((int) $item->id, $data);
                $item = $this->tenantDailyOfficeExpenseRepository->findDailyOfficeExpense((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Daily office expense not found'), [], 404);
                }

                $this->attachOfficeToExpense($item);

                return $this->sendResponse(true, __('Daily office expense updated successfully'), $item);
            }

            $item = $this->tenantDailyOfficeExpenseRepository->createDailyOfficeExpense($data);
            $item = $this->tenantDailyOfficeExpenseRepository->findDailyOfficeExpense((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Daily office expense not found'), [], 404);
            }

            $this->attachOfficeToExpense($item);

            return $this->sendResponse(true, __('Daily office expense created successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantDailyOfficeExpenseService storeDailyOfficeExpense', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function dailyOfficeExpenseDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantDailyOfficeExpenseRepository->findDailyOfficeExpense($id);
        if (!$item) {
            return $this->sendResponse(false, __('Daily office expense not found'), [], 404);
        }

        $this->attachOfficeToExpense($item);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteDailyOfficeExpense(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantDailyOfficeExpenseRepository->findDailyOfficeExpense($id);
        if (!$item) {
            return $this->sendResponse(false, __('Daily office expense not found'), [], 404);
        }

        $this->tenantDailyOfficeExpenseRepository->delete($id);

        return $this->sendResponse(true, __('Daily office expense deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function attachOfficeToExpenseList(array &$data): void
    {
        if (!isset($data['data']) || !is_iterable($data['data'])) {
            return;
        }

        $officeIds = [];
        foreach ($data['data'] as $item) {
            $officeIds[] = (int) $item->office_id;
        }

        $officeMap = $this->resolveOfficeMap($officeIds);
        foreach ($data['data'] as $item) {
            $officeId = (int) $item->office_id;
            $item->setAttribute('office', $officeMap[$officeId] ?? null);
        }
    }

    protected function attachOfficeToExpense(TenantDailyOfficeExpense $item): void
    {
        $officeMap = $this->resolveOfficeMap([(int) $item->office_id]);
        $item->setAttribute('office', $officeMap[(int) $item->office_id] ?? null);
    }

    protected function resolveOfficeMap(array $officeIds): array
    {
        $officeIds = array_values(array_unique(array_filter(array_map('intval', $officeIds))));
        if (empty($officeIds)) {
            return [];
        }

        $offices = TenantOffice::query()
            ->whereIn('id', $officeIds)
            ->get(['id', 'branch_name']);

        $map = [];
        foreach ($offices as $office) {
            $map[(int) $office->id] = [
                'id' => (int) $office->id,
                'branch_name' => (string) $office->branch_name,
            ];
        }

        return $map;
    }
}
