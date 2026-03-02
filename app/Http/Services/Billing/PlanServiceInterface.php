<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\PlanCreateRequest;

interface PlanServiceInterface
{
    public function getDataTableData($request): array;
    public function planCreateData($request): array;
    public function planEditData($id): array;
    public function storeOrUpdatePlan(PlanCreateRequest $request): array;
    public function deletePlan($id): array;
}
