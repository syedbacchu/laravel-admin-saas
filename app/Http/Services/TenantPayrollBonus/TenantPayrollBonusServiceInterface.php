<?php

namespace App\Http\Services\TenantPayrollBonus;

use App\Http\Requests\TenantApi\TenantPayrollBonusCreateRequest;
use Illuminate\Http\Request;

interface TenantPayrollBonusServiceInterface
{
    public function bonusList(Request $request): array;

    public function storeBonus(TenantPayrollBonusCreateRequest $request): array;

    public function bonusDetails(Request $request, int $id): array;

    public function deleteBonus(Request $request, int $id): array;
}
