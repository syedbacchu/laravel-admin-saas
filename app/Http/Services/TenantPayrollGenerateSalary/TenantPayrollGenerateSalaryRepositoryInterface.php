<?php

namespace App\Http\Services\TenantPayrollGenerateSalary;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantPayrollGeneratedSalary;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantPayrollGenerateSalaryRepositoryInterface extends BaseRepositoryInterface
{
    public function generatedSalaryList(Request $request): array;

    public function createGeneratedSalary(array $data): Model;

    public function findGeneratedSalary(int $id): ?TenantPayrollGeneratedSalary;

    public function deleteSalarySheetRows(int $generatedSalaryId): int;

    public function insertSalarySheetRows(array $rows): bool;

    public function salarySheetRows(int $generatedSalaryId): EloquentCollection;
}
