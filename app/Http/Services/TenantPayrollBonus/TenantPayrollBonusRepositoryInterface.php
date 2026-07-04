<?php

namespace App\Http\Services\TenantPayrollBonus;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantPayrollBonus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantPayrollBonusRepositoryInterface extends BaseRepositoryInterface
{
    public function bonusList(Request $request): array;

    public function createBonus(array $data): Model;

    public function findBonus(int $id): ?TenantPayrollBonus;
}
