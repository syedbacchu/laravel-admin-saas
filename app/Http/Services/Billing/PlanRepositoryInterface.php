<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface PlanRepositoryInterface extends BaseRepositoryInterface
{
    public function planList(Request $request): array;
    public function createPlan(array $data): Model;
}
